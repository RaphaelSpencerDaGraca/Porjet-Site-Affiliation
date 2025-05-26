<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { text-align: center; margin-bottom: 1rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #333; padding: 5px; }
    .right { text-align: right; }
  </style>
</head>
<body>
  <h1>Facture n°<?= htmlspecialchars((string) $tx['id']) ?></h1>
  <p><strong>Date :</strong> <?= htmlspecialchars($tx['created_at'] ?? '') ?></p>

  <h2>Créditeur (vous)</h2>
  <p><strong>Pseudo :</strong> <?= htmlspecialchars($tx['user_name'] ?? '') ?></p>
  <p><strong>Email :</strong> <?= htmlspecialchars($tx['user_email'] ?? '') ?></p>

  <h2>Bienfaiteur (Affiliagram)</h2>
  <p>Affiliagram SARL<br>123 rue du code<br>75000 Paris</p>

  <h2>Articles</h2>
  <table>
    <thead>
      <tr>
        <th>Désignation</th>
        <th>Quantité</th>
        <th class="right">Prix unitaire</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($boost): ?>
        <tr>
          <td>
            Boost <?= htmlspecialchars($boost['brand_name']   ?? '') ?>
            (<?= htmlspecialchars($boost['item_type']   ?? '') ?>
             #<?= htmlspecialchars((string) ($boost['item_id'] ?? '')) ?>)
          </td>
          <td class="right">1</td>
          <td class="right"><?= number_format($tx['amount'] ?? 0, 2, ',', ' ') ?> €</td>
          <td class="right"><?= number_format($tx['amount'] ?? 0, 2, ',', ' ') ?> €</td>
        </tr>
      <?php else: ?>
        <tr>
          <td colspan="4" style="text-align:center">Pas d’article boost associé</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <h2>Récapitulatif</h2>
  <table style="width:50%; float:right">
    <tr>
      <td><strong>Sous-total :</strong></td>
      <td class="right"><?= number_format($tx['amount'] ?? 0, 2, ',', ' ') ?> €</td>
    </tr>
    <tr>
      <td><strong>TVA (20%) :</strong></td>
      <?php 
        $base = $tx['amount'] ?? 0;
        $tva  = $base * 0.20;
      ?>
      <td class="right"><?= number_format($tva, 2, ',', ' ') ?> €</td>
    </tr>
    <tr>
      <td><strong>Total TTC :</strong></td>
      <?php $ttc = $base + ($base * 0.20); ?>
      <td class="right"><strong><?= number_format($ttc, 2, ',', ' ') ?> €</strong></td>
    </tr>
  </table>
</body>
</html>
