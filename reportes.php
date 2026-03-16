<div class="header-info" style="flex-direction: column; align-items: flex-start;">
    <h2 style="color: #2ecc71; margin-bottom: 15px;">📊 GENERAR REPORTES PDF</h2>
    
    <form action="generar_reporte.php" method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        
        <div>
            <label style="display:block; font-size: 12px; color: #888;">Tipo de Reporte</label>
            <select name="tipo_reporte" style="padding: 10px; background: #222; color: white; border: 1px solid #333; border-radius: 5px;">
                <option value="diario">Ventas del Día</option>
                <option value="semanal">Últimos 7 Días</option>
                <option value="personalizado">Rango Personalizado</option>
            </select>
        </div>

        <div>
            <label style="display:block; font-size: 12px; color: #888;">Desde</label>
            <input type="date" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" style="padding: 9px; background: #222; color: white; border: 1px solid #333; border-radius: 5px;">
        </div>

        <div>
            <label style="display:block; font-size: 12px; color: #888;">Hasta</label>
            <input type="date" name="fecha_fin" value="<?php echo date('Y-m-d'); ?>" style="padding: 9px; background: #222; color: white; border: 1px solid #333; border-radius: 5px;">
        </div>

        <button type="submit" style="background: #2ecc71; color: black; border: none; padding: 11px 20px; border-radius: 5px; font-weight: bold; cursor: pointer;">
            📄 GENERAR PDF
        </button>
    </form>
</div>