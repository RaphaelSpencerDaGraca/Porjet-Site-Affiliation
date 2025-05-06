<?php
// Page de connexion
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Affiliagram</title>
    <link rel="stylesheet" href="../../../css/auth.css">
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
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="forgot-password-link">
                    <a href="index.php?controller=user&action=forgotPassword">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="button-primary">Se connecter</button>
            </form>

            <div class="auth-footer">
                <p>Vous n'avez pas de compte ? <a href="index.php?controller=auth&action=register">S'inscrire</a></p>
            </div>
        </div>

        <div class="auth-features">
            <h2>Bienvenue sur Affiliagram</h2>
            <p>La plateforme qui vous permet de gérer et partager facilement vos liens d'affiliation.</p>

            <div class="feature">
                <div class="feature-icon">🔗</div>
                <div class="feature-text">
                    <h3>Gérez vos liens</h3>
                    <p>Centralisez tous vos liens d'affiliation au même endroit.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-text">
                    <h3>Suivez vos performances</h3>
                    <p>Consultez les statistiques de vos liens et optimisez vos revenus.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">🚀</div>
                <div class="feature-text">
                    <h3>Partagez facilement</h3>
                    <p>Partagez vos liens sur toutes vos plateformes préférées en un clic.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/auth.js"></script>
</body>
</html>