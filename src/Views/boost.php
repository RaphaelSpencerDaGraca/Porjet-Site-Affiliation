<?php
// Récupérer les variables depuis le contrôleur
$itemType = isset($itemType) ? $itemType : '';
$itemId = isset($itemId) ? $itemId : '';
$itemName = isset($itemName) ? $itemName : '';
$brandName = isset($brandName) ? $brandName : '';
$activeBoostCount = isset($activeBoostCount) ? $activeBoostCount : 0;
$user = isset($user) ? $user : $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booster votre élément - Affiliagram</title>
    <link rel="stylesheet" href="../css/boost.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/boost.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo">Affiliagram</div>
        <div class="user-icon">
            <?php if (isset($user) && !empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil">
            <?php else: ?>
                <img src="../img/account.png" alt="Avatar par défaut">
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <!-- Données pour JavaScript -->
    <div id="boost-data"
         data-stripe-key="<?php echo htmlspecialchars(STRIPE_PUBLISHABLE_KEY); ?>"
         data-item-type="<?php echo htmlspecialchars($itemType); ?>"
         data-item-id="<?php echo htmlspecialchars($itemId); ?>"
         style="display: none;">
    </div>

    <nav>
        <ul class="breadcrumb">
            <li><a href="index.php">Accueil</a></li>
            <li>›</li>
            <li><a href="index.php?controller=user&action=profile">Mon profil</a></li>
            <li>›</li>
            <li>Booster un élément</li>
        </ul>
    </nav>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="message error"><?php echo $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="boost-form-container">
        <div class="boost-form">
            <h1>Booster votre élément</h1>

            <div class="boost-details">
                <h3>Détails du boost</h3>
                <div class="boost-info">
                    <span class="label">Élément à booster:</span>
                    <span class="value"><?php echo htmlspecialchars($itemName); ?></span>
                </div>
                <?php if (!empty($brandName)): ?>
                    <div class="boost-info">
                        <span class="label">Marque:</span>
                        <span class="value"><?php echo htmlspecialchars($brandName); ?></span>
                    </div>
                <?php endif; ?>
                <div class="boost-info">
                    <span class="label">Durée du boost:</span>
                    <span class="value">7 jours</span>
                </div>
                <div class="boost-info">
                    <span class="label">Prix:</span>
                    <span class="value">1,00 €</span>
                </div>
            </div>

            <!-- Formulaire de paiement Stripe Elements -->
            <div id="card-payment-form" class="payment-form">
                <form id="payment-form">
                    <div class="card-form-header">
                        <h4><i class="fas fa-credit-card"></i> Informations de paiement</h4>
                    </div>

                    <div class="form-group">
                        <label for="card-element">Carte bancaire</label>
                        <div id="card-element" class="stripe-element">
                            <!-- Stripe Elements sera inséré ici -->
                        </div>
                        <div id="card-errors" class="error-message" role="alert"></div>
                    </div>

                    <div class="security-info">
                        <div class="security-badge">
                            <i class="fas fa-lock"></i>
                            <span>Paiement 100% sécurisé avec chiffrement SSL</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="index.php?controller=user&action=profile" class="button button-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" id="submit-payment" class="button button-primary">
                            <span id="button-text"><i class="fas fa-bolt"></i> Payer 1,00 €</span>
                            <div id="spinner" class="spinner hidden"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-links">
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Nous contacter</a>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Affiliagram - Tous droits réservés
        </div>
    </div>
</footer>

<script>
    // Configuration Stripe avec Elements (méthode sécurisée)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initialisation du paiement avec Stripe Elements...');

        // Récupérer la clé publique Stripe
        const boostData = document.getElementById('boost-data');
        const stripeKey = boostData.getAttribute('data-stripe-key');
        const itemType = boostData.getAttribute('data-item-type');
        const itemId = boostData.getAttribute('data-item-id');

        if (!stripeKey) {
            console.error('Clé Stripe manquante');
            return;
        }

        // Initialiser Stripe
        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();

        // Créer l'élément de carte
        const cardElement = elements.create('card', {
            style: {
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
            }
        });

        // Monter l'élément dans le DOM
        cardElement.mount('#card-element');

        // Gérer les erreurs en temps réel
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Gérer la soumission du formulaire
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const submitButton = document.getElementById('submit-payment');
            const buttonText = document.getElementById('button-text');
            const spinner = document.getElementById('spinner');
            const errorDiv = document.getElementById('card-errors');

            // Désactiver le bouton
            submitButton.disabled = true;
            buttonText.style.display = 'none';
            spinner.classList.remove('hidden');

            try {
                // Créer d'abord un PaymentIntent côté serveur
                console.log('Création du PaymentIntent...');
                const response = await fetch('index.php?controller=boost&action=createPaymentIntent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        item_type: itemType,
                        item_id: itemId
                    })
                });

                const data = await response.json();
                console.log('Réponse serveur:', data);

                if (data.error) {
                    throw new Error(data.error);
                }

                // Confirmer le paiement avec Stripe Elements
                console.log('Confirmation du paiement...');
                const result = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement
                    }
                });

                if (result.error) {
                    // Afficher l'erreur
                    errorDiv.textContent = result.error.message;
                } else {
                    // Paiement réussi
                    console.log('Paiement réussi:', result.paymentIntent);
                    window.location.href = 'index.php?controller=boost&action=success&payment_intent=' + result.paymentIntent.id;
                }

            } catch (error) {
                console.error('Erreur:', error);
                errorDiv.textContent = error.message || 'Une erreur est survenue';
            } finally {
                // Réactiver le bouton
                submitButton.disabled = false;
                buttonText.style.display = 'inline-flex';
                spinner.classList.add('hidden');
            }
        });
    });
</script>

</body>
</html>