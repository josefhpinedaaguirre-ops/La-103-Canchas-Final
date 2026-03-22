<?php
session_start();
// Asegúrate de que el archivo se llame exactamente conexion.php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- CAMBIO IMPORTANTE: Ahora usamos los nombres seguros que pusimos en el HTML ---
    // También agregamos mysqli_real_escape_string para evitar inyecciones SQL (seguridad extra)
    $correo = mysqli_real_escape_string($conexion, $_POST['correo_103']);
    $pass   = $_POST['clave_103']; // La clave la validamos abajo

    // 1. CAMBIAMOS 'Usuarios' por 'usuarios' (minúscula para Linux/Railway)
    // 2. Verifica si en tu tabla es 'contrasena' o 'password'. 
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
        echo "<script>alert('Correo no registrado o error en la base de datos'); window.history.back();</script>";
    }
}
mysqli_close($conexion);
?>
