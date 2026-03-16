<?php
ob_start();
require('fpdf.php'); 
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo_reporte'];
    
    // Capturamos las fechas del rango personalizado si existen
    $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d');
    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');

    class PDF extends FPDF {
        function Header() {
            $this->SetFillColor(20, 20, 20);
            $this->Rect(0, 0, 210, 35, 'F');
            $this->SetFont('Arial', 'B', 18);
            $this->SetTextColor(46, 204, 113); 
            $this->Cell(0, 15, iconv('UTF-8', 'windows-1252', 'Canchas La 103 - Informe Financiero'), 0, 1, 'C');
            $this->SetFont('Arial', 'I', 10);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', 'Detalle de Reservas, Canchas y Recaudo'), 0, 1, 'C');
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

    // LÓGICA DE CONSULTAS CON FILTRO Y ORDEN ASCENDENTE
    if ($tipo == 'diario') {
        $titulo = 'REPORTE DE VENTAS DEL DÍA: ' . date('d/m/Y');
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva 
                FROM Reservas r 
                JOIN Usuarios u ON r.id_usuario = u.id 
                JOIN Canchas c ON r.id_cancha = c.id
                WHERE r.fecha_reserva = CURDATE()
                ORDER BY r.hora_inicio ASC"; // Orden cronológico
    } elseif ($tipo == 'personalizado') {
        $titulo = 'REPORTE DEL ' . date("d/m/Y", strtotime($fecha_inicio)) . ' AL ' . date("d/m/Y", strtotime($fecha_fin));
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva 
                FROM Reservas r 
                JOIN Usuarios u ON r.id_usuario = u.id 
                JOIN Canchas c ON r.id_cancha = c.id
                WHERE r.fecha_reserva BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC"; // Orden cronológico por rango
    } else {
        $titulo = 'REPORTE DETALLADO - ÚLTIMOS 7 DÍAS';
        $sql = "SELECT r.id, u.nombre AS usuario, c.nombre_cancha, r.precio_total_cancha, r.hora_inicio, r.fecha_reserva 
                FROM Reservas r 
                JOIN Usuarios u ON r.id_usuario = u.id 
                JOIN Canchas c ON r.id_cancha = c.id
                WHERE r.fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC"; // Orden cronológico
    }

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $titulo), 0, 1);
    $pdf->Ln(5);

    // Encabezados
    $pdf->SetFillColor(46, 204, 113);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial', 'B', 9);
    
    $pdf->Cell(12, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Tipo Cancha', 1, 0, 'C', true);
    $pdf->Cell(55, 10, 'Jugador', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Hora', 1, 0, 'C', true);
    $pdf->Cell(33, 10, 'Monto', 1, 1, 'C', true);

    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 9);
    
    $res = mysqli_query($conexion, $sql);
    $total_recaudado = 0;

    while($row = mysqli_fetch_assoc($res)) {
        $pdf->Cell(12, 10, $row['id'], 1, 0, 'C');
        $fecha_formateada = date("d/m/Y", strtotime($row['fecha_reserva']));
        $pdf->Cell(25, 10, $fecha_formateada, 1, 0, 'C');
        $pdf->Cell(35, 10, iconv('UTF-8', 'windows-1252', $row['nombre_cancha']), 1, 0, 'C');
        $pdf->Cell(55, 10, iconv('UTF-8', 'windows-1252', $row['usuario']), 1, 0);
        $pdf->Cell(30, 10, $row['hora_inicio'], 1, 0, 'C');
        $pdf->Cell(33, 10, '$' . number_format($row['precio_total_cancha']), 1, 1, 'R');
        
        $total_recaudado += $row['precio_total_cancha'];
    }

    // Fila de Total
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(157, 12, 'MONTO TOTAL RECAUDADO:', 1, 0, 'R', true);
    $pdf->Cell(33, 12, '$' . number_format($total_recaudado), 1, 1, 'R', true);

    ob_end_clean();
    $pdf->Output('I', 'Reporte_La103_Ventas.pdf');
}
?>