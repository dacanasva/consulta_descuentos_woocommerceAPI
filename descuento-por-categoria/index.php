<?php

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$woocommerce = new Client(
    'http://localhost:8080/prueba/',
    'ck_b9b5800c725e421a83bb60cdcdc4508a0646759e',
    'cs_89008f77b2a456d58b695972e94652983c516fbe', 
    [
        'wp_api' => true,
        'version' => 'wc/v3',
    ]
);

try {

    $categories = $woocommerce->get('products/categories');

    if (empty($categories)) {
        echo "No se encontraron categorías.\n";
        exit;
    }
    echo "Categorías disponibles:\n";

    foreach ($categories as $index => $category) {
        echo "[$index] {$category->name}\n";
    }

    echo "Selecciona el número de la categoría a la que deseas aplicar el descuento: ";
    $selectedIndex = trim(fgets(STDIN));

    if (!isset($categories[$selectedIndex])) {
        echo "Selección inválida. Por favor, inténtalo de nuevo.\n";
        exit;
    }
    $selectedCategory = $categories[$selectedIndex];
    echo "Has seleccionado la categoría: {$selectedCategory->name}\n";

    echo "Introduce el porcentaje de descuento (por ejemplo, 20 para un 20%): ";
    $discountPercentage = trim(fgets(STDIN));

    echo "Introduce la fecha y hora de inicio del descuento (formato YYYY-MM-DD HH:MM): ";
    $startDateTime = trim(fgets(STDIN));
    $startDateTime = date('Y-m-d\TH:i:s', strtotime($startDateTime));

    echo "Introduce la fecha y hora de finalización del descuento (formato YYYY-MM-DD HH:MM): ";
    $endDateTime = trim(fgets(STDIN));
    $endDateTime = date('Y-m-d\TH:i:s', strtotime($endDateTime));

    $products = $woocommerce->get('products', ['category' => $selectedCategory->id]);

    if (empty($products)) {
        echo "No se encontraron productos en la categoría seleccionada.\n";
        exit;
    }

    echo "Aplicando un {$discountPercentage}% de descuento a los productos de la categoría desde {$startDateTime} hasta {$endDateTime}...\n";

    foreach ($products as $product) {
        $originalPrice = $product->regular_price;
        $newPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        $woocommerce->put("products/{$product->id}", [
            'sale_price' => number_format($newPrice, 2, '.', ''),
            'date_on_sale_from' => $startDateTime,
            'date_on_sale_to' => $endDateTime
        ]);
        echo "Producto actualizado: {$product->name}, Nuevo precio: $newPrice\n";
    }
    echo "Descuento aplicado con éxito a todos los productos de la categoría seleccionada.\n";

} catch (HttpClientException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
