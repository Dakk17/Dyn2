<?php
// database.php
require_once 'config.php';

/**
 * Funkce pro připojení k databázi.
 * @return mysqli|false Objekt připojení k databázi nebo false při chybě.
 */
function connect_db() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return false; 
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>