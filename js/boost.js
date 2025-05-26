// Configuration Stripe
const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
const elements = stripe.elements();

// Formatage automatique des champs
document.getElementById('card-number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;

    if (formattedValue.length > 19) {
        formattedValue = formattedValue.substr(0, 19);
    }

    e.target.value = formattedValue;

    // Détection du type de carte
    detectCardType(value);
});

document.getElementById('card-expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substr(0, 2) + '/' + value.substr(2, 2);
    }
    e.target.value = value;
});

document.getElementById('card-cvc').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});

// Détection du type de carte
function detectCardType(number) {
    const cardIcons = document.querySelectorAll('.card-icons i');
    cardIcons.forEach(icon => icon.classList.remove('active'));

    if (number.startsWith('4')) {
        document.querySelector('.fa-cc-visa').classList.add('active');
    } else if (number.startsWith('5') || number.startsWith('2')) {
        document.querySelector('.fa-cc-mastercard').classList.add('active');
    } else if (number.startsWith('34') || number.startsWith('37')) {
        document.querySelector('.fa-cc-amex').classList.add('active');
    }
}

// Gérer le changement de méthode de paiement
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const cardForm = document.getElementById('card-payment-form');
        const paypalForm = document.getElementById('paypal-payment-form');

        if (this.value === 'card') {
            cardForm.style.display = 'block';
            paypalForm.style.display = 'none';
            document.getElementById('card-method').classList.add('selected');
            document.getElementById('paypal-method').classList.remove('selected');
        } else {
            cardForm.style.display = 'none';
            paypalForm.style.display = 'block';
            document.getElementById('card-method').classList.remove('selected');
            document.getElementById('paypal-method').classList.add('selected');
        }
    });
});

// Validation du formulaire
function validateCardForm() {
    const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
    const cardExpiry = document.getElementById('card-expiry').value;
    const cardCvc = document.getElementById('card-cvc').value;
    const cardholderName = document.getElementById('cardholder-name').value;

    let errors = [];

    // Validation numéro de carte (algorithme de Luhn simplifié)
    if (cardNumber.length < 13 || cardNumber.length > 19) {
        errors.push('Numéro de carte invalide');
    }

    // Validation date d'expiration
    if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
        errors.push('Date d\'expiration invalide (MM/AA)');
    } else {
        const [month, year] = cardExpiry.split('/');
        const currentDate = new Date();
        const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);

        if (expiryDate < currentDate || parseInt(month) < 1 || parseInt(month) > 12) {
            errors.push('Date d\'expiration invalide');
        }
    }

    // Validation CVV
    if (cardCvc.length < 3 || cardCvc.length > 4) {
        errors.push('CVV invalide');
    }

    // Validation nom
    if (cardholderName.trim().length < 2) {
        errors.push('Nom du porteur requis');
    }

    return errors;
}

// Gérer la soumission du formulaire
document.getElementById('payment-form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const errors = validateCardForm();
    const errorDiv = document.getElementById('card-errors');

    if (errors.length > 0) {
        errorDiv.innerHTML = errors.join('<br>');
        return;
    }

    errorDiv.innerHTML = '';

    const submitButton = document.getElementById('submit-payment');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    // Désactiver le bouton et afficher le spinner
    submitButton.disabled = true;
    buttonText.style.display = 'none';
    spinner.classList.remove('hidden');

    try {
        // Récupérer les données de la carte
        const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
        const cardExpiry = document.getElementById('card-expiry').value;
        const cardCvc = document.getElementById('card-cvc').value;
        const cardholderName = document.getElementById('cardholder-name').value;

        // Séparer mois et année
        const [expMonth, expYear] = cardExpiry.split('/');

        // Créer un Intent de paiement avec les données de carte
        const response = await fetch('index.php?controller=boost&action=createPaymentIntent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                item_type: '<?php echo $itemType; ?>',
                item_id: '<?php echo $itemId; ?>',
                card_data: {
                    number: cardNumber,
                    exp_month: parseInt(expMonth),
                    exp_year: parseInt('20' + expYear),
                    cvc: cardCvc,
                    name: cardholderName
                }
            })
        });

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        // Confirmer le paiement avec Stripe
        const result = await stripe.confirmCardPayment(data.client_secret, {
            payment_method: {
                card: {
                    number: cardNumber,
                    exp_month: parseInt(expMonth),
                    exp_year: parseInt('20' + expYear),
                    cvc: cardCvc,
                },
                billing_details: {
                    name: cardholderName,
                }
            }
        });

        if (result.error) {
            // Afficher l'erreur
            errorDiv.textContent = result.error.message;
        } else {
            // Paiement réussi
            window.location.href = 'index.php?controller=boost&action=success&payment_intent=' + result.paymentIntent.id;
        }

    } catch (error) {
        errorDiv.textContent = error.message;
    } finally {
        submitButton.disabled = false;
        buttonText.style.display = 'inline-flex';
        spinner.classList.add('hidden');
    }
});