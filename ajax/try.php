<?php
require('../public/assets/plugins/fpdf182/fpdf.php');
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'hhello');
$pdf->Output('try.pdf', 'D');?>