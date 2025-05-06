<?php
// public/index.php

// 0) Démarrer la session pour gérer les messages flash, la connexion utilisateur, etc.
session_start();

// 1) Charger la connexion PDO
require_once __DIR__ . '/../src/Core/dbconnect.php';    // définit $db (PDO instance)

// 2) Charger les classes (BaseModel, Model, Controller)
require_once __DIR__ . '/../src/Models/BaseModel.php';
require_once __DIR__ . '/../src/Models/BrandModel.php';
require_once __DIR__ . '/../src/Models/UserModel.php';
require_once __DIR__ . '/../src/Controllers/BrandController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

// 3) Instancier les modèles et les contrôleurs
$brandModel      = new BrandModel($pdo);
$userModel       = new UserModel($pdo);
$brandController = new BrandController($brandModel);
$userController  = new UserController($userModel);
$authController  = new AuthController($userModel);

// 4) Lire les paramètres de routing
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'brand';
$action     = isset($_GET['action'])     ? $_GET['action']           : 'index';

// 5) Dispatcher la requête
switch ($controller) {
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

    default:
        // Contrôleur inconnu → 404
        http_response_code(404);
        echo "Contrôleur « {$controller} » introuvable.";
        break;
}