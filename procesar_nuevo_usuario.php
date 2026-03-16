<?php
include("conexion.php");
session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena']; // El valor que viene de tu formulario HTML
    $tel    = $_POST['telefono'];

    // 1. LA SOLUCIÓN DEFINITIVA: 
    // Enviamos el valor a 'contrasena' Y a 'password' al mismo tiempo
    // Así cumplimos con lo que pide tu base de datos y no sale el Fatal Error.
    $sql = "INSERT INTO Usuarios (nombre, correo, contrasena, password, telefono, rol) 
            VALUES ('$nombre', '$correo', '$pass', '$pass', '$tel', 'cliente')";

    if (mysqli_query($conexion, $sql)) {
        // 2. Obtenemos el ID generado
        $nuevo_id = mysqli_insert_id($conexion);

        // 3. Creamos la sesión para que entre de una
        $_SESSION['id'] = $nuevo_id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = 'cliente';

        // 4. Redirección al index
        echo "<script>
                alert('¡Registro exitoso, " . $nombre . "! Ya puedes elegir tu cancha.');
                window.location.href='index.php';
              </script>";
    } else {
        echo "Error al registrar: " . mysqli_error($conexion);
    }
}
mysqli_close($conexion);
?>