<?php
class category {

    private $categoryId;
    private $categoryName;
    private $description;

    public function __construct($categoryId, $categoryName, $description){
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
        $this->description = $description;
    }

    public function addCategory(){
        // logic
    }

    public function updateCategory(){
        // logic
    }
}
?>