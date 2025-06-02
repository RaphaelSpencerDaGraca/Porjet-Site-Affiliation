<?php

/**
 * Contrôleur pour la gestion des liens d'affiliation
 */
class AffiliateLinkController {
    private $affiliateLinkModel;
    private $brandModel;
    private $userModel;
    private $boostModel;

    /**
     * Constructeur - initialise les modèles
     */
    public function __construct($affiliateLinkModel, $brandModel, $userModel, $boostModel ) {
        $this->affiliateLinkModel = $affiliateLinkModel;
        $this->brandModel = $brandModel;
        $this->userModel = $userModel;
        $this->boostModel = $boostModel; 
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
     * Affiche la liste des liens d'affiliation de l'utilisateur connecté
     */
    public function index() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $links = $this->affiliateLinkModel->findByUserId($userId);

        // Rediriger vers la page de profil
        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche les détails d'un lien d'affiliation
     */
    public function show($id) {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $link = $this->affiliateLinkModel->findById($id);

        if (!$link) {
            $_SESSION['error'] = "Lien d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que le lien appartient à l'utilisateur ou que l'utilisateur est admin
        if ($link['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce lien d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Rediriger vers la page de profil
        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store() {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $brandId = $_POST['brand_id'] ?? '';
        $customLink = $_POST['custom_link'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

        // Validation
        $errors = [];
        if (empty($brandId)) $errors[] = "Vous devez sélectionner une marque.";

        // Vérifier que la marque existe et est active
        if (!empty($brandId)) {
            $brand = $this->brandModel->findById($brandId);
            if (!$brand || !$brand['is_active']) {
                $errors[] = "La marque sélectionnée n'est pas disponible.";
            }
        }

        // Vérifier si un lien existe déjà pour cet utilisateur et cette marque
        if ($this->affiliateLinkModel->exists($userId, $brandId)) {
            $errors[] = "Vous avez déjà un lien d'affiliation pour cette marque.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=user&action=profile');
            exit;
        }


        $linkData = [
            'user_id' => $userId,
            'brand_id' => $brandId,
            'custom_link' => $customLink,
            'expiry_date' => $expiryDate
        ];

        $linkId = $this->affiliateLinkModel->create($linkData);

        if ($linkId) {
            $_SESSION['success'] = "Lien d'affiliation créé avec succès.";

            // Correction: rediriger vers le profil via UserController pour s'assurer que $user est défini
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la création du lien d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'un lien d'affiliation
     */
    public function edit($id) {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $link = $this->affiliateLinkModel->findById($id);

        if (!$link) {
            $_SESSION['error'] = "Lien d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Vérifier que le lien appartient à l'utilisateur ou que l'utilisateur est admin
        if ($link['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce lien d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        // Rediriger vers la page de profil avec l'ID du lien à éditer
        header('Location: index.php?controller=user&action=profile&edit_link=' . $id);
        exit;
    }

    /**
     * Traite la soumission du formulaire d'édition
     */
    public function update($id) {
        $this->checkAuth();

        $userId = $_SESSION['user']['id'];
        $link = $this->affiliateLinkModel->findById($id);

        if (!$link) {
            $_SESSION['error'] = "Lien d'affiliation non trouvé.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }


        if ($link['user_id'] != $userId && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas accès à ce lien d'affiliation.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        $customLink = $_POST['custom_link'] ?? '';
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;


        $linkData = [
            'custom_link' => $customLink,
            'expiry_date' => $expiryDate,
            'is_active' => $isActive
        ];

        $updated = $this->affiliateLinkModel->update($id, $linkData);

        if ($updated) {
            $_SESSION['success'] = "Lien d'affiliation mis à jour avec succès.";
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du lien d'affiliation.";
            header('Location: index.php?controller=affiliateLink&action=edit&id=' . $id);
        }
        exit;
    }

    /**
     * Supprime un lien d'affiliation
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
        $link = $this->affiliateLinkModel->findById($id);

        // Vérifier si le lien existe et appartient à l'utilisateur connecté
        if (!$link || $link['user_id'] != $userId) {
            $_SESSION['error'] = "Ce lien d'affiliation n'existe pas ou vous n'avez pas les droits pour le supprimer.";
            header('Location: index.php?controller=user&action=profile');
            exit;
        }

        if ($this->affiliateLinkModel->delete($id)) {
            $this->boostModel->deleteByItem('link', $id);
            $_SESSION['success'] = "Lien d'affiliation supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du lien d'affiliation.";
        }

        header('Location: index.php?controller=user&action=profile');
        exit;
    }

    /**
     * Affiche tous les liens d'affiliation (admin uniquement)
     */
    public function adminIndex() {
        $this->checkAdmin();


        $brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;

        if ($brandId) {
            $links = $this->affiliateLinkModel->findByBrandId($brandId);
            $brand = $this->brandModel->findById($brandId);
            $pageTitle = "Liens d'affiliation pour la marque " . $brand['name'];
        } else {

            $query = "SELECT a.*, u.pseudo as user_pseudo, b.name as brand_name
                    FROM affiliate_links a
                    JOIN users u ON a.user_id = u.id
                    JOIN brands b ON a.brand_id = b.id
                    ORDER BY a.created_at DESC";
            $stmt = $this->affiliateLinkModel->getDb()->prepare($query);
            $stmt->execute();
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pageTitle = "Tous les liens d'affiliation";
        }


        $brands = $this->brandModel->findAll();

        include __DIR__ . '/../Views/admin/affiliate_links/index.php';
    }

    /**
     * Vérifie et désactive les liens d'affiliation expirés
     * Cette méthode peut être appelée par un cron job
     */
    public function checkExpired() {
        $count = $this->affiliateLinkModel->deactivateExpired();

        if (isset($_GET['admin']) && $_GET['admin'] == 1) {
            $this->checkAdmin();
            $_SESSION['success'] = "$count liens d'affiliation expirés ont été désactivés.";
            header('Location: index.php?controller=affiliateLink&action=adminIndex');
            exit;
        }


        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'deactivated_count' => $count]);
        exit;
    }

    /**
     * Génère des statistiques sur les liens d'affiliation (admin uniquement)
     */
    public function statistics() {
        $this->checkAdmin();


        $query = "SELECT b.name as brand_name, COUNT(a.id) as link_count
                FROM brands b
                LEFT JOIN affiliate_links a ON b.id = a.brand_id
                GROUP BY b.id
                ORDER BY link_count DESC";
        $stmt = $this->affiliateLinkModel->getDb()->prepare($query);
        $stmt->execute();
        $brandStats = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $query = "SELECT u.pseudo as user_name, COUNT(a.id) as link_count
                FROM users u
                JOIN affiliate_links a ON u.id = a.user_id
                GROUP BY u.id
                ORDER BY link_count DESC
                LIMIT 10";
        $stmt = $this->affiliateLinkModel->getDb()->prepare($query);
        $stmt->execute();
        $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $query = "SELECT 
                    COUNT(*) as total_links,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_links,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_links,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT brand_id) as unique_brands
                FROM affiliate_links";
        $stmt = $this->affiliateLinkModel->getDb()->prepare($query);
        $stmt->execute();
        $generalStats = $stmt->fetch(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/affiliate_links/statistics.php';
    }

    /**
     * Exporte les liens d'affiliation au format CSV (admin uniquement)
     */
    public function export() {
        $this->checkAdmin();


        $brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;

        if ($brandId) {
            $links = $this->affiliateLinkModel->findByBrandId($brandId);
            $brand = $this->brandModel->findById($brandId);
            $filename = "liens_affiliation_" . $this->slugify($brand['name']) . "_" . date('Y-m-d') . ".csv";
        } else {
            // Récupérer tous les liens d'affiliation avec les noms d'utilisateurs et de marques
            $query = "SELECT a.*, u.pseudo as user_pseudo, u.email as user_email, b.name as brand_name
                    FROM affiliate_links a
                    JOIN users u ON a.user_id = u.id
                    JOIN brands b ON a.brand_id = b.id
                    ORDER BY a.created_at DESC";
            $stmt = $this->affiliateLinkModel->getDb()->prepare($query);
            $stmt->execute();
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "tous_liens_affiliation_" . date('Y-m-d') . ".csv";
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
            'Lien personnalisé',
            'Date de création',
            'Date d\'expiration',
            'Actif'
        ]);


        foreach ($links as $link) {
            fputcsv($output, [
                $link['id'],
                $link['user_pseudo'] ?? '',
                $link['user_email'] ?? '',
                $link['brand_name'] ?? '',
                $link['custom_link'],
                $link['created_at'],
                $link['expiry_date'] ?? 'Pas d\'expiration',
                $link['is_active'] ? 'Oui' : 'Non'
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