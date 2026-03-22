<?php
session_start();

// --- 1. BLOQUEO DE CACHÉ (Seguridad para que no puedan volver atrás al cerrar sesión) ---
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

include("conexion.php");

// 2. Verificamos que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    header("Location: registro.html"); 
    exit();
}

$id_usuario_logueado = $_SESSION['id'];
$rol_usuario = $_SESSION['rol'];

/**
 * 3. LÓGICA DE FILTRADO
 */
if ($rol_usuario === 'admin') {
    $where_clause = ""; 
} else {
    $where_clause = "WHERE r.id_usuario = '$id_usuario_logueado'"; 
}

// 4. Consulta con JOIN
$sql = "SELECT r.id, u.nombre AS cliente, c.nombre_cancha, r.fecha_reserva, r.hora_inicio, r.hora_fin, r.precio_total_cancha, r.estado_reserva 
        FROM reservas r
        JOIN usuarios u ON r.id_usuario = u.id
        JOIN canchas c ON r.id_cancha = c.id
        $where_clause
        ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas | La 103</title>
    
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .tabla-container { background: #1a1a1a; padding: 25px; border-radius: 15px; border: 1px solid #333; max-width: 1000px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #2ecc71; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #2ecc71; color: black; padding: 15px; text-align: left; font-size: 13px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #222; font-size: 14px; }
        tr:hover { background: #222; }
        
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .confirmada { background: #27ae60; color: white; }
        .pendiente { background: #f1c40f; color: black; }
        .finalizada { background: #444; color: #bbb; }
        
        .btn-volver { display: inline-block; margin-bottom: 20px; color: #2ecc71; text-decoration: none; font-weight: bold; font-size: 14px; }
        
        /* Botón Actualizar */
        .btn-update { 
            background: transparent; 
            border: 1px solid #3498db; 
            color: #3498db; 
            padding: 5px 10px; 
            border-radius: 5px; 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-update:hover { background: #3498db; color: white; }
        
        .vacio { text-align: center; padding: 40px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <div class="tabla-container">
        <a href="index.php" class="btn-volver">← Volver al Menú</a>
        <h2><?php echo ($rol_usuario === 'admin') ? '📋 Gestión Administrativa' : '📅 Mis Partidos Agendados'; ?></h2>
        
        <table>
            <thead>
                <tr>
                    <?php if($rol_usuario === 'admin'): ?> <th>Cliente</th> <?php endif; ?>
                    <th>Cancha</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th style="text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($resultado) > 0) {
                    while($row = mysqli_fetch_assoc($resultado)) { 
                ?>
                <tr>
                    <?php if($rol_usuario === 'admin'): ?> 
                        <td style="color: #2ecc71; font-weight: bold;"><?php echo htmlspecialchars($row['cliente']); ?></td> 
                    <?php endif; ?>
                    
                    <td><?php echo htmlspecialchars($row['nombre_cancha']); ?></td>
                    <td><strong><?php echo date("d/m/Y", strtotime($row['fecha_reserva'])); ?></strong></td>
                    <td><?php echo substr($row['hora_inicio'], 0, 5) . " - " . substr($row['hora_fin'], 0, 5); ?></td>
                    <td style="font-weight: bold; color: #2ecc71;">$<?php echo number_format($row['precio_total_cancha'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="badge <?php echo strtolower($row['estado_reserva']); ?>">
                            <?php echo strtoupper($row['estado_reserva']); ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <?php if($row['estado_reserva'] !== 'finalizada'): ?>
                            <a href="editar_reserva.php?id=<?php echo $row['id']; ?>" class="btn-update">
                                ⚙️ Actualizar
                            </a>
                        <?php else: ?>
                            <small style="color: #555;">Sin acciones</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    $cols = ($rol_usuario === 'admin') ? 7 : 6;
                    echo "<tr><td colspan='$cols' class='vacio'>No hay reservas registradas. ⚽</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>
