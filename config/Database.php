<?php
class Database {
    private static $connection = null;

    private function __construct(){}

    public static function connection(){
        if (self::$connection === null) {
            require_once(__DIR__ . "/db.php");
            self::$connection = $conn;
        }

        return self::$connection;
    }
}
?>
