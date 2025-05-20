<?php
/**
 * Contrôleur pour la gestion des boosts
 */
class BoostController {
    private $boostModel;
    private $affiliateLinkModel;
    private $affiliateCodeModel;
    private $userModel;
    private $brandModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($boostModel, $affiliateLinkModel, $affiliateCodeModel, $userModel, $brandModel) {
        $this->boostModel = $boostModel;
        $this->affiliateLinkModel = $affiliateLinkModel;
        $this->affiliateCodeModel = $affiliateCodeModel;
        $this->userModel = $userModel;
        $this->brandModel = $brandModel;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    /**
     * Affiche le formulaire de boost
     * Cette méthode initialise les variables $itemType, $itemId et $itemName
     * qui sont utilisées dans la vue boost.php
     */
    public function showBoostForm() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer les paramètres depuis l'URL
        $itemType = isset($_GET['item_type']) ? $_GET['item_type'] : '';
        $itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

        // Validation des paramètres d'entrée
        if (empty($itemType) || empty($itemId)) {
            $_SESSION['error'] = "Paramètres manquants pour le boost.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si $itemType est valide
        if ($itemType !== 'link' && $itemType !== 'code') {
            $_SESSION['error'] = "Type d'élément invalide.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'utilisateur a déjà 3 boosts actifs
        $activeBoostCount = $this->boostModel->countActiveByUserId($userId);
        if ($activeBoostCount >= 3) {
            $_SESSION['error'] = "Vous avez déjà 3 boosts actifs. Vous ne pouvez pas en ajouter davantage.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'élément est déjà boosté
        if ($this->boostModel->isItemBoosted($itemType, $itemId)) {
            $_SESSION['error'] = "Cet élément est déjà boosté.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Récupérer les informations de l'élément à booster
        $item = null;
        $itemName = '';
        $brandName = '';

        if ($itemType === 'link') {
            $item = $this->affiliateLinkModel->findById($itemId);
            if ($item) {
                $itemName = "Lien d'affiliation: " . $item['custom_link'];

                // Récupérer le nom de la marque si disponible
                if (!empty($item['brand_id'])) {
                    $brand = $this->brandModel->findById($item['brand_id']);
                    if ($brand) {
                        $brandName = $brand['name'];
                    }
                }
            }
        } elseif ($itemType === 'code') {
            $item = $this->affiliateCodeModel->findById($itemId);
            if ($item) {
                $itemName = "Code promo: " . $item['code'];

                // Récupérer le nom de la marque si disponible
                if (!empty($item['brand_id'])) {
                    $brand = $this->brandModel->findById($item['brand_id']);
                    if ($brand) {
                        $brandName = $brand['name'];
                    }
                }
            }
        }

        if (!$item) {
            $_SESSION['error'] = "L'élément à booster n'existe pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que l'élément appartient à l'utilisateur
        if ($item['user_id'] != $userId) {
            $_SESSION['error'] = "Vous ne pouvez pas booster cet élément car il ne vous appartient pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Préparer les données pour la vue
        $userData = $this->userModel->findById($userId);

        // Définir les variables directement dans la portée globale
        // Cela évite les problèmes liés à extract() ou à la portée des variables
        global $itemType, $itemId, $itemName, $brandName, $activeBoostCount, $user;

        $user = $userData;

        // Inclure la vue du formulaire de boost
        include __DIR__ . '/../Views/boost.php';
    }

    /**
     * Traite l'achat d'un boost
     */
    public function processBoost() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer les données du formulaire
        $itemType = $_POST['item_type'] ?? '';
        $itemId = $_POST['item_id'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'card';

        // Vérifier les données
        if (empty($itemType) || empty($itemId)) {
            $_SESSION['error'] = "Données manquantes pour le boost.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Convertir $itemId en nombre
        $itemId = (int) $itemId;

        // Vérifier si $itemType est valide
        if ($itemType !== 'link' && $itemType !== 'code') {
            $_SESSION['error'] = "Type d'élément invalide.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'utilisateur a déjà 3 boosts actifs
        $activeBoostCount = $this->boostModel->countActiveByUserId($userId);
        if ($activeBoostCount >= 3) {
            $_SESSION['error'] = "Vous avez déjà 3 boosts actifs. Vous ne pouvez pas en ajouter davantage.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'élément est déjà boosté
        if ($this->boostModel->isItemBoosted($itemType, $itemId)) {
            $_SESSION['error'] = "Cet élément est déjà boosté.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Récupérer l'élément
        $item = null;
        if ($itemType === 'link') {
            $item = $this->affiliateLinkModel->findById($itemId);
        } elseif ($itemType === 'code') {
            $item = $this->affiliateCodeModel->findById($itemId);
        }

        if (!$item) {
            $_SESSION['error'] = "L'élément à booster n'existe pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que l'élément appartient à l'utilisateur
        if ($item['user_id'] != $userId) {
            $_SESSION['error'] = "Vous ne pouvez pas booster cet élément car il ne vous appartient pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Simuler un traitement de paiement
        // Note: Dans une application réelle, cette partie serait remplacée par l'intégration d'une passerelle de paiement
        $paymentReference = 'BOOST-' . uniqid();
        $paymentSuccessful = true; // Simuler un paiement réussi

        if (!$paymentSuccessful) {
            $_SESSION['error'] = "Erreur lors du traitement du paiement. Veuillez réessayer.";
            header('Location: index.php?controller=boost&action=showBoostForm&item_type=' . $itemType . '&item_id=' . $itemId);
            exit;
        }

        // Dates de début et de fin du boost (7 jours)
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Créer le boost
        $boostData = [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'amount' => 1.00, // 1 euro
            'payment_id' => $paymentReference
        ];

        $boostId = $this->boostModel->create($boostData);

        if (!$boostId) {
            $_SESSION['error'] = "Une erreur est survenue lors de la création du boost.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Enregistrer la transaction
        $transactionData = [
            'user_id' => $userId,
            'amount' => 1.00,
            'type' => 'boost',
            'status' => 'completed',
            'payment_method' => $paymentMethod,
            'reference_id' => $paymentReference,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'boost_id' => $boostId
        ];

        $this->boostModel->createTransaction($transactionData);

        // Mettre à jour l'élément pour indiquer qu'il est boosté
        if ($itemType === 'link') {
            $this->affiliateLinkModel->update($itemId, [
                'is_boosted' => 1,
                'boost_end_date' => $endDate
            ]);
        } elseif ($itemType === 'code') {
            $this->affiliateCodeModel->update($itemId, [
                'is_boosted' => 1,
                'boost_end_date' => $endDate
            ]);
        }

        $_SESSION['success'] = "Votre élément a été boosté avec succès pour 7 jours !";
        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche l'historique des boosts de l'utilisateur
     */
    public function history() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Mettre à jour le statut des boosts (expire les boosts en fin de validité)
        $this->boostModel->updateStatus();

        // Récupérer l'historique des boosts
        $boosts = $this->boostModel->findAllByUserId($userId);

        // Récupérer les informations de l'utilisateur
        $user = $this->userModel->findById($userId);

        // Inclure la vue
        include __DIR__ . '/../Views/boost/history.php';
    }

    /**
     * Annule un boost actif
     */
    public function cancel($boostId) {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer le boost
        $boost = $this->boostModel->findById($boostId);

        if (!$boost) {
            $_SESSION['error'] = "Le boost spécifié n'existe pas.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Vérifier que le boost appartient à l'utilisateur
        if ($boost['user_id'] != $userId) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à annuler ce boost.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Vérifier que le boost est actif
        if ($boost['status'] !== 'active') {
            $_SESSION['error'] = "Ce boost n'est pas actif et ne peut pas être annulé.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Annuler le boost
        $this->boostModel->update($boostId, [
            'status' => 'cancelled'
        ]);

        // Mettre à jour l'élément
        if ($boost['item_type'] === 'link') {
            $this->affiliateLinkModel->update($boost['item_id'], [
                'is_boosted' => 0,
                'boost_end_date' => null
            ]);
        } elseif ($boost['item_type'] === 'code') {
            $this->affiliateCodeModel->update($boost['item_id'], [
                'is_boosted' => 0,
                'boost_end_date' => null
            ]);
        }

        $_SESSION['success'] = "Le boost a été annulé avec succès.";
        header('Location: index.php?controller=boost&action=history');
        exit;
    }
}