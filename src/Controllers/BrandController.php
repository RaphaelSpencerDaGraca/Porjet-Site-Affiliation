<?php

/**
 * Contrôleur pour la gestion des marques
 */
class BrandController
{
    private $brandModel;

    /**
     * Constructeur - initialise le modèle
     */
    public function __construct($brandModel)
    {
        $this->brandModel = $brandModel;
    }

    /**
     * Vérifie si l'utilisateur est connecté et est administrateur
     */
    private function checkAdmin()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Accès refusé. Vous devez être administrateur pour accéder à cette page.";
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Affiche la liste des marques
     */
    public function index()
    {
        // Pour la liste publique des marques, on affiche uniquement les marques actives
        $brands = $this->brandModel->findActive();
        include 'views/brands/index.php';
    }

    /**
     * Affiche la liste complète des marques (pour l'administration)
     */
    public function adminIndex()
    {
        $this->checkAdmin();


        $brands = $this->brandModel->findAll();
        include 'views/admin/brands/index.php';
    }

    /**
     * Affiche les détails d'une marque
     */
    public function show($id)
    {
        $brand = $this->brandModel->findById($id);
        if (!$brand) {
            $_SESSION['error'] = "Marque non trouvée.";
            header('Location: index.php?controller=brand&action=index');
            exit;
        }

        // Si la marque n'est pas active et que l'utilisateur n'est pas admin, rediriger
        if (!$brand['is_active'] && (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')) {
            $_SESSION['error'] = "Cette marque n'est pas disponible.";
            header('Location: index.php?controller=brand&action=index');
            exit;
        }

        include 'views/brands/show.php';
    }

    /**
     * Affiche le formulaire de création d'une marque (admin uniquement)
     */
    public function create()
    {
        $this->checkAdmin();
        include 'views/admin/brands/create.php';
    }

    /**
     * Traite la soumission du formulaire de création
     */
    public function store()
    {
        $this->checkAdmin();

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $websiteUrl = $_POST['website_url'] ?? '';
        $bonus = !empty($_POST['bonus']) ? (int)$_POST['bonus'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (empty($name)) $errors[] = "Le nom de la marque est requis.";

        // Vérifier si le nom existe déjà
        if ($this->brandModel->findByName($name)) {
            $errors[] = "Une marque avec ce nom existe déjà.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?controller=brand&action=create');
            exit;
        }

        $logoUrl = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoUrl = $this->uploadLogo($_FILES['logo']);
            if (!$logoUrl) {
                $_SESSION['error'] = "Erreur lors de l'upload du logo.";
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?controller=brand&action=create');
                exit;
            }
        }


        $brandData = [
            'name' => $name,
            'description' => $description,
            'logo_url' => $logoUrl,
            'website_url' => $websiteUrl,
            'bonus' => $bonus,
            'is_active' => $isActive
        ];

        $brandId = $this->brandModel->create($brandData);

        if ($brandId) {
            $_SESSION['success'] = "Marque créée avec succès.";
            header('Location: index.php?controller=brand&action=adminIndex');
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la marque.";
            header('Location: index.php?controller=brand&action=create');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'une marque (admin uniquement)
     */
    public function edit($id)
    {
        $this->checkAdmin();

        $brand = $this->brandModel->findById($id);
        if (!$brand) {
            $_SESSION['error'] = "Marque non trouvée.";
            header('Location: index.php?controller=brand&action=adminIndex');
            exit;
        }

        include 'views/admin/brands/edit.php';
    }

    /**
     * Traite la soumission du formulaire d'édition
     */
    public function update($id)
    {
        $this->checkAdmin();

        $brand = $this->brandModel->findById($id);
        if (!$brand) {
            $_SESSION['error'] = "Marque non trouvée.";
            header('Location: index.php?controller=brand&action=adminIndex');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $websiteUrl = $_POST['website_url'] ?? '';
        $bonus = !empty($_POST['bonus']) ? (int)$_POST['bonus'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];
        if (empty($name)) $errors[] = "Le nom de la marque est requis.";

        // Vérifier si le nom existe déjà (sauf pour la marque actuelle)
        $existingBrand = $this->brandModel->findByName($name);
        if ($existingBrand && $existingBrand['id'] != $id) {
            $errors[] = "Une autre marque utilise déjà ce nom.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=brand&action=edit&id=' . $id);
            exit;
        }

        // Préparer les données de mise à jour
        $brandData = [
            'name' => $name,
            'description' => $description,
            'website_url' => $websiteUrl,
            'bonus' => $bonus,
            'is_active' => $isActive
        ];

        // Traiter l'upload d'un nouveau logo si présent
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoUrl = $this->uploadLogo($_FILES['logo']);
            if ($logoUrl) {
                $brandData['logo_url'] = $logoUrl;

                // Supprimer l'ancien logo si existant
                if (!empty($brand['logo_url'])) {
                    $this->deleteLogo($brand['logo_url']);
                }
            } else {
                $_SESSION['error'] = "Erreur lors de l'upload du nouveau logo.";
                header('Location: index.php?controller=brand&action=edit&id=' . $id);
                exit;
            }
        }

        // Mettre à jour la marque
        $updated = $this->brandModel->update($id, $brandData);

        if ($updated) {
            $_SESSION['success'] = "Marque mise à jour avec succès.";
            header('Location: index.php?controller=brand&action=adminIndex');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la marque.";
            header('Location: index.php?controller=brand&action=edit&id=' . $id);
        }
        exit;
    }

    /**
     * Supprime une marque (admin uniquement)
     */
    public function delete($id)
    {
        $this->checkAdmin();

        $brand = $this->brandModel->findById($id);
        if (!$brand) {
            $_SESSION['error'] = "Marque non trouvée.";
            header('Location: index.php?controller=brand&action=adminIndex');
            exit;
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {

            if (!empty($brand['logo_url'])) {
                $this->deleteLogo($brand['logo_url']);
            }

            if ($this->brandModel->delete($id)) {
                $_SESSION['success'] = "Marque supprimée avec succès.";
                header('Location: index.php?controller=brand&action=adminIndex');
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de la marque.";
                header('Location: index.php?controller=brand&action=adminIndex');
            }
            exit;
        }


        include 'views/admin/brands/delete.php';
    }

    /**
     * Méthode utilitaire pour uploader un logo
     */
    private function uploadLogo($file)
    {
        $uploadDir = 'uploads/logos/';


        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }


        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('logo_') . '.' . $extension;
        $targetPath = $uploadDir . $filename;


        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }


        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        }

        return false;
    }

    /**
     * Méthode utilitaire pour supprimer un logo
     */
    private function deleteLogo($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
 * Affiche les infos d'une marque par son nom (pour l'URL ?action=showByName&name=...)
 */
    public function showByName()
    {
        $name = $_GET['name'] ?? '';
        // sécurisation minimale
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $brand = $this->brandModel->findByName($name);
        $links = $this->brandModel->findLinksByBrandId((int)$brand['id']);
        $codes = $this->brandModel->findCodesByBrandId((int)$brand['id']);

        // on inclut la vue, qui utilisera $brand
        include __DIR__ . '/../Views/avantages-site.php';
    }

    public function searchSite()
    {
        $brands = $this->brandModel->findActive();
        
        // on inclut la vue, qui utilisera $brand
        include __DIR__ . '/../Views/recherche-site.php';
    }
    
}