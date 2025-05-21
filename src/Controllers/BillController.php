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
        // ✂ on enlève session_start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(403);
            die('Utilisateur non connecté.');
        }

        $tx = $this->model->getLastCompletedTransaction($userId);
        if (!$tx) {
            die('Aucune transaction valide trouvée.');
        }

        $boost = !empty($tx['boost_id'])
            ? $this->model->getBoostDetails((int)$tx['boost_id'])
            : null;

        ob_start();
        // ⚠ bien appeler la vue bill_invoice.php si c’est son nom
        include __DIR__ . '/../Views/bill.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("facture_{$tx['id']}.pdf", ['Attachment' => false]);
        exit;
    }
}
