<?php
class Report {

    private $reportId;
    private $reportType;
    private $startDate;
    private $endDate;
    private $generatedDate;
    private $totalProfit;

    public function __construct($reportId, $reportType, $startDate, $endDate, $generatedDate, $totalProfit) {
        $this->reportId = $reportId;
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->generatedDate = $generatedDate;
        $this->totalProfit = $totalProfit;
    }

    public function calculateMonthlyProfit($month, $year){
        // logic
        return $this->totalProfit;
    }

    public function exportToExcel(){
        // logic
    }
}
?>