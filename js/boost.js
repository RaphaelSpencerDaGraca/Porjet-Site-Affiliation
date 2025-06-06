document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Initialisation Stripe Elements ===');

    // Récupérer les données depuis les attributs data
    const boostDataDiv = document.getElementById('boost-data');
    if (!boostDataDiv) {
        console.error('❌ Élément boost-data non trouvé');
        return;
    }

    const stripeKey = boostDataDiv.getAttribute('data-stripe-key');
    const itemType = boostDataDiv.getAttribute('data-item-type');
    const itemId = boostDataDiv.getAttribute('data-item-id');

    console.log('Données récupérées:', {
        stripeKey: stripeKey ? '✅ Présente' : '❌ Manquante',
        itemType: itemType,
        itemId: itemId
    });

    if (!stripeKey) {
        console.error('❌ Clé Stripe manquante');
        showError('Configuration Stripe manquante');
        return;
    }

    // Vérifier que l'élément card existe
    const cardElementContainer = document.getElementById('card-element');
    if (!cardElementContainer) {
        console.error('❌ Élément #card-element non trouvé');
        return;
    }

    try {
        initializeStripePayment(stripeKey, itemType, itemId);
    } catch (error) {
        console.error('💥 Erreur lors de l\'initialisation:', error);
        showError('Erreur d\'initialisation du paiement');
    }
});

/**
 * Initialise le système de paiement Stripe
 */
function initializeStripePayment(stripeKey, itemType, itemId) {
    console.log('Initialisation de Stripe...');

    // Initialiser Stripe
    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();

    // Configuration du style
    const elementStyle = {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    console.log('Création de l\'élément carte...');
    const cardElement = elements.create('card', {
        style: elementStyle,
        hidePostalCode: true
    });

    console.log('Montage de l\'élément carte...');
    cardElement.mount('#card-element');
    console.log('✅ Élément carte monté avec succès');

    // Configurer les gestionnaires d'événements
    setupEventHandlers(stripe, cardElement, itemType, itemId);
}

/**
 * Configure tous les gestionnaires d'événements
 */
function setupEventHandlers(stripe, cardElement, itemType, itemId) {
    // Gérer les changements de la carte en temps réel
    cardElement.on('change', handleCardChange);

    // Gérer la soumission du formulaire
    const form = document.getElementById('payment-form');
    if (!form) {
        console.error('❌ Formulaire #payment-form non trouvé');
        return;
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        handlePaymentSubmission(stripe, cardElement, itemType, itemId);
    });

    console.log('✅ Gestionnaires d\'événements configurés');
}

/**
 * Gère les changements de l'élément carte
 */
function handleCardChange(event) {
    const errorDiv = document.getElementById('card-errors');
    if (!errorDiv) return;

    if (event.error) {
        console.log('Erreur carte:', event.error.message);
        errorDiv.textContent = event.error.message;
        errorDiv.style.display = 'block';
    } else {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
    }
}

/**
 * Gère la soumission du formulaire de paiement
 */
async function handlePaymentSubmission(stripe, cardElement, itemType, itemId) {
    console.log('=== Début du processus de paiement ===');

    // Gérer l'état de l'interface
    const uiElements = setLoadingState(true);

    try {
        // 1. Créer le PaymentIntent côté serveur
        const paymentIntentData = await createPaymentIntent(itemType, itemId);

        // 2. Confirmer le paiement avec Stripe
        const paymentResult = await confirmPayment(stripe, cardElement, paymentIntentData.client_secret);

        if (paymentResult.error) {
            handlePaymentError(paymentResult.error);
        } else {
            handlePaymentSuccess(paymentResult.paymentIntent);
        }

    } catch (error) {
        console.error('💥 Erreur lors du paiement:', error);
        showError(error.message || 'Une erreur est survenue lors du paiement');
    } finally {
        setLoadingState(false, uiElements);
        console.log('=== Fin du processus de paiement ===');
    }
}

/**
 * Crée un PaymentIntent côté serveur
 */
