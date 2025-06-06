<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Chemin vers Composer autoload

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

/**
 * Contrôleur pour la gestion des boosts
 */
class BoostController {
    private $boostModel;
    private $affiliateLinkModel;
    private $affiliateCodeModel;
    private $userModel;
    private $brandModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($boostModel, $affiliateLinkModel, $affiliateCodeModel, $userModel, $brandModel) {
        $this->boostModel = $boostModel;
        $this->affiliateLinkModel = $affiliateLinkModel;
        $this->affiliateCodeModel = $affiliateCodeModel;
        $this->userModel = $userModel;
        $this->brandModel = $brandModel;

        // Configurer Stripe
        Stripe::setApiKey(STRIPE_SECRET_KEY);
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
     * Affiche le formulaire de boost
     */
    public function showBoostForm() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer les paramètres depuis l'URL
        $itemType = isset($_GET['item_type']) ? $_GET['item_type'] : '';
        $itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

        // Validation des paramètres d'entrée
        if (empty($itemType) || empty($itemId)) {
            $_SESSION['error'] = "Paramètres manquants pour le boost.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si $itemType est valide
        if ($itemType !== 'link' && $itemType !== 'code') {
            $_SESSION['error'] = "Type d'élément invalide.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'utilisateur a déjà 3 boosts actifs
        $activeBoostCount = $this->boostModel->countActiveByUserId($userId);
        if ($activeBoostCount >= 3) {
            $_SESSION['error'] = "Vous avez déjà 3 boosts actifs. Vous ne pouvez pas en ajouter davantage.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier si l'élément est déjà boosté
        if ($this->boostModel->isItemBoosted($itemType, $itemId)) {
            $_SESSION['error'] = "Cet élément est déjà boosté.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Récupérer les informations de l'élément à booster
        $item = null;
        $itemName = '';
        $brandName = '';

        if ($itemType === 'link') {
            $item = $this->affiliateLinkModel->findById($itemId);
            if ($item) {
                $itemName = "Lien d'affiliation: " . $item['custom_link'];
                if (!empty($item['brand_id'])) {
                    $brand = $this->brandModel->findById($item['brand_id']);
                    if ($brand) {
                        $brandName = $brand['name'];
                    }
                }
            }
        } elseif ($itemType === 'code') {
            $item = $this->affiliateCodeModel->findById($itemId);
            if ($item) {
                $itemName = "Code promo: " . $item['code'];
                if (!empty($item['brand_id'])) {
                    $brand = $this->brandModel->findById($item['brand_id']);
                    if ($brand) {
                        $brandName = $brand['name'];
                    }
                }
            }
        }

        if (!$item) {
            $_SESSION['error'] = "L'élément à booster n'existe pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que l'élément appartient à l'utilisateur
        if ($item['user_id'] != $userId) {
            $_SESSION['error'] = "Vous ne pouvez pas booster cet élément car il ne vous appartient pas.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Récupérer les données de l'utilisateur
        $user = $this->userModel->findById($userId);

        // IMPORTANT : Définir les variables avant d'inclure la vue
        // Ces variables seront disponibles dans boost.php
        $itemType        = $itemType;
        $itemId          = $itemId;
        $itemName        = $itemName;
        $brandName       = $brandName;
        $activeBoostCount = $activeBoostCount;
        $user            = $user;

        // Inclure la vue du formulaire de boost
        include __DIR__ . '/../Views/boost.php';
    }

    /**
     * Crée un PaymentIntent Stripe
     */
    public function createPaymentIntent() {
        // Empêcher toute sortie HTML
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Forcer le header JSON immédiatement
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            // Vérifier que nous sommes bien dans une requête AJAX
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                !isset($_SERVER['HTTP_CONTENT_TYPE']) &&
                strpos($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/json') === false) {
                // Ce n'est pas une requête AJAX/JSON
                http_response_code(400);
                die(json_encode(['error' => 'Requête invalide - JSON attendu']));
            }

            // Démarrer la session si nécessaire
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Vérifier la session utilisateur
            if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
                http_response_code(401);
                die(json_encode(['error' => 'Session expirée. Veuillez vous reconnecter.']));
            }

            // Récupérer les données JSON
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                http_response_code(400);
                die(json_encode(['error' => 'Aucune donnée reçue']));
            }

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                die(json_encode(['error' => 'Données JSON invalides']));
            }

            // Extraire et valider les données
            $itemType = isset($input['item_type']) ? trim($input['item_type']) : '';
            $itemId   = isset($input['item_id'])   ? intval($input['item_id'])   : 0;

            if (empty($itemType) || empty($itemId)) {
                http_response_code(400);
                die(json_encode(['error' => 'Paramètres manquants']));
            }

            if (!in_array($itemType, ['link', 'code'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Type d\'élément invalide']));
            }

            $userId = intval($_SESSION['user']['id']);

            // Vérifications métier
            $activeCount = $this->boostModel->countActiveByUserId($userId);
            if ($activeCount >= 3) {
                http_response_code(400);
                die(json_encode(['error' => 'Vous avez déjà 3 boosts actifs']));
            }

            if ($this->boostModel->isItemBoosted($itemType, $itemId)) {
                http_response_code(400);
                die(json_encode(['error' => 'Cet élément est déjà boosté']));
            }

            // Vérifier l'élément
            $item = null;
            if ($itemType === 'link') {
                $item = $this->affiliateLinkModel->findById($itemId);
            } elseif ($itemType === 'code') {
                $item = $this->affiliateCodeModel->findById($itemId);
            }

            if (!$item || $item['user_id'] != $userId) {
                http_response_code(403);
                die(json_encode(['error' => 'Élément non trouvé ou non autorisé']));
            }

            // Créer une session unique
            $sessionId = 'boost_' . uniqid() . '_' . time();

            // Enregistrer dans la base de données
            global $pdo;
            if (!$pdo) {
                http_response_code(500);
                die(json_encode(['error' => 'Erreur de connexion à la base de données']));
            }

            $query = "INSERT INTO stripe_sessions (session_id, user_id, item_type, item_id, amount, status) 
                      VALUES (:session_id, :user_id, :item_type, :item_id, :amount, 'pending')";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([
                ':session_id' => $sessionId,
                ':user_id'    => $userId,
                ':item_type'  => $itemType,
                ':item_id'    => $itemId,
                ':amount'     => 1.00
            ]);

            if (!$result) {
                http_response_code(500);
                die(json_encode(['error' => 'Erreur lors de l\'enregistrement de la session']));
            }

            // Configurer Stripe
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            // Créer le PaymentIntent
            try {
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount'      => 100, // 1.00 EUR en centimes
                    'currency'    => 'eur',
                    'description' => 'Boost ' . $itemType . ' #' . $itemId,
                    'metadata'    => [
                        'session_id' => $sessionId,
                        'user_id'    => strval($userId),
                        'item_type'  => $itemType,
                        'item_id'    => strval($itemId),
                    ],
                ]);

