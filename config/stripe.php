<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Configuration Stripe
 * Remplacez les clés par vos vraies clés Stripe
 */

// Mode test ou production
define('STRIPE_MODE', 'test'); // 'test' ou 'live'

if (STRIPE_MODE === 'test') {
    // Clés de test
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_votre_cle_publique_test');
    define('STRIPE_SECRET_KEY', 'sk_test_votre_cle_secrete_test');
} else {
    // Clés de production
    define('STRIPE_PUBLISHABLE_KEY', 'pk_live_votre_cle_publique_live');
    define('STRIPE_SECRET_KEY', 'sk_live_votre_cle_secrete_live');
}

// Webhook endpoint secret (optionnel pour les webhooks)
define('STRIPE_WEBHOOK_SECRET', 'whsec_votre_secret_webhook');

// Configuration produit Stripe
define('STRIPE_BOOST_PRODUCT_ID', 'prod_SLYfJyKa1hJG6G');
define('STRIPE_BOOST_PRICE', 100); // Prix en centimes (1.00 EUR = 100 centimes)
define('STRIPE_CURRENCY', 'eur');

