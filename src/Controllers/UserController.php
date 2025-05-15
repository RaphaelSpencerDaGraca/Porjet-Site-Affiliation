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

        // S'assurer que l'utilisateur est toujours correctement récupéré
        $user = $this->userModel->findById($userId);

        // Vérification supplémentaire - Si $user est null ou vide, utiliser les données de la session
        if (!$user) {
            $user = [
                'id' => $userId,
                'pseudo' => $_SESSION['user']['pseudo'] ?? 'Utilisateur',
                'email' => $_SESSION['user']['email'] ?? '',
                'profile_picture' => ''
            ];

            // Journaliser l'erreur pour débogage
            error_log("ERREUR: Impossible de récupérer l'utilisateur ID: $userId");
        }

        // Récupération des liens d'affiliation
        $links = $this->affiliateLinkModel->findByUserId($userId) ?: [];

        // Récupération des codes d'affiliation
        $codes = $this->affiliateCodeModel->findByUserId($userId) ?: [];

        // Récupération des marques actives pour le formulaire
        $brands = $this->brandModel->findActive() ?: [];

        // Initialiser les variables pour l'édition de code
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

        // Initialiser les variables pour l'édition de lien
        $linkToEdit = null;
        $brandForLink = null;

        // Si on demande l'édition d'un lien, récupérer les détails du lien et de sa marque
        $editLinkId = isset($_GET['edit_link']) ? (int)$_GET['edit_link'] : null;

        if ($editLinkId) {
            $linkToEdit = $this->affiliateLinkModel->findById($editLinkId);

            if ($linkToEdit && $linkToEdit['user_id'] == $userId) {
                // Récupérer les informations de la marque associée au lien
                $brandForLink = $this->brandModel->findById($linkToEdit['brand_id']);

                // Si la marque n'est pas trouvée, créer un tableau vide avec un nom par défaut
                if (!$brandForLink) {
                    $brandForLink = ['name' => 'Marque inconnue'];
                }
            } else {
                // Réinitialiser si le lien n'appartient pas à l'utilisateur ou n'existe pas
                $linkToEdit = null;
                $brandForLink = null;
            }
        }

        // Inclusion de la vue
        include __DIR__ . '/../Views/profile.php';
    }

    /**
     * Met à jour les informations du profil utilisateur
     */
    public function updateProfile() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);

        $pseudo = $_POST['pseudo'] ?? '';
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];

        if (empty($pseudo)) {
            $errors[] = "Le pseudo est requis.";
        }

        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }

        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        if ($email !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
            }
        }

        // Vérifier le mot de passe actuel
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            if (empty($currentPassword)) {
                $errors[] = "Le mot de passe actuel est requis pour effectuer des modifications.";
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Le mot de passe actuel est incorrect.";
            }

            // Vérifier le nouveau mot de passe
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 8) {
                    $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                }

                if ($newPassword !== $confirmPassword) {
                    $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Mise à jour des données
        $userData = [
            'pseudo' => $pseudo,
            'email' => $email
        ];

        // Mise à jour du mot de passe si nécessaire
        if (!empty($newPassword)) {
            $userData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $updated = $this->userModel->update($userId, $userData);

        if ($updated) {
            // Mettre à jour la session avec les nouvelles informations
            $_SESSION['user']['pseudo'] = $pseudo;
            $_SESSION['user']['email'] = $email;

            $_SESSION['success'] = "Votre profil a été mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de votre profil.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Met à jour la photo de profil de l'utilisateur
     */
    public function editProfilePicture() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];

        // Vérifier si un fichier a été envoyé
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Vérifier le type de fichier
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = "Le format de l'image n'est pas accepté. Utilisez JPG, PNG ou GIF.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Limiter la taille du fichier (5 Mo)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "L'image est trop volumineuse. Taille maximale: 5 Mo.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Créer le dossier de destination s'il n'existe pas
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('profile_') . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        // Déplacer le fichier téléchargé
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Supprimer l'ancienne image si elle existe
            $user = $this->userModel->findById($userId);
            if (!empty($user['profile_picture']) && file_exists($user['profile_picture']) && $user['profile_picture'] != 'uploads/profile_pictures/default.png') {
                unlink($user['profile_picture']);
            }

            // Mettre à jour le chemin de l'image dans la base de données
            $userData = [
                'profile_picture' => $targetPath
            ];

            $updated = $this->userModel->update($userId, $userData);

            if ($updated) {
                $_SESSION['user']['profile_picture'] = $targetPath;
                $_SESSION['success'] = "Votre photo de profil a été mise à jour avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de la photo de profil.";
            }
        } else {
            $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }
}