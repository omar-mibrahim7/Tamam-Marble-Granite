<?php
require_once("category.php");

class product {

    private $productId;
    private $productName;
    private $productType;
    private $price;
    private $dimensions;
    private $stock;
    private $category;
    private $image;
    private $description;
    private $material;
    private $color;
    private $finish;
    private $thickness;
    private $sizes;
    private $edgeOptions;
    private $waterResistance;
    private $heatResistance;
    private $scratchResistance;
    private $applicationType;

    public function __construct(
        $productId,
        $productName,
        $productType,
        $price,
        $dimensions,
        $stock,
        $category,
        $image = "",
        $description = "",
        $material = "",
        $color = "",
        $finish = "",
        $thickness = "",
        $sizes = "",
        $edgeOptions = "",
        $waterResistance = "",
        $heatResistance = "",
        $scratchResistance = "",
        $applicationType = ""
    ) {
        $this->productId   = $productId;
        $this->productName = $productName;
        $this->productType = $productType;
        $this->price       = $price;
        $this->dimensions  = $dimensions;
        $this->stock       = $stock;
        $this->category    = $category;
        $this->image       = $image;
        $this->description = $description;
        $this->material    = $material;
        $this->color       = $color;
        $this->finish      = $finish;
        $this->thickness   = $thickness;
        $this->sizes       = $sizes;
        $this->edgeOptions = $edgeOptions;
        $this->waterResistance   = $waterResistance;
        $this->heatResistance    = $heatResistance;
        $this->scratchResistance = $scratchResistance;
        $this->applicationType   = $applicationType;
    }

    public function getDetails(){
        return [
            "id" => $this->productId,
            "name" => $this->productName,
            "type" => $this->productType,
            "price" => $this->price,
            "dimensions" => $this->dimensions,
            "stock" => $this->stock,
            "image" => $this->image,
            "description" => $this->description,
            "material" => $this->material,
            "color" => $this->color,
            "finish" => $this->finish,
            "thickness" => $this->thickness,
            "sizes" => $this->sizes,
            "edge_options" => $this->edgeOptions,
            "water_resistance" => $this->waterResistance,
            "heat_resistance" => $this->heatResistance,
            "scratch_resistance" => $this->scratchResistance,
            "application_type" => $this->applicationType
        ];
    }

    public function getId()    { return $this->productId; }
    public function getName()  { return $this->productName; }
    public function getType()  { return $this->productType; }
    public function getPrice() { return $this->price; }
    public function getStock() { return $this->stock; }

    public static function normalizeType($productType){
        $productType = trim((string)$productType);
        return in_array($productType, ['Marble', 'Granite'], true) ? $productType : '';
    }

    public static function imageUrl($image, $fallback = '../pic/product-2.jpg'){
        $image = trim((string)$image);

        if ($image === '') {
            return $fallback;
        }

        $normalized = str_replace('\\', '/', $image);

        if (preg_match('/^https?:\/\//i', $normalized) || strpos($normalized, '/') === 0 || strpos($normalized, '../pic/') === 0) {
            return $normalized;
        }

        if (strpos($normalized, 'View/pic/') === 0) {
            return '../../' . $normalized;
        }

        if (strpos($normalized, 'pic/') === 0) {
            return '../' . $normalized;
        }

        if (strpos($normalized, '/') === false) {
            return '../pic/' . $normalized;
        }

        return $normalized;
    }

    public static function findById($conn, $productId, $includeDeleted = false){
        $sql = "SELECT * FROM products WHERE product_id = ?";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $product ?: null;
    }

    public static function countByType($conn, $productType){
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM products WHERE product_type = ? AND is_deleted = 0");
        mysqli_stmt_bind_param($stmt, "s", $productType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total'] ?? 0);
    }

    public static function findByType($conn, $productType, $limit, $offset){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM products
             WHERE product_type = ? AND is_deleted = 0
             ORDER BY product_id DESC
             LIMIT ? OFFSET ?"
        );
        mysqli_stmt_bind_param($stmt, "sii", $productType, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $products;
    }

    public static function countAll($conn){
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM products WHERE is_deleted = 0");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total'] ?? 0);
    }

    public static function findAll($conn, $limit, $offset){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT p.*, c.category_name
             FROM products p
             LEFT JOIN categories c ON c.category_id = p.category_id
             WHERE p.is_deleted = 0
             ORDER BY p.product_id DESC
             LIMIT ? OFFSET ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $products;
    }

    public static function categoryIdForType($conn, $productType){
        $description = "Natural {$productType} stones";
        $stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $productType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $category = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($category) {
            return (int)$category['category_id'];
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO categories (category_name, description) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $productType, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return (int)mysqli_insert_id($conn);
    }

    public static function create($conn, $data){
        $categoryId = self::categoryIdForType($conn, $data['product_type']);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO products
                (product_name, product_type, price, dimensions, stock, category_id, image, description, material, color, finish, thickness, sizes, edge_options, water_resistance, heat_resistance, scratch_resistance, application_type, is_best_selling)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssdsii" . str_repeat("s", 12) . "i",
            $data['product_name'],
            $data['product_type'],
            $data['price'],
            $data['dimensions'],
            $data['stock'],
            $categoryId,
            $data['image'],
            $data['description'],
            $data['material'],
            $data['color'],
            $data['finish'],
            $data['thickness'],
            $data['sizes'],
            $data['edge_options'],
            $data['water_resistance'],
            $data['heat_resistance'],
            $data['scratch_resistance'],
            $data['application_type'],
            $data['is_best_selling']
        );

        $success = mysqli_stmt_execute($stmt);
        $insertId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        return $success ? $insertId : false;
    }

    public static function updateById($conn, $productId, $data){
        $categoryId = self::categoryIdForType($conn, $data['product_type']);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE products
             SET product_name = ?,
                 product_type = ?,
                 price = ?,
                 dimensions = ?,
                 stock = ?,
                 category_id = ?,
                 image = ?,
                 description = ?,
                 material = ?,
                 color = ?,
                 finish = ?,
                 thickness = ?,
                 sizes = ?,
                 edge_options = ?,
                 water_resistance = ?,
                 heat_resistance = ?,
                 scratch_resistance = ?,
                 application_type = ?,
                 is_best_selling = ?
             WHERE product_id = ? AND is_deleted = 0"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssdsii" . str_repeat("s", 12) . "ii",
            $data['product_name'],
            $data['product_type'],
            $data['price'],
            $data['dimensions'],
            $data['stock'],
            $categoryId,
            $data['image'],
            $data['description'],
            $data['material'],
            $data['color'],
            $data['finish'],
            $data['thickness'],
            $data['sizes'],
            $data['edge_options'],
            $data['water_resistance'],
            $data['heat_resistance'],
            $data['scratch_resistance'],
            $data['application_type'],
            $data['is_best_selling'],
            $productId
        );

        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public static function softDelete($conn, $productId){
        $stmt = mysqli_prepare($conn, "UPDATE products SET is_deleted = 1, deleted_at = NOW() WHERE product_id = ? AND is_deleted = 0");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }
public static function search($conn, $query) {

    $like = "%" . $query . "%";

    $stmt = mysqli_prepare($conn,
        "SELECT product_id, product_name, product_type, image
         FROM products
         WHERE is_deleted = 0
         AND (
            product_name LIKE ?
            OR color LIKE ?
            OR product_type LIKE ?
         )
         LIMIT 8"
    );

    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $products;
}
}
?>