                // Succès - retourner le JSON
                echo json_encode([
                    'success'       => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'id'            => $paymentIntent->id,
                    'session_id'    => $sessionId
                ]);
            } catch (\Stripe\Exception\CardException $e) {
                // Erreur de carte spécifique
                echo json_encode(['error' => $this->getCardErrorMessage($e->getError()->code)]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Autres erreurs Stripe
                echo json_encode(['error' => 'Erreur de paiement: ' . $e->getMessage()]);
            } catch (Exception $e) {
                http_response_code(500);
                error_log('Erreur createPaymentIntent: ' . $e->getMessage());
                die(json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]));
            }
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Erreur createPaymentIntent générale: ' . $e->getMessage());
            die(json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]));
        }
    }

    /**
     * Retourne un message d'erreur en français pour les codes d'erreur Stripe
     */
    private function getCardErrorMessage($errorCode) {
        $messages = [
            'card_declined'         => 'Votre carte a été refusée',
            'incorrect_cvc'         => 'Le code de sécurité (CVV) est incorrect',
            'expired_card'          => 'Votre carte a expiré',
            'insufficient_funds'    => 'Fonds insuffisants sur votre carte',
            'incorrect_number'      => 'Le numéro de carte est incorrect',
            'invalid_cvc'           => 'Le code de sécurité (CVV) est invalide',
            'invalid_expiry_month'  => 'Le mois d\'expiration est invalide',
            'invalid_expiry_year'   => 'L\'année d\'expiration est invalide',
            'invalid_number'        => 'Le numéro de carte est invalide',
            'processing_error'      => 'Une erreur s\'est produite lors du traitement',
            'card_declined_generic' => 'Votre carte a été refusée pour une raison inconnue',
        ];

        return isset($messages[$errorCode]) 
            ? $messages[$errorCode] 
            : 'Erreur de paiement. Veuillez vérifier vos informations.';
    }

    /**
     * Crée une session Stripe en base de données
     */
    private function createStripeSession($sessionId, $userId, $itemType, $itemId, $amount) {
        try {
            $query = "INSERT INTO stripe_sessions (session_id, user_id, item_type, item_id, amount, status) 
                      VALUES (:session_id, :user_id, :item_type, :item_id, :amount, 'pending')";
            $stmt = $this->boostModel->getDb()->prepare($query);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':item_type', $itemType);
            $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur création session Stripe: " . $e->getMessage());
            throw new Exception("Erreur lors de la création de la session");
        }
    }

    /**
     * Gère le succès du paiement
     */
    public function success() {
        $this->checkAuth();

        $paymentIntentId = $_GET['payment_intent'] ?? '';

        if (empty($paymentIntentId)) {
            $_SESSION['error'] = "Paiement non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        try {
            // Configurer Stripe
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            // Récupérer le PaymentIntent depuis Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                // Récupérer les métadonnées
                $sessionId = $paymentIntent->metadata->session_id;
                $userId    = $_SESSION['user']['id'];
                $itemType  = $paymentIntent->metadata->item_type;
                $itemId    = $paymentIntent->metadata->item_id;

                // Vérifier si ce paiement a déjà été traité
                global $pdo;

                // Vérifier d'abord si le boost existe déjà
                $query = "SELECT id FROM boosts WHERE payment_id = :payment_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':payment_id', $paymentIntentId);
                $stmt->execute();

                if ($stmt->fetch()) {
                    // Déjà traité
                    $_SESSION['success'] = "Votre boost est déjà actif !";
                    header('Location: index.php?controller=user&action=profile');
                    exit;
                }

                // Récupérer la session Stripe
                $query = "SELECT * FROM stripe_sessions WHERE session_id = :session_id AND user_id = :user_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':session_id', $sessionId);
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
                $session = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($session && $session['status'] === 'pending') {
                    // Commencer une transaction
                    $pdo->beginTransaction();

                    try {
                        // 1. Créer le boost
                        $startDate = date('Y-m-d H:i:s');
                        $endDate   = date('Y-m-d H:i:s', strtotime('+7 days'));

                        $boostData = [
                            'user_id'    => $userId,
                            'item_type'  => $itemType,
                            'item_id'    => $itemId,
                            'start_date' => $startDate,
                            'end_date'   => $endDate,
                            'status'     => 'active',
                            'amount'     => 1.00,
                            'payment_id' => $paymentIntentId
                        ];

                        $boostId = $this->boostModel->create($boostData);

                        if (!$boostId) {
                            throw new Exception("Erreur lors de la création du boost");
                        }

                        // 2. Créer la transaction
                        $query = "INSERT INTO transactions (user_id, amount, type, status, payment_method, reference_id, item_type, item_id, boost_id) 
                                  VALUES (:user_id, :amount, :type, :status, :payment_method, :reference_id, :item_type, :item_id, :boost_id)";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([
                            ':user_id'       => $userId,
                            ':amount'        => 1.00,
                            ':type'          => 'boost',
                            ':status'        => 'completed',
                            ':payment_method'=> 'card',
                            ':reference_id'  => $paymentIntentId,
                            ':item_type'     => $itemType,
                            ':item_id'       => $itemId,
                            ':boost_id'      => $boostId
                        ]);

                        // 3. Mettre à jour l'élément boosté
                        if ($itemType === 'link') {
                            $query = "UPDATE affiliate_links SET is_boosted = 1, boost_end_date = :end_date WHERE id = :id";
                        } else {
                            $query = "UPDATE affiliate_codes SET is_boosted = 1, boost_end_date = :end_date WHERE id = :id";
                        }
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':end_date', $endDate);
                        $stmt->bindParam(':id', $itemId);
                        $stmt->execute();

                        // 4. Mettre à jour la session Stripe
                        $query = "UPDATE stripe_sessions SET status = 'completed', updated_at = NOW() WHERE session_id = :session_id";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':session_id', $sessionId);
                        $stmt->execute();

                        // Valider la transaction
                        $pdo->commit();

                        $_SESSION['success'] = "Votre élément a été boosté avec succès pour 7 jours !";
                    } catch (Exception $e) {
                        // Annuler la transaction en cas d'erreur
                        $pdo->rollBack();
                        throw $e;
                    }
                } else {
                    $_SESSION['error'] = "Session de paiement invalide ou déjà traitée.";
                }
            } else {
                $_SESSION['error'] = "Le paiement n'a pas été complété. Statut: " . $paymentIntent->status;
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $_SESSION['error'] = "Erreur lors de la vérification du paiement: " . $e->getMessage();
            error_log("Erreur Stripe dans success(): " . $e->getMessage());
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors du traitement: " . $e->getMessage();
            error_log("Erreur dans success(): " . $e->getMessage());
        }

        // À la fin de success(), juste après avoir validé la transaction et mis $_SESSION['success'] :
        header('Location: index.php?controller=bill&action=generate');

        exit;
    }

    /**
     * Récupère une session Stripe depuis la base de données
     */
    private function getStripeSession($sessionId) {
        try {
            $query = "SELECT * FROM stripe_sessions WHERE session_id = :session_id";
            $stmt = $this->boostModel->getDb()->prepare($query);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Traite un paiement réussi (utilisé en interne)
     */
    private function processSuccessfulPayment($session, $paymentIntentId) {
        try {
            $userId   = $session['user_id'];
            $itemType = $session['item_type'];
            $itemId   = $session['item_id'];

            // Dates de début et de fin du boost (7 jours)
            $startDate = date('Y-m-d H:i:s');
            $endDate   = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Créer le boost
            $boostData = [
                'user_id'    => $userId,
                'item_type'  => $itemType,
                'item_id'    => $itemId,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => 'active',
                'amount'     => 1.00,
                'payment_id' => $paymentIntentId
            ];

            $boostId = $this->boostModel->create($boostData);

            if ($boostId) {
                // Enregistrer la transaction
                $transactionData = [
                    'user_id'       => $userId,
                    'amount'        => 1.00,
                    'type'          => 'boost',
                    'status'        => 'completed',
                    'payment_method'=> 'card',
                    'reference_id'  => $paymentIntentId,
                    'item_type'     => $itemType,
                    'item_id'       => $itemId,
                    'boost_id'      => $boostId
                ];

                $this->boostModel->createTransaction($transactionData);

                // Mettre à jour l'élément pour indiquer qu'il est boosté
                if ($itemType === 'link') {
                    $this->affiliateLinkModel->update($itemId, [
                        'is_boosted'     => 1,
                        'boost_end_date' => $endDate
                    ]);
                } elseif ($itemType === 'code') {
                    $this->affiliateCodeModel->update($itemId, [
                        'is_boosted'     => 1,
                        'boost_end_date' => $endDate
                    ]);
                }

                // Marquer la session comme complétée
                $this->updateStripeSession($session['session_id'], 'completed');
            }
        } catch (Exception $e) {
            error_log("Erreur traitement paiement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Met à jour le statut d'une session Stripe
     */
    private function updateStripeSession($sessionId, $status) {
        try {
            $query = "UPDATE stripe_sessions SET status = :status, updated_at = NOW() WHERE session_id = :session_id";
            $stmt = $this->boostModel->getDb()->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur mise à jour session: " . $e->getMessage());
        }
    }

    /**
     * Traite l'achat d'un boost (version simplifiée)
     */
    public function processBoost() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer les données du formulaire
        $itemType     = $_POST['item_type']     ?? '';
        $itemId       = $_POST['item_id']       ?? '';
        $paymentMethod= $_POST['payment_method'] ?? 'card';

        // Vérifier les données
        if (empty($itemType) || empty($itemId)) {
            $_SESSION['error'] = "Données manquantes pour le boost.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Si c'est un paiement par carte, rediriger vers Stripe
        if ($paymentMethod === 'card') {
            header('Location: index.php?controller=boost&action=showBoostForm&item_type=' . $itemType . '&item_id=' . $itemId);
            exit;
        }

        // Traitement pour PayPal ou autres méthodes...
        // (code existant pour PayPal peut être ajouté ici)
    }

    /**
     * Affiche l'historique des boosts de l'utilisateur
     */
    public function history() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Mettre à jour le statut des boosts (expire les boosts en fin de validité)
        $this->boostModel->updateStatus();

        // Récupérer l'historique des boosts
        $boosts = $this->boostModel->findAllByUserId($userId);

        // Récupérer les informations de l'utilisateur
        $user = $this->userModel->findById($userId);

        // Inclure la vue
        include __DIR__ . '/../Views/boost/history.php';
    }

    /**
     * Annule un boost actif
     */
    public function cancel($boostId) {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer le boost
        $boost = $this->boostModel->findById($boostId);

        if (!$boost) {
            $_SESSION['error'] = "Le boost spécifié n'existe pas.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Vérifier que le boost appartient à l'utilisateur
        if ($boost['user_id'] != $userId) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à annuler ce boost.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Vérifier que le boost est actif
        if ($boost['status'] !== 'active') {
            $_SESSION['error'] = "Ce boost n'est pas actif et ne peut pas être annulé.";
            header('Location: index.php?controller=boost&action=history');
            exit;
        }

        // Annuler le boost
        $this->boostModel->update($boostId, [
            'status' => 'cancelled'
        ]);

        // Mettre à jour l'élément associé
        if ($boost['item_type'] === 'link') {
            $this->affiliateLinkModel->update($boost['item_id'], [
                'is_boosted'     => 0,
                'boost_end_date' => null
            ]);
        } elseif ($boost['item_type'] === 'code') {
            $this->affiliateCodeModel->update($boost['item_id'], [
                'is_boosted'     => 0,
                'boost_end_date' => null
            ]);
        }

        $_SESSION['success'] = "Le boost a été annulé avec succès.";
        header('Location: index.php?controller=boost&action=history');
        exit;
    }
}