async function createPaymentIntent(itemType, itemId) {
    console.log('Création du PaymentIntent...');

    const response = await fetch('index.php?controller=boost&action=createPaymentIntent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            item_type: itemType,
            item_id: itemId
        })
    });

    console.log('Status de la réponse:', response.status);

    if (!response.ok) {
        throw new Error(`Erreur serveur: ${response.status}`);
    }

    const data = await response.json();
    console.log('Réponse du serveur:', data);

    if (data.error) {
        throw new Error(data.error);
    }

    if (!data.client_secret) {
        throw new Error('Client secret manquant dans la réponse');
    }

    return data;
}

/**
 * Confirme le paiement avec Stripe
 */
async function confirmPayment(stripe, cardElement, clientSecret) {
    console.log('Confirmation du paiement avec Stripe...');

    return await stripe.confirmCardPayment(clientSecret, {
        payment_method: {
            card: cardElement
        }
    });
}

/**
 * Gère les erreurs de paiement
 */
function handlePaymentError(error) {
    console.error('Erreur Stripe:', error);

    // Messages d'erreur traduits
    const errorMessages = {
        'card_declined': 'Votre carte a été refusée',
        'expired_card': 'Votre carte a expiré',
        'incorrect_cvc': 'Le code de sécurité est incorrect',
        'processing_error': 'Erreur de traitement, veuillez réessayer',
        'incorrect_number': 'Le numéro de carte est incorrect',
        'insufficient_funds': 'Fonds insuffisants',
        'invalid_expiry_month': 'Mois d\'expiration invalide',
        'invalid_expiry_year': 'Année d\'expiration invalide'
    };

    const errorMessage = errorMessages[error.code] || error.message;
    showError(errorMessage);
}

/**
 * Gère le succès du paiement
 */
function handlePaymentSuccess(paymentIntent) {
    console.log('✅ Paiement confirmé:', paymentIntent);

    // Afficher un message de succès
    showSuccessMessage();

    // Redirection vers la page de succès
    setTimeout(() => {
        const redirectUrl = 'index.php?controller=boost&action=success&payment_intent=' + paymentIntent.id;
        console.log('Redirection vers:', redirectUrl);
        window.location.href = redirectUrl;
    }, 2000);
}

/**
 * Affiche un message de succès
 */
function showSuccessMessage() {
    const form = document.getElementById('payment-form');
    if (!form) return;

    const successDiv = document.createElement('div');
    successDiv.style.cssText = `
        background: #d4edda;
        color: #155724;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: center;
        font-weight: bold;
        border: 1px solid #c3e6cb;
    `;
    successDiv.innerHTML = `
        <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
        ✅ Paiement réussi ! Veuillez attendre que la facture se télécharge automatiquement sur votre appareil.<br>
        <button id="back-to-profile" style="
            background-color: #155724;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        ">
            Retour au profil
        </button>
    `;

    form.parentElement.insertBefore(successDiv, form);
    form.style.display = 'none';

    document.getElementById('back-to-profile').addEventListener('click', function() {
        window.location.href = 'index.php?controller=user&action=profile';
    });
}

/**
 * Affiche un message d'erreur
 */
function showError(message) {
    const errorDiv = document.getElementById('card-errors');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

/**
 * Gère l'état de chargement de l'interface
 */
function setLoadingState(isLoading, elements = null) {
    const submitButton = document.getElementById('submit-payment');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    if (!elements) {
        elements = { submitButton, buttonText, spinner };
    }

    if (isLoading) {
        if (elements.submitButton) elements.submitButton.disabled = true;
        if (elements.buttonText) elements.buttonText.style.display = 'none';
        if (elements.spinner) elements.spinner.classList.remove('hidden');
    } else {
        if (elements.submitButton) elements.submitButton.disabled = false;
        if (elements.buttonText) elements.buttonText.style.display = 'inline-flex';
        if (elements.spinner) elements.spinner.classList.add('hidden');
    }

    return elements;
}
