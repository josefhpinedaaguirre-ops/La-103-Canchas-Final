<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena'];
    $tel    = $_POST['telefono'];

    // IMPORTANTE: 'usuarios' en minúscula para Railway
    $sql = "INSERT INTO usuarios (nombre, correo, contrasena, telefono, rol) 
            VALUES ('$nombre', '$correo', '$pass', '$tel', 'cliente')";

    if (mysqli_query($conexion, $sql)) {
        // Guardamos los datos en la sesión para que el "Bienvenido" no salga vacío
        $_SESSION['id'] = mysqli_insert_id($conexion);
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = 'cliente';

        echo "<script>
                alert('¡Bienvenido a La 103, $nombre!');
                window.location.href='index.php';
              </script>";
    } else {
        // Esto te dirá el error real si falla
        die("Error en el registro: " . mysqli_error($conexion));
    }
}
?>
