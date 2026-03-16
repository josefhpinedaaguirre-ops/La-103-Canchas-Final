<?php
// Reporte de errores para que no nos de 500 a ciegas
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Usamos exactamente los nombres que tienes en Railway
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT') ?: "3306"; // Si no hay puerto, usa 3306

    $conexion = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($conexion, "utf8");

} catch (Exception $e) {
    // Si falla la conexión, mostramos el error en pantalla en vez de un 500
    die("Fallo en la conexión: " . $e->getMessage());
}
?>
