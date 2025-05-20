<?php
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
    <script src="../js/boost.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

            <form action="index.php?controller=boost&action=processBoost" method="post">
                <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($itemType); ?>">
                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($itemId); ?>">

                <div class="payment-options">
                    <h3>Choisissez votre méthode de paiement</h3>

                    <div class="payment-method selected">
                        <input type="radio" id="card" name="payment_method" value="card" checked>
                        <label for="card">
                            <div class="icon"><i class="fas fa-credit-card"></i></div>
                            <div class="details">
                                <div class="name">Carte bancaire</div>
                                <div class="description">Paiement sécurisé par carte</div>
                            </div>
                        </label>
                    </div>

                    <div class="payment-method">
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

                <div class="action-buttons">
                    <a href="index.php?controller=user&action=profile" class="button button-secondary">Annuler</a>
                    <button type="submit" class="button button-primary">Payer et booster</button>
                </div>
            </form>
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