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
    $id = $_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM Implementos WHERE id = $id");
    header("Location: admin_inventario.php");
}

// 2. AGREGAR O EDITAR
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre_objeto'];
    $cantidad = $_POST['cantidad_total'];
    $estado = $_POST['estado_objeto'];

    if (isset($_POST['id_editar']) && !empty($_POST['id_editar'])) {
        // ACTUALIZAR (Ajustado a tus nombres de columna reales)
        $id = $_POST['id_editar'];
        $sql = "UPDATE Implementos SET 
                nombre_objeto='$nombre', 
                cantidad_total='$cantidad', 
                estado_objeto='$estado' 
                WHERE id=$id";
    } else {
        // INSERTAR NUEVO
        $sql = "INSERT INTO Implementos (nombre_objeto, cantidad_total, estado_objeto) 
                VALUES ('$nombre', '$cantidad', '$estado')";
    }
    mysqli_query($conexion, $sql);
    header("Location: admin_inventario.php");
}

// 3. OBTENER DATOS PARA EDITAR
$edit_data = null;
if (isset($_GET['editar'])) {
    $id_edit = $_GET['editar'];
    $res = mysqli_query($conexion, "SELECT * FROM Implementos WHERE id = $id_edit");
    $edit_data = mysqli_fetch_assoc($res);
}

// 4. CONSULTA GENERAL
$inventario = mysqli_query($conexion, "SELECT * FROM Implementos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | La 103</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: white; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        .container { width: 95%; max-width: 900px; background: #1a1a1a; padding: 25px; border-radius: 15px; border: 1px solid #333; }
        h2 { color: #2ecc71; border-bottom: 2px solid #2ecc71; padding-bottom: 10px; }
        
        /* Formulario Ajustado */
        form { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 30px; background: #252525; padding: 15px; border-radius: 10px; }
        input, select { padding: 10px; border-radius: 5px; border: 1px solid #444; background: #111; color: white; }
        .btn-guardar { background: #2ecc71; color: black; font-weight: bold; border: none; cursor: pointer; padding: 0 20px; border-radius: 5px; }
        
        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2ecc71; color: black; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #333; }
        tr:hover { background: #222; }
        
        .estado-tag { padding: 4px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold; }
        .bueno { background: #2ecc7122; color: #2ecc71; border: 1px solid #2ecc71; }
        .regular { background: #f1c40f22; color: #f1c40f; border: 1px solid #f1c40f; }
        .malo { background: #e74c3c22; color: #e74c3c; border: 1px solid #e74c3c; }

        .btn-edit { color: #f1c40f; text-decoration: none; margin-right: 15px; font-weight: bold; }
        .btn-del { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .back-link { margin-top: 20px; color: #888; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠️ Gestión de Inventario - La 103</h2>
    
    <form action="" method="POST">
        <input type="hidden" name="id_editar" value="<?php echo $edit_data['id'] ?? ''; ?>">
        
        <input type="text" name="nombre_objeto" placeholder="Nombre del objeto" 
               value="<?php echo $edit_data['nombre_objeto'] ?? ''; ?>" required>
        
        <input type="number" name="cantidad_total" placeholder="Cant." 
               value="<?php echo $edit_data['cantidad_total'] ?? ''; ?>" required>

        <select name="estado_objeto">
            <option value="bueno" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'bueno') ? 'selected' : ''; ?>>Bueno</option>
            <option value="regular" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
            <option value="malo" <?php echo (isset($edit_data['estado_objeto']) && $edit_data['estado_objeto'] == 'malo') ? 'selected' : ''; ?>>Malo</option>
        </select>
        
        <button type="submit" class="btn-guardar"><?php echo $edit_data ? 'ACTUALIZAR' : 'AGREGAR'; ?></button>
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
                <td><?php echo $row['nombre_objeto']; ?></td>
                <td><strong><?php echo $row['cantidad_total']; ?></strong></td>
                <td>
                    <span class="estado-tag <?php echo $row['estado_objeto']; ?>">
                        <?php echo $row['estado_objeto']; ?>
                    </span>
                </td>
                <td>
                    <a href="?editar=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                    <a href="?eliminar=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('¿Seguro?')">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="index.php" class="back-link">← Volver al Panel</a>
</div>

</body>
</html>