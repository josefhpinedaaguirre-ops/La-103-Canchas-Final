<?php
session_start();

// --- 1. BLOQUEO DE CACHÉ (Para que no puedan volver atrás al cerrar sesión) ---
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

include("conexion.php");

// 2. Verificación de seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$hoy = date('Y-m-d');

// --- NUEVA LÓGICA: CANCELACIÓN MASIVA ---
if (isset($_POST['cancelar_seleccionadas'])) {
    if (!empty($_POST['reservas_cancelar'])) {
        foreach ($_POST['reservas_cancelar'] as $id_reserva) {
            $id_reserva = mysqli_real_escape_string($conexion, $id_reserva);
            
            // Cambiamos el estado a 'finalizada' para liberar el horario
            $sql_cancelar = "UPDATE reservas SET estado_reserva = 'finalizada' WHERE id = '$id_reserva'";
            mysqli_query($conexion, $sql_cancelar);
        }
        echo "<script>alert('✅ Reservas canceladas y horarios liberados.'); window.location.href='admin_reservas.php';</script>";
    } else {
        echo "<script>alert('⚠️ No seleccionaste ninguna reserva.');</script>";
    }
}

// --- CONSULTA ---
$sql = "SELECT r.id, u.nombre, c.nombre_cancha, r.fecha_reserva, r.hora_inicio, r.precio_total_cancha, r.estado_reserva, 
               GROUP_CONCAT(DISTINCT CONCAT('⚽ ', p.cantidad, 'x ', i.nombre_objeto) SEPARATOR '<br>') AS lista_implementos,
               MAX(pa.metodo_pago) as metodo_pago 
        FROM reservas r 
        JOIN usuarios u ON r.id_usuario = u.id 
        JOIN canchas c ON r.id_cancha = c.id 
        LEFT JOIN prestamos p ON r.id = p.id_reserva
        LEFT JOIN implementos i ON p.id_implemento = i.id
        LEFT JOIN pagos pa ON r.id = pa.id_reserva
        WHERE r.fecha_reserva >= '$hoy'
        GROUP BY r.id, u.nombre, c.nombre_cancha, r.fecha_reserva, r.hora_inicio, r.precio_total_cancha, r.estado_reserva
        ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC"; 

$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Reservas | La 103</title>
    
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .header-info { background: #1a1a1a; padding: 20px; border-radius: 12px; border-left: 5px solid #2ecc71; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-regresar { background: #2ecc71; color: black; padding: 12px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
        
        /* Botón Cancelar */
        .btn-cancelar-masivo { background: #e74c3c; color: white; border: none; padding: 10px 15px; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-bottom: 10px; }
        .btn-cancelar-masivo:hover { background: #c0392b; transform: scale(1.02); }

        table { width: 100%; border-collapse: collapse; background: #1a1a1a; border-radius: 10px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #2ecc71; color: black; font-size: 11px; text-transform: uppercase; }

        .fila-hoy { border-left: 4px solid #f1c40f; background: #22201e !important; }
        .btn-accion { padding: 8px 12px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 11px; display: inline-block; }
        .btn-confirmar { background: #f1c40f; color: black; }
        .btn-finalizar { background: #3498db; color: white; }
        
        .estado { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; display: inline-block; }
        .pendiente { background: #e74c3c; color: white; }
        .confirmada { background: #2ecc71; color: black; }
        .finalizada { background: #444; color: #888; }
        
        .chk-box { width: 18px; height: 18px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="header-info">
        <div>
            <h2 style="color: #2ecc71; margin: 0;">📋 GESTIÓN DE RESERVAS</h2>
            <p style="color: #888; margin: 5px 0 0 0;">Control total de horarios y pagos</p>
        </div>
        <a href="index.php" class="btn-regresar">🏠 VOLVER AL PANEL</a>
    </div>

    <form action="" method="POST" onsubmit="return confirm('⚠️ ¿Estás seguro de cancelar las reservas seleccionadas? Esto liberará los horarios inmediatamente.');">
        
        <button type="submit" name="cancelar_seleccionadas" class="btn-cancelar-masivo">
            🗑️ Cancelar Seleccionadas
        </button>

        <table>
            <thead>
                <tr>
                    <th>Sel.</th>
                    <th>ID</th>
                    <th>Jugador</th>
                    <th>Cancha</th> 
                    <th>Fecha / Hora</th>
                    <th>Implementos</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Gestión</th>
                </tr>
            </thead>
            <tbody>
                <?php while($res = mysqli_fetch_assoc($resultado)): 
                    $es_hoy = ($res['fecha_reserva'] == $hoy) ? 'fila-hoy' : '';
                ?>
                <tr class="<?php echo $es_hoy; ?>">
                    <td>
                        <?php if($res['estado_reserva'] != 'finalizada'): ?>
                            <input type="checkbox" name="reservas_cancelar[]" value="<?php echo $res['id']; ?>" class="chk-box">
                        <?php endif; ?>
                    </td>
                    <td>#<?php echo $res['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($res['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($res['nombre_cancha']); ?></td>
                    <td>
                        <strong><?php echo date("d/m/Y", strtotime($res['fecha_reserva'])); ?></strong><br>
                        <small>⏰ <?php echo $res['hora_inicio']; ?></small>
                    </td>
                    <td style="color:#f1c40f; font-size: 12px;">
                        <?php echo $res['lista_implementos'] ?: '<span style="color:#555">Ninguno</span>'; ?>
                    </td>
                    <td style="color:#2ecc71; font-weight:bold;">$<?php echo number_format($res['precio_total_cancha']); ?></td>
                    <td>
                        <span class="estado <?php echo $res['estado_reserva']; ?>">
                            <?php 
                                if($res['estado_reserva'] == 'pendiente') echo '⏳ PENDIENTE';
                                elseif($res['estado_reserva'] == 'confirmada') echo '✔️ PAGADO';
                                else echo '🏁 TERMINADO';
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php if($res['estado_reserva'] == 'pendiente'): ?>
                            <a href="actualizar_pago.php?id=<?php echo $res['id']; ?>" class="btn-accion btn-confirmar">Pagar</a>
                        <?php elseif($res['estado_reserva'] == 'confirmada'): ?>
                            <a href="finalizar_reserva.php?id=<?php echo $res['id']; ?>" class="btn-accion btn-finalizar" onclick="return confirm('¿Finalizar turno y recibir equipos?')">Finalizar</a>
                        <?php else: ?>
                            <span style="color:#555; font-size: 11px;">📦 LIBERADO</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </form>

</body>
</html>
