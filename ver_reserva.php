<?php
session_start();
include("conexion.php");

// 1. Verificamos que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    header("Location: reservar.html");
    exit();
}

$id_usuario_logueado = $_SESSION['id'];
$rol_usuario = $_SESSION['rol'];

/**
 * 2. LÓGICA DE FILTRADO:
 * Si es admin, no ponemos el WHERE para que vea todo.
 * Si es cliente, filtramos por su ID de sesión.
 */
if ($rol_usuario === 'admin') {
    $where_clause = ""; // Sin filtro
} else {
    $where_clause = "WHERE r.id_usuario = '$id_usuario_logueado'"; // Solo lo suyo
}

// 3. Consulta con JOIN y el filtro aplicado
$sql = "SELECT r.id, u.nombre AS cliente, c.nombre_cancha, r.fecha_reserva, r.hora_inicio, r.hora_fin, r.precio_total_cancha, r.estado_reserva 
        FROM Reservas r
        JOIN Usuarios u ON r.id_usuario = u.id
        JOIN Canchas c ON r.id_cancha = c.id
        $where_clause
        ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";

$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas | La 103</title>
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .tabla-container { background: #1a1a1a; padding: 25px; border-radius: 15px; border: 1px solid #333; max-width: 900px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #2ecc71; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #2ecc71; color: black; padding: 15px; text-align: left; font-size: 13px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #222; font-size: 14px; }
        tr:hover { background: #222; }
        
        /* Badges de estado */
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .confirmada { background: #27ae60; color: white; }
        .pendiente { background: #f1c40f; color: black; }
        
        .btn-volver { display: inline-block; margin-bottom: 20px; color: #2ecc71; text-decoration: none; font-weight: bold; font-size: 14px; transition: 0.3s; }
        .btn-volver:hover { color: #27ae60; }
        
        .vacio { text-align: center; padding: 40px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <div class="tabla-container">
        <a href="index.php" class="btn-volver">← Volver al Menú</a>
        <h2><?php echo ($rol_usuario === 'admin') ? '📋 Control Total de Turnos' : '📅 Mis Partidos Agendados'; ?></h2>
        
        <table>
            <thead>
                <tr>
                    <?php if($rol_usuario === 'admin'): ?> <th>Cliente</th> <?php endif; ?>
                    <th>Cancha</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Precio</th>
                    <th>Estado</th>
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
                    <td><?php echo $row['fecha_reserva']; ?></td>
                    <td><?php echo substr($row['hora_inicio'], 0, 5) . " - " . substr($row['hora_fin'], 0, 5); ?></td>
                    <td style="font-weight: bold;">$<?php echo number_format($row['precio_total_cancha'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="badge <?php echo $row['estado_reserva']; ?>">
                            <?php echo strtoupper($row['estado_reserva']); ?>
                        </span>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='vacio'>No tienes reservas registradas todavía. ⚽</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>