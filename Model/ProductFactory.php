<?php
require_once(__DIR__ . "/product.php");
require_once(__DIR__ . "/category.php");

class ProductFactory {
    public static function fromRow($row){
        $category = new category(
            $row['category_id'] ?? null,
            $row['category_name'] ?? ($row['product_type'] ?? ''),
            $row['category_description'] ?? ''
        );

        return new product(
            $row['product_id'] ?? null,
            $row['product_name'] ?? '',
            $row['product_type'] ?? '',
            $row['price'] ?? 0,
            $row['dimensions'] ?? '',
            $row['stock'] ?? 0,
            $category,
            $row['image'] ?? '',
            $row['description'] ?? '',
            $row['material'] ?? '',
            $row['color'] ?? '',
            $row['finish'] ?? '',
            $row['thickness'] ?? '',
            $row['sizes'] ?? '',
            $row['edge_options'] ?? '',
            $row['water_resistance'] ?? '',
            $row['heat_resistance'] ?? '',
            $row['scratch_resistance'] ?? '',
            $row['application_type'] ?? ''
        );
    }
}
?>
