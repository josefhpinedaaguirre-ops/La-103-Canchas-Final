<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id'])) {
    die("Error: No has iniciado sesión.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario    = $_SESSION['id'];
    $id_cancha     = $_POST['id_cancha'];
    $fecha_reserva = $_POST['fecha_reserva'];
    $hora_inicio   = $_POST['hora_inicio'];
    $hora_fin       = $_POST['hora_fin'];

    // --- 1. VALIDACIÓN DE DISPONIBILIDAD INTELIGENTE ---
    // Buscamos si existe una reserva para ese horario (sin importar el estado)
    $sql_check = "SELECT id, estado_reserva FROM Reservas 
                  WHERE id_cancha = '$id_cancha' 
                  AND fecha_reserva = '$fecha_reserva' 
                  AND hora_inicio = '$hora_inicio'";
    
    $res_check = mysqli_query($conexion, $sql_check);
    $reserva_existente = mysqli_fetch_assoc($res_check);

    // --- 2. LÓGICA DE PRECIOS AUTOMÁTICOS ---
    $dia_semana = date('w', strtotime($fecha_reserva));
    $hora_entera = (int)date('H', strtotime($hora_inicio));
    $precio_total = ($dia_semana == 0 || $dia_semana == 6 || $hora_entera >= 17) ? 120000 : 80000;
    $estado = 'pendiente';

    if ($reserva_existente) {
        // Si existe y NO está finalizada, bloqueamos por duplicado activo
        if ($reserva_existente['estado_reserva'] != 'finalizada') {
            echo "<script>
                    alert('⚠️ ERROR: La cancha ya está reservada para esa hora. Por favor elige otro horario.');
                    window.history.back();
                  </script>";
            exit();
        } else {
            // SI ESTÁ FINALIZADA: Reutilizamos la fila (UPDATE) para evitar el error de Duplicate Entry de MySQL
            $id_reutilizar = $reserva_existente['id'];
            $sql_accion = "UPDATE Reservas SET 
                           id_usuario = '$id_usuario', 
                           hora_fin = '$hora_fin', 
                           precio_total_cancha = '$precio_total', 
                           estado_reserva = 'pendiente' 
                           WHERE id = '$id_reutilizar'";
            $id_nueva_reserva = $id_reutilizar;
        }
    } else {
        // SI NO EXISTE: Insertamos un registro nuevo normalmente
        $sql_accion = "INSERT INTO Reservas (id_usuario, id_cancha, fecha_reserva, hora_inicio, hora_fin, precio_total_cancha, estado_reserva) 
                       VALUES ('$id_usuario', '$id_cancha', '$fecha_reserva', '$hora_inicio', '$hora_fin', '$precio_total', '$estado')";
    }

    // --- 3. EJECUTAR INSERCIÓN O ACTUALIZACIÓN ---
    if (mysqli_query($conexion, $sql_accion)) {
        
        // Si fue una inserción nueva, obtenemos el ID generado
        if (!isset($id_nueva_reserva)) {
            $id_nueva_reserva = mysqli_insert_id($conexion);
        }

        // --- 4. LÓGICA DE PRÉSTAMOS Y DESCUENTO DE INVENTARIO ---
        if (isset($_POST['cantidades']) && is_array($_POST['cantidades'])) {
            foreach ($_POST['cantidades'] as $id_imp => $cant) {
                $cant = (int)$cant; 

                if ($cant > 0) {
                    $sql_prestamo = "INSERT INTO Prestamos (id_reserva, id_implemento, cantidad) 
                                     VALUES ('$id_nueva_reserva', '$id_imp', '$cant')";
                    mysqli_query($conexion, $sql_prestamo);

                    $sql_update_stock = "UPDATE Implementos 
                                         SET cantidad_total = cantidad_total - $cant 
                                         WHERE id = '$id_imp' AND cantidad_total >= $cant";
                    mysqli_query($conexion, $sql_update_stock);
                }
            }
        }

        echo "<script>
                alert('✅ ¡Reserva registrada con éxito! Valor: $" . number_format($precio_total) . "');
                window.location.href='index.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
mysqli_close($conexion);
?>