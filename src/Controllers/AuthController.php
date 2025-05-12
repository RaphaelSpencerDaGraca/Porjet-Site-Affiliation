<?php

/**
 * Contrôleur pour la gestion de l'authentification
 */
class AuthController
{
    private $userModel;

    /**
     * Constructeur - initialise le modèle utilisateur
     */
    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function login()
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers son profil
        if (isset($_SESSION['user'])) {
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Authentification
            $user = $this->userModel->authenticate($email, $password);

            if ($user === false) {
                $_SESSION['error'] = "Email ou mot de passe incorrect.";
                $_SESSION['form_data'] = ['email' => $email];
                header('Location: index.php?controller=auth&action=login');
                exit;
            } elseif (isset($user['error']) && $user['error'] === 'account_not_activated') {
                $_SESSION['error'] = "Votre compte n'est pas encore activé. Veuillez vérifier votre email.";
                $_SESSION['form_data'] = ['email' => $email];
                header('Location: index.php?controller=auth&action=login');
                exit;
            }

            // Authentification réussie
            $_SESSION['user'] = $user;
            $_SESSION['success'] = "Connexion réussie. Bienvenue, " . $user['pseudo'] . "!";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Afficher le formulaire de connexion
        include __DIR__ . '/../Views/auth/login.php';

    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function register()
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers son profil
        if (isset($_SESSION['user'])) {
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $terms = isset($_POST['terms']) ? true : false;

            // Validation
            $errors = [];
            if (empty($pseudo)) $errors[] = "Le pseudo est requis.";
            if (empty($email)) $errors[] = "L'email est requis.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email est invalide.";
            if (empty($password)) $errors[] = "Le mot de passe est requis.";
            if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            if ($password !== $confirmPassword) $errors[] = "Les mots de passe ne correspondent pas.";
            if (!$terms) $errors[] = "Vous devez accepter les conditions générales d'utilisation.";

            // Vérifier si l'email ou le pseudo existe déjà
            if ($this->userModel->findByEmail($email)) {
                $errors[] = "Cet email est déjà utilisé.";
            }
            if ($this->userModel->findByPseudo($pseudo)) {
                $errors[] = "Ce pseudo est déjà utilisé.";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = ['pseudo' => $pseudo, 'email' => $email];
                header('Location: index.php?controller=auth&action=register');
                exit;
            }

            // Créer l'utilisateur
            $activationToken = bin2hex(random_bytes(16));
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $userData = [
                'pseudo' => $pseudo,
                'email' => $email,
                'password_hash' => $passwordHash,
                'activation_token' => $activationToken,
                'is_active' => 0 // Compte non activé par défaut
            ];

            $userId = $this->userModel->create($userData);

            if ($userId) {
                // Envoyer un email d'activation (simulé ici)
                $activationLink = "index.php?controller=auth&action=activate&token=" . $activationToken;

                // Dans un environnement de production, envoyez un véritable email ici
                // mail($email, 'Activation de votre compte', "Cliquez sur ce lien pour activer votre compte: $activationLink");

                $_SESSION['success'] = "Votre compte a été créé avec succès. Un email d'activation a été envoyé à votre adresse. (Lien simulé: $activationLink)";
                header('Location: index.php?controller=auth&action=login');
            } else {
                $_SESSION['error'] = "Erreur lors de la création du compte.";
                $_SESSION['form_data'] = ['pseudo' => $pseudo, 'email' => $email];
                header('Location: index.php?controller=auth&action=register');
            }
            exit;
        }

        // Afficher le formulaire d'inscription
        include __DIR__ . '/../Views/auth/register.php';

    }

    /**
     * Active un compte utilisateur
     */
    public function activate()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token d'activation invalide.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Rechercher l'utilisateur avec ce token d'activation
        $query = "SELECT id FROM users WHERE activation_token = :token";
        $stmt = $this->userModel->getDb()->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Activer le compte
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
     * Déconnexion de l'utilisateur
     */
    public function logout()
    {
        // Détruire la session
        session_unset();
        session_destroy();

        // Rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    }
}