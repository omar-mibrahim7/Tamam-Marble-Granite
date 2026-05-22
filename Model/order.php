<?php
require_once("orderItem.php");
require_once("invoice.php");

class Order {

    private $orderId;
    private $orderDate;
    private $status;

    private $orderItems = []; // composition
    private $invoice;         // composition

    public function __construct($orderId, $orderDate){
        $this->orderId   = $orderId;
        $this->orderDate = $orderDate;
        $this->status    = "pending";
        $this->invoice   = new Invoice(null, $this);
    }

    public function addItem($product, $quantity){
        $item = new OrderItem($product, $quantity);
        $this->orderItems[] = $item;
    }

    public function updateStatus($status){
        $this->status = $status;
    }

    public function calculateTotal(){
        $total = 0;
        foreach($this->orderItems as $item){
            $total += $item->getSubtotal();
        }
        return $total;
    }

    // --- Getters ---
    public function getOrderId()   { return $this->orderId; }
    public function getOrderDate() { return $this->orderDate; }
    public function getStatus()    { return $this->status; }
    public function getItems()     { return $this->orderItems; }
}
?>