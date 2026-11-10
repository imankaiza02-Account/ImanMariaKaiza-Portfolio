<?php
session_start();
include "DonRegConnection.php";
require 'fpdf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Forbidden");
}

$res = $object->query("SELECT user_id, username, email, role, contactDetails, approvalStatus, dateJoined FROM users ORDER BY role ASC, dateJoined DESC");

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'All Users Report',0,1,'C');
$pdf->Ln(4);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,8,'ID',1);
$pdf->Cell(60,8,'Username',1);
$pdf->Cell(70,8,'Email',1);
$pdf->Cell(30,8,'Role',1);
$pdf->Cell(30,8,'Phone',1);
$pdf->Cell(30,8,'Status',1);
$pdf->Cell(30,8,'Joined',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
while ($r = $res->fetch_assoc()) {
    $pdf->Cell(30,7,$r['user_id'],1);
    $pdf->Cell(60,7,substr($r['username'],0,30),1);
    $pdf->Cell(70,7,substr($r['email'],0,45),1);
    $pdf->Cell(30,7,$r['role'],1);
    $pdf->Cell(30,7,substr($r['contactDetails']?:'-',0,15),1);
    $pdf->Cell(30,7,$r['approvalStatus'],1);
    $pdf->Cell(30,7,substr($r['dateJoined']?:'-',0,16),1);
    $pdf->Ln();
}

$pdf->Output('I','users_'.date('Ymd').'.pdf');
exit;