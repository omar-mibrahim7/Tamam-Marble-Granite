<?php
require_once("user.php");
require_once("order.php");

class Staff extends User {

    private $position;
    private $orders = []; // relation

    public function __construct($userId, $fullName, $email, $password, $phone, $address, $position) {
        parent::__construct($userId, $fullName, $email, $password, $phone, $address);
        $this->position = $position;
    }

    public function viewPersonalOrders(){}

    public function viewCompanyOrders(){}

    public function viewOrderDetails($orderId){}

    public function updateOrderStatus($orderId, $status){
        // مثال logic بسيط
        foreach($this->orders as $order){
            if($order->getId() == $orderId){
                $order->updateStatus($status);
            }
        }
    }

    public function addProduct(){}

    public function editProduct($productId){}

    public function deleteProduct($productId){}

    public function generateInvoice($orderId){}

    public function requestEngineerVisit($orderId){}
}
?>