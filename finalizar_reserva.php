<?php
session_start();
include("conexion.php");

// Verificación de seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado.");
}

if (isset($_GET['id'])) {
    $id_reserva = mysqli_real_escape_string($conexion, $_GET['id']);

    // 1. OBTENER LOS IMPLEMENTOS (Tablas en minúscula: prestamos)
    $sql_prestamos = "SELECT id_implemento, cantidad FROM prestamos WHERE id_reserva = '$id_reserva'";
    $res_prestamos = mysqli_query($conexion, $sql_prestamos);

    if ($res_prestamos) {
        while ($item = mysqli_fetch_assoc($res_prestamos)) {
            $id_imp = $item['id_implemento'];
            $cant = $item['cantidad'];
            
            // Sumamos al inventario (Tabla en minúscula: implementos)
            mysqli_query($conexion, "UPDATE implementos SET cantidad_total = cantidad_total + $cant WHERE id = '$id_imp'");
        }
    }

    // 2. BORRAR LOS REGISTROS DE PRÉSTAMOS
    mysqli_query($conexion, "DELETE FROM prestamos WHERE id_reserva = '$id_reserva'");
    
    // 3. ACTUALIZAR EL ESTADO A FINALIZADA (Tabla en minúscula: reservas)
    $sql_finalizar = "UPDATE reservas SET estado_reserva = 'finalizada' WHERE id = '$id_reserva'";
    
    if (mysqli_query($conexion, $sql_finalizar)) {
        echo "<script>
                alert('✅ Turno Finalizado: Inventario devuelto y horario disponible.');
                window.location.href = 'admin_reservas.php';
              </script>";
    } else {
        echo "Error al finalizar el turno: " . mysqli_error($conexion);
    }
} else {
    header("Location: admin_reservas.php");
}
?>
