<?php

/*id produit stripe = prod_SLYfJyKa1hJG6G*/
$itemType = isset($itemType) ? $itemType : '';
$itemId = isset($itemId) ? $itemId : '';
$itemName = isset($itemName) ? $itemName : '';
$brandName = isset($brandName) ? $brandName : '';
$activeBoostCount = isset($activeBoostCount) ? $activeBoostCount : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booster votre élément - Affiliagram</title>
    <link rel="stylesheet" href="../css/boost.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="../js/boost.js"></script>
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo">Affiliagram</div>
        <div class="user-icon">
            <?php if (isset($_SESSION['user']) && !empty($_SESSION['user']['profile_picture']) && file_exists($_SESSION['user']['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['user']['profile_picture']); ?>" alt="Photo de profil">
            <?php else: ?>
                <img src="../img/account.png" alt="Avatar par défaut">
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
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
                <div class="boost-info">
                    <span class="label">Durée du boost:</span>
                    <span class="value">7 jours</span>
                </div>
                <div class="boost-info">
                    <span class="label">Prix:</span>
                    <span class="value">1,00 €</span>
                </div>
            </div>

            <div class="payment-options">
                <h3>Choisissez votre méthode de paiement</h3>

                <div class="payment-method selected" id="card-method">
                    <input type="radio" id="card" name="payment_method" value="card" checked>
                    <label for="card">
                        <div class="icon"><i class="fas fa-credit-card"></i></div>
                        <div class="details">
                            <div class="name">Carte bancaire</div>
                            <div class="description">Paiement sécurisé par carte</div>
                        </div>
                    </label>
                </div>

                <div class="payment-method" id="paypal-method">
                    <input type="radio" id="paypal" name="payment_method" value="paypal">
                    <label for="paypal">
                        <div class="icon"><i class="fab fa-paypal"></i></div>
                        <div class="details">
                            <div class="name">PayPal</div>
                            <div class="description">Paiement via votre compte PayPal</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Formulaire de paiement par carte -->
            <div id="card-payment-form" class="payment-form">
                <form id="payment-form">
                    <div class="card-form-header">
                        <h4><i class="fas fa-credit-card"></i> Informations de la carte</h4>
                    </div>

                    <div class="card-form-grid">
                        <div class="form-group full-width">
                            <label for="card-number">Numéro de carte</label>
                            <div class="card-input-container">
                                <input
                                        type="text"
                                        id="card-number"
                                        name="card-number"
                                        placeholder="1234 5678 9012 3456"
                                        maxlength="19"
                                        required
                                >
                                <div class="card-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="card-expiry">Date d'expiration</label>
                            <input
                                    type="text"
                                    id="card-expiry"
                                    name="card-expiry"
                                    placeholder="MM/AA"
                                    maxlength="5"
                                    required
                            >
                        </div>

                        <div class="form-group">
                            <label for="card-cvc">
                                CVV
                                <span class="tooltip">
                                    <i class="fas fa-question-circle"></i>
                                    <span class="tooltip-text">Code à 3 chiffres au dos de votre carte</span>
                                </span>
                            </label>
                            <input
                                    type="text"
                                    id="card-cvc"
                                    name="card-cvc"
                                    placeholder="123"
                                    maxlength="4"
                                    required
                            >
                        </div>

                        <div class="form-group full-width">
                            <label for="cardholder-name">Nom du porteur</label>
                            <input
                                    type="text"
                                    id="cardholder-name"
                                    name="cardholder-name"
                                    placeholder="Nom comme inscrit sur la carte"
                                    required
                            >
                        </div>
                    </div>

                    <div class="security-info">
                        <div class="security-badge">
                            <i class="fas fa-lock"></i>
                            <span>Paiement 100% sécurisé avec chiffrement SSL</span>
                        </div>
                    </div>

                    <div id="card-errors" class="error-message"></div>

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

            <!-- Formulaire PayPal (caché par défaut) -->
            <div id="paypal-payment-form" class="payment-form" style="display: none;">
                <form action="index.php?controller=boost&action=processBoost" method="post">
                    <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($itemType); ?>">
                    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($itemId); ?>">
                    <input type="hidden" name="payment_method" value="paypal">

                    <div class="paypal-info">
                        <div class="paypal-logo">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <p>Vous serez redirigé vers PayPal pour finaliser votre paiement</p>
                    </div>

                    <div class="action-buttons">
                        <a href="index.php?controller=user&action=profile" class="button button-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="button button-paypal">
                            <i class="fab fa-paypal"></i> Payer avec PayPal
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



</body>
</html>