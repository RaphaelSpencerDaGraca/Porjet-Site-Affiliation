<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliagram - Mes offres d'affiliation</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
        <a href="#" class="logo">Affiliagram</a>
        <div class="header-right">
            <div class="profile-icon"></div>
        </div>
    </header>

    <div class="zone-pub">Zone Pub</div>

    <div class="content-wrapper">
        <div class="main-area">
            <div class="back-link">
                <a href="#">Retour</a>
            </div>

            <main>
                <h1>Mes offres d'affiliation</h1>

                <div class="offres-liste">
                    <!-- Offre 1 -->
                    <div class="offre-card">
                        <div class="offre-info">
                            <div class="offre-details">
                                <div class="site-name">Nom du site ou de l'application</div>
                                <div class="code-link">Code ou lien</div>
                                <div class="consultations">X consultations</div>
                            </div>
                            <button class="delete-btn">Supprimer</button>
                        </div>
                    </div>

                    <!-- Offre 2 -->
                    <div class="offre-card">
                        <div class="offre-info">
                            <div class="offre-details">
                                <div class="site-name">Nom du site ou de l'application</div>
                                <div class="code-link">Code ou lien</div>
                                <div class="consultations">X consultations</div>
                            </div>
                            <button class="delete-btn">Supprimer</button>
                        </div>
                    </div>

                    <!-- Offre 3 -->
                    <div class="offre-card">
                        <div class="offre-info">
                            <div class="offre-details">
                                <div class="site-name">Nom du site ou de l'application</div>
                                <div class="code-link">Code ou lien</div>
                                <div class="consultations">X consultations</div>
                            </div>
                            <button class="delete-btn">Supprimer</button>
                        </div>
                    </div>

                    <!-- Offre 4 -->
                    <div class="offre-card">
                        <div class="offre-info">
                            <div class="offre-details">
                                <div class="site-name">Nom du site ou de l'application</div>
                                <div class="code-link">Code ou lien</div>
                                <div class="consultations">X consultations</div>
                            </div>
                            <button class="delete-btn">Supprimer</button>
                        </div>
                    </div>
                </div>

                <div class="add-btn-container">
                    <button class="add-btn">
                        <span>+</span> Ajouter une offre d'affiliation
                    </button>
                </div>
            </main>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">À propos</a>
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">Contact</a>
            </div>
            <div class="copyright">
                &copy; 2025 Affiliagram. Tous droits réservés.
            </div>
        </div>
    </footer>
</div>

<!-- Modal pour ajouter une nouvelle offre (structure HTML uniquement) -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Ajouter une offre d'affiliation</h2>
            <button class="close-btn">&times;</button>
        </div>
        <form id="addOfferForm">
            <div class="form-group">
                <label for="siteName">Nom du site ou de l'application</label>
                <input type="text" id="siteName" required>
            </div>
            <div class="form-group">
                <label for="codeLink">Code ou lien</label>
                <input type="text" id="codeLink" required>
            </div>
            <button type="submit" class="btn-primary">Ajouter</button>
        </form>
    </div>
</div>
</body>
</html>