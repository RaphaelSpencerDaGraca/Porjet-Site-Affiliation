<?php

/**
 * Contrôleur pour la gestion des utilisateurs
 */
class UserController
{
    private $userModel;

    /**
     * Constructeur - initialise le modèle
     */
    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Affiche la liste des utilisateurs
     */
    public function index()
    {
        $users = $this->userModel->findAll();
        include 'views/users/index.php';
    }

    /**
     * Affiche les détails d'un utilisateur
     */
    public function show($id)
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        include 'views/users/show.php';
    }

    /**
     * Affiche le formulaire de création d'un utilisateur
     */
    public function create()
    {
        include 'views/users/create.php';
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store() {
        $pseudo = $_POST['pseudo'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';


        $errors = [];
        if (empty($pseudo)) $errors[] = "Le pseudo est requis.";
        if (empty($email)) $errors[] = "L'email est requis.";
        if (empty($password)) $errors[] = "Le mot de passe est requis.";
        if ($password !== $confirmPassword) $errors[] = "Les mots de passe ne correspondent pas.";


        if ($this->userModel->findByPseudo($pseudo)) {
            $errors[] = "Ce pseudo est déjà utilisé.";
        }
        if ($this->userModel->findByEmail($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = ['pseudo' => $pseudo, 'email' => $email];
            header('Location: index.php?controller=user&action=create');
            exit;
        }

        // Créer l'utilisateur
        $activationToken = bin2hex(random_bytes(16));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'pseudo' => $pseudo,
            'email' => $email,
            'password_hash' => $passwordHash,
            'activation_token' => $activationToken
        ];

        $userId = $this->userModel->create($userData);

        if ($userId) {
            $_SESSION['success'] = "Utilisateur créé avec succès. Un email d'activation serait normalement envoyé.";
            header('Location: index.php?controller=user&action=index');
        } else {
            $_SESSION['error'] = "Erreur lors de la création de l'utilisateur.";
            header('Location: index.php?controller=user&action=create');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'un utilisateur
     */
    public function edit($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        include 'views/users/edit.php';
    }

    /**
     * Traite la soumission du formulaire d'édition
     */
    public function update($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        $pseudo = $_POST['pseudo'] ?? '';
        $email = $_POST['email'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;


        $errors = [];
        if (empty($pseudo)) $errors[] = "Le pseudo est requis.";
        if (empty($email)) $errors[] = "L'email est requis.";


        $existingPseudo = $this->userModel->findByPseudo($pseudo);
        if ($existingPseudo && $existingPseudo['id'] != $id) {
            $errors[] = "Ce pseudo est déjà utilisé.";
        }

        $existingEmail = $this->userModel->findByEmail($email);
        if ($existingEmail && $existingEmail['id'] != $id) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=user&action=edit&id=' . $id);
            exit;
        }


        $userData = [
            'pseudo' => $pseudo,
            'email' => $email,
            'is_active' => $is_active
        ];

        $updated = $this->userModel->update($id, $userData);

        if ($updated) {
            $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
            header('Location: index.php?controller=user&action=show&id=' . $id);
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur.";
            header('Location: index.php?controller=user&action=edit&id=' . $id);
        }
        exit;
    }

    /**
     * Supprime un utilisateur
     */
    public function delete($id) {
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=user&action=index');
            exit;
        }

        // Confirmer la suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = "Utilisateur supprimé avec succès.";
                header('Location: index.php?controller=user&action=index');
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
                header('Location: index.php?controller=user&action=show&id=' . $id);
            }
            exit;
        }


        include 'views/users/delete.php';
    }

    /**
     * Active un compte utilisateur
     */
    public function activate($token) {
        $query = "SELECT id FROM users WHERE activation_token = :token";
        $stmt = $this->userModel->getDb()->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $updated = $this->userModel->update($user['id'], [
                'is_active' => 1,
                'activation_token' => null
            ]);

            if ($updated) {
                $_SESSION['success'] = "Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'activation du compte.";
            }
        } else {
            $_SESSION['error'] = "Token d'activation invalide ou expiré.";
        }

        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    /**
     * Traite la demande de connexion
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($email, $password);

            if ($user === false) {
                $_SESSION['error'] = "Email ou mot de passe incorrect.";
                header('Location: index.php?controller=auth&action=login');
            } elseif (isset($user['error']) && $user['error'] === 'account_not_activated') {
                $_SESSION['error'] = "Votre compte n'est pas encore activé. Veuillez vérifier votre email.";
                header('Location: index.php?controller=auth&action=login');
            } else {
                // Authentification réussie
                $_SESSION['user'] = $user;
                $_SESSION['success'] = "Connexion réussie. Bienvenue, " . $user['pseudo'] . "!";
                header('Location: index.php?controller=dashboard&action=index');
            }
            exit;
        }


        include 'views/auth/login.php';
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {

        session_unset();
        session_destroy();


        header('Location: index.php');
        exit;
    }
}