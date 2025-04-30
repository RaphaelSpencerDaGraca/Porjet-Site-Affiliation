<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliagram - Inscription</title>
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
                <h1>Inscription</h1>

                <div class="alert info">
                    Créez votre compte pour commencer à partager vos liens d'affiliation.
                </div>

                <form action="#" method="post">
                    <div class="form-group">
                        <label for="pseudo">Pseudo</label>
                        <input type="text" id="pseudo" name="pseudo" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">S'inscrire</button>
                    </div>

                    <div class="form-footer">
                        <p>Vous avez déjà un compte ? <a href="login_form.php">Connectez-vous</a></p>
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