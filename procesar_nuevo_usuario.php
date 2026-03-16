<?php
include("conexion.php");
session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $pass   = $_POST['contrasena']; 
    $tel    = $_POST['telefono'];

    // 1. TABLA EN MINÚSCULA: 'usuarios' (Para que Railway no falle)
    // 2. Quitamos 'password' si tu tabla solo usa 'contrasena' 
    // (O asegúrate de que tu tabla en Railway tenga AMBAS columnas)
    $sql = "INSERT INTO usuarios (nombre, correo, contrasena, telefono, rol) 
            VALUES ('$nombre', '$correo', '$pass', '$tel', 'cliente')";

    if (mysqli_query($conexion, $sql)) {
        // Obtenemos el ID generado
        $nuevo_id = mysqli_insert_id($conexion);

        // Creamos la sesión para que entre de una sin loguearse de nuevo
        $_SESSION['id'] = $nuevo_id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = 'cliente';

        echo "<script>
                alert('¡Registro exitoso, " . $nombre . "! Ya puedes elegir tu cancha.');
                window.location.href='index.php';
              </script>";
    } else {
        // Esto te dirá exactamente si falta una columna o si el nombre está mal
        echo "Error al registrar en La 103: " . mysqli_error($conexion);
    }
}
mysqli_close($conexion);
?>
