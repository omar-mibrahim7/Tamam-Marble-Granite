<?php
class Wishlist {

    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    private function productExists($productId){
        $stmt = mysqli_prepare($this->conn, "SELECT product_id FROM products WHERE product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) ? true : false;
        mysqli_stmt_close($stmt);

        return $exists;
    }

    public function getItemsByUser($userId){
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT
                wi.customer_id,
                wi.product_id,
                wi.created_at,
                p.product_name,
                p.product_type,
                p.price,
                p.dimensions,
                p.stock,
                p.image,
                p.description,
                p.material,
                p.color,
                p.finish,
                p.thickness,
                p.sizes,
                p.edge_options,
                p.water_resistance,
                p.heat_resistance,
                p.scratch_resistance,
                p.application_type
             FROM wishlist_items wi
             INNER JOIN products p ON p.product_id = wi.product_id
             WHERE wi.customer_id = ?
             ORDER BY wi.created_at DESC, wi.product_id DESC"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $items = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $items;
    }

    public function toggleItem($userId, $productId){
        if (!$this->productExists($productId)) {
            return false;
        }

        if ($this->isInWishlist($userId, $productId)) {
            return $this->removeItem($userId, $productId) ? 'removed' : false;
        }

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO wishlist_items (customer_id, product_id) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success ? 'added' : false;
    }

    public function removeItem($userId, $productId){
        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE FROM wishlist_items WHERE customer_id = ? AND product_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public function isInWishlist($userId, $productId){
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT 1 FROM wishlist_items WHERE customer_id = ? AND product_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) ? true : false;
        mysqli_stmt_close($stmt);

        return $exists;
    }
}
?>
