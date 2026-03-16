<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena'];

    // AGREGAMOS 'rol' A LA CONSULTA SQL
    $sql = "SELECT id, nombre, contrasena, rol FROM Usuarios WHERE correo = '$correo'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        // Verificamos la contraseña en texto plano
        if ($pass === $usuario['contrasena']) {
            
            // GUARDAMOS TODO EN LA SESIÓN
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol']; // <--- ESTA ES LA CLAVE PARA EL ADMIN

            // Redireccionamos al INDEX (donde se verá el menú según el rol)
            header("Location: index.php");
            exit(); 
            
        } else {
            echo "<script>alert('Clave incorrecta'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Correo no registrado'); window.history.back();</script>";
    }
}
mysqli_close($conexion);
?>