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
            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
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

    <div class="main-content">
        <div class="profile-section">
            <h1>Mon profil</h1>

            <div class="profile-picture">
                <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
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
                    <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
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
                <h2>Mes liens d'affiliation</h2>
                <button id="show-affiliate-form-btn" class="button-primary">Ajouter un lien</button>
            </div>


            <div id="affiliate-form-container" class="affiliate-form-container" style="display: none;">
                <div class="affiliate-form">
                    <h3>Ajouter un lien ou code d'affiliation</h3>

                    <div class="form-tabs">
                        <button type="button" class="tab-btn active" data-tab="link-form">Lien d'affiliation</button>
                        <button type="button" class="tab-btn" data-tab="code-form">Code promo</button>
                    </div>

                    <div class="tab-content">

                        <form id="link-form" action="index.php?controller=affiliateLink&action=store" method="post" class="tab-pane active">
                            <div class="form-group">
                                <label for="brand_id_link">Marque</label>
                                <select id="brand_id_link" name="brand_id" required>
                                    <option value="">Sélectionnez une marque</option>
                                    <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="custom_link">Lien d'affiliation</label>
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
                        <form id="code-form" action="index.php?controller=affiliateLink&action=store" method="post" class="tab-pane">
                            <div class="form-group">
                                <label for="brand_id_code">Marque</label>
                                <select id="brand_id_code" name="brand_id" required>
                                    <option value="">Sélectionnez une marque</option>
                                    <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                    <?php endforeach; ?>
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

            <?php if(empty($links)): ?>
                <div style="text-align: center; padding: 30px 0;">
                    <p>Vous n'avez pas encore de liens d'affiliation.</p>
                    <p>Commencez par ajouter votre premier lien d'affiliation en cliquant sur le bouton ci-dessus.</p>
                </div>
            <?php else: ?>
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
                                <?php if(!empty($link['code'])): ?>
                                    <p><strong>Code:</strong> <span style="background-color: #f5f5f5; padding: 3px 6px; border-radius: 4px;"><?php echo htmlspecialchars($link['code']); ?></span></p>
                                <?php endif; ?>
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
                            <button onclick="copyToClipboard('<?php echo !empty($link['custom_link']) ? htmlspecialchars($link['custom_link']) : htmlspecialchars($link['code']); ?>')">Copier le lien/code</button>
                            <button onclick="location.href='index.php?controller=affiliateLink&action=delete&id=<?php echo $link['id']; ?>'" class="button-primary">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination - à implémenter si nécessaire -->
                <?php if(count($links) > 10): ?>
                    <div class="pagination">
                        <div class="page-number active">1</div>
                        <div class="page-number">2</div>
                        <div class="page-number">3</div>
                        <div class="page-number">›</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../js/profil.js"></script>
</body>
</html>