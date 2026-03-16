<?php
// Reportar errores para saber qué pasa
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Railway nos da estas variables. Si no existen, usa los valores de tu PC
    $host = getenv('MYSQLHOST') ?: "localhost";
    $user = getenv('MYSQLUSER') ?: "root";
    $pass = getenv('MYSQLPASSWORD') ?: "root"; 
    $db   = getenv('MYSQLDATABASE') ?: "la_103";
    $port = getenv('MYSQLPORT') ?: "3306";

    $conexion = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($conexion, "utf8");

} catch (mysqli_sql_exception $e) {
    // Esto te dirá exactamente qué falló si vuelve a pasar
    die("Error crítico de conexión: " . $e->getMessage());
}
?>
