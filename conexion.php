<?php
// Esto ayuda a ver errores reales de conexión
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Leemos las variables que mostraste en tu captura
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT') ?: "3306"; // Si no hay puerto, usa el 3306 por defecto

    $conexion = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($conexion, "utf8");

} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
