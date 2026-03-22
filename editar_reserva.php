<?php
session_start();
include("conexion.php");

// 1. SEGURIDAD: Verificar logueo
if (!isset($_SESSION['id'])) {
    header("Location: registro.html");
    exit();
}

$id_reserva = mysqli_real_escape_string($conexion, $_GET['id'] ?? '');

// 2. OBTENER DATOS ACTUALES DE LA RESERVA
$sql_reserva = "SELECT * FROM reservas WHERE id = '$id_reserva'";
$res_reserva = mysqli_query($conexion, $sql_reserva);
$datos = mysqli_fetch_assoc($res_reserva);

// Si no existe la reserva o no es del usuario (y no es admin), para afuera
if (!$datos || ($_SESSION['rol'] !== 'admin' && $datos['id_usuario'] != $_SESSION['id'])) {
    header("Location: ver_reservas.php");
    exit();
}

// 3. CONSULTAR CANCHAS DISPONIBLES
$res_canchas = mysqli_query($conexion, "SELECT id, nombre_cancha FROM canchas WHERE estado = 'disponible'");

$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva | La 103</title>
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .editar-box { background: #1a1a1a; padding: 30px; border-radius: 20px; width: 400px; border-top: 4px solid #3498db; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h2 { text-align: center; color: #3498db; text-transform: uppercase; margin-bottom: 25px; }
        .campo { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #aaa; font-size: 14px; }
        select, input { width: 100%; padding: 12px; border: 1px solid #333; border-radius: 8px; background: #252525; color: white; outline: none; box-sizing: border-box; }
        .btn-actualizar { width: 100%; padding: 15px; background: #3498db; border: none; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 10px; }
        .btn-actualizar:hover { background: #2980b9; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

    <div class="editar-box">
        <h2>Editar Reserva #<?php echo $id_reserva; ?></h2>
        
        <form action="procesar_edicion.php" method="POST" id="formEditar">
            <input type="hidden" name="id_reserva" value="<?php echo $id_reserva; ?>">
            
            <div class="campo">
                <label>Cancha</label>
                <select name="id_cancha" required>
                    <?php while($c = mysqli_fetch_assoc($res_canchas)): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $datos['id_cancha']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre_cancha']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label>Fecha</label>
                <input type="date" name="fecha_reserva" id="fecha" min="<?php echo $hoy; ?>" value="<?php echo $datos['fecha_reserva']; ?>" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="campo" style="flex: 1;">
                    <label>Hora Inicio</label>
                    <input type="time" name="hora_inicio" id="hInicio" value="<?php echo substr($datos['hora_inicio'], 0, 5); ?>" required>
                </div>
                <div class="campo" style="flex: 1;">
                    <label>Hora Fin</label>
                    <input type="time" name="hora_fin" id="hFin" value="<?php echo substr($datos['hora_fin'], 0, 5); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn-actualizar">Guardar Cambios</button>
            <a href="ver_reservas.php" class="back-link">← Cancelar y volver</a>
        </form>
    </div>

    <script>
        // Reutilizamos tu lógica de validación de tiempo
        const hInicio = document.getElementById('hInicio');
        const hFin = document.getElementById('hFin');

        function validarTiempo() {
            if (hInicio.value && hFin.value) {
                const inicio = new Date(`2026-01-01T${hInicio.value}`);
                const fin = new Date(`2026-01-01T${hFin.value}`);
                const diff = Math.floor((fin - inicio) / 60000);

                if (diff > 70) {
                    alert("⚠️ Máximo 1 hora y 10 minutos por reserva.");
                    hFin.value = "";
                } else if (diff <= 0) {
                    alert("⚠️ La hora de fin debe ser mayor a la de inicio.");
                    hFin.value = "";
                }
            }
        }

        hInicio.addEventListener('change', validarTiempo);
        hFin.addEventListener('change', validarTiempo);
    </script>
</body>
</html>
