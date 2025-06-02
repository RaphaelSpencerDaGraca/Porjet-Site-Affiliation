// boost.js - Script pour la page de boost avec Stripe Elements et Docker

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation du paiement avec Stripe Elements...');

    // Configuration pour Docker
    const getCurrentPort = () => window.location.port;
    const isIDEServer = getCurrentPort() === '80';

    // Si on est sur le serveur IDE, utiliser Docker sur port 80
    const API_BASE_URL = isIDEServer ? 'http://localhost/Affiliagram/public/' : '';

    console.log('Configuration:', {
        currentPort: getCurrentPort(),
        isIDEServer: isIDEServer,
        apiBaseUrl: API_BASE_URL
    });

    // Récupérer les données depuis les attributs data
    const boostDataDiv = document.getElementById('boost-data');
    if (!boostDataDiv) {
        console.error('Élément boost-data non trouvé');
        return;
    }

    const stripeKey = boostDataDiv.getAttribute('data-stripe-key');
    const itemType = boostDataDiv.getAttribute('data-item-type');
    const itemId = boostDataDiv.getAttribute('data-item-id');

    console.log('Données boost:', {
        stripeKey: stripeKey ? 'OK' : 'Manquante',
        itemType: itemType,
        itemId: itemId
    });

    if (!stripeKey) {
        console.error('Clé Stripe manquante');
        return;
    }

    // Initialiser Stripe
    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();

    // Style pour Stripe Elements
    const style = {
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

    // Créer l'élément de carte
    const cardElement = elements.create('card', {
        style: style,
        hidePostalCode: true
    });

    // Monter l'élément dans le DOM
    cardElement.mount('#card-element');

    // Gérer les changements et erreurs
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
            displayError.style.display = 'block';
        } else {
            displayError.textContent = '';
            displayError.style.display = 'none';
        }
    });

    // Gérer la soumission du formulaire
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        console.log('=== Début du processus de paiement ===');

        const submitButton = document.getElementById('submit-payment');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        const errorDiv = document.getElementById('card-errors');

        // Désactiver le bouton
        submitButton.disabled = true;
        buttonText.style.display = 'none';
        spinner.classList.remove('hidden');

        try {
            // 1. Créer le PaymentIntent côté serveur
            console.log('Création du PaymentIntent...');
            console.log('URL appelée:', API_BASE_URL + 'index.php?controller=boost&action=createPaymentIntent');
            console.log('Données envoyées:', {
                item_type: itemType,
                item_id: itemId
            });

            const response = await fetch(API_BASE_URL + 'index.php?controller=boost&action=createPaymentIntent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                // Important: pour les requêtes cross-origin vers Docker
                mode: isIDEServer ? 'cors' : 'same-origin',
                credentials: 'include',
                body: JSON.stringify({
                    item_type: itemType,
                    item_id: itemId
                })
            });

            console.log('Réponse reçue - Status:', response.status);
            console.log('Réponse reçue - Headers:', Object.fromEntries(response.headers.entries()));

            // Capturer la réponse comme texte d'abord
            const responseText = await response.text();
            console.log('Réponse brute (100 premiers caractères):', responseText.substring(0, 100));

            // Vérifier si c'est une erreur HTML
            if (responseText.includes('<!DOCTYPE') || responseText.includes('<html') ||
                responseText.includes('502 Bad Gateway') || responseText.includes('404') ||
                responseText.includes('Fatal error') || responseText.includes('Warning')) {

                console.error('Erreur HTML/PHP reçue au lieu de JSON');

                // Afficher l'erreur
                const errorContainer = document.createElement('div');
                errorContainer.style.cssText = `
                    background: #fee; 
                    padding: 20px; 
                    margin: 20px; 
                    border: 2px solid #f00; 
                    position: fixed;
                    top: 20px;
                    left: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-height: 400px;
                    overflow-y: auto;
                `;
                errorContainer.innerHTML = `
                    <button onclick="this.parentElement.remove()" style="float: right; background: #f00; color: white; border: none; padding: 5px 10px; cursor: pointer;">✕</button>
                    <h3>Erreur serveur</h3>
                    <p>Le serveur n'a pas retourné du JSON. Vérifiez que vous accédez bien via Docker (port 80).</p>
                    <details>
                        <summary>Détails techniques</summary>
                        <pre>${responseText.substring(0, 1000)}</pre>
                    </details>
                `;
                document.body.appendChild(errorContainer);

                throw new Error('Erreur serveur - voir les détails ci-dessus');
            }

            // Parser le JSON
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Données JSON parsées:', data);
            } catch (parseError) {
                console.error('Erreur parsing JSON:', parseError);
                throw new Error('Réponse invalide du serveur');
            }

            // Vérifier les erreurs
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data.client_secret) {
                console.error('Réponse incomplète:', data);
                throw new Error('Client secret manquant');
            }

            // 2. Confirmer le paiement avec Stripe Elements
            console.log('Confirmation du paiement...');
            const {error, paymentIntent} = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: {
                    card: cardElement
                }
            });

            if (error) {
                // Afficher l'erreur Stripe
                console.error('Erreur Stripe:', error);

                // Messages d'erreur en français
                let errorMessage = error.message;
                const errorMessages = {
                    'card_declined': 'Votre carte a été refusée',
                    'expired_card': 'Votre carte a expiré',
                    'incorrect_cvc': 'Le code de sécurité est incorrect',
                    'processing_error': 'Erreur de traitement, veuillez réessayer',
                    'incorrect_number': 'Le numéro de carte est incorrect',
                    'insufficient_funds': 'Fonds insuffisants'
                };

                if (error.code && errorMessages[error.code]) {
                    errorMessage = errorMessages[error.code];
                }

                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            } else {
                // Paiement réussi
                console.log('✅ Paiement réussi:', paymentIntent);

                // Message de succès
                const successMessage = document.createElement('div');
                successMessage.style.cssText = `
                    background: #d4edda;
                    color: #155724;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    text-align: center;
                    font-weight: bold;
                    font-size: 18px;
                `;
                successMessage.innerHTML = `
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                    ✅ Paiement réussi !<br>
                    <small>Activation du boost en cours...</small>
                `;
                form.parentElement.insertBefore(successMessage, form);

                // Cacher le formulaire
                form.style.display = 'none';

                // Redirection avec le bon chemin
                const redirectUrl = isIDEServer
                    ? 'http://localhost/Affiliagram/public/index.php?controller=boost&action=success&payment_intent=' + paymentIntent.id
                    : 'index.php?controller=boost&action=success&payment_intent=' + paymentIntent.id;

                setTimeout(() => {
                    console.log('Redirection vers:', redirectUrl);
                    window.location.href = redirectUrl;
                }, 2000);
            }

        } catch (error) {
            console.error('💥 Erreur:', error);
            errorDiv.textContent = error.message || 'Une erreur est survenue';
            errorDiv.style.display = 'block';

            // Message d'aide supplémentaire si on est sur l'IDE
            if (isIDEServer && error.message.includes('fetch')) {
                errorDiv.innerHTML += '<br><small>Assurez-vous que Docker est démarré et accessible sur http://localhost/</small>';
            }
        } finally {
            // Réactiver le bouton
            submitButton.disabled = false;
            buttonText.style.display = 'inline-flex';
            spinner.classList.add('hidden');

            console.log('=== Fin du processus de paiement ===');
        }
    });

    console.log('✅ Stripe Elements initialisé avec succès');

    // Test de connexion au serveur Docker (en mode debug uniquement)
    if (isIDEServer) {
        console.log('Test de connexion à Docker...');
        fetch(API_BASE_URL + 'index.php')
            .then(r => {
                console.log('✅ Docker accessible');
            })
            .catch(e => {
                console.error('❌ Docker non accessible:', e);
                alert('Attention: Docker ne semble pas accessible. Vérifiez qu\'il est démarré.');
            });
    }
});