<?php
abstract class User {

    protected $userId;
    protected $fullName;
    protected $email;
    protected $password;
    protected $phone;
    protected $address;

    public function __construct($userId, $fullName, $email, $password, $phone, $address){
        $this->userId   = $userId;
        $this->fullName = $fullName;
        $this->email    = $email;
        $this->password = $password;
        $this->phone    = $phone;
        $this->address  = $address;
    }

    public function getFullName() { return $this->fullName; }
    public function getEmail()    { return $this->email; }
    public function getPhone()    { return $this->phone; }
    public function getAddress()  { return $this->address; }

    public function login($email, $password): bool {
        return true;
    }

    public function logout(): void {
        // logic
    }

    public function updateProfile(): void {
        // logic
    }
}
?>