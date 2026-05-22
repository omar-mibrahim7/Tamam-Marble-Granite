<?php
require_once("product.php");

class OrderItem {

    private $product;
    private $quantity;
    private $subtotal;

    public function __construct($product, $quantity){
        $this->product  = $product;
        $this->quantity = $quantity;
        $this->calculateSubtotal();
    }

    public function calculateSubtotal(){
        $this->subtotal = $this->product->getPrice() * $this->quantity;
    }

    public function getSubtotal() { return $this->subtotal; }
    public function getProduct()  { return $this->product; }
    public function getQuantity() { return $this->quantity; }
}
?>