<?php /** @var array|false $brand */ ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $brand
            ? 'Avantages de ' . htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8')
            : 'Marque introuvable'
        ?>
    </title>
    <link rel="stylesheet" href="../css/avantages-sites.css">
    <script defer src="../js/avantages-site.js"></script>
</head>
<body>
    <header>
        <h1>Affiliagram</h1>
        <div class="lien-compte">
            <a href="index.php?controller=user&action=profile">
                <img src="../img/account.png" alt="Compte utilisateur">
            </a>
        </div>
    </header>

    <div id="main-container">
        <div class="pub">Zone Pub</div>

        <a href="javascript:history.back()" class="bouton-retour">← Retour</a>

        <div id="affichage-site">
            <?php if ($brand): ?>
                <img
                    src="<?= htmlspecialchars($brand['logo_url'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Logo <?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="info-parrainage">
                    <strong><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p>
                        Liens disponibles : <span><?= count($links) ?></span><br>
                        Codes disponibles : <span><?= count($codes) ?></span>
                    </p>
                    <strong>Avantages filleul</strong>
                    <p><?= nl2br(htmlspecialchars($brand['description_bonus'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php if (!empty($brand['website_url'])): ?>
                        <p>
                            <a
                                href="<?= htmlspecialchars($brand['website_url'], ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank"
                                rel="noopener"
                            >
                                Visiter le site
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="info-parrainage">
                    <p class="error">
                        Aucune marque trouvée pour
                        « <?= htmlspecialchars($_GET['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> ».
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="choix-lien-code">
            <p id="affiche-liens" class="affichage-liste">Liens de parrainage</p>
            <hr>
            <p id="affiche-codes" class="affichage-liste">Codes de parrainage</p>
        </div>

        <hr class="barre">

        <!-- Liste des liens -->
        <ul id="liste-liens">
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $link): ?>
                    <li class="item <?= $link['is_boosted'] ? 'boosted' : '' ?>">
                        <?php if ($link['is_boosted']): ?>
                            <img src="../img/flamme.png" alt="Boosté" class="flame-icon">
                        <?php endif; ?>

                        <img src="../img/account.png" alt="Avatar" class="user-avatar">
                        <div>
                            <div class="specs-user">
                                <strong><?= htmlspecialchars($link['pseudo'], ENT_QUOTES) ?></strong>
                            </div>
                            <button
                              onclick="window.open('<?= htmlspecialchars($link['custom_link'], ENT_QUOTES) ?>','_blank')"
                            >
                                Vers le site
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Aucun lien disponible.</li>
            <?php endif; ?>
        </ul>

        <!-- Liste des codes -->
        <ul id="liste-codes" style="display:none;">
            <?php if (!empty($codes)): ?>
                <?php foreach ($codes as $code): ?>
                    <li class="item <?= $code['is_boosted'] ? 'boosted' : '' ?>">
                        <?php if ($code['is_boosted']): ?>
                            <img src="../img/flamme.png" alt="Boosté" class="flame-icon">
                        <?php endif; ?>

                        <img src="../img/account.png" alt="Avatar" class="user-avatar">
                        <div>
                            <div class="specs-user">
                                <strong><?= htmlspecialchars($code['pseudo'], ENT_QUOTES) ?></strong>
                            </div>
                            <p class="espace-code"><?= htmlspecialchars($code['code'], ENT_QUOTES) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Aucun code disponible.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
