<?php
include("conexion.php");

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$pass   = $_POST['password']; // La que viene del formulario

// LA CLAVE DE SEGURIDAD: Encriptamos la contraseña
$pass_encriptada = password_hash($pass, PASSWORD_BCRYPT);

$sql = "INSERT INTO Usuarios (nombre, correo, password, rol) 
        VALUES ('$nombre', '$correo', '$pass_encriptada', 'cliente')";

if (mysqli_query($conexion, $sql)) {
    echo "Usuario registrado con éxito.";
}
?>