<?php
session_start();
require 'fpdf.php';
include 'DonRegConnection.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff','Admin'])) {
    die('Access denied');
}

$res = $object->query("
    SELECT i.item_name, i.quantity, i.created_at, i.expiry_date, c.category_name
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE i.status='In Inventory' 
    ORDER BY i.created_at DESC
");

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Active Inventory Report',0,1,'L');

$pdf->Ln(4);

// Headers
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,8,'Item Name',1);
$pdf->Cell(20,8,'Quantity',1,0,'C');
$pdf->Cell(45,8,'Category',1,0,'C');
$pdf->Cell(30,8,'Expiry Date',1,0,'C');
$pdf->Cell(35,8,'Date Added',1,1,'C');

$pdf->SetFont('Arial','',9);

while ($r = $res->fetch_assoc()) {
    $pdf->Cell(60,7,substr($r['item_name'],0,35),1);
    $pdf->Cell(20,7,(int)$r['quantity'],1,0,'C');
    $pdf->Cell(45,7,substr($r['category_name'] ?: 'Uncategorized',0,25),1,0,'C');
    $pdf->Cell(30,7,$r['expiry_date'] ?: '-',1,0,'C');
    $pdf->Cell(35,7,substr($r['created_at'],0,16),1,1,'C');
}

$pdf->Output('I','active_inventory_'.date('Ymd').'.pdf');
exit;