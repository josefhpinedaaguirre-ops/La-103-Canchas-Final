<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id'])) {
    header("Location: registro.html");
    exit();
}

// 1. Consultamos las canchas
$sql_canchas = "SELECT id, nombre_cancha FROM Canchas WHERE estado = 'disponible'";
$res_canchas = mysqli_query($conexion, $sql_canchas);

// 2. Consultamos los implementos (Usando tus nombres de columna reales)
$sql_implementos = "SELECT id, nombre_objeto, cantidad_total FROM Implementos WHERE cantidad_total > 0";
$res_implementos = mysqli_query($conexion, $sql_implementos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Cancha | La 103</title>
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .reserva-box { background: #1a1a1a; padding: 30px; border-radius: 20px; width: 400px; border-top: 4px solid #2ecc71; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h2 { text-align: center; color: #2ecc71; text-transform: uppercase; margin-bottom: 25px; font-size: 20px; }
        .campo { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #aaa; font-size: 14px; }
        select, input { width: 100%; padding: 12px; border: 1px solid #333; border-radius: 8px; background: #252525; color: white; outline: none; box-sizing: border-box; transition: 0.3s; }
        select:focus, input:focus { border-color: #2ecc71; }
        
        /* Estilos para los implementos */
        .seccion-implementos { background: #222; padding: 15px; border-radius: 10px; margin: 20px 0; border: 1px dashed #444; }
        .impl-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .impl-info { font-size: 13px; }
        .impl-cant { width: 60px !important; padding: 5px !important; text-align: center; }

        .btn-reservar { width: 100%; padding: 15px; background: #2ecc71; border: none; border-radius: 8px; color: black; font-weight: bold; font-size: 16px; cursor: pointer; text-transform: uppercase; margin-top: 10px; transition: 0.3s; }
        .btn-reservar:hover { background: #27ae60; transform: translateY(-2px); }
        .tarifa-info { background: #252525; padding: 10px; border-radius: 8px; border-left: 3px solid #f1c40f; margin-bottom: 15px; font-size: 12px; color: #ccc; }
    </style>
</head>
<body>
    <div class="reserva-box">
        <p style="text-align:center; color:#2ecc71;">Bienvenido, <strong><?php echo $_SESSION['nombre']; ?></strong> ⚽</p>
        <h2>Reserva tu Turno</h2>

        <div class="tarifa-info">
            📌 <strong>Tarifas:</strong><br>
            • Lun-Vie antes 5pm: <b>$80,000</b><br>
            • Lun-Vie después 5pm: <b>$120,000</b><br>
            • Sáb-Dom todo el día: <b>$120,000</b>
        </div>

        <form action="guardar_reserva.php" method="POST">
            <div class="campo">
                <label>Selecciona la Cancha</label>
                <select name="id_cancha" required>
                    <?php while($cancha = mysqli_fetch_assoc($res_canchas)): ?>
                        <option value="<?php echo $cancha['id']; ?>"><?php echo htmlspecialchars($cancha['nombre_cancha']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label>Fecha del Partido</label>
                <input type="date" name="fecha_reserva" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="campo" style="flex: 1;">
                    <label>Hora Inicio</label>
                    <input type="time" name="hora_inicio" required>
                </div>
                <div class="campo" style="flex: 1;">
                    <label>Hora Fin</label>
                    <input type="time" name="hora_fin" required>
                </div>
            </div>

            <div class="seccion-implementos">
                <label style="color: #2ecc71; font-weight: bold; margin-bottom: 10px; display: block;">⚽ Préstamo gratuito:</label>
                <?php while($impl = mysqli_fetch_assoc($res_implementos)): ?>
                    <div class="impl-item">
                        <div class="impl-info">
                            <strong><?php echo htmlspecialchars($impl['nombre_objeto']); ?></strong>
                            <br><small style="color: #666;">Disponibles: <?php echo $impl['cantidad_total']; ?></small>
                        </div>
                        <input type="number" name="cantidades[<?php echo $impl['id']; ?>]" 
                               class="impl-cant" min="0" max="<?php echo $impl['cantidad_total']; ?>" value="0">
                    </div>
                <?php endwhile; ?>
            </div>

            <button type="submit" class="btn-reservar">Agendar Partido</button>
            <a href="index.php" style="display:block; text-align:center; color:#888; text-decoration:none; margin-top:15px; font-size:13px;">← Volver atrás</a>
        </form>
    </div>
</body>
</html>