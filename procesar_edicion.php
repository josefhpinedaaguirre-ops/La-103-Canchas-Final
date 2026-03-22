<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_reserva = mysqli_real_escape_string($conexion, $_POST['id_reserva']);
    $id_cancha = mysqli_real_escape_string($conexion, $_POST['id_cancha']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha_reserva']);
    $inicio = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $fin = mysqli_real_escape_string($conexion, $_POST['hora_fin']);

    // 1. VALIDACIÓN DE DISPONIBILIDAD (Que no choque con otra reserva que NO sea esta misma)
    $sql_choque = "SELECT id FROM reservas 
                   WHERE id_cancha = '$id_cancha' 
                   AND fecha_reserva = '$fecha' 
                   AND estado_reserva != 'finalizada'
                   AND id != '$id_reserva'
                   AND (
                       ('$inicio' >= hora_inicio AND '$inicio' < hora_fin) OR 
                       ('$fin' > hora_inicio AND '$fin' <= hora_fin) OR 
                       (hora_inicio >= '$inicio' AND hora_inicio < '$fin')
                   )";

    $res_choque = mysqli_query($conexion, $sql_choque);

    if (mysqli_num_rows($res_choque) > 0) {
        echo "<script>
                alert('❌ Error: Ese horario ya está ocupado por otro equipo en esta cancha.');
                window.history.back();
              </script>";
        exit();
    }

    // 2. ACTUALIZAR LA RESERVA
    $sql_update = "UPDATE reservas SET 
                    id_cancha = '$id_cancha', 
                    fecha_reserva = '$fecha', 
                    hora_inicio = '$inicio', 
                    hora_fin = '$fin' 
                    WHERE id = '$id_reserva'";

    if (mysqli_query($conexion, $sql_update)) {
        echo "<script>
                alert('✅ Reserva actualizada correctamente.');
                window.location.href = 'ver_reserva.php';
              </script>";
    } else {
        echo "Error actualizando: " . mysqli_error($conexion);
    }
}
?>
