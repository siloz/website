<?php

	require('config.php');
	require('utils.php');
	require('classes/silo.class.php');
	require('classes/user.class.php');
	require('pdf/fpdf.php');
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	$silo = new Silo($_POST['id']);
	$tmp = explode("-", $silo->start_date);
	$months = array("Jan","Feb","Mar","Apr","May","June","July","Aug","Sept","Oct","Nov","Dec");
	$day = intval($tmp[2], 10);
	if ($day == 1)
		$day = $day."st";
	else if ($day == 2)
		$day = $day."nd";
	else 
		$day = $day."th";
	$d = $months[intval($tmp[1], 10) - 1]." ".$day.", ".$tmp[0];
	$url = "http://www.siloz.com/silo/".$silo->shortname;
	$purpose = substr($silo->purpose, 0, 90)."..";
	$pdf = new FPDF('P', 'mm', 'Letter');
	$pdf->AddPage();
	$pdf->Image('images/businesscardtemplate.png', 0, 0, -150);
	$pdf->SetFont('Helvetica','',10);	
	$pdf->SetTextColor(47, 141, 203);
	//URL
	$pdf->SetXY(18, 50);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(107, 50);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(18, 108.5);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(107, 108.5);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(18, 167);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(107, 167);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(18, 225.5);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(107, 225.5);
	$pdf->Cell(90,6,$url,0,1,'C');		
	
	$pdf->SetTextColor(0, 0, 0);
	//Start date
	$pdf->SetXY(18, 58.5);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(107, 58.5);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(18, 117);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(107, 117);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(18, 175.5);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(107, 175.5);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(18, 234);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(107, 234);
	$pdf->Cell(90,6,$d,0,1,'C');		

	//Purpose
	$pdf->SetXY(20, 68);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(109, 68);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(20, 126.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(109, 126.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(20, 185);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(109, 185);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(20, 243.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(109, 243.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	
	//$pdf->Text(48,63,$d);		
	//$pdf->Text(20,71,$purpose);				

	//$pdf->Text(137,63,$d);		
	$pdf->Output();
?>