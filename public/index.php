<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/stripe.php';
global $pdo;
// public/index.php

// 0) Démarrer la session pour gérer les messages flash, la connexion utilisateur, etc.
session_start();

// 1) Charger la connexion PDO
require_once __DIR__ . '/../src/Core/dbconnect.php';    // définit $db (PDO instance)

// 2) Charger les classes (BaseModel, Model, Controller)
require_once __DIR__ . '/../src/Models/BaseModel.php';
require_once __DIR__ . '/../src/Models/BrandModel.php';
require_once __DIR__ . '/../src/Models/UserModel.php';
require_once __DIR__ . '/../src/Models/AffiliateLinkModel.php';
require_once __DIR__ . '/../src/Models/AffiliateCodeModel.php';
require_once __DIR__ . '/../src/Models/BoostModel.php';

require_once __DIR__ . '/../src/Controllers/BrandController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AffiliateLinkController.php';
require_once __DIR__ . '/../src/Controllers/AffiliateCodeController.php';
require_once __DIR__ . '/../src/Controllers/HomeController.php';
require_once __DIR__ . '/../src/Controllers/BoostController.php';


// 3) Instancier les modèles et les contrôleurs
$brandModel      = new BrandModel($pdo);
$userModel       = new UserModel($pdo);
$affiliateLinkModel = new AffiliateLinkModel($pdo);
$affiliateCodeModel = new AffiliateCodeModel($pdo);
$boostModel = new BoostModel($pdo);

$brandController = new BrandController($brandModel);
$userController  = new UserController($userModel, $affiliateLinkModel, $affiliateCodeModel, $brandModel,$boostModel);
$authController  = new AuthController($userModel);
$affiliateLinkController = new AffiliateLinkController($affiliateLinkModel, $brandModel, $userModel);
$affiliateCodeController = new AffiliateCodeController($affiliateCodeModel, $brandModel, $userModel);
$homeController = new HomeController();
$boostController = new BoostController($boostModel, $affiliateLinkModel, $affiliateCodeModel, $userModel,$brandModel);


// 4) Lire les paramètres de routing
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'home';
$action     = isset($_GET['action'])     ? $_GET['action']           : 'index';

// 5) Dispatcher la requête
switch ($controller) {
    case 'home':
        // Gestion de la page d'accueil
        if (method_exists($homeController, $action)) {
            $homeController->{$action}();
        } else {
            // Action inexistante → 404
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur Home.";
        }
        break;

    case 'brand':
        // On garde l'instance $brandController
        if (method_exists($brandController, $action)) {
            // Appel dynamique de l'action
            $brandController->{$action}();
        } else {
            // Action inexistante → 404
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur Brand.";
        }
        break;

    case 'user':
        if (method_exists($userController, $action)) {
            // Appel dynamique de l'action
            $userController->{$action}();
        } else {
            // Action inexistante → 404
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur User.";
        }
        break;

    case 'auth':
        if (method_exists($authController, $action)) {
            // Appel dynamique de l'action
            $authController->{$action}();
        } else {
            // Action inexistante → 404
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur Auth.";
        }
        break;

    case 'affiliatelink':
        if (method_exists($affiliateLinkController, $action)) {
            // Vérifier si le paramètre ID est nécessaire
            if ($action === 'delete' || $action === 'edit' || $action === 'show'||$action === 'update') {
                // Ces actions ont besoin d'un ID
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                if ($id === null) {
                    echo "Erreur: ID manquant pour l'action $action";
                    exit;
                }
                $affiliateLinkController->{$action}($id);
            } else {
                $affiliateLinkController->{$action}();
            }
        } else {
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur AffiliateLink.";
        }
        break;

    case 'affiliatecode':
        if (method_exists($affiliateCodeController, $action)) {
            // Vérifier si le paramètre ID est nécessaire
            if ($action === 'delete' || $action === 'edit' || $action === 'show' || $action === 'update') {
                // Ces actions ont besoin d'un ID
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                if ($id === null) {
                    echo "Erreur: ID manquant pour l'action $action";
                    exit;
                }
                $affiliateCodeController->{$action}($id);
            } else {
                $affiliateCodeController->{$action}();
            }
        } else {
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur AffiliateCode.";
        }
        break;

    case 'bill':
        if ($action === 'generate') {
            require_once __DIR__ . '/../src/Models/BillModel.php';
            require_once __DIR__ . '/../src/Controllers/BillController.php';

            $billModel      = new BillModel($pdo);
            $billController = new BillController($billModel);
            $billController->generate();
        } else {
            http_response_code(404);
            echo "Action « {$action} » introuvable pour BillController.";
        }
        break;

    case 'boost':
        if (method_exists($boostController, $action)) {
            // Actions qui nécessitent des paramètres spéciaux
            if ($action === 'cancel') {
                $id = isset($_GET['id']) ? $_GET['id'] : null;
                if ($id === null) {
                    $_SESSION['error'] = "ID manquant pour annuler le boost";
                    header('Location: index.php?controller=boost&action=history');
                    exit;
                }
                $boostController->cancel($id);
            } else {
                // Toutes les autres actions (showBoostForm, createPaymentIntent, success, history, etc.)
                $boostController->{$action}();
            }
        } else {
            http_response_code(404);
            echo "Action « {$action} » introuvable pour le contrôleur Boost.";
        }
        break;

    default:
        // Contrôleur inconnu → 404
        http_response_code(404);
        echo "Contrôleur « {$controller} » introuvable.";
        break;
}
?>