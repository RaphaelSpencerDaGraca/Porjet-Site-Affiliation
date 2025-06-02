<?php
require_once('vendor/autoload.php');

$stripe = new \Stripe\StripeClient("sk_test_51RQrRFPpmzvLOWW7RZJ6qcIEvhzW00kpl61pUOhPXtL1jQy4G1yzqdSjWqc0hBWMIOFAZnuI9fX0klm8wh8jbOd400K5Cz0G1B");

$product = $stripe->products->create([
    'name' => 'Boost lien/code',
    'description' => '1€',
]);
echo "Success! Here is your starter subscription product id: " . $product->id . "\n";

$price = $stripe->prices->create([
    'unit_amount' => 1,
    'currency' => 'eur',
    'product' => $product['id'],
]);
echo "Success! Here is your starter subscription price id: " . $price->id . "\n";

?>