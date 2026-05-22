<?php
require_once("order.php");
require_once("product.php");

class BookingRequest {

    private $bookingId;
    private $bookingDate;
    private $dimensions;
    private $notes;
    private $engineerRequested;
    private $status;

    private $product; // relation
    private $order;   // relation
    private $conn;
    private $lastError;
public function getBookingId()         { return $this->bookingId; }
public function getDimensions()        { return $this->dimensions; }
public function isEngineerRequested()  { return $this->engineerRequested; }
public function getStatus()            { return $this->status; }
public function getLastError()          { return $this->lastError; }
    public function __construct($bookingId = null, $bookingDate = null, $product = null, $conn = null){
        $this->bookingId = $bookingId;
        $this->bookingDate = $bookingDate;
        $this->product = $product;
        $this->conn = $conn;
        $this->engineerRequested = false;
        $this->status = "pending";
        $this->lastError = "";
    }

    public function booking(){
        // logic
    }

    public function enterProductDimensions($dimensions){
        $this->dimensions = $dimensions;
    }

    public function submitBookingInformation(){
        // logic
    }

    public function requestEngineerVisit(){
        $this->engineerRequested = true;
    }

   public function createOrder(){

    $this->order = new Order(
        null,              // orderId
        date("Y-m-d")      // orderDate
    );

    return $this->order;
}

    private function cleanDecimal($value){
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return null;
        }

        return number_format((float)$value, 2, '.', '');
    }

    public function createBooking($userId, $data, $cartItems, $measurements){
        if (!$this->conn) {
            $this->lastError = "Database connection is required.";
            return false;
        }

        if (empty($cartItems)) {
            $this->lastError = "Cart is empty.";
            return false;
        }

        $firstItem = $cartItems[0];
        $firstProductId = (int)$firstItem['product_id'];
        $bookingDate = date("Y-m-d");
        $dimensions = "See booking request items";
        $status = "pending";
        $engineerRequested = !empty($data['needs_engineering_visit']) ? 1 : 0;
        $notes = $data['notes'] ?? '';
        $fullName = $data['full_name'] ?? '';
        $phone = $data['phone'] ?? '';
        $whatsapp = $data['whatsapp_number'] ?? '';
        $city = $data['city'] ?? '';
        $area = $data['area'] ?? '';

        mysqli_begin_transaction($this->conn);

        try {
            $stmt = mysqli_prepare(
                $this->conn,
                "INSERT INTO booking_requests
                    (customer_id, product_id, booking_date, dimensions, notes, engineer_requested, status, full_name, phone, whatsapp, city, area)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                throw new Exception(mysqli_error($this->conn));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "iisssissssss",
                $userId,
                $firstProductId,
                $bookingDate,
                $dimensions,
                $notes,
                $engineerRequested,
                $status,
                $fullName,
                $phone,
                $whatsapp,
                $city,
                $area
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }

            $bookingId = mysqli_insert_id($this->conn);
            mysqli_stmt_close($stmt);

            $itemStmt = mysqli_prepare(
                $this->conn,
                "INSERT INTO booking_request_items
                    (booking_id, product_id, product_name, requested_length, requested_width, quantity)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if (!$itemStmt) {
                throw new Exception(mysqli_error($this->conn));
            }

   foreach ($cartItems as $item) {
    $productId = (int)$item['product_id'];
    $productName = $item['product_name'];
    $quantity = max(1, (int)$item['quantity']);

    $lengths = $measurements['length'][$productId] ?? [];
    $widths  = $measurements['width'][$productId] ?? [];

    if (!is_array($lengths)) $lengths = [$lengths];
    if (!is_array($widths))  $widths  = [$widths];

    for ($i = 0; $i < $quantity; $i++) {
        $requestedLength = $this->cleanDecimal($lengths[$i] ?? null);
        $requestedWidth  = $this->cleanDecimal($widths[$i] ?? null);
        $pieceQty = 1;

        mysqli_stmt_bind_param(
            $itemStmt,
            "iisssi",
            $bookingId,
            $productId,
            $productName,
            $requestedLength,
            $requestedWidth,
            $pieceQty
        );

        if (!mysqli_stmt_execute($itemStmt)) {
            throw new Exception(mysqli_stmt_error($itemStmt));
        }
    }
}
            mysqli_stmt_close($itemStmt);

            $deleteStmt = mysqli_prepare(
                $this->conn,
                "DELETE ci
                 FROM cart_items ci
                 INNER JOIN carts c ON c.cart_id = ci.cart_id
                 WHERE c.customer_id = ?"
            );

            if (!$deleteStmt) {
                throw new Exception(mysqli_error($this->conn));
            }

            mysqli_stmt_bind_param($deleteStmt, "i", $userId);

            if (!mysqli_stmt_execute($deleteStmt)) {
                throw new Exception(mysqli_stmt_error($deleteStmt));
            }

            mysqli_stmt_close($deleteStmt);
            mysqli_commit($this->conn);

            $this->bookingId = $bookingId;
            return $bookingId;

        } catch (Throwable $e) {
            mysqli_rollback($this->conn);
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}
?>
