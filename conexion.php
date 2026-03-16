<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Railway inyecta estas variables automáticamente si haces el "Add Reference"
// Usamos getenv() para leer los valores reales del servidor
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: ""; // En local suele ser vacío o root
$db   = getenv('MYSQLDATABASE') ?: "la_103";
$port = getenv('MYSQLPORT') ?: "28578"; // Usamos el puerto que vimos en tus capturas

// Añadimos el puerto a la conexión, que es vital en Railway
$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Configurar caracteres latinos (para que se vean bien las tildes y la Ñ)
mysqli_set_charset($conexion, "utf8");
?>
