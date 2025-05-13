<?php
// Page d'accueil d'Affiliagram
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliagram - Gérez vos liens d'affiliation facilement</title>
    <link rel="stylesheet" href="../css/home.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo">Affiliagram</div>
    </div>
</header>

<div class="hero-section">
    <div class="hero-content">
        <h1>Simplifiez votre marketing d'affiliation</h1>
        <p class="hero-subtitle">La plateforme tout-en-un pour gérer, suivre et optimiser vos liens d'affiliation</p>

        <div class="hero-buttons">
            <a href="index.php?controller=auth&action=login" class="button button-login">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </a>
            <a href="index.php?controller=auth&action=register" class="button button-register">
                <i class="fas fa-user-plus"></i> S'inscrire
            </a>
        </div>
    </div>

    <div class="hero-image">
        <div class="dashboard-preview">
            <div class="preview-header">
                <div class="preview-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="preview-title">Tableau de bord</div>
            </div>
            <div class="preview-content">
                <div class="preview-stats">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-link"></i></div>
                        <div class="stat-info">
                            <div class="stat-value">28</div>
                            <div class="stat-label">Liens actifs</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <div class="stat-info">
                            <div class="stat-value">1.2k</div>
                            <div class="stat-label">Vues</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
                        <div class="stat-info">
                            <div class="stat-value">345€</div>
                            <div class="stat-label">Revenus</div>
                        </div>
                    </div>
                </div>
                <div class="preview-chart">
                    <div class="chart-bars">
                        <div class="chart-bar" style="height: 40%;"></div>
                        <div class="chart-bar" style="height: 70%;"></div>
                        <div class="chart-bar" style="height: 50%;"></div>
                        <div class="chart-bar" style="height: 80%;"></div>
                        <div class="chart-bar" style="height: 65%;"></div>
                        <div class="chart-bar" style="height: 90%;"></div>
                        <div class="chart-bar" style="height: 75%;"></div>
                    </div>
                </div>
                <div class="preview-links">
                    <div class="preview-link">
                        <div class="preview-link-icon"><i class="fas fa-tag"></i></div>
                        <div class="preview-link-text">Amazon</div>
                        <div class="preview-link-stats">450 vues</div>
                    </div>
                    <div class="preview-link">
                        <div class="preview-link-icon"><i class="fas fa-tag"></i></div>
                        <div class="preview-link-text">AliExpress</div>
                        <div class="preview-link-stats">327 vues</div>
                    </div>
                    <div class="preview-link">
                        <div class="preview-link-icon"><i class="fas fa-tag"></i></div>
                        <div class="preview-link-text">Booking</div>
                        <div class="preview-link-stats">214 vues</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="features-section">
    <div class="feature-card">
        <div class="feature-icon">
            <i class="fas fa-link"></i>
        </div>
        <h3>Gestion centralisée</h3>
        <p>Stockez tous vos liens d'affiliation au même endroit et accédez-y depuis n'importe quel appareil.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h3>Statistiques détaillées</h3>
        <p>Suivez les performances de vos liens et analysez vos revenus en temps réel.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <i class="fas fa-share-alt"></i>
        </div>
        <h3>Partage facile</h3>
        <p>Partagez vos liens rapidement sur toutes vos plateformes préférées en un seul clic.</p>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-links">
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">À propos</a>
            <a href="#">Nous contacter</a>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Affiliagram - Tous droits réservés
        </div>
    </div>
</footer>
</body>
</html>