<?php
$host = "localhost";
$db_name = "tienda_ropa_test";
$username = "root"; // Usuario por defecto en phpMyAdmin
$password = "";     // Contraseña por defecto vacía en XAMPP

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>