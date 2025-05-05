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
            <img src="../img/account.png" alt="Compte utilisateur">
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
                    <p>liens disponibles : <span><?= (int)$brand['bonus'] ?></span></p>
                    <strong>Avantages filleul</strong>
                    <p><?= nl2br(htmlspecialchars($brand['description'], ENT_QUOTES, 'UTF-8')) ?></p>
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

        <ul id="liste-liens">
            <li>
                <img src="../img/account.png" alt="">
                <div>
                    <div class="specs-user">
                        <strong>`pseudo`</strong>
                        <strong>`score`/5</strong>
                    </div>
                    <br>
                    <button>Vers le site!</button>
                </div>
            </li>
        </ul>

        <ul id="liste-codes">
            <li>
                <img src="../img/account.png" alt="">
                <div>
                    <div class="specs-user">
                        <strong>`pseudo`</strong>
                        <strong>`score`/5</strong>
                    </div>
                    <br>
                    <p class="espace-code">`Code`</p>
                </div>
            </li>
        </ul>
    </div>
</body>
</html>
