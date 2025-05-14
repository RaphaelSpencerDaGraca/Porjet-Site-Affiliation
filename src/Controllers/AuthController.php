<?php
/**
 * Contrôleur pour la gestion de l'authentification
 */
class AuthController {
    private $userModel;

    /**
     * Constructeur - initialise le modèle utilisateur
     */
    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function register() {
        // Si l'utilisateur est déjà connecté, rediriger vers le profil
        if (isset($_SESSION['user'])) {
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];

            if (empty($pseudo)) {
                $errors[] = "Le pseudo est requis.";
            } elseif (strlen($pseudo) < 3 || strlen($pseudo) > 50) {
                $errors[] = "Le pseudo doit contenir entre 3 et 50 caractères.";
            }

            if (empty($email)) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            }

            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            } elseif (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            // Vérifier si le pseudo existe déjà
            if (!empty($pseudo) && $this->userModel->findByPseudo($pseudo)) {
                $errors[] = "Ce pseudo est déjà utilisé.";
            }

            // Vérifier si l'email existe déjà
            if (!empty($email) && $this->userModel->findByEmail($email)) {
                $errors[] = "Cet email est déjà utilisé.";
            }

            if (empty($errors)) {
                // Hacher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Créer un token d'activation si nécessaire
                $activationToken = bin2hex(random_bytes(16)); // 32 caractères

                // Créer l'utilisateur
                $userData = [
                    'pseudo' => $pseudo,
                    'email' => $email,
                    'password_hash' => $hashedPassword, // Utiliser 'password' selon la structure de la BD
                    'is_active' => 1, // Actif par défaut
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'activation_token' => $activationToken
                ];

                $userId = $this->userModel->create($userData);

                if ($userId) {
                    // Récupérer l'utilisateur créé
                    $user = $this->userModel->findById($userId);

                    // Connecter l'utilisateur
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'pseudo' => $user['pseudo'],
                        'email' => $user['email'],
                        'is_active' => $user['is_active'],
                        'profile_picture' => $user['profile_picture'] ?? null
                    ];

                    $_SESSION['success'] = "Inscription réussie. Bienvenue " . $user['pseudo'] . " !";
                    header('Location: index.php?controller=user&action=profile');
                    exit;
                } else {
                    $errors[] = "Une erreur est survenue lors de l'inscription.";
                }
            }

            // S'il y a des erreurs, les stocker pour les afficher dans la vue
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST; // Pour repopuler le formulaire
                header('Location: index.php?controller=auth&action=register');
                exit;
            }
        }

        // Afficher la vue d'inscription
        include __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, rediriger vers le profil
        if (isset($_SESSION['user'])) {
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            // Validation
            $errors = [];

            if (empty($email)) {
                $errors[] = "L'email est requis.";
            }

            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            }

            if (empty($errors)) {
                // Récupérer l'utilisateur par son email
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) { // Utiliser 'password' ici aussi
                    // Vérifier si le compte est actif
                    if (!$user['is_active']) {
                        $errors[] = "Votre compte n'est pas actif. Veuillez l'activer en cliquant sur le lien dans l'email que nous vous avons envoyé.";
                    } else {
                        // Connecter l'utilisateur
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'pseudo' => $user['pseudo'],
                            'email' => $user['email'],
                            'is_active' => $user['is_active'],
                            'profile_picture' => $user['profile_picture'] ?? null
                        ];

                        if ($remember) {
                            // Si "Se souvenir de moi" est coché, créer un cookie qui dure 30 jours
                            $token = bin2hex(random_bytes(32));
                            // Stocker le token en base de données avec l'ID de l'utilisateur et une date d'expiration
                            // ...

                            // Définir le cookie
                            setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                        }

                        $_SESSION['success'] = "Connexion réussie. Bienvenue " . $user['pseudo'] . " !";
                        header('Location: index.php?controller=user&action=profile');
                        exit;
                    }
                } else {
                    $errors[] = "Email ou mot de passe incorrect.";
                }
            }

            // S'il y a des erreurs, les stocker pour les afficher dans la vue
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST; // Pour repopuler le formulaire
                header('Location: index.php?controller=auth&action=login');
                exit;
            }
        }

        // Afficher la vue de connexion
        include __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        // Supprimer le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        // Supprimer le cookie "Se souvenir de moi"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 42000, '/');
            // Supprimer également le token de la base de données
            // ...
        }

        // Détruire la session
        session_destroy();

        // Rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    }

    /**
     * Active le compte utilisateur
     */
    public function activate() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token d'activation manquant.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Chercher l'utilisateur avec ce token
        $query = "SELECT * FROM users WHERE activation_token = :token";
        $stmt = $this->userModel->getDb()->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Token d'activation invalide.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Activer le compte
        $data = [
            'is_active' => 1,
            'activation_token' => null // Effacer le token après activation
        ];

        if ($this->userModel->update($user['id'], $data)) {
            $_SESSION['success'] = "Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'activation de votre compte.";
        }

        header('Location: index.php?controller=auth&action=login');
        exit;
    }
}