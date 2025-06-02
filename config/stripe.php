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
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51RQrRFPpmzvLOWW7tDSberF5yFVwheeg09rKaNTxZhU4fUp2gZ5WuZ7fBkiCmQfG4uHfLvA6XjiowJdriw46rZ2400CL90suUk');
    define('STRIPE_SECRET_KEY', 'sk_test_51RQrRFPpmzvLOWW7RZJ6qcIEvhzW00kpl61pUOhPXtL1jQy4G1yzqdSjWqc0hBWMIOFAZnuI9fX0klm8wh8jbOd400K5Cz0G1B');
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

