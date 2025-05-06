<?php

/**
 * Fichier index.php - Contrôleur frontal qui sert de routeur pour l'application
 *
 * Ce fichier est le point d'entrée unique de l'application. Il analyse l'URL,
 * détermine quel contrôleur et quelle action appeler, et initialise les objets nécessaires.
 */

// Démarrer la session
session_start();

// Charger la configuration
require_once 'config/config.php';

// Charger les fonctions utilitaires
require_once 'helpers/functions.php';

// Charger les modèles
require_once 'src/models/BaseModel.php';
require_once 'src/models/UserModel.php';
require_once 'src/models/BrandModel.php';
require_once 'src/models/AffiliateLinkModel.php';


// Charger les contrôleurs
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/BrandController.php';
require_once 'src/controllers/AffiliateLinkController.php';
require_once 'controllers/DashboardController.php';

// Établir la connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Initialiser les modèles
$userModel = new UserModel($db);
$brandModel = new BrandModel($db);
$affiliateLinkModel = new AffiliateLinkModel($db);
$userDeviceModel = new UserDeviceModel($db);

// Déterminer le contrôleur et l'action à partir de l'URL
// Exemples d'URL:
// index.php?controller=brand&action=show&id=5
// index.php?controller=affiliateLink&action=create
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'home';
$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Route principale basée sur le contrôleur
switch ($controller) {
    case 'home':
        include 'views/home.php';
        break;

    case 'auth':
        // Actions d'authentification (login, register, logout, etc.)
        $userController = new UserController($userModel);

        switch ($action) {
            case 'login':
                $userController->login();
                break;

            case 'logout':
                $userController->logout();
                break;

            case 'register':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController->store();
                } else {
                    $userController->create();
                }
                break;

            case 'activate':
                $token = $_GET['token'] ?? '';
                $userController->activate($token);
                break;

            default:
                // Action non reconnue
                header('HTTP/1.0 404 Not Found');
                include 'views/404.php';
                break;
        }
        break;

    case 'user':
        // Gestion des utilisateurs (admin)
        $userController = new UserController($userModel);

        switch ($action) {
            case 'index':
                $userController->index();
                break;

            case 'show':
                if ($id) {
                    $userController->show($id);
                } else {
                    header('Location: index.php?controller=user&action=index');
                }
                break;

            case 'create':
                $userController->create();
                break;

            case 'store':
                $userController->store();
                break;

            case 'edit':
                if ($id) {
                    $userController->edit($id);
                } else {
                    header('Location: index.php?controller=user&action=index');
                }
                break;

            case 'update':
                if ($id) {
                    $userController->update($id);
                } else {
                    header('Location: index.php?controller=user&action=index');
                }
                break;

            case 'delete':
                if ($id) {
                    $userController->delete($id);
                } else {
                    header('Location: index.php?controller=user&action=index');
                }
                break;

            default:
                // Action non reconnue
                header('HTTP/1.0 404 Not Found');
                include 'views/404.php';
                break;
        }
        break;

    case 'brand':
        // Gestion des marques
        $brandController = new BrandController($brandModel);

        switch ($action) {
            case 'index':
                $brandController->index();
                break;

            case 'adminindex':
                $brandController->adminIndex();
                break;

            case 'show':
                if ($id) {
                    $brandController->show($id);
                } else {
                    header('Location: index.php?controller=brand&action=index');
                }
                break;

            case 'create':
                $brandController->create();
                break;

            case 'store':
                $brandController->store();
                break;

            case 'edit':
                if ($id) {
                    $brandController->edit($id);
                } else {
                    header('Location: index.php?controller=brand&action=adminindex');
                }
                break;

            case 'update':
                if ($id) {
                    $brandController->update($id);
                } else {
                    header('Location: index.php?controller=brand&action=adminindex');
                }
                break;

            case 'delete':
                if ($id) {
                    $brandController->delete($id);
                } else {
                    header('Location: index.php?controller=brand&action=adminindex');
                }
                break;

            default:
                // Action non reconnue
                header('HTTP/1.0 404 Not Found');
                include 'views/404.php';
                break;
        }
        break;

    case 'affiliatelink':
        // Gestion des liens d'affiliation
        $affiliateLinkController = new AffiliateLinkController($affiliateLinkModel, $brandModel, $userModel);

        switch ($action) {
            case 'index':
                $affiliateLinkController->index();
                break;

            case 'adminindex':
                $affiliateLinkController->adminIndex();
                break;

            case 'show':
                if ($id) {
                    $affiliateLinkController->show($id);
                } else {
                    header('Location: index.php?controller=affiliatelink&action=index');
                }
                break;

            case 'create':
                $affiliateLinkController->create();
                break;

            case 'store':
                $affiliateLinkController->store();
                break;

            case 'edit':
                if ($id) {
                    $affiliateLinkController->edit($id);
                } else {
                    header('Location: index.php?controller=affiliatelink&action=index');
                }
                break;

            case 'update':
                if ($id) {
                    $affiliateLinkController->update($id);
                } else {
                    header('Location: index.php?controller=affiliatelink&action=index');
                }
                break;

            case 'delete':
                if ($id) {
                    $affiliateLinkController->delete($id);
                } else {
                    header('Location: index.php?controller=affiliatelink&action=index');
                }
                break;

            case 'checkexpired':
                $affiliateLinkController->checkExpired();
                break;

            case 'statistics':
                $affiliateLinkController->statistics();
                break;

            case 'export':
                $affiliateLinkController->export();
                break;

            default:
                // Action non reconnue
                header('HTTP/1.0 404 Not Found');
                include 'views/404.php';
                break;
        }
        break;

    case 'dashboard':
        // Tableau de bord utilisateur
        $dashboardController = new DashboardController($userModel, $brandModel, $affiliateLinkModel);

        switch ($action) {
            case 'index':
                $dashboardController->index();
                break;

            case 'profile':
                $dashboardController->profile();
                break;

            case 'updateprofile':
                $dashboardController->updateProfile();
                break;

            case 'changepassword':
                $dashboardController->changePassword();
                break;

            case 'updatepassword':
                $dashboardController->updatePassword();
                break;

            case 'statistics':
                $dashboardController->statistics();
                break;

            default:
                // Action non reconnue
                header('HTTP/1.0 404 Not Found');
                include 'views/404.php';
                break;
        }
        break;

    case 'device':
        // Gestion des appareils utilisateurs (si implémenté)
        if (isset($_SESSION['user'])) {
            $userId = $_SESSION['user']['id'];

            switch ($action) {
                case 'index':
                    $devices = $userDeviceModel->getUserDevices($userId);
                    include 'views/devices/index.php';
                    break;

                case 'remove':
                    if ($id) {
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
                            if ($userDeviceModel->removeDevice($id, $userId)) {
                                $_SESSION['success'] = "Appareil supprimé avec succès.";
                            } else {
                                $_SESSION['error'] = "Erreur lors de la suppression de l'appareil.";
                            }
                            header('Location: index.php?controller=device&action=index');
                            exit;
                        }
                        include 'views/devices/delete.php';
                    } else {
                        header('Location: index.php?controller=device&action=index');
                    }
                    break;

                default:
                    // Action non reconnue
                    header('HTTP/1.0 404 Not Found');
                    include 'views/404.php';
                    break;
            }

            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        break;

    default:
        header('HTTP/1.0 404 Not Found');
        include 'views/404.php';
        break;
}