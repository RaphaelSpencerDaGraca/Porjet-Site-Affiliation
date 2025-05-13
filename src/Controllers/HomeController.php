<?php
/**
 * Contrôleur pour la page d'accueil
 */
class HomeController {

    /**
     * Affiche la page d'accueil
     */
    public function index() {
        // Inclure directement la vue de la page d'accueil
        include __DIR__ . '/../Views/home.php';
    }
}