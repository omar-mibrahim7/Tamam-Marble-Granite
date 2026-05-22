<?php
require_once("product.php");

class CartItem {

    private $product;   // relation
    private $quantity;
    private $subtotal;
public function getQuantity() { return $this->quantity; }
public function getSubtotal() { return $this->subtotal; }
    public function __construct($product, $quantity){
        $this->product = $product;
        $this->quantity = $quantity;
        $this->calculateSubtotal();
    }

    public function calculateSubtotal(){
        $this->subtotal = $this->product->getPrice() * $this->quantity;
    }

    public function updateQuantity($quantity){
        $this->quantity = $quantity;
        $this->calculateSubtotal();
    }

    public function getProduct(){
        return $this->product;
    }
}
?>