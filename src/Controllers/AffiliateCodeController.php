<?php

/**
 * Contrôleur pour la gestion des codes d'affiliation
 */
class AffiliateCodeController {
    private $affiliateCodeModel;
    private $brandModel;
    private $userModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($affiliateCodeModel, $brandModel, $userModel) {
        $this->affiliateCodeModel = $affiliateCodeModel;
        $this->brandModel = $brandModel;
        $this->userModel = $userModel;
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
     * Vérifie si l'utilisateur est administrateur
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Accès refusé. Vous devez être administrateur pour accéder à cette page.";
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Affiche la liste des codes d'affiliation de l'utilisateur connecté
     */
    public function index() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $codes = $this->affiliateCodeModel->findByUserId($userId);

        // Rediriger vers la page de profil
        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche les détails d'un code d'affiliation
     */
    public function show($id) {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        if (!$code) {
            $_SESSION['error'] = "Code d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que le code appartient à l'utilisateur ou que l'utilisateur est admin
        if ($code['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Rediriger vers la page de profil avec l'ID du code à modifier
        header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        exit;
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $brandId = $_POST['brand_id'] ?? '';
        $code = $_POST['code'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 1; // Par défaut actif

        // Validation
        $errors = [];
        if (empty($brandId)) $errors[] = "Vous devez sélectionner une marque.";
        if (empty($code)) $errors[] = "Le code d'affiliation ne peut pas être vide.";

        // Vérifier que la marque existe et est active
        if (!empty($brandId)) {
            $brand = $this->brandModel->findById($brandId);
            if (!$brand || !$brand['is_active']) {
                $errors[] = "La marque sélectionnée n'est pas disponible.";
            }
        }

        // Vérifier si ce code existe déjà pour cet utilisateur et cette marque
        if ($this->affiliateCodeModel->exists($userId, $brandId, $code)) {
            $errors[] = "Vous avez déjà ajouté ce code d'affiliation pour cette marque.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $codeData = [
            'user_id' => $userId,
            'brand_id' => $brandId,
            'code' => $code,
            'expiry_date' => $expiryDate,
            'is_active' => $isActive
        ];

        $codeId = $this->affiliateCodeModel->create($codeData);

        if ($codeId) {
            $_SESSION['success'] = "Code d'affiliation créé avec succès.";
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la création du code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'un code d'affiliation
     */
    public function edit($id) {
        $this->checkAuth();

        // Rediriger vers la page de profil avec l'ID du code à éditer
        header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        exit;
    }

    /**
     * Traite la soumission du formulaire d'édition
     * Modifiée pour fonctionner avec ou sans le paramètre ID dans l'URL
     */
    public function update($id = null) {
        $this->checkAuth();

        // Si l'ID n'est pas passé comme paramètre, essayer de le récupérer à partir de POST ou GET
        if ($id === null) {
            $id = $_POST['code_id'] ?? ($_GET['id'] ?? null);
        }

        if (!$id) {
            $_SESSION['error'] = "ID du code d'affiliation manquant.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        if (!$code) {
            $_SESSION['error'] = "Code d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        if ($code['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce code d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $codeValue = $_POST['code'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];
        if (empty($codeValue)) $errors[] = "Le code d'affiliation ne peut pas être vide.";

        // Vérifier si ce code existe déjà pour cet utilisateur et cette marque (sauf pour lui-même)
        if ($codeValue !== $code['code'] && $this->affiliateCodeModel->exists($userId, $code['brand_id'], $codeValue)) {
            $errors[] = "Vous avez déjà un code d'affiliation identique pour cette marque.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
            exit;
        }

        $codeData = [
            'code' => $codeValue,
            'expiry_date' => $expiryDate,
            'is_active' => $isActive
        ];

        $updated = $this->affiliateCodeModel->update($id, $codeData);

        if ($updated) {
            $_SESSION['success'] = "Code d'affiliation mis à jour avec succès.";
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du code d'affiliation.";
            header('Location: index.php?controller=user&action=profile&edit_code=' . $id);
        }
        exit;
    }

    /**
     * Supprime un code d'affiliation
     */
    public function delete($id)
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $code = $this->affiliateCodeModel->findById($id);

        // Vérifier si le code existe et appartient à l'utilisateur connecté
        if (!$code || $code['user_id'] != $userId) {
            $_SESSION['error'] = "Ce code d'affiliation n'existe pas ou vous n'avez pas les droits pour le supprimer.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        if ($this->affiliateCodeModel->delete($id)) {
            $_SESSION['success'] = "Code d'affiliation supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du code d'affiliation.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche tous les codes d'affiliation (admin uniquement)
     */
    public function adminIndex() {
        $this->checkAdmin();

        $brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;

        if ($brandId) {
            $codes = $this->affiliateCodeModel->findByBrandId($brandId);
            $brand = $this->brandModel->findById($brandId);
            $pageTitle = "Codes d'affiliation pour la marque " . $brand['name'];
        } else {
            $query = "SELECT a.*, u.pseudo as user_pseudo, b.name as brand_name
                    FROM affiliate_codes a
                    JOIN users u ON a.user_id = u.id
                    JOIN brands b ON a.brand_id = b.id
                    ORDER BY a.created_at DESC";
            $stmt = $this->affiliateCodeModel->getDb()->prepare($query);
            $stmt->execute();
            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pageTitle = "Tous les codes d'affiliation";
        }

        $brands = $this->brandModel->findAll();

        include __DIR__ . '/../Views/admin/affiliate_codes/index.php';
    }

    /**
     * Vérifie et désactive les codes d'affiliation expirés
     * Cette méthode peut être appelée par un cron job
     */
    public function checkExpired() {
        $count = $this->affiliateCodeModel->deactivateExpired();

        if (isset($_GET['admin']) && $_GET['admin'] == 1) {
            $this->checkAdmin();
            $_SESSION['success'] = "$count codes d'affiliation expirés ont été désactivés.";
            header('Location: index.php?controller=affiliateCode&action=adminIndex');
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'deactivated_count' => $count]);
        exit;
    }

    /**
     * Génère des statistiques sur les codes d'affiliation (admin uniquement)
     */
    public function statistics() {
        $this->checkAdmin();

        $query = "SELECT b.name as brand_name, COUNT(a.id) as code_count
                FROM brands b
                LEFT JOIN affiliate_codes a ON b.id = a.brand_id
                GROUP BY b.id
                ORDER BY code_count DESC";
        $stmt = $this->affiliateCodeModel->getDb()->prepare($query);
        $stmt->execute();
        $brandStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT u.pseudo as user_name, COUNT(a.id) as code_count
                FROM users u
                JOIN affiliate_codes a ON u.id = a.user_id
                GROUP BY u.id
                ORDER BY code_count DESC
                LIMIT 10";
        $stmt = $this->affiliateCodeModel->getDb()->prepare($query);
        $stmt->execute();
        $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT 
                    COUNT(*) as total_codes,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_codes,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_codes,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT brand_id) as unique_brands
                FROM affiliate_codes";
        $stmt = $this->affiliateCodeModel->getDb()->prepare($query);
        $stmt->execute();
        $generalStats = $stmt->fetch(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/affiliate_codes/statistics.php';
    }

    /**
     * Exporte les codes d'affiliation au format CSV (admin uniquement)
     */
    public function export() {
        $this->checkAdmin();

        $brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;

        if ($brandId) {
            $codes = $this->affiliateCodeModel->findByBrandId($brandId);
            $brand = $this->brandModel->findById($brandId);
            $filename = "codes_affiliation_" . $this->slugify($brand['name']) . "_" . date('Y-m-d') . ".csv";
        } else {
            // Récupérer tous les codes d'affiliation avec les noms d'utilisateurs et de marques
            $query = "SELECT a.*, u.pseudo as user_pseudo, u.email as user_email, b.name as brand_name
                    FROM affiliate_codes a
                    JOIN users u ON a.user_id = u.id
                    JOIN brands b ON a.brand_id = b.id
                    ORDER BY a.created_at DESC";
            $stmt = $this->affiliateCodeModel->getDb()->prepare($query);
            $stmt->execute();
            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "tous_codes_affiliation_" . date('Y-m-d') . ".csv";
        }

        $output = fopen('php://output', 'w');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Utilisateur',
            'Email',
            'Marque',
            'Code',
            'Date de création',
            'Date d\'expiration',
            'Actif'
        ]);

        foreach ($codes as $code) {
            fputcsv($output, [
                $code['id'],
                $code['user_pseudo'] ?? '',
                $code['user_email'] ?? '',
                $code['brand_name'] ?? '',
                $code['code'],
                $code['created_at'],
                $code['expiry_date'] ?? 'Pas d\'expiration',
                $code['is_active'] ? 'Oui' : 'Non'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Convertit une chaîne en slug (pour les noms de fichiers)
     */
    private function slugify($text) {
        // Remplacer les caractères non alphanumériques par des tirets
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Translittération
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Supprimer les caractères indésirables
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Supprimer les tirets en début et fin
        $text = trim($text, '-');

        // Convertir en minuscules
        $text = strtolower($text);

        return $text ?: 'n-a';
    }
}