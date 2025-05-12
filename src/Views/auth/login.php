<?php
// Page de connexion modernisée
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Affiliagram</title>
    <link rel="stylesheet" href="../css/login.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo">Affiliagram</div>
    </div>
</header>

<div class="zone-pub">
    Zone Pub
</div>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Connexion</h1>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="message success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="message error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="index.php?controller=auth&action=login" method="post">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>

                <div class="forgot-password-link">
                    <a href="index.php?controller=user&action=forgotPassword">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="button-primary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="index.php?controller=auth&action=register">S'inscrire</a></p>
            </div>
        </div>

        <div class="auth-features">
            <h2>Bienvenue sur Affiliagram</h2>
            <p>La plateforme qui vous permet de gérer et partager facilement vos liens d'affiliation.</p>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="feature-text">
                    <h3>Gérez vos liens</h3>
                    <p>Centralisez tous vos liens d'affiliation au même endroit et accédez-y facilement depuis n'importe quel appareil.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-text">
                    <h3>Suivez vos performances</h3>
                    <p>Consultez les statistiques détaillées de vos liens et optimisez vos revenus grâce à nos outils d'analyse.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-share-alt"></i>
                </div>
                <div class="feature-text">
                    <h3>Partagez facilement</h3>
                    <p>Partagez vos liens sur toutes vos plateformes préférées en un seul clic et augmentez votre portée.</p>
                </div>
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

<script src="../../../js/auth.js"></script>
</body>
</html>