<?php
session_start();
include("conexion.php");

// SEGURIDAD: Solo el admin entra aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// --- LÓGICA DEL CRUD ---

// 1. ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['eliminar']);
    mysqli_query($conexion, "DELETE FROM implementos WHERE id = $id");
    header("Location: admin_inventario.php");
}

// 2. AGREGAR O EDITAR
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_objeto']);
    $cantidad = (int)$_POST['cantidad_total']; // Forzamos a entero
    $estado = mysqli_real_escape_string($conexion, $_POST['estado_objeto']);

    // VALIDACIÓN PHP: Si la cantidad es menor a 0, la forzamos a 0
    if ($cantidad < 0) {
        $cantidad = 0;
    }

    if (isset($_POST['id_editar']) && !empty($_POST['id_editar'])) {
        $id = $_POST['id_editar'];
        $sql = "UPDATE implementos SET 
                nombre_objeto='$nombre', 
                cantidad_total='$cantidad', 
                estado_objeto='$estado' 
                WHERE id=$id";
    } else {
        $sql = "INSERT INTO implementos (nombre_objeto, cantidad_total, estado_objeto) 
                VALUES ('$nombre', '$cantidad', '$estado')";
    }
    mysqli_query($conexion, $sql);
    header("Location: admin_inventario.php");
}

// 3. OBTENER DATOS PARA EDITAR
$edit_data = null;
if (isset($_GET['editar'])) {
    $id_edit = mysqli_real_escape_string($conexion, $_GET['editar']);
    $res = mysqli_query($conexion, "SELECT * FROM implementos WHERE id = $id_edit");
    $edit_data = mysqli_fetch_assoc($res);
}

// 4. CONSULTA GENERAL
$inventario = mysqli_query($conexion, "SELECT * FROM implementos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | La 103</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: white; display: flex; flex-direction: column; align-items: center; padding: 20px; margin: 0; }
        .container { width: 95%; max-width: 900px; background: #1a1a1a; padding: 25px; border-radius: 15px; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #2ecc71; border-bottom: 2px solid #2ecc71; padding-bottom: 10px; text-transform: uppercase; }
        
        form { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 30px; background: #252525; padding: 15px; border-radius: 10px; border: 1px solid #444; }
        input, select { padding: 12px; border-radius: 8px; border: 1px solid #444; background: #111; color: white; outline: none; }
        input:focus { border-color: #2ecc71; }
        .btn-guardar { background: #2ecc71; color: black; font-weight: bold; border: none; cursor: pointer; padding: 0 25px; border-radius: 8px; transition: 0.3s; }
        .btn-guardar:hover { background: #27ae60; transform: scale(1.02); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2ecc71; color: black; padding: 12px; text-align: left; text-transform: uppercase; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #333; }
        tr:hover { background: #222; }
        
        .estado-tag { padding: 5px 10px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: bold; display: inline-block; }
        .bueno { background: #2ecc7122; color: #2ecc71; border: 1px solid #2ecc71; }
        .regular { background: #f1c40f22; color: #f1c40f; border: 1px solid #f1c40f; }
        .malo { background: #e74c3c22; color: #e74c3c; border: 1px solid #e74c3c; }

        .btn-edit { color: #f1c40f; text-decoration: none; margin-right: 15px; font-weight: bold; border: 1px solid #f1c40f; padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .btn-del { color: #e74c3c; text-decoration: none; font-weight: bold; border: 1px solid #e74c3c; padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .btn-edit:hover { background: #f1c40f; color: black; }
        .btn-del:hover { background: #e74c3c; color: white; }
        .back-link { margin-top: 25px; color: #2ecc71; text-decoration: none; font-size: 14px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠️ Inventario de Implementos</h2>
    
    <form action="" method="POST">
        <input type="hidden" name="id_editar" value="<?php echo $edit_data['id'] ?? ''; ?>">
        
        <input type="text" name="nombre_objeto" placeholder="Nombre (Ej: Balón Golty)" 
               value="<?php echo htmlspecialchars($edit_data['nombre_objeto'] ?? ''); ?>" required>
        
        <input type="number" name="cantidad_total" placeholder="Cant." min="0"
               value="<?php echo $edit_data['cantidad_total'] ?? ''; ?>" required>

        <select name="estado_objeto">
            <option value="bueno" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'bueno') ? 'selected' : ''; ?>>Bueno</option>
            <option value="regular" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
            <option value="malo" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'malo') ? 'selected' : ''; ?>>Malo</option>
        </select>
        
        <button type="submit" class="btn-guardar"><?php echo $edit_data ? 'GUARDAR' : 'AGREGAR'; ?></button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Objeto</th>
                <th>Cantidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($inventario)): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($row['nombre_objeto']); ?></strong></td>
                <td><?php echo $row['cantidad_total']; ?> unds.</td>
                <td>
                    <span class="estado-tag <?php echo $row['estado_objeto']; ?>">
                        <?php echo $row['estado_objeto']; ?>
                    </span>
                </td>
                <td>
                    <a href="?editar=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                    <a href="?eliminar=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('¿Eliminar este objeto del inventario?')">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="index.php" class="back-link">← Volver al Panel de Control</a>
</div>

</body>
</html>
