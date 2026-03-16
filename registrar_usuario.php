<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexion.php");

// Asegurarse de que la petición venga por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Acceso inválido. Usa el formulario de registro: <a href=\"registro.html\">Registro</a>";
    exit;
}

// Saneado básico y valores por defecto
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$pass   = $_POST['contrasena'] ?? '';
$tel    = trim($_POST['telefono'] ?? '');

// Validaciones simples
if ($nombre === '' || $correo === '' || $pass === '') {
    echo "Faltan campos obligatorios. <a href=\"registro.html\">Volver</a>";
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "Correo no válido. <a href=\"registro.html\">Volver</a>";
    exit;
}

// Hashear la contraseña
$pass_hash = password_hash($pass, PASSWORD_DEFAULT);

// Usar prepared statement para evitar inyecciones y errores por comillas
$query = "INSERT INTO Usuarios (nombre, correo, contrasena, telefono, rol) VALUES (?, ?, ?, ?, 'cliente')";
$stmt = mysqli_prepare($conexion, $query);
if (!$stmt) {
    echo "Error en la preparación de la consulta: " . mysqli_error($conexion);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $pass_hash, $tel);

if (mysqli_stmt_execute($stmt)) {
    // Registrar sesión y redirigir a la página de reservas
    $user_id = mysqli_insert_id($conexion);
    session_start();
    $_SESSION['usuario_id'] = $user_id; // clave unificada
    $_SESSION['nombre'] = $nombre;
    $_SESSION['rol'] = 'cliente';

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    header('Location: donde.php');
    exit;
} else {
    // Manejar error de clave duplicada (dependiente de la BD)
    $errno = mysqli_errno($conexion);
    if ($errno === 1062) {
        echo "El correo ya está registrado. <a href=\"registro.html\">Volver</a>";
    } else {
        echo "Error al registrar usuario: " . mysqli_error($conexion);
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>