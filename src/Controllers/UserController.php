<?php
/**
 * Contrôleur pour la gestion des utilisateurs
 */
class UserController {
    private $userModel;
    private $affiliateLinkModel;
    private $affiliateCodeModel;
    private $brandModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($userModel, $affiliateLinkModel, $affiliateCodeModel, $brandModel) {
        $this->userModel = $userModel;
        $this->affiliateLinkModel = $affiliateLinkModel;
        $this->affiliateCodeModel = $affiliateCodeModel;
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
     * Affiche la page de profil de l'utilisateur connecté
     */
    public function profile() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);

        // Récupération des liens d'affiliation
        $links = $this->affiliateLinkModel->findByUserId($userId);

        // Récupération des codes d'affiliation
        $codes = $this->affiliateCodeModel->findByUserId($userId);

        // Récupération des marques actives pour le formulaire
        $brands = $this->brandModel->findActive();

        // Initialiser les variables pour l'édition
        $codeToEdit = null;
        $brandForCode = null;

        // Si on demande l'édition d'un code, récupérer les détails du code et de sa marque
        $editCodeId = isset($_GET['edit_code']) ? (int)$_GET['edit_code'] : null;

        if ($editCodeId) {
            $codeToEdit = $this->affiliateCodeModel->findById($editCodeId);

            if ($codeToEdit && $codeToEdit['user_id'] == $userId) {
                // Récupérer les informations de la marque associée au code
                $brandForCode = $this->brandModel->findById($codeToEdit['brand_id']);

                // Si la marque n'est pas trouvée, créer un tableau vide avec un nom par défaut
                if (!$brandForCode) {
                    $brandForCode = ['name' => 'Marque inconnue'];
                }
            } else {
                // Réinitialiser si le code n'appartient pas à l'utilisateur ou n'existe pas
                $codeToEdit = null;
                $brandForCode = null;
            }
        }

        // Inclusion de la vue
        include __DIR__ . '/../Views/profile.php';
    }

    // Autres méthodes du contrôleur (updateProfile, editProfilePicture, etc.)
}