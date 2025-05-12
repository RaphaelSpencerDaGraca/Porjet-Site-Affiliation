<?php
// Page d'inscription modernisée
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Affiliagram</title>
    <link rel="stylesheet" href="../css/register.css">
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
            <h1>Créer un compte</h1>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="message success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="message error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
                <div class="message error">
                    <ul>
                        <?php foreach($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="index.php?controller=auth&action=register" method="post" id="register-form">
                <div class="form-group">
                    <label for="pseudo"><i class="fas fa-user"></i> Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" placeholder="Choisissez un pseudo unique" required value="<?php echo isset($_SESSION['form_data']['pseudo']) ? htmlspecialchars($_SESSION['form_data']['pseudo']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Minimum 8 caractères" required minlength="8">
                    <div class="password-strength">
                        <div class="password-meter">
                            <div class="password-meter-fill" id="password-strength-meter"></div>
                        </div>
                        <div class="strength-text" id="strength-text">Choisissez un mot de passe fort</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Retapez votre mot de passe" required>
                </div>

                <div class="form-group-terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">J'accepte les <a href="index.php?controller=page&action=terms" target="_blank">conditions générales d'utilisation</a> et la <a href="index.php?controller=page&action=privacy" target="_blank">politique de confidentialité</a>.</label>
                </div>

                <button type="submit" class="button-primary">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous avez déjà un compte ? <a href="index.php?controller=auth&action=login">Se connecter</a></p>
            </div>
        </div>

        <div class="auth-features">
            <h2>Rejoignez Affiliagram</h2>
            <p>Créez votre compte gratuitement et commencez à gérer vos liens d'affiliation comme un pro.</p>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="feature-text">
                    <h3>Inscription simple</h3>
                    <p>Créez votre compte en quelques secondes et commencez à utiliser Affiliagram immédiatement.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="feature-text">
                    <h3>Marques partenaires</h3>
                    <p>Accédez à des centaines de programmes d'affiliation des marques les plus populaires du marché.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="feature-text">
                    <h3>Maximisez vos revenus</h3>
                    <p>Optimisez vos gains grâce à nos outils d'analyse et nos conseils d'experts en marketing d'affiliation.</p>
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

<script src="../../../js/register.js">
</script>
</body>
</html>