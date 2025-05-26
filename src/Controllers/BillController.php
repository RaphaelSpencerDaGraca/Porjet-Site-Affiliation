<?php
// src/Controllers/BillController.php

require_once __DIR__ . '/../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

class BillController
{
    private BillModel $model;

    public function __construct(BillModel $model)
    {
        $this->model = $model;
    }

    public function generate()
{
    // Récupération de l'utilisateur connecté
    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
        http_response_code(403);
        die('Utilisateur non connecté.');
    }

    // 1) Récupérer la dernière transaction "completed" de l'utilisateur
    $tx = $this->model->getLastCompletedTransaction($userId);
    if (!$tx) {
        die('Aucune transaction valide trouvée.');
    }

    // 2) Si boost, récupérer ses détails
    $boost = null;
    if (!empty($tx['boost_id'])) {
        $boost = $this->model->getBoostDetails((int)$tx['boost_id']);
    }

    // 3) Générer le HTML via la vue
    ob_start();
    include __DIR__ . '/../Views/bill.php';  // ou bill.php selon votre nommage
    $html = ob_get_clean();

    // 4) Configurer Dompdf et rendre le PDF
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 5) Forcer le téléchargement du PDF
    $filename = "facture_{$tx['id']}.pdf";
    $dompdf->stream($filename, [
        'Attachment' => true
    ]);

    header('Location: index.php?controller=user&action=profile');
    
    exit;
}

}
