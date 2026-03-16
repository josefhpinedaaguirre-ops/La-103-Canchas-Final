<?php
session_start();
include("conexion.php");

// Verificación de seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit("No autorizado");
}

// 1. Obtener el ID de la reserva y consultar su precio ya calculado
if (isset($_GET['id'])) {
    $id_reserva_get = $_GET['id'];
    
    // Traemos el precio que se calculó automáticamente al reservar
    $query_reserva = "SELECT precio_total_cancha FROM Reservas WHERE id = '$id_reserva_get'";
    $res_query = mysqli_query($conexion, $query_reserva);
    $datos_reserva = mysqli_fetch_assoc($res_query);
    
    if (!$datos_reserva) {
        exit("Reserva no encontrada");
    }
    
    $monto_fijo = $datos_reserva['precio_total_cancha'];
} else {
    header("Location: admin_reservas.php");
    exit();
}

// 2. Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_reserva = $_POST['id_reserva'];
    $monto = $_POST['monto']; // Viene del campo readonly
    $metodo = $_POST['metodo_pago'];

    // Insertamos en la tabla Pagos
    $sql_pago = "INSERT INTO Pagos (id_reserva, monto_pagado, metodo_pago) VALUES ('$id_reserva', '$monto', '$metodo')";
    
    // Actualizamos el estado de la reserva
    $sql_update = "UPDATE Reservas SET estado_reserva = 'confirmada' WHERE id = '$id_reserva'";

    if (mysqli_query($conexion, $sql_pago) && mysqli_query($conexion, $sql_update)) {
        echo "<script>alert('Pago registrado con éxito'); window.location.href='admin_reservas.php';</script>";
    } else {
        echo "Error en la operación: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Pago | La 103</title>
    <style>
        body { background: #0f0f0f; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .pago-box { background: #1a1a1a; padding: 30px; border-radius: 15px; border-top: 4px solid #f1c40f; width: 350px; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
        h3 { color: #f1c40f; text-align: center; margin-top: 0; }
        label { display: block; margin-top: 15px; color: #aaa; font-size: 14px; }
        select, input { width: 100%; padding: 12px; margin-top: 5px; background: #252525; color: white; border: 1px solid #333; border-radius: 8px; box-sizing: border-box; outline: none; }
        input[readonly] { background: #121212; color: #2ecc71; font-weight: bold; border-color: #2ecc71; cursor: not-allowed; }
        .btn { background: #2ecc71; color: black; font-weight: bold; border: none; padding: 15px; width: 100%; border-radius: 8px; cursor: pointer; text-transform: uppercase; margin-top: 20px; transition: 0.3s; }
        .btn:hover { background: #27ae60; }
        .cancelar { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="pago-box">
        <h3>💰 Registrar Pago</h3>
        <p style="text-align: center; font-size: 12px; color: #888;">Reserva #<?php echo $id_reserva_get; ?></p>
        
        <form method="POST">
            <input type="hidden" name="id_reserva" value="<?php echo $id_reserva_get; ?>">
            
            <label>Monto a Cobrar (Tarifa Fija)</label>
            <input type="number" name="monto" value="<?php echo $monto_fijo; ?>" readonly>

            <label>Método de Pago</label>
            <select name="metodo_pago" required>
                <option value="efectivo">💵 Efectivo</option>
                <option value="nequi">📱 Nequi</option>
                <option value="daviplata">📱 Daviplata</option>
                <option value="transferencia">🏦 Transferencia</option>
            </select>

            <button type="submit" class="btn">Confirmar y Finalizar</button>
            <a href="admin_reservas.php" class="cancelar">Volver sin cambios</a>
        </form>
    </div>
</body>
</html>