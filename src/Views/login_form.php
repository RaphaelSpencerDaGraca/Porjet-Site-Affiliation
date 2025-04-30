<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliagram - Connexion</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <header>
        <div class="logo">Affiliagram</div>
        <div class="header-right">
            <a href="connexion.php" class="profile-link">Profil</a>
            <div class="profile-icon">
                <i class="fas fa-user-circle fa-lg"></i>
            </div>
        </div>
    </header>

    <div class="zone-pub">
        <h2>Zone Pub</h2>
    </div>

    <div class="back-link">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="content-wrapper">
        <div class="main-area">
            <div class="form-container">
                <h1>Connexion</h1>

                <div class="alert info">
                    Connectez-vous pour accéder à votre compte et gérer vos liens d'affiliation.
                </div>

                <form action="#" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <div class="remember-password">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="mot_de_passe_oublie.php" class="forgot-password">Mot de passe oublié ?</a>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Se connecter</button>
                    </div>

                    <div class="form-footer">
                        <p>Vous n'avez pas de compte ? <a href="signin_form.php">Inscrivez-vous</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">CGU</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">Contact</a>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Affiliagram - Tous droits réservés
            </div>
        </div>
    </footer>
</div>
</body>
</html>