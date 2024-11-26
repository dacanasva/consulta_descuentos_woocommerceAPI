<?php

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

// Configura el cliente de WooCommerce
$woocommerce = new Client(
    'http://localhost:8080/prueba/', // URL de tu tienda
    'ck_c4d2196a60f90e91deedebf0dfcd29957e2130f3', // Tu Consumer Key
    'cs_3d0d0dede462ab5b35d248b4205ec50f945566d7', // Tu Consumer Secret
    [
        'wp_api' => true,
        'version' => 'wc/v3', 
    ]
);

try {
    // Obtener productos
    $products = $woocommerce->get('products');
    print_r($products);
} catch (HttpClientException $e) {
    // Manejo de errores
    echo 'Error: ' . $e->getMessage();
}