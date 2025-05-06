<?php
/**
 * Contrôleur pour la gestion du profil utilisateur
 */
class UserController {
    private $userModel;
    private $affiliateLinkModel;
    private $brandModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($userModel, $affiliateLinkModel, $brandModel) {
        $this->userModel = $userModel;
        $this->affiliateLinkModel = $affiliateLinkModel;
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
     * Affiche le profil de l'utilisateur connecté
     */
    public function profile() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php');
            exit;
        }

        // Récupérer les liens d'affiliation de l'utilisateur
        $links = $this->affiliateLinkModel->findByUserId($userId);

        include 'views/users/profile.php';
    }

    /**
     * Traite la soumission du formulaire de mise à jour du profil
     */
    public function updateProfile() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php');
            exit;
        }

        // Récupérer les données du formulaire
        $pseudo = $_POST['pseudo'] ?? '';
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];
        if (empty($pseudo)) $errors[] = "Le pseudo est obligatoire.";
        if (empty($email)) $errors[] = "L'email est obligatoire.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email est invalide.";

        // Vérifier si le pseudo est déjà utilisé (sauf par l'utilisateur actuel)
        if ($pseudo !== $user['pseudo']) {
            $existingUser = $this->userModel->findByPseudo($pseudo);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = "Ce pseudo est déjà utilisé.";
            }
        }

        // Vérifier si l'email est déjà utilisé (sauf par l'utilisateur actuel)
        if ($email !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        }

        // Si l'utilisateur souhaite changer son mot de passe
        if (!empty($newPassword)) {
            // Vérifier le mot de passe actuel
            if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
                $errors[] = "Le mot de passe actuel est incorrect.";
            }

            // Vérifier que le nouveau mot de passe est conforme aux exigences
            if (strlen($newPassword) < 8) {
                $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
            }

            // Vérifier que les deux mots de passe correspondent
            if ($newPassword !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Préparer les données à mettre à jour
        $userData = [
            'pseudo' => $pseudo,
            'email' => $email
        ];

        // Si un nouveau mot de passe est fourni, le hacher
        if (!empty($newPassword)) {
            $userData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Mettre à jour l'utilisateur
        $updated = $this->userModel->update($userId, $userData);

        if ($updated) {
            // Mettre à jour les données de session
            $_SESSION['user']['pseudo'] = $pseudo;
            $_SESSION['user']['email'] = $email;

            $_SESSION['success'] = "Votre profil a été mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche la page pour changer la photo de profil
     */
    public function editProfilePicture() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];

        // Si un fichier a été envoyé
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/profile_pictures/';

            // Créer le répertoire s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Vérifier le type de fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
                $_SESSION['error'] = "Seuls les fichiers JPG, PNG et GIF sont acceptés.";
                header('Location: index.php?controller=user&action=profile');
                exit;
            }

            // Générer un nom unique pour le fichier
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $userId . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                // Récupérer l'ancienne photo de profil
                $user = $this->userModel->findById($userId);
                $oldPicture = $user['profile_picture'] ?? null;

                // Mettre à jour l'utilisateur avec le nouveau chemin de la photo
                $updated = $this->userModel->update($userId, ['profile_picture' => $targetPath]);

                if ($updated) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($oldPicture && file_exists($oldPicture)) {
                        unlink($oldPicture);
                    }

                    $_SESSION['success'] = "Votre photo de profil a été mise à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour de la photo de profil.";
                }
            } else {
                $_SESSION['error'] = "Erreur lors de l'upload du fichier.";
            }
        } else {
            $_SESSION['error'] = "Aucun fichier sélectionné ou erreur lors de l'upload.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Réinitialisation du mot de passe (étape 1 : demande)
     */
    public function forgotPassword() {
        include 'views/auth/forgot_password.php';
    }

    /**
     * Traite la demande de réinitialisation de mot de passe
     */
    public function processForgotPassword() {
        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            $_SESSION['error'] = "Veuillez entrer votre adresse email.";
            header('Location: index.php?controller=user&action=forgotPassword');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            // Générer un token de réinitialisation
            $resetToken = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Enregistrer le token en base de données
            $this->userModel->update($user['id'], [
                'reset_token' => $resetToken,
                'reset_token_expiry' => $expiry
            ]);

            // Dans un environnement de production, envoyer un email avec le lien
            // Ici, on simule l'envoi d'email
            $resetLink = "index.php?controller=user&action=resetPassword&token=" . $resetToken;

            $_SESSION['success'] = "Un email a été envoyé à votre adresse avec les instructions pour réinitialiser votre mot de passe. (Lien simulé: $resetLink)";
        } else {
            // Pour des raisons de sécurité, ne pas indiquer si l'email existe ou non
            $_SESSION['success'] = "Si cette adresse email est enregistrée dans notre système, un email avec les instructions de réinitialisation sera envoyé.";
        }

        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token de réinitialisation manquant.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Vérifier si le token est valide et non expiré
        $query = "SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $stmt = $this->userModel->getDb()->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Token de réinitialisation invalide ou expiré.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        include 'views/auth/reset_password.php';
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe
     */
    public function processResetPassword() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Vérifications
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
            header('Location: index.php?controller=user&action=resetPassword&token=' . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
            header('Location: index.php?controller=user&action=resetPassword&token=' . urlencode($token));
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
            header('Location: index.php?controller=user&action=resetPassword&token=' . urlencode($token));
            exit;
        }

        // Vérifier si le token est valide et non expiré
        $query = "SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $stmt = $this->userModel->getDb()->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Token de réinitialisation invalide ou expiré.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Mettre à jour le mot de passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $updated = $this->userModel->update($user['id'], [
            'password_hash' => $passwordHash,
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);

        if ($updated) {
            $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $_SESSION['error'] = "Erreur lors de la réinitialisation du mot de passe.";
        }

        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    /**
     * Récupère la base de données
     */
    public function getDb() {
        return $this->userModel->getDb();
    }