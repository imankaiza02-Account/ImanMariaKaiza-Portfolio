<?php
session_start();
require 'fpdf.php';
include 'DonRegConnection.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff','Admin'])) {
    die('Access denied');
}

$res = $object->query("
    SELECT i.item_name, i.quantity, i.status, i.created_at, i.distributed_date, i.expiry_date, c.category_name
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    ORDER BY i.created_at DESC
");

$pdf = new FPDF('L','mm','A4'); 
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Full Inventory Report',0,1,'L');

$pdf->Ln(4);

// Table Headers
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,8,'Item Name',1);
$pdf->Cell(18,8,'Quantity',1,0,'C');
$pdf->Cell(38,8,'Category',1,0,'C');
$pdf->Cell(25,8,'Expiry',1,0,'C');
$pdf->Cell(30,8,'Added',1,0,'C');
$pdf->Cell(30,8,'Distributed',1,0,'C');
$pdf->Cell(25,8,'Status',1,1,'C');

$pdf->SetFont('Arial','',8);

while ($r = $res->fetch_assoc()) {
    $pdf->Cell(40,7,substr($r['item_name'],0,22),1);
    $pdf->Cell(18,7,(int)$r['quantity'],1,0,'C');
    $pdf->Cell(38,7,substr($r['category_name'] ?: 'Uncategorized',0,20),1,0,'C');
    $pdf->Cell(25,7,substr($r['expiry_date'] ?: '-',0,12),1,0,'C');
    $pdf->Cell(30,7,substr($r['created_at'],0,16),1,0,'C');
    $pdf->Cell(30,7,substr($r['distributed_date'] ?: '-',0,16),1,0,'C');
    $pdf->Cell(25,7,$r['status'],1,1,'C');
}

$pdf->Output('I','full_inventory_'.date('Ymd').'.pdf');
exit;