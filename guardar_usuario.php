<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena'];

    // ENCRIPTACIÓN: Convertimos la clave en un código irreconocible
    $pass_encriptada = password_hash($pass, PASSWORD_BCRYPT);

    $sql = "INSERT INTO Usuarios (nombre, correo, password, rol) 
            VALUES ('$nombre', '$correo', '$pass_encriptada', 'cliente')";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Registro exitoso, ya puedes iniciar sesión'); window.location.href='login.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>