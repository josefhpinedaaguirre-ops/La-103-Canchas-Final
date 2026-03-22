<?php
// 1. ACTIVAR REPORTES DE ERROR
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Bogota');

ob_start();

if (!file_exists('fpdf.php')) {
    die("Error: No se encuentra el archivo 'fpdf.php'.");
}

require('fpdf.php'); 
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo_reporte'] ?? 'diario';
    
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? mysqli_real_escape_string($conexion, $_POST['fecha_inicio']) : date('Y-m-d');
    $fecha_fin = !empty($_POST['fecha_fin']) ? mysqli_real_escape_string($conexion, $_POST['fecha_fin']) : date('Y-m-d');

    class PDF extends FPDF {
        function Header() {
            $this->SetFillColor(20, 20, 20);
            $this->Rect(0, 0, 210, 35, 'F');
            $this->SetFont('Arial', 'B', 18);
            $this->SetTextColor(46, 204, 113); 
            $this->Cell(0, 15, iconv('UTF-8', 'windows-1252', 'Canchas La 103 - Recaudo Real'), 0, 1, 'C');
            $this->SetFont('Arial', 'I', 10);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', 'Ingresos por Pagos Realizados'), 0, 1, 'C');
            $this->Ln(15);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100);
            $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 11);

    // --- LÓGICA DE FILTROS POR FECHA DE PAGO (Ventas del momento) ---
    // Nota: Se usa pa.fecha_pago (o como se llame tu columna en la tabla pagos)
    
    if ($tipo == 'diario') {
        $hoy = date('Y-m-d');
        $titulo = 'INGRESOS RECIBIDOS HOY: ' . date('d/m/Y');
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva, pa.fecha_pago 
                FROM pagos pa
                JOIN reservas r ON pa.id_reserva = r.id
                JOIN usuarios u ON r.id_usuario = u.id 
                JOIN canchas c ON r.id_cancha = c.id
                WHERE DATE(pa.fecha_pago) = '$hoy'
                ORDER BY pa.fecha_pago DESC";
    } elseif ($tipo == 'personalizado') {
        $titulo = 'VENTAS DEL ' . date("d/m/Y", strtotime($fecha_inicio)) . ' AL ' . date("d/m/Y", strtotime($fecha_fin));
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva, pa.fecha_pago 
                FROM pagos pa
                JOIN reservas r ON pa.id_reserva = r.id
                JOIN usuarios u ON r.id_usuario = u.id 
                JOIN canchas c ON r.id_cancha = c.id
                WHERE DATE(pa.fecha_pago) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY pa.fecha_pago ASC";
    } else {
        $titulo = 'RECAUDO TOTAL - ÚLTIMOS 7 DÍAS';
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva, pa.fecha_pago 
                FROM pagos pa
                JOIN reservas r ON pa.id_reserva = r.id
                JOIN usuarios u ON r.id_usuario = u.id 
                JOIN canchas c ON r.id_cancha = c.id
                WHERE DATE(pa.fecha_pago) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                ORDER BY pa.fecha_pago ASC";
    }

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $titulo), 0, 1);
    $pdf->Ln(5);

    // Encabezados
    $pdf->SetFillColor(46, 204, 113);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(12, 10, 'ID Res', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'F. Partido', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Cancha', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Jugador', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Fecha Pago', 1, 0, 'C', true);
    $pdf->Cell(33, 10, 'Monto', 1, 1, 'C', true);

    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 8);
    
    $res = mysqli_query($conexion, $sql);
    
    if (!$res) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    $total_recaudado = 0;
    while($row = mysqli_fetch_assoc($res)) {
        $pdf->Cell(12, 10, $row['id'], 1, 0, 'C');
        $f_partido = date("d/m/Y", strtotime($row['fecha_reserva']));
        $pdf->Cell(25, 10, $f_partido, 1, 0, 'C');
        $pdf->Cell(35, 10, iconv('UTF-8', 'windows-1252', $row['nombre_cancha']), 1, 0, 'C');
        $pdf->Cell(50, 10, iconv('UTF-8', 'windows-1252', $row['usuario']), 1, 0);
        
        // Mostramos cuándo se hizo el pago realmente
        $f_pago = date("d/m/Y H:i", strtotime($row['fecha_pago']));
        $pdf->Cell(35, 10, $f_pago, 1, 0, 'C');
        
        $pdf->Cell(33, 10, '$' . number_format($row['precio_total_cancha']), 1, 1, 'R');
        $total_recaudado += $row['precio_total_cancha'];
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(157, 12, 'TOTAL DINERO ENTRADO:', 1, 0, 'R', true);
    $pdf->Cell(33, 12, '$' . number_format($total_recaudado), 1, 1, 'R', true);

    if (ob_get_length()) ob_end_clean();
    $pdf->Output('I', 'Reporte_Ventas_Real.pdf');
    exit; 
}
?>
