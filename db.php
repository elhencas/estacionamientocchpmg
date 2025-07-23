<?php
$host = 'localhost';
$db = 'estacionamiento';
$user = 'root'; // Cambia esto si tienes un usuario diferente
$pass = 'Alfa2010%2017'; // Cambia esto si tienes una contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
