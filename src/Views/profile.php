<?php
// Ce fichier est la vue pour la page de profil utilisateur
// Il sera inclus par la méthode profile() du UserController
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Affiliagram</title>
    <link rel="stylesheet" href="../css/profil.css">

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

<div class="zone-pub">
    Zone Pub
</div>

<div class="information">
    <p>Postez un maximum de liens et codes de parrainage pour être mis en avant!</p>
    <button onclick="window.location.href='index.php?controller=brand&action=searchSite';" class="button-primary"><strong>Je découvre les liens</strong></button>
</div>

<div class="container">
    <nav>
        <ul class="breadcrumb">
            <li><a href="index.php">Accueil</a></li>
            <li>›</li>
            <li>Mon profil</li>
        </ul>
    </nav>

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

    <!-- Formulaire d'édition de code - Apparaît uniquement lorsqu'on édite un code -->
    <?php if (isset($codeToEdit) && $codeToEdit): ?>
        <div id="edit-code-modal" class="affiliate-form-container" style="display: block;">
            <div class="affiliate-form">
                <h3>Modifier le code de parrainage</h3>

                <form action="index.php?controller=affiliateCode&action=update&id=<?php echo $codeToEdit['id']; ?>" method="post">
                    <!-- Champ caché pour passer l'ID du code -->
                    <input type="hidden" name="code_id" value="<?php echo $codeToEdit['id']; ?>">

                    <div class="form-group">
                        <label for="brand_edit">Marque</label>
                        <input type="text" id="brand_edit" value="<?php echo isset($brandForCode) && isset($brandForCode['name']) ? htmlspecialchars($brandForCode['name']) : 'Marque inconnue'; ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="code_edit">Code promo</label>
                        <input type="text" id="code_edit" name="code" value="<?php echo htmlspecialchars($codeToEdit['code']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date_edit">Date d'expiration (optionnel)</label>
                        <input type="date" id="expiry_date_edit" name="expiry_date" value="<?php echo !empty($codeToEdit['expiry_date']) ? date('Y-m-d', strtotime($codeToEdit['expiry_date'])) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo isset($codeToEdit['is_active']) && $codeToEdit['is_active'] ? 'checked' : ''; ?>>
                            Code actif
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="index.php?controller=user&action=profile" class="button cancel-btn">Annuler</a>
                        <button type="submit" class="button-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <!-- Formulaire d'édition de lien - Apparaît uniquement lorsqu'on édite un lien -->
    <?php if (isset($linkToEdit) && $linkToEdit): ?>
        <div id="edit-link-modal" class="affiliate-form-container" style="display: block;">
            <div class="affiliate-form">
                <h3>Modifier le lien de parrainage</h3>

                <form action="index.php?controller=affiliateLink&action=update&id=<?php echo $linkToEdit['id']; ?>" method="post">
                    <!-- Champ caché pour passer l'ID du lien -->
                    <input type="hidden" name="link_id" value="<?php echo $linkToEdit['id']; ?>">

                    <div class="form-group">
                        <label for="brand_edit_link">Marque</label>
                        <input type="text" id="brand_edit_link" value="<?php echo isset($brandForLink) && isset($brandForLink['name']) ? htmlspecialchars($brandForLink['name']) : 'Marque inconnue'; ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="custom_link_edit">Lien de parrainage</label>
                        <input type="url" id="custom_link_edit" name="custom_link" value="<?php echo htmlspecialchars($linkToEdit['custom_link']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date_edit_link">Date d'expiration (optionnel)</label>
                        <input type="date" id="expiry_date_edit_link" name="expiry_date" value="<?php echo !empty($linkToEdit['expiry_date']) ? date('Y-m-d', strtotime($linkToEdit['expiry_date'])) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo isset($linkToEdit['is_active']) && $linkToEdit['is_active'] ? 'checked' : ''; ?>>
                            Lien actif
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="index.php?controller=user&action=profile" class="button cancel-btn">Annuler</a>
                        <button type="submit" class="button-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="main-content">
        <div class="profile-section">
            <h1>Mon profil</h1>

            <div class="profile-picture">
                <?php if (isset($user) && !empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil">
                <?php else: ?>
                    <img src="../img/account.png" alt="Avatar par défaut">
                <?php endif; ?>
                <div class="profile-picture-overlay" onclick="document.getElementById('profile-picture-upload').click();">
                    <span>Changer</span>
                </div>
            </div>

            <form action="index.php?controller=user&action=editProfilePicture" method="post" enctype="multipart/form-data" id="profile-picture-form">
                <input type="file" name="profile_picture" id="profile-picture-upload" class="hidden" accept="image/jpeg,image/png,image/gif" onchange="document.getElementById('profile-picture-form').submit();">
            </form>

            <form action="index.php?controller=user&action=updateProfile" method="post">
                <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?php echo isset($user) && isset($user['pseudo']) ? htmlspecialchars($user['pseudo']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($user) && isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_password">Mot de passe actuel (requis pour les modifications)</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Entrez votre mot de passe actuel">
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 caractères">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre nouveau mot de passe">
                </div>

                <button type="submit" class="button-primary">Mettre à jour mon profil</button>
            </form>
        </div>

        <div class="affiliate-links-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Mes liens de parrainage</h2>
                <button id="show-affiliate-form-btn" class="button-primary">Ajouter un lien</button>
            </div>


            <div id="affiliate-form-container" class="affiliate-form-container" style="display: none;">
                <div class="affiliate-form">
                    <h3>Ajouter un lien ou code de parrainage</h3>

                    <div class="form-tabs">
                        <button type="button" class="tab-btn active" data-tab="link-form">Lien de parrainage</button>
                        <button type="button" class="tab-btn" data-tab="code-form">Code promo</button>
                    </div>

                    <div class="tab-content">

                        <form id="link-form" action="index.php?controller=affiliateLink&action=store" method="post" class="tab-pane active">
                            <div class="form-group">
                                <label for="brand_id_link">Marque</label>
                                <select id="brand_id_link" name="brand_id" required>
                                    <option value="">Sélectionnez une marque</option>
                                    <?php if (isset($brands) && is_array($brands)): ?>
                                        <?php foreach($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Aucune marque disponible</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="custom_link">Lien de parrainage</label>
                                <input type="url" id="custom_link" name="custom_link" required placeholder="https://exemple.com/ref=votrecode">
                            </div>

                            <div class="form-group">
                                <label for="expiry_date_link">Date d'expiration (optionnel)</label>
                                <input type="date" id="expiry_date_link" name="expiry_date">
                            </div>

                            <div class="form-actions">
                                <button type="button" class="cancel-btn" id="cancel-link-form">Annuler</button>
                                <button type="submit" class="button-primary">Ajouter le lien</button>
                            </div>
                        </form>

                        <!-- Formulaire de code promo -->
                        <form id="code-form" action="index.php?controller=affiliateCode&action=store" method="post" class="tab-pane">
                            <div class="form-group">
                                <label for="brand_id_code">Marque</label>
                                <select id="brand_id_code" name="brand_id" required>
                                    <option value="">Sélectionnez une marque</option>
                                    <?php if (isset($brands) && is_array($brands)): ?>
                                        <?php foreach($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Aucune marque disponible</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="code">Code promo</label>
                                <input type="text" id="code" name="code" required placeholder="PROMO10">
                            </div>

                            <div class="form-group">
                                <label for="expiry_date_code">Date d'expiration (optionnel)</label>
                                <input type="date" id="expiry_date_code" name="expiry_date">
                            </div>

                            <div class="form-actions">
                                <button type="button" class="cancel-btn" id="cancel-code-form">Annuler</button>
                                <button type="submit" class="button-primary">Ajouter le code</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if(empty($links) && empty($codes)): ?>
                <div style="text-align: center; padding: 30px 0;">
                    <p>Vous n'avez pas encore de liens ou codes de parrainage.</p>
                    <p>Commencez par ajouter votre premier lien ou code de parrainage en cliquant sur le bouton ci-dessus.</p>
                </div>
            <?php else: ?>
                <!-- Affichage des liens d'affiliation -->
                <?php if(!empty($links)): ?>
                    <h3>Mes liens de parrainage</h3>
                    <?php foreach($links as $link): ?>
                        <div class="affiliate-card">
                            <div class="affiliate-info">
                                <div class="brand-logo">
                                    <?php if(!empty($link['logo_url']) && file_exists($link['logo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($link['logo_url']); ?>" alt="<?php echo htmlspecialchars($link['brand_name']); ?>">
                                    <?php else: ?>
                                        <div style="width: 80px; height: 80px; display: flex; justify-content: center; align-items: center; background-color: #f5f5f5; font-size: 24px; font-weight: bold;">
                                            <?php echo htmlspecialchars(substr($link['brand_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="affiliate-details">
                                    <h3><?php echo htmlspecialchars($link['brand_name']); ?></h3>
                                    <?php if(!empty($link['custom_link'])): ?>
                                        <p><strong>Lien:</strong>
                                            <a href="<?php echo htmlspecialchars($link['custom_link']); ?>" target="_blank" style="word-break: break-all;">
                                                <?php echo htmlspecialchars($link['custom_link']); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    <p>
                                        <strong>Statut:</strong>
                                        <?php if($link['is_active']): ?>
                                            <span style="color: green;">Actif</span>
                                        <?php else: ?>
                                            <span style="color: red;">Inactif</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if(!empty($link['expiry_date'])): ?>
                                        <p><strong>Expire le:</strong> <?php echo date('d/m/Y', strtotime($link['expiry_date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="affiliate-stats">
                                <div class="stat">
                                    <strong>X consultations</strong>
                                </div>
                                <?php if(!empty($link['bonus'])): ?>
                                    <div class="stat">
                                        <strong>Bonus:</strong> <?php echo htmlspecialchars($link['bonus']); ?>€
                                    </div>
                                <?php endif; ?>
                                <div class="stat">
                                    <strong>Créé le:</strong> <?php echo date('d/m/Y', strtotime($link['created_at'])); ?>
                                </div>
                            </div>

                            <div class="affiliate-actions">
                                <button onclick="location.href='index.php?controller=affiliateLink&action=edit&id=<?php echo $link['id']; ?>'">Modifier</button>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($link['custom_link']); ?>')">Copier le lien</button>
                                <button onclick="location.href='index.php?controller=affiliateLink&action=delete&id=<?php echo $link['id']; ?>'" class="button-danger">Supprimer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Affichage des codes d'affiliation - MAINTENANT INDÉPENDANT de l'affichage des liens -->
                <?php if(!empty($codes)): ?>
                    <h3>Mes codes promo</h3>
                    <?php foreach($codes as $code): ?>
                        <div class="affiliate-card">
                            <div class="affiliate-info">
                                <div class="brand-logo">
                                    <?php if(!empty($code['logo_url']) && file_exists($code['logo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($code['logo_url']); ?>" alt="<?php echo htmlspecialchars($code['brand_name']); ?>">
                                    <?php else: ?>
                                        <div style="width: 80px; height: 80px; display: flex; justify-content: center; align-items: center; background-color: #f5f5f5; font-size: 24px; font-weight: bold;">
                                            <?php echo htmlspecialchars(substr($code['brand_name'] ?? '?', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="affiliate-details">
                                    <h3><?php echo htmlspecialchars($code['brand_name'] ?? 'Marque inconnue'); ?></h3>
                                    <p><strong>Code:</strong> <span style="background-color: #f5f5f5; padding: 3px 6px; border-radius: 4px;"><?php echo htmlspecialchars($code['code']); ?></span></p>
                                    <p>
                                        <strong>Statut:</strong>
                                        <?php if(isset($code['is_active']) && $code['is_active']): ?>
                                            <span style="color: green;">Actif</span>
                                        <?php else: ?>
                                            <span style="color: red;">Inactif</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if(!empty($code['expiry_date'])): ?>
                                        <p><strong>Expire le:</strong> <?php echo date('d/m/Y', strtotime($code['expiry_date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="affiliate-stats">
                                <div class="stat">
                                    <strong>X utilisations</strong>
                                </div>
                                <?php if(!empty($code['bonus'])): ?>
                                    <div class="stat">
                                        <strong>Bonus:</strong> <?php echo htmlspecialchars($code['bonus']); ?>€
                                    </div>
                                <?php endif; ?>
                                <div class="stat">
                                    <strong>Créé le:</strong> <?php echo isset($code['created_at']) ? date('d/m/Y', strtotime($code['created_at'])) : 'Date inconnue'; ?>
                                </div>
                            </div>

                            <div class="affiliate-actions">
                                <!-- Bouton modifier mis à jour pour utiliser le paramètre edit_code -->
                                <button onclick="location.href='index.php?controller=user&action=profile&edit_code=<?php echo $code['id']; ?>'">Modifier</button>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($code['code']); ?>')">Copier le code</button>
                                <button onclick="location.href='index.php?controller=affiliateCode&action=delete&id=<?php echo $code['id']; ?>'" class="button-danger">Supprimer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../js/profil.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si la page est chargée avec un paramètre edit_code ou edit_link, faire défiler jusqu'au formulaire d'édition
        <?php if (isset($codeToEdit) && $codeToEdit): ?>
        const editCodeForm = document.getElementById('edit-code-modal');
        if (editCodeForm) {
            editCodeForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        <?php endif; ?>

        <?php if (isset($linkToEdit) && $linkToEdit): ?>
        const editLinkForm = document.getElementById('edit-link-modal');
        if (editLinkForm) {
            editLinkForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        <?php endif; ?>
    document.addEventListener('DOMContentLoaded', function() {
        // Si la page est chargée avec un paramètre edit_code, faire défiler jusqu'au formulaire d'édition
        <?php if (isset($codeToEdit) && $codeToEdit): ?>
        const editForm = document.getElementById('edit-code-modal');
        if (editForm) {
            editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        <?php endif; ?>

        // Fonction pour copier le texte dans le presse-papier
        window.copyToClipboard = function(text) {
            const textarea = document.createElement('textarea');
            textarea.textContent = text;
            textarea.style.position = 'fixed';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                alert('Copié dans le presse-papier !');
            } catch (err) {
                console.error('Impossible de copier le texte:', err);
                alert('Impossible de copier le texte. Veuillez réessayer.');
            } finally {
                document.body.removeChild(textarea);
            }
        };

        // Gestionnaires pour le formulaire d'ajout
        const showFormBtn = document.getElementById('show-affiliate-form-btn');
        const formContainer = document.getElementById('affiliate-form-container');
        const cancelLinkBtn = document.getElementById('cancel-link-form');
        const cancelCodeBtn = document.getElementById('cancel-code-form');
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        if (showFormBtn && formContainer) {
            showFormBtn.addEventListener('click', function() {
                formContainer.style.display = 'block';
                formContainer.scrollIntoView({ behavior: 'smooth' });
            });
        }

        if (cancelLinkBtn) {
            cancelLinkBtn.addEventListener('click', function() {
                formContainer.style.display = 'none';
            });
        }

        if (cancelCodeBtn) {
            cancelCodeBtn.addEventListener('click', function() {
                formContainer.style.display = 'none';
            });
        }

        // Gestion des onglets
        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const tabId = this.dataset.tab;

                // Enlever la classe active de tous les onglets
                tabBtns.forEach(function(btn) {
                    btn.classList.remove('active');
                });

                // Cacher tous les contenus d'onglet
                tabPanes.forEach(function(pane) {
                    pane.classList.remove('active');
                });

                // Activer l'onglet cliqué
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>
</body>
</html>