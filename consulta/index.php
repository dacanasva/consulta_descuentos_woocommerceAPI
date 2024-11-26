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

    // Procesar y mostrar solo los datos deseados
    foreach ($products as $product) {
        $sku = $product->sku;
        $name = $product->name;
        $price = $product->regular_price;
        $sale_price = $product->sale_price;
        $discount_percentage = $sale_price && $price ? round((($price - $sale_price) / $price) * 100, 2) . '%' : 'No aplica';
        $categories = array_map(function ($category) {
            return $category->name;
        }, $product->categories);

        echo "SKU: $sku\n";
        echo "Nombre: $name\n";
        echo "Precio: $price\n";
        echo "Precio rebajado: $sale_price\n";
        echo "Porcentaje de descuento: $discount_percentage\n";
        echo "Categorías: " . implode(', ', $categories) . "\n";
        echo "----------------------\n";
    }
} catch (HttpClientException $e) {
    // Manejo de errores
    echo 'Error: ' . $e->getMessage();
}
