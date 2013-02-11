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
	$pdf->Image('images/new_businesscardtemplate.png', 0, 0, -150);
	$pdf->SetFont('Helvetica','',10);	
	$pdf->SetTextColor(47, 141, 203);

	//Purpose
	$pdf->SetXY(41.5, 40.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');
	$pdf->SetXY(130.5, 40.5);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(41.5, 91.25);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(130.5, 91.25);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(41.5, 142);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(130.5, 142);
	$pdf->MultiCell(85,4,$purpose,0,'J');		

	$pdf->SetXY(41.5, 192.75);
	$pdf->MultiCell(85,4,$purpose,0,'J');		
	$pdf->SetXY(130.5, 192.75);
	$pdf->MultiCell(85,4,$purpose,0,'J');

	//URL
	$pdf->SetXY(13, 47.75);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(102, 47.75);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(13, 98.65);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(102, 98.65);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(13, 149.5);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(102, 149.5);
	$pdf->Cell(90,6,$url,0,1,'C');		

	$pdf->SetXY(13, 200.25);
	$pdf->Cell(90,6,$url,0,1,'C');		
	$pdf->SetXY(102, 200.25);
	$pdf->Cell(90,6,$url,0,1,'C');	
	
	$pdf->SetTextColor(0, 0, 0);

	//Start date
	$pdf->SetXY(14.5, 56.25);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(104.5, 56.25);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(14.5, 107.25);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(104, 107.25);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(14.5, 158);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(104, 158);
	$pdf->Cell(90,6,$d,0,1,'C');		

	$pdf->SetXY(14.5, 208.75);
	$pdf->Cell(90,6,$d,0,1,'C');		
	$pdf->SetXY(104, 208.75);
	$pdf->Cell(90,6,$d,0,1,'C');
	
	//$pdf->Text(48,63,$d);		
	//$pdf->Text(20,71,$purpose);				

	//$pdf->Text(137,63,$d);		
	$pdf->Output();
?>