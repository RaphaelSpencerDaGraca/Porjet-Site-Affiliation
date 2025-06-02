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

        // Préparer les données pour la vue
        $userData = $this->userModel->findById($userId);

        // Définir les variables directement dans la portée globale
        global $itemType, $itemId, $itemName, $brandName, $activeBoostCount, $user;

        $user = $userData;

        // Inclure la vue du formulaire de boost
        include __DIR__ . '/../Views/boost.php';
    }

    /**
     * Crée un PaymentIntent Stripe
     */
    public function createPaymentIntent() {
        $this->checkAuth();

        // Définir le header pour JSON
        header('Content-Type: application/json');

        try {
            // Récupérer les données JSON
            $input = json_decode(file_get_contents('php://input'), true);

            $itemType = $input['item_type'] ?? '';
            $itemId = $input['item_id'] ?? '';
            $cardData = $input['card_data'] ?? null;

            if (empty($itemType) || empty($itemId)) {
                throw new Exception('Données manquantes');
            }

            $userId = $_SESSION['user']['id'];

            // Vérifications de sécurité (similaires à processBoost)
            if ($this->boostModel->countActiveByUserId($userId) >= 3) {
                throw new Exception('Vous avez déjà 3 boosts actifs');
            }

            if ($this->boostModel->isItemBoosted($itemType, $itemId)) {
                throw new Exception('Cet élément est déjà boosté');
            }

            // Vérifier que l'élément appartient à l'utilisateur
            $item = null;
            if ($itemType === 'link') {
                $item = $this->affiliateLinkModel->findById($itemId);
            } elseif ($itemType === 'code') {
                $item = $this->affiliateCodeModel->findById($itemId);
            }

            if (!$item || $item['user_id'] != $userId) {
                throw new Exception('Élément non trouvé ou non autorisé');
            }

            // Créer une session Stripe en base de données
            $sessionId = uniqid('boost_', true);
            $this->createStripeSession($sessionId, $userId, $itemType, $itemId, 1.00);

            // Préparer les données de paiement
            $paymentData = [
                'amount' => 100, // 1.00 EUR en centimes
                'currency' => 'eur',
                'metadata' => [
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                ],
            ];

            // Si nous avons des données de carte, créer une PaymentMethod d'abord
            if ($cardData) {
                // Créer une PaymentMethod avec les données de carte
                $paymentMethod = \Stripe\PaymentMethod::create([
                    'type' => 'card',
                    'card' => [
                        'number' => $cardData['number'],
                        'exp_month' => $cardData['exp_month'],
                        'exp_year' => $cardData['exp_year'],
                        'cvc' => $cardData['cvc'],
                    ],
                    'billing_details' => [
                        'name' => $cardData['name'],
                    ],
                ]);

                // Créer le PaymentIntent avec la PaymentMethod
                $paymentData['payment_method'] = $paymentMethod->id;
                $paymentData['confirmation_method'] = 'manual';
                $paymentData['confirm'] = true;
                $paymentData['return_url'] = 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?controller=boost&action=success';
            }

            // Créer le PaymentIntent
            $paymentIntent = PaymentIntent::create($paymentData);

            // Gérer la réponse selon le statut
            if ($paymentIntent->status === 'succeeded') {
                // Paiement immédiatement réussi
                $this->processSuccessfulPayment(
                    $this->getStripeSession($sessionId),
                    $paymentIntent->id
                );

                echo json_encode([
                    'success' => true,
                    'redirect_url' => 'index.php?controller=user&action=profile'
                ]);
            } elseif ($paymentIntent->status === 'requires_action') {
                // Nécessite une action supplémentaire (3D Secure)
                echo json_encode([
                    'requires_action' => true,
                    'client_secret' => $paymentIntent->client_secret
                ]);
            } else {
                // Autres cas
                echo json_encode([
                    'client_secret' => $paymentIntent->client_secret,
                    'session_id' => $sessionId
                ]);
            }
            
        } catch (\Stripe\Exception\CardException $e) {
            // Erreur de carte spécifique
            echo json_encode(['error' => $this->getCardErrorMessage($e->getError()->code)]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Autres erreurs Stripe
            echo json_encode(['error' => 'Erreur de paiement: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Traduit les codes d'erreur Stripe en messages français
     */
    private function getCardErrorMessage($errorCode) {
        $messages = [
            'card_declined' => 'Votre carte a été refusée.',
            'expired_card' => 'Votre carte a expiré.',
            'incorrect_cvc' => 'Le code de sécurité de votre carte est incorrect.',
            'incorrect_number' => 'Le numéro de votre carte est incorrect.',
            'insufficient_funds' => 'Votre carte ne dispose pas de fonds suffisants.',
            'invalid_cvc' => 'Le code de sécurité de votre carte est invalide.',
            'invalid_expiry_month' => 'Le mois d\'expiration de votre carte est invalide.',
            'invalid_expiry_year' => 'L\'année d\'expiration de votre carte est invalide.',
            'invalid_number' => 'Le numéro de votre carte est invalide.',
            'processing_error' => 'Une erreur s\'est produite lors du traitement de votre carte.',
        ];

        return $messages[$errorCode] ?? 'Erreur de carte bancaire.';
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
            // Récupérer le PaymentIntent depuis Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                $sessionId = $paymentIntent->metadata->session_id;
                $userId = $_SESSION['user']['id'];

                // Vérifier si ce paiement a déjà été traité
                $session = $this->getStripeSession($sessionId);

                if ($session && $session['status'] === 'pending' && $session['user_id'] == $userId) {
                    // Traiter le boost
                    $this->processSuccessfulPayment($session, $paymentIntentId);
                    $_SESSION['success'] = "Votre élément a été boosté avec succès pour 7 jours !";
                } else {
                    $_SESSION['error'] = "Ce paiement a déjà été traité ou est invalide.";
                }
            } else {
                $_SESSION['error'] = "Le paiement n'a pas été complété.";
            }

        } catch (ApiErrorException $e) {
            $_SESSION['error'] = "Erreur lors de la vérification du paiement.";
            error_log("Erreur Stripe: " . $e->getMessage());
        }

        header('Location: index.php?controller=user&action=profile');
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
     * Traite un paiement réussi
     */
    private function processSuccessfulPayment($session, $paymentIntentId) {
        try {
            $userId = $session['user_id'];
            $itemType = $session['item_type'];
            $itemId = $session['item_id'];

            // Dates de début et de fin du boost (7 jours)
            $startDate = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Créer le boost
            $boostData = [
                'user_id' => $userId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'amount' => 1.00,
                'payment_id' => $paymentIntentId
            ];

            $boostId = $this->boostModel->create($boostData);

            if ($boostId) {
                // Enregistrer la transaction
                $transactionData = [
                    'user_id' => $userId,
                    'amount' => 1.00,
                    'type' => 'boost',
                    'status' => 'completed',
                    'payment_method' => 'card',
                    'reference_id' => $paymentIntentId,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'boost_id' => $boostId
                ];

                $this->boostModel->createTransaction($transactionData);

                // Mettre à jour l'élément pour indiquer qu'il est boosté
                if ($itemType === 'link') {
                    $this->affiliateLinkModel->update($itemId, [
                        'is_boosted' => 1,
                        'boost_end_date' => $endDate
                    ]);
                } elseif ($itemType === 'code') {
                    $this->affiliateCodeModel->update($itemId, [
                        'is_boosted' => 1,
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
     * Traite l'achat d'un boost (version PayPal ou autre)
     */
    public function processBoost() {
        $this->checkAuth();
        $userId = $_SESSION['user']['id'];

        // Récupérer les données du formulaire
        $itemType = $_POST['item_type'] ?? '';
        $itemId = $_POST['item_id'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'card';

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
        // (code existant pour PayPal)
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
    }
}