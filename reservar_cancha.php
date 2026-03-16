<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: reservar.html"); // Si no hay sesión, lo devuelve al login
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar | La 103</title>
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .reserva-box { background: #1a1a1a; padding: 30px; border-radius: 20px; width: 450px; border-top: 4px solid #2ecc71; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h2 { text-align: center; color: #2ecc71; text-transform: uppercase; margin-bottom: 25px; }
        h3 { color: #f1c40f; font-size: 16px; text-transform: uppercase; margin-top: 20px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        .campo { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #aaa; font-size: 14px; }
        select, input, textarea { width: 100%; padding: 12px; border: 1px solid #333; border-radius: 8px; background: #252525; color: white; box-sizing: border-box; outline: none; }
        textarea { resize: none; height: 60px; font-family: inherit; }
        .implementos-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; }
        .btn-reservar { width: 100%; padding: 15px; background: #2ecc71; border: none; border-radius: 8px; color: black; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 20px; transition: 0.3s; }
        .btn-reservar:hover { background: #27ae60; transform: scale(1.02); }
        .volver { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="reserva-box">
        <p style="text-align:center; color:#2ecc71; margin-top: 0;">Jugador: <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
        <h2>⚽ Reserva tu Turno</h2>
        
        <form action="guardar_reserva.php" method="POST">
            <div class="campo">
                <label>Selecciona la Cancha</label>
                <select name="id_cancha" required>
                    <option value="1">Sintética #1 (Fútbol 5)</option>
                    <option value="2">Sintética #2 (Fútbol 7)</option>
                    <option value="3">Sintética #3 (Fútbol 11)</option>
                </select>
            </div>

            <div class="campo">
                <label>Fecha del Encuentro</label>
                <input type="date" name="fecha_reserva" required>
            </div>

            <div class="implementos-grid">
                <div class="campo">
                    <label>Hora Inicio</label>
                    <input type="time" name="hora_inicio" required>
                </div>
                <div class="campo">
                    <label>Hora Fin</label>
                    <input type="time" name="hora_fin" required>
                </div>
            </div>

            <h3>🎒 Préstamo de Implementos</h3>
            
            <div class="implementos-grid">
                <div class="campo">
                    <label>Implemento</label>
                    <select name="id_implemento">
                        <option value="">Ninguno</option>
                        <option value="1">Balón Profesional</option>
                        <option value="2">Juego de Petos (10 unidades)</option>
                        <option value="3">Guantes de Portero</option>
                        <option value="4">Tula de Conos</option>
                    </select>
                </div>
                <div class="campo">
                    <label>Cantidad</label>
                    <input type="number" name="cantidad_prestamo" value="1" min="1">
                </div>
            </div>

            <div class="campo">
                <label>Notas o Referencias</label>
                <textarea name="notas_prestamo" placeholder="Ej: Se requiere balón de microfútbol..."></textarea>
            </div>

            <button type="submit" class="btn-reservar">Confirmar Reserva y Préstamo</button>
            <a href="index.php" class="volver">← Volver al Menú Principal</a>
        </form>
    </div>
</body>
</html>