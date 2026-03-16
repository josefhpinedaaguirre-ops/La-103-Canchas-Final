<?php
// Habilitamos el reporte de errores detallado para evitar el error 500 a ciegas
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Estas variables las toma automáticamente del panel de Railway
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT') ?: "3306"; // Usa el puerto interno 3306 por defecto

    // Establecemos la conexión
    $conexion = mysqli_connect($host, $user, $pass, $db, $port);
    
    // Configuramos el conjunto de caracteres a utf8 para evitar problemas con tildes
    mysqli_set_charset($conexion, "utf8");

} catch (Exception $e) {
    // Si algo falla, nos dirá exactamente qué es en lugar de dar un error genérico
    die("Error de conexión a 'La 103': " . $e->getMessage());
}
?>
