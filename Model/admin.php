<?php
require_once("user.php");
require_once("report.php");

class Admin extends User {

    private $role;
    private $reports = []; // relation

    public function __construct($userId, $fullName, $email, $password, $phone, $address, $role) {
        parent::__construct($userId, $fullName, $email, $password, $phone, $address);
        $this->role = $role;
    }

    public function viewPersonalRevenue(){}

    public function viewCompanyRevenue(){}

    public function calculateMonthlyFactoryProfit($month, $year){}

    public function exportConfirmedOrdersToExcel(){}

    public function addStaff($position){}

    public function deleteStaff($staffId){}
}
?>