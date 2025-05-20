<!-- Vue pour l'historique des boosts -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des boosts - Affiliagram</title>
    <link rel="stylesheet" href="../css/history.css">
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
            <li>Historique des boosts</li>
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

    <div class="boost-history-container">
        <div class="boost-history">
            <h1>Historique de vos boosts</h1>

            <div class="boost-summary">
                <?php
                $activeCount = 0;
                $expiredCount = 0;
                $cancelledCount = 0;

                if (isset($boosts) && is_array($boosts)):
                foreach ($boosts as $boost) {
                    if ($boost['status'] === 'active' && strtotime($boost['end_date']) > time()) {
                        $activeCount++;
                    } elseif ($boost['status'] === 'cancelled') {
                        $cancelledCount++;
                    } else {
                        $expiredCount++;
                    }
                }
                endif;
                ?>
                <div class="boost-stat">
                    <div class="number"><?php echo $activeCount; ?>/3</div>
                    <div class="label">Boosts actifs</div>
                </div>
                <div class="boost-stat">
                    <div class="number"><?php echo $expiredCount; ?></div>
                    <div class="label">Boosts expirés</div>
                </div>
                <div class="boost-stat">
                    <div class="number"><?php echo $cancelledCount; ?></div>
                    <div class="label">Boosts annulés</div>
                </div>
            </div>

            <div class="boost-list">
                <?php if (empty($boosts)): ?>
                    <div class="no-boosts">
                        <p>Vous n'avez pas encore de boost.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($boosts as $boost): ?>
                        <?php
                        // Déterminer le statut réel
                        $realStatus = $boost['status'];
                        if ($realStatus === 'active' && strtotime($boost['end_date']) < time()) {
                            $realStatus = 'expired';
                        }

                        $statusClass = '';
                        $statusText = '';

                        switch ($realStatus) {
                            case 'active':
                                $statusClass = 'status-active';
                                $statusText = 'Actif';
                                break;
                            case 'expired':
                                $statusClass = 'status-expired';
                                $statusText = 'Expiré';
                                break;
                            case 'cancelled':
                                $statusClass = 'status-cancelled';
                                $statusText = 'Annulé';
                                break;
                        }
                        ?>
                        <div class="boost-card">
                            <div class="boost-details">
                                <h3><?php echo htmlspecialchars($boost['brand_name'] ?? 'Marque inconnue'); ?></h3>
                                <p>
                                    <strong>Type:</strong>
                                    <?php echo $boost['item_type'] === 'link' ? 'Lien d\'affiliation' : 'Code promo'; ?>
                                </p>
                                <p><strong>Valeur:</strong> <?php echo htmlspecialchars($boost['item_value'] ?? ''); ?></p>
                                <p>
                                    <strong>Période:</strong>
                                    Du <?php echo date('d/m/Y', strtotime($boost['start_date'])); ?>
                                    au <?php echo date('d/m/Y', strtotime($boost['end_date'])); ?>
                                </p>
                                <p><strong>Statut:</strong> <span class="boost-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></p>
                            </div>
                            <?php if ($realStatus === 'active'): ?>
                                <div class="boost-actions">
                                    <form action="index.php?controller=boost&action=cancel&id=<?php echo $boost['id']; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce boost ? Cette action est irréversible.');">
                                        <button type="submit">Annuler</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <a href="index.php?controller=user&action=profile" class="button-back">
                <i class="fas fa-arrow-left"></i> Retour au profil
            </a>
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