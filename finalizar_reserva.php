<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado.");
}

if (isset($_GET['id'])) {
    $id_reserva = $_GET['id'];

    // 1. OBTENER LOS IMPLEMENTOS PARA DEVOLVERLOS AL INVENTARIO
    $sql_prestamos = "SELECT id_implemento, cantidad FROM Prestamos WHERE id_reserva = '$id_reserva'";
    $res_prestamos = mysqli_query($conexion, $sql_prestamos);

    // Corregido: Usamos la variable correcta ($res_prestamos)
    if ($res_prestamos) {
        while ($item = mysqli_fetch_assoc($res_prestamos)) {
            $id_imp = $item['id_implemento'];
            $cant = $item['cantidad'];
            
            // Sumamos de nuevo al inventario real
            mysqli_query($conexion, "UPDATE Implementos SET cantidad_total = cantidad_total + $cant WHERE id = '$id_imp'");
        }
    }

    // 2. BORRAR LOS REGISTROS DE PRÉSTAMOS DE ESTA RESERVA
    // Esto es necesario para que la próxima vez que alguien use este horario, 
    // la tabla de préstamos esté limpia para el nuevo cliente.
    mysqli_query($conexion, "DELETE FROM Prestamos WHERE id_reserva = '$id_reserva'");
    
    // 3. ACTUALIZAR EL ESTADO A FINALIZADA (NO BORRAR)
    // Cambiamos el estado para que el archivo guardar_reserva.php sepa que puede reutilizar esta fila.
    $sql_finalizar = "UPDATE Reservas SET estado_reserva = 'finalizada' WHERE id = '$id_reserva'";
    
    if (mysqli_query($conexion, $sql_finalizar)) {
        echo "<script>
                alert('✅ Turno Finalizado: Inventario devuelto y horario disponible para nueva reserva.');
                window.location.href = 'admin_reservas.php';
              </script>";
    } else {
        echo "Error al finalizar el turno: " . mysqli_error($conexion);
    }
}
?>