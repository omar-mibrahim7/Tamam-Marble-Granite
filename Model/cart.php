<?php
require_once("cartItem.php");

class Cart {

    private $cartId;
    private $cartItems = []; // composition
    private $conn;

    
public function getItems()  { return $this->cartItems; }
public function getCartId() { return $this->cartId; }
public function isEmpty()   { return empty($this->cartItems); }
    public function __construct($cartId = null, $conn = null){
        $this->cartId = $cartId;
        $this->conn = $conn;
    }

    public function addProduct($product){

        $item = new CartItem($product, 1); // quantity = 1
        $this->cartItems[] = $item;
    }

    public function removeProduct($product){

        foreach($this->cartItems as $key => $item){
            if($item->getProduct() == $product){
                unset($this->cartItems[$key]);
            }
        }
    }

    private function requireConnection(){
        if (!$this->conn) {
            throw new Exception("Database connection is required.");
        }
    }

    public function getOrCreateCartId($userId){
        $this->requireConnection();

        $stmt = mysqli_prepare($this->conn, "SELECT cart_id FROM carts WHERE customer_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cart = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($cart) {
            $this->cartId = (int)$cart['cart_id'];
            return $this->cartId;
        }

        $stmt = mysqli_prepare($this->conn, "INSERT INTO carts (customer_id) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->cartId = mysqli_insert_id($this->conn);
        return $this->cartId;
    }

    public function productExists($productId){
        $this->requireConnection();

        $stmt = mysqli_prepare($this->conn, "SELECT product_id FROM products WHERE product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) ? true : false;
        mysqli_stmt_close($stmt);

        return $exists;
    }

    public function getItemsByUser($userId){
        $this->requireConnection();
        $cartId = $this->getOrCreateCartId($userId);

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT
                ci.cart_item_id,
                ci.cart_id,
                ci.product_id,
                ci.quantity,
                ci.subtotal,
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
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             INNER JOIN products p ON p.product_id = ci.product_id
             WHERE c.customer_id = ? AND ci.cart_id = ?
             ORDER BY ci.cart_item_id DESC"
        );
        mysqli_stmt_bind_param($stmt, "ii", $userId, $cartId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $items = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $items;
    }

    public function addItem($userId, $productId, $quantity = 1){
        $this->requireConnection();

        $quantity = max(1, (int)$quantity);
        $cartId = $this->getOrCreateCartId($userId);

        $stmt = mysqli_prepare($this->conn, "SELECT price FROM products WHERE product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$product) {
            return false;
        }

        $price = (float)$product['price'];

        $stmt = mysqli_prepare($this->conn, "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ii", $cartId, $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($item) {
            $cartItemId = (int)$item['cart_item_id'];
            $newQuantity = (int)$item['quantity'] + $quantity;
            $subtotal = $newQuantity * $price;

            $stmt = mysqli_prepare($this->conn, "UPDATE cart_items SET quantity = ?, subtotal = ? WHERE cart_item_id = ? AND cart_id = ?");
            mysqli_stmt_bind_param($stmt, "idii", $newQuantity, $subtotal, $cartItemId, $cartId);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $success;
        }

        $subtotal = $quantity * $price;
        $stmt = mysqli_prepare($this->conn, "INSERT INTO cart_items (cart_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiid", $cartId, $productId, $quantity, $subtotal);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public function removeItem($userId, $productId){
        $this->requireConnection();

        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE ci
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.customer_id = ? AND ci.product_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public function clearCart($userId){
        $this->requireConnection();

        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE ci
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.customer_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    public function countItems($userId){
        $this->requireConnection();

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT COUNT(*) AS total
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.customer_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total'] ?? 0);
    }
}
?>
