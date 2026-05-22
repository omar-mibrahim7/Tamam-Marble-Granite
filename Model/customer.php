<?php
require_once("user.php");
require_once("bookingRequest.php");
//require_once("cart.php");
require_once("contactMessage.php");

class Customer extends User {

    private $cart;
    private $bookingRequests = [];
    private $messages = [];

    public function __construct($userId, $fullName, $email, $password, $phone, $address) {
    parent::__construct($userId, $fullName, $email, $password, $phone, $address);

    //$this->cart = new Cart(null); // أو أي id
}

    public function register(){}

    public function forgotPassword(){}

    public function resetPassword(){}

    public function searchForProducts(){}

    public function navigateWebsiteMenu(){}

    public function displayBestSellingProducts(){}

    public function displayMarbleProducts(){}

    public function displayGraniteProducts(){}

    public function displayProductCount(){}

    public function viewProductDetails(){}

    public function addToCart($product){
        $this->cart->addProduct($product);
    }

    public function removeProductFromCart(){}

    public function booking($product){

    $booking = new BookingRequest(
        null,                // bookingId
        date("Y-m-d"),       // bookingDate
        $product             // relation
    );

    $this->bookingRequests[] = $booking;
}
    public function enterProductDimensions(){}

    public function submitBookingInformation(){}

    public function trackOrder(){}

    public function viewFavorites(){}

    public function removeFromWishlist(){}

    public function rateProducts(){}

   public function sendMessage($subject, $body){

    $msg = new ContactMessage(
        null,
        $subject,
        $body,
        date("Y-m-d"),
        $this->userId
    );

    $this->messages[] = $msg;
}
}
?>