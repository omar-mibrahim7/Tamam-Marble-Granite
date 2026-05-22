<?php
session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/product.php");

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode([]);
    exit;
}

$results = product::search($conn, $query);

// نضيف الـ image URL لكل منتج
foreach ($results as &$p) {
    $p['image_url'] = product::imageUrl($p['image']);
    // رابط صفحة المنتج حسب النوع
    $p['link'] = $p['product_type'] === 'Marble' ? 'marble.php' : 'granite.php';
}

echo json_encode($results);