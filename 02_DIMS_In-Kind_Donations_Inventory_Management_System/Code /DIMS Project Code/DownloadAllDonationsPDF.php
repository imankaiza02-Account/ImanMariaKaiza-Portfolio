<?php
session_start();
require 'fpdf.php'; 
include 'DonRegConnection.php';

if (!isset($_SESSION['user_id'])) die('Access denied');
$id = (int)$_SESSION['user_id'];

//Fetch all donations for this donor (NO STATUS ANYMORE)
$donStmt = $object->prepare("
    SELECT d.donation_id, d.total_items, d.donation_date, u.username
    FROM donations d 
    JOIN users u ON d.donor_id = u.user_id
    WHERE d.donor_id = ?
    ORDER BY d.donation_date DESC
");
$donStmt->bind_param("i", $id);
$donStmt->execute();
$donRes = $donStmt->get_result();

//PDF Setup
$pdf = new FPDF();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'All Donations Report',0,1,'C');
$pdf->Ln(4);

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,'Generated: '.date('F j, Y, g:i a'),0,1,'R');
$pdf->Ln(6);

//Get username (first row only)
$first = $donRes->fetch_assoc();
if ($first) {
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,7,'Username: ' . $first['username'], 0, 1);
    $pdf->Ln(5);
}

//Loop through donations
$don = $first;
while ($don) {

    //Donation header (NO STATUS)
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(
        0,
        7,
        'Donation Number: ' . $don['donation_id'] . 
        '   |   Date: ' . date('M j, Y', strtotime($don['donation_date'])),
        0,
        1
    );
    $pdf->Ln(3);

    //Fetch items
    $itemStmt = $object->prepare("
        SELECT item_name, quantity, description, itemStatus
        FROM donation_items
        WHERE donation_id = ?
        ORDER BY item_id ASC
    ");
    $itemStmt->bind_param("i", $don['donation_id']);
    $itemStmt->execute();
    $items = $itemStmt->get_result();

    //Table header
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(60,7,'Item Name',1,0,'C');
    $pdf->Cell(20,7,'Quantity',1,0,'C');
    $pdf->Cell(35,7,'Status',1,0,'C');
    $pdf->Cell(75,7,'Description',1,1,'C');

    //Table rows with wrapping
    $pdf->SetFont('Arial','',10);

    while ($it = $items->fetch_assoc()) {

        //Text wrapping
        $itemName = wordwrap($it['item_name'], 28, "\n");
        $desc = $it['description'] ? wordwrap($it['description'], 55, "\n") : '-';
        $status = wordwrap($it['itemStatus'], 18, "\n");

        //Compute max row height
        $lines = max(
            substr_count($itemName, "\n") + 1,
            substr_count($status, "\n") + 1,
            substr_count($desc, "\n") + 1
        );

        $rowHeight = 6 * $lines;

        //Current X/Y tracking
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        //Item Name
        $pdf->MultiCell(60, 6, $itemName, 1, 'L');
        $pdf->SetXY($x + 60, $y);

        //Quantity
        $pdf->MultiCell(20, 6, (int)$it['quantity'], 1, 'C');
        $pdf->SetXY($x + 80, $y);

        //Status
        $pdf->MultiCell(35, 6, $status, 1, 'C');
        $pdf->SetXY($x + 115, $y);

        //Description
        $pdf->MultiCell(75, 6, $desc, 1, 'L');
    }

    $pdf->Ln(8);
    $don = $donRes->fetch_assoc();
}
$pdf->Output('I','all_donations_'.date('Ymd').'.pdf');
exit;
?>