<?php
function product_admin_upload_image($fieldName, &$error){
    $error = '';

    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        $error = 'image';
        return false;
    }

    if ((int)$_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
        $error = 'image_size';
        return false;
    }

    $originalName = (string)$_FILES[$fieldName]['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($extension, $allowedExtensions, true)) {
        $error = 'image_type';
        return false;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES[$fieldName]['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimeTypes, true)) {
            $error = 'image_type';
            return false;
        }
    }

    $uploadDir = __DIR__ . "/../View/pic/products";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $error = 'image_save';
        return false;
    }

    $fileName = 'product_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
        $error = 'image_save';
        return false;
    }

    return 'pic/products/' . $fileName;
}

function product_admin_collect_data($currentImage = ''){
    $productName = trim($_POST['product_name'] ?? '');
    $productType = Product::normalizeType($_POST['product_type'] ?? '');
    $priceRaw = trim((string)($_POST['price'] ?? '0'));
    $priceRaw = str_replace(',', '.', $priceRaw);
    $stock = max(0, (int)($_POST['stock'] ?? 0));

    if ($productName === '' || strlen($productName) > 150) {
        return [null, 'name'];
    }

    if ($productType === '') {
        return [null, 'type'];
    }

    if ($priceRaw === '' || !is_numeric($priceRaw) || (float)$priceRaw < 0) {
        return [null, 'price'];
    }

    $uploadError = '';
    $uploadedImage = product_admin_upload_image('product_image', $uploadError);
    if ($uploadedImage === false) {
        return [null, $uploadError];
    }

    $image = $uploadedImage !== '' ? $uploadedImage : $currentImage;

    $data = [
        'product_name' => $productName,
        'product_type' => $productType,
        'price' => (float)$priceRaw,
        'dimensions' => trim($_POST['slab_size'] ?? ($_POST['dimensions'] ?? '')),
        'stock' => $stock,
        'image' => $image,
        'description' => trim($_POST['description'] ?? ''),
        'material' => trim($_POST['material'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'finish' => trim($_POST['finish'] ?? ''),
        'thickness' => trim($_POST['thickness'] ?? ''),
        'sizes' => trim($_POST['sizes'] ?? ''),
        'edge_options' => trim($_POST['edge_options'] ?? ''),
        'water_resistance' => trim($_POST['water_resistance'] ?? ''),
        'heat_resistance' => trim($_POST['heat_resistance'] ?? ''),
        'scratch_resistance' => trim($_POST['scratch_resistance'] ?? ''),
        'application_type' => trim($_POST['application_type'] ?? ''),
        'is_best_selling' => isset($_POST['is_best_selling']) && (string)$_POST['is_best_selling'] === '1' ? 1 : 0
    ];

    return [$data, ''];
}
?>
