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

$csvPath = readline("Introduce la ruta del archivo CSV: ");

if (!file_exists($csvPath)) {
    echo "Archivo CSV no encontrado: {$csvPath}\n";
    exit;
}

$startDate = readline("Introduce la fecha de inicio (Formato: YYYY-MM-DD HH:MM): ");
$startDateTime = DateTime::createFromFormat('Y-m-d H:i', $startDate);
if (!$startDateTime) {
    echo "Formato de fecha inválido. Use 'YYYY-MM-DD HH:MM'.\n";
    exit;
}

$endDate = readline("Introduce la fecha de fin (Formato: YYYY-MM-DD HH:MM): ");
$endDateTime = DateTime::createFromFormat('Y-m-d H:i', $endDate);
if (!$endDateTime) {
    echo "Formato de fecha inválido. Use 'YYYY-MM-DD HH:MM'.\n";
    exit;
}

if ($startDateTime > $endDateTime) {
    echo "La fecha de inicio no puede ser mayor que la fecha de fin.\n";
    exit;
}

try {
    $file = fopen($csvPath, 'r');

    if ($file === false) {
        echo "No se pudo abrir el archivo CSV.\n";
        exit;
    }
    $headers = fgetcsv($file);
    if (!$headers || !in_array('sku', $headers) || !in_array('descuento', $headers)) {
        echo "El archivo CSV debe tener las columnas 'sku' y 'descuento'.\n";
        fclose($file);
        exit;
    }
    while (($row = fgetcsv($file)) !== false) {
        $data = array_combine($headers, $row);
        $sku = $data['sku'];
        $discountPercentage = (float)$data['descuento'];

        $products = $woocommerce->get('products', ['sku' => $sku]);
        if (empty($products)) {
            echo "Producto con SKU {$sku} no encontrado.\n";
            continue;
        }
        $product = $products[0];

        $originalPrice = $product->regular_price;
        $newPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        $woocommerce->put("products/{$product->id}", [
            'sale_price' => number_format($newPrice, 2, '.', ''),
            'date_on_sale_from' => $startDateTime->format('Y-m-d\TH:i:s'),
            'date_on_sale_to' => $endDateTime->format('Y-m-d\TH:i:s'),
        ]);
        echo "Producto actualizado: SKU {$sku}, Nuevo precio: {$newPrice}, Descuento desde {$startDate} hasta {$endDate}.\n";
    }
    fclose($file);
    echo "Procesamiento de descuentos completado.\n";

} catch (HttpClientException $e) {
    echo 'Error en la API de WooCommerce: ' . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo 'Error general: ' . $e->getMessage() . "\n";
}
