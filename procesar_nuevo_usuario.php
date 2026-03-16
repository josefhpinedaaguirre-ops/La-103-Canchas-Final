<?php
include("conexion.php");
session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena'];
    $tel    = $_POST['telefono'];

    // 1. CAMBIO CLAVE: 'usuarios' en minúscula para que Railway no explote
    // 2. Mantenemos 'contrasena' y 'password' como pediste para evitar el Fatal Error
    $sql = "INSERT INTO usuarios (nombre, correo, contrasena, password, telefono, rol) 
            VALUES ('$nombre', '$correo', '$pass', '$pass', '$tel', 'cliente')";

    if (mysqli_query($conexion, $sql)) {
        // Obtenemos el ID generado
        $nuevo_id = mysqli_insert_id($conexion);

        // IMPORTANTE: Guardamos el nombre en la sesión para que el Index no salga vacío
        $_SESSION['id'] = $nuevo_id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = 'cliente';

        // Redirección limpia
        echo "<script>
                alert('¡Registro exitoso, " . $nombre . "! Bienvenido a La 103.');
                window.location.href='index.php';
              </script>";
    } else {
        // Si falla, esto nos dirá qué columna falta en Railway
        die("Error al registrar: " . mysqli_error($conexion));
    }
}
mysqli_close($conexion);
?>
