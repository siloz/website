<?php
	require('config.php');
	require('utils.php');
	require('classes/silo.class.php');
	require('classes/user.class.php');
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	$id = param_get('id');
	$check = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE id = '$id'"));
	$silo = new Silo($id);

	if (!$check) {
		echo "<script>window.location = 'index.php';</script>";
	}

	$start = strtotime($silo->created_date);
	$start_date = date("M jS, Y", $start);
	$end = strtotime($silo->end_date);
	$end_date = date("M jS, Y", $end);
	$end_time = date("g:i a", $end);
	$end_dayofweek = date("l", $end);

	$flyer = '<td align="center" style="padding: 30px">
		<img src="images/logo.png" width="289" height="62"></img>
		<p>We are having a community fundraiser on <i>'.SITE_NAME.'.com</i>, from '.$start_date.' - '.$end_date.'. The silo will close at '.$end_time.' on that '.$end_dayofweek.'.</p>
		<p>Cause: '.$silo->getPurpose().'
		<p>Please help us by donating items to our fundraiser (silo); these items then sell to the local public.</p>
		<p>'.ACTIVE_URL.'silos/'.$silo->shortname.'</p>
	</td>';
?>

<head>
	<link rel="stylesheet" type="text/css" media="print" href="print.css" />
</head>

<table style="font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 15pt;" width="100%" height="100%">
<tr>
	<?=$flyer?>
	<?=$flyer?>

</tr>
<tr>
	<?=$flyer?>
	<?=$flyer?>
</tr>
</table>