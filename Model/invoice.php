<?php
require_once("order.php");

class Invoice {

    private $invoiceId;
    private $totalAmount;
    private $issueDate;
    private $order; // relation

    public function __construct($invoiceId, $order){
        $this->invoiceId = $invoiceId;
        $this->order = $order;
        $this->issueDate = date("Y-m-d");

        $this->generateInvoice();
    }

    public function generateInvoice(){
        $this->totalAmount = $this->order->calculateTotal();
    }

    public function getTotalAmount(){
        return $this->totalAmount;
    }
}
?>