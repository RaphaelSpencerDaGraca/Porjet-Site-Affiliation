<?php

/**
 * Contrôleur pour la gestion des codes d'affiliation
 */
class AffiliateCodeController {
    private $affiliateCodeModel;
    private $brandModel;
    private $userModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($affiliateCodeModel, $brandModel, $userModel) {
        $this->affiliateCodeModel = $affiliateCodeModel;
        $this->brandModel = $brandModel;
        $this->userModel = $userModel;
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
     * Vérifie si l'utilisateur est administrateur
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Accès refusé. Vous devez être administrateur pour accéder à cette page.";
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Affiche la liste des codes d'affiliation de l'utilisateur connecté
     */
    public function index() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $codes = $this->affiliateCodeModel->findByUserId($userId);

        // Rediriger vers la page de profil
        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche les détails d'un code d'affiliation
     */
    public function show($id) {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        if (!$code) {
            $_SESSION['error'] = "Code d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que le code appartient à l'utilisateur ou que l'utilisateur est admin
        if ($code['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Rediriger vers la page de profil avec l'ID du code à modifier
        header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        exit;
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $brandId = $_POST['brand_id'] ?? '';
        $code = $_POST['code'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 1; // Par défaut actif

        // Validation
        $errors = [];
        if (empty($brandId)) $errors[] = "Vous devez sélectionner une marque.";
        if (empty($code)) $errors[] = "Le code d'affiliation ne peut pas être vide.";

        // Vérifier que la marque existe et est active
        if (!empty($brandId)) {
            $brand = $this->brandModel->findById($brandId);
            if (!$brand || !$brand['is_active']) {
                $errors[] = "La marque sélectionnée n'est pas disponible.";
            }
        }

        // Vérifier si ce code existe déjà pour cet utilisateur et cette marque
        if ($this->affiliateCodeModel->exists($userId, $brandId, $code)) {
            $errors[] = "Vous avez déjà ajouté ce code d'affiliation pour cette marque.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $codeData = [
            'user_id' => $userId,
            'brand_id' => $brandId,
            'code' => $code,
            'expiry_date' => $expiryDate,
            'is_active' => $isActive
        ];

        $codeId = $this->affiliateCodeModel->create($codeData);

        if ($codeId) {
            $_SESSION['success'] = "Code d'affiliation créé avec succès.";
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la création du code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'un code d'affiliation
     */
    public function edit($id) {
        $this->checkAuth();

        // Rediriger vers la page de profil avec l'ID du code à éditer
        header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        exit;
    }

    /**
     * Traite la soumission du formulaire d'édition
     * Modifiée pour fonctionner avec ou sans le paramètre ID dans l'URL
     */
    public function update($id = null) {
        $this->checkAuth();

        // Si l'ID n'est pas passé comme paramètre, essayer de le récupérer à partir de POST ou GET
        if ($id === null) {
            $id = $_POST['code_id'] ?? ($_GET['id'] ?? null);
        }

        if (!$id) {
            $_SESSION['error'] = "ID du code d'affiliation manquant.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        if (!$code) {
            $_SESSION['error'] = "Code d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        if ($code['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $codeValue = $_POST['code'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];
        if (empty($codeValue)) $errors[] = "Le code d'affiliation ne peut pas être vide.";

        // Vérifier si ce code existe déjà pour cet utilisateur et cette marque (sauf pour lui-même)
        if ($codeValue !== $code['code'] && $this->affiliateCodeModel->exists($userId, $code['brand_id'], $codeValue)) {
            $errors[] = "Vous avez déjà un code d'affiliation identique pour cette marque.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
            exit;
        }

        $codeData = [
            'code' => $codeValue,
            'expiry_date' => $expiryDate,
            'is_active' => $isActive
        ];

        $updated = $this->affiliateCodeModel->update($id, $codeData);

        if ($updated) {
            $_SESSION['success'] = "Code d'affiliation mis à jour avec succès.";
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du code d'affiliation.";
            header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        }
        exit;
    }

    /**
     * Supprime un code d'affiliation
     */
    public function delete($id)
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        // Vérifier si le code existe et appartient à l'utilisateur connecté
        if (!$code || $code['user_id'] != $userId) {
            $_SESSION['error'] = "Ce code d'affiliation n'existe pas ou vous n'avez pas les droits pour le supprimer.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        if ($this->affiliateCodeModel->delete($id)) {
            $_SESSION['success'] = "Code d'affiliation supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du code d'affiliation.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    // Autres méthodes du contrôleur (adminIndex, checkExpired, statistics, export, etc.)
}