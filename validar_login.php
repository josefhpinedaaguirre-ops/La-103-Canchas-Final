<?php
session_start();
// Asegúrate de que el archivo se llame exactamente conexion.php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Usamos los nombres de los campos que vienen de tu formulario HTML
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena'];

    // 1. CAMBIAMOS 'Usuarios' por 'usuarios' (minúscula para Linux/Railway)
    // 2. Verifica si en tu tabla es 'contrasena' o 'password'. 
    // Si sigue fallando, cambia 'contrasena' por 'password' abajo.
    $sql = "SELECT id, nombre, contrasena, rol FROM usuarios WHERE correo = '$correo'";
    
    // Ejecutamos la consulta
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        // Verificamos la contraseña
        if ($pass === $usuario['contrasena']) {
            
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redireccionamos al INDEX principal
            header("Location: index.php");
            exit(); 
            
        } else {
            echo "<script>alert('Clave incorrecta'); window.history.back();</script>";
        }
    } else {
        // Si el error 500 sigue, es probable que la tabla no tenga la columna 'correo'
        echo "<script>alert('Correo no registrado o error en tabla'); window.history.back();</script>";
    }
}
mysqli_close($conexion);
?>
