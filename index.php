<?php
// 1. Iniciamos la sesión para poder leer los datos del usuario
session_start();

// 2. EL ESCUDO: Si no existe el ID en la sesión, significa que no se ha logueado.
if (!isset($_SESSION['id'])) {
    header("Location: reservar.html");
    exit();
}

// 3. Traemos el nombre y el rol que guardamos en validar_login.php
$nombre = $_SESSION['nombre'];
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'cliente';
$id_usuario = $_SESSION['id']; // Guardamos el ID para usarlo en los filtros
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel La 103 | Inicio</title>
    <style>
        /* Estilo general negro y verde */
        body { 
            margin: 0; font-family: 'Segoe UI', sans-serif; 
            background: #0f0f0f; color: white; 
            display: flex; flex-direction: column; align-items: center; 
            min-height: 100vh;
        }
        .header { 
            width: 100%; padding: 20px; background: #1a1a1a; 
            border-bottom: 3px solid #2ecc71; text-align: center; 
            box-sizing: border-box;
        }
        .header h1 { color: #2ecc71; margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        
        .bienvenida { margin: 40px 0; text-align: center; }
        .bienvenida h2 { font-weight: 300; }
        .bienvenida span { color: #2ecc71; font-weight: bold; border-bottom: 2px solid #2ecc71; }

        .menu-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 25px; width: 95%; max-width: 1100px; padding: 20px; 
        }

        .opcion-card { 
            background: #1a1a1a; padding: 30px; border-radius: 20px; 
            text-align: center; border: 1px solid #333; 
            transition: all 0.3s ease; cursor: pointer; 
            text-decoration: none; color: white; 
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .opcion-card:hover { 
            border-color: #2ecc71; 
            transform: translateY(-10px); 
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.15); 
        }
        .opcion-card i { font-size: 50px; display: block; margin-bottom: 15px; font-style: normal; }
        .opcion-card h3 { margin: 10px 0; color: #2ecc71; font-size: 20px; }
        .opcion-card p { color: #888; font-size: 14px; line-height: 1.5; margin-bottom: 15px; }

        /* Estilo especial para la tarjeta de Admin (Amarillo) */
        .admin-card { border: 1px solid #f1c40f55; }
        .admin-card h3 { color: #f1c40f; }
        .admin-card:hover { border-color: #f1c40f; box-shadow: 0 10px 30px rgba(241, 196, 15, 0.15); }

        /* Estilo especial para la tarjeta de Reportes (Rojo) */
        .reporte-card { border: 1px solid #e74c3c55; cursor: default; }
        .reporte-card h3 { color: #e74c3c; }
        .reporte-card:hover { border-color: #e74c3c; transform: none; box-shadow: 0 10px 30px rgba(231, 76, 60, 0.15); }
        
        .select-reporte {
            width: 100%; padding: 10px; background: #252525; color: white; 
            border: 1px solid #444; border-radius: 8px; margin-bottom: 15px; outline: none;
        }
        .btn-descargar {
            width: 100%; padding: 12px; background: #e74c3c; border: none; 
            border-radius: 8px; color: white; font-weight: bold; 
            cursor: pointer; transition: 0.3s; text-transform: uppercase; font-size: 12px;
        }
        .btn-descargar:hover { background: #c0392b; }

        .btn-salir { 
            margin-top: 50px; color: #e74c3c; text-decoration: none; 
            font-weight: bold; border: 2px solid #e74c3c; 
            padding: 12px 30px; border-radius: 10px; 
            transition: 0.3s; text-transform: uppercase; font-size: 13px;
            margin-bottom: 40px;
        }
        .btn-salir:hover { background: #e74c3c; color: white; box-shadow: 0 0 20px rgba(231, 76, 60, 0.4); }

        /* Estilo para los inputs de fecha en la tarjeta */
        .input-fecha-reporte {
            width: 44%; padding: 8px; background: #222; color: white; border: 1px solid #444; border-radius: 5px; font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>⚽ CANCHAS LA 103</h1>
    </div>

    <div class="bienvenida">
        <h2>¡Bienvenido al equipo, <span><?php echo htmlspecialchars($nombre); ?></span>!</h2>
        <p style="color: #666; font-size: 12px;">Sesión iniciada como: <?php echo strtoupper($rol); ?></p>
        <p>Selecciona tu jugada para hoy:</p>
    </div>

    <div class="menu-grid">
        <a href="reservar.php" class="opcion-card">
            <i>📅</i>
            <h3>Agendar Partido</h3>
            <p>Reserva tu cancha sintética y asegura el turno para tu equipo antes de que se agoten.</p>
        </a>

        <?php if ($rol != 'admin'): ?>
        <a href="ver_reserva.php" class="opcion-card">
            <i>📋</i>
            <h3>Mis Reservas</h3>
            <p>Consulta tus partidos programados, horarios y el estado de tus pagos pendientes.</p>
        </a>
        <?php endif; ?>

        <?php if ($rol === 'admin'): ?>
        <a href="admin_reservas.php" class="opcion-card admin-card">
            <i>💰</i>
            <h3>Control Total</h3>
            <p>Panel exclusivo: Gestiona deudas, confirma pagos y revisa TODAS las reservas de la sede.</p>
        </a>

        <a href="admin_inventario.php" class="opcion-card" style="border: 1px solid #3498db55;">
            <i style="color: #3498db;">📦</i>
            <h3 style="color: #3498db;">Inventario Real</h3>
            <p>Agrega, edita o elimina implementos deportivos sin entrar a la base de datos.</p>
        </a>

        <div class="opcion-card reporte-card">
            <i>📄</i>
            <h3>Informes PDF</h3>
            <p>Descarga el balance de ingresos detallado de la sede.</p>
            <form action="generar_reporte.php" method="POST" target="_blank">
                <select name="tipo_reporte" id="tipo_reporte" class="select-reporte" onchange="toggleFechas()">
                    <option value="diario">Ingresos de Hoy</option>
                    <option value="semanal">Resumen Semanal</option>
                    <option value="personalizado">Rango Personalizado</option>
                </select>

                <div id="rango_fechas" style="display: none; margin-bottom: 15px; justify-content: space-between; align-items: center; display: none;">
                    <input type="date" name="fecha_inicio" class="input-fecha-reporte" value="<?php echo date('Y-m-d'); ?>">
                    <span style="color: #888;">a</span>
                    <input type="date" name="fecha_fin" class="input-fecha-reporte" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit" class="btn-descargar">Descargar Reporte</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <a href="logout.php" class="btn-salir">Cerrar Sesión Segura</a>

    <script>
    function toggleFechas() {
        var tipo = document.getElementById('tipo_reporte').value;
        var divFechas = document.getElementById('rango_fechas');
        // Usamos flex para que los inputs queden alineados si se muestra
        divFechas.style.display = (tipo === 'personalizado') ? 'flex' : 'none';
    }
    </script>

</body>
</html>