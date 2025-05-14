<?php

  
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="../js/recherche-site.js" type="module"></script>
  <link rel="stylesheet" href="../css/recherche-site.css">
  <title>Affiliagram</title>
</head>
<body>
  <header>
    <h1>Affiliagram</h1>
    <div class="lien-compte">
      <a href="index.php?controller=user&action=profile">
        <img src="../img/account.png" alt="">
      </a>
    </div>
  </header>

  <div id="main-container">
    <div class="pub">Zone Pub</div>

    <div class="input-recherche">
      <strong><label for="recherche">Rechercher :</label></strong>
      <input type="text" name="recherche" id="recherche">
    </div>

    <ul id="liste-sites">
      <?php foreach ($brands as $brand): ?>
        <li>
          <div class="affichage-site">
            <div class="logo-container">
              <img
                src="<?= htmlspecialchars($brand['logo_url']) ?>"
                alt="Logo de <?= htmlspecialchars($brand['name']) ?>"
              >
              </div>

            <strong><?= htmlspecialchars($brand['name']) ?></strong>

            <p>Liens disponibles: <span><?= htmlspecialchars($brand['link_count'] ?? 0) ?></span></p>
            <p>Codes disponibles: <span><?= htmlspecialchars($brand['code_count'] ?? 0) ?></span></p>
            <p>Bonus jusqu’à <span><?= htmlspecialchars($brand['bonus']) ?></span> €</p>

            <button
              onclick="window.location.href='index.php?controller=brand&action=showByName&name=<?= $brand['name'] ?>';"
            >
              Je le veux !
            </button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</body>
</html>
