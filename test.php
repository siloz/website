<?php
$cr_date = mysql_fetch_row(mysql_query("SELECT created_date FROM silos WHERE silo_id = '$silo_id'")); 
$created = $cr_date[0];
$now = strtotime("now");
$runTime = 1;

$i = 1 * $runTime;
$upto = $i * 7;
while ($i <= $upto) {
	$add = "date_add('".$created."', interval ".$i." day)";
	$var = "d".$i;
	$$var = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND joined_date < $add AND removed_date = 0"));
	$new = $i + $runTime;
	$new_date = strtotime($created . " +".$new." day");
	echo $new."<br><br>";
	echo $var."<br><br>";
	echo $new_date."<br><br>";
	if ($new_date > $now) { break; } else { $i = $i + $runTime; }
}
?>