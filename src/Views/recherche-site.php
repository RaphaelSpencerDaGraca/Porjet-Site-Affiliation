<?php
require_once '../../dbconnect.php';

$stmt = $pdo->query("
  SELECT 
    b.id,
    b.name,
    b.bonus,
    b.logo_url,
    b.website_url,
    COUNT(al.id) AS link_count
  FROM brands AS b
  LEFT JOIN affiliate_links AS al
    ON al.brand_id   = b.id
   AND al.is_active = 1
  WHERE b.is_active = 1
  GROUP BY b.id
  ORDER BY COUNT(al.id) DESC
");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <img src="../img/account.png" alt="">
    </div>
  </header>

  <div id="main-container">
    <div class="pub">Zone Pub</div>

    <div class="input-recherche">
      <strong><label for="recherche">Rechercher :</label></strong>
      <input type="text" name="recherche" id="recherche">
    </div>

    <hr>

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

            <p>Liens disponibles: <span><?= htmlspecialchars($brand['link_count']) ?></span></p>
            <p>Bonus jusqu’à <span><?= htmlspecialchars($brand['bonus']) ?></span> €</p>

            <button
              onclick="window.location.href='avantages-site.php?name=<?= $brand['name'] ?>';"
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
