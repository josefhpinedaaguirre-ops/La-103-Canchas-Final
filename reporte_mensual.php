<?php
// 1. Bloqueo total de errores que dañan el PDF
error_reporting(0);
ini_set('display_errors', 0);

// 2. Ruta de fuentes
define('FPDF_FONTPATH', 'font/'); 

require('fpdf.php'); 
include("conexion.php");
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado.");
}

/**
 * 3. SOLUCIÓN DEFINITIVA PARA TEXTO:
 * Usamos iconv que viene en todos los PHP por defecto.
 * Si iconv falla, devolvemos el texto tal cual.
 */
function limpiar_texto($txt) {
    if (function_exists('iconv')) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt);
    }
    return $txt; 
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',15);
        $this->SetTextColor(46, 204, 113); 
        $this->Cell(0,10, limpiar_texto('CANCHAS LA 103 - REPORTE DE INGRESOS'),0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->SetTextColor(100);
        $this->Cell(0,10, 'Generado el: '.date('d/m/Y'),0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10, limpiar_texto('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Consulta de totales por mes
$sql_meses = "SELECT MONTH(fecha_reserva) as mes, YEAR(fecha_reserva) as anio, SUM(precio_total_cancha) as total_mes 
              FROM Reservas 
              WHERE estado_reserva = 'confirmada' 
              GROUP BY anio, mes 
              ORDER BY anio DESC, mes DESC";

$res_meses = mysqli_query($conexion, $sql_meses);

if (mysqli_num_rows($res_meses) == 0) {
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10, limpiar_texto('No hay ingresos confirmados para reportar todavía.'), 0, 1, 'C');
}

while ($mes_data = mysqli_fetch_assoc($res_meses)) {
    $mes_num = $mes_data['mes'];
    $anio = $mes_data['anio'];
    
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10, "MES: $mes_num / $anio - TOTAL: $".number_format($mes_data['total_mes'], 0, ',', '.'), 1, 1, 'L', true);
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(60, 7, 'Jugador', 1);
    $pdf->Cell(40, 7, 'Fecha', 1);
    $pdf->Cell(50, 7, 'Cancha', 1);
    $pdf->Cell(40, 7, 'Ingreso', 1, 1);
    
    $sql_detalles = "SELECT u.nombre, r.fecha_reserva, c.nombre_cancha, r.precio_total_cancha 
                     FROM Reservas r
                     JOIN Usuarios u ON r.id_usuario = u.id
                     JOIN Canchas c ON r.id_cancha = c.id
                     WHERE MONTH(r.fecha_reserva) = $mes_num AND YEAR(r.fecha_reserva) = $anio 
                     AND r.estado_reserva = 'confirmada'";
    
    $res_detalles = mysqli_query($conexion, $sql_detalles);
    $pdf->SetFont('Arial','',10);
    
    while ($det = mysqli_fetch_assoc($res_detalles)) {
        $pdf->Cell(60, 7, limpiar_texto($det['nombre']), 1);
        $pdf->Cell(40, 7, $det['fecha_reserva'], 1);
        $pdf->Cell(50, 7, limpiar_texto($det['nombre_cancha']), 1);
        $pdf->Cell(40, 7, '$'.number_format($det['precio_total_cancha'], 0, ',', '.'), 1, 1);
    }
    $pdf->Ln(5);
}

// Limpiar el buffer para que no haya ni un solo espacio de sobra
if (ob_get_length()) ob_end_clean();

$pdf->Output('I', 'Reporte_La103.pdf'); 
?>