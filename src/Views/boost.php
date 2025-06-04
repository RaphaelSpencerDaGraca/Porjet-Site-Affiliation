<?php
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
    <script src="https://js.stripe.com/v3/"></script>
    <script src="../js/boost.js"></script>
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
    <!-- Données pour JavaScript (configuration sécurisée) -->
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
                            <!-- Stripe Elements sera inséré ici par le JavaScript -->
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
                            <span id="button-text">
                                <i class="fas fa-bolt"></i> Payer 1,00 €
                            </span>
                            <div id="spinner" class="spinner hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
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
