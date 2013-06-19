<html>
<head>
    <style type="text/css">
    .flyer {
	padding: 30px;
	margin-bottom: 10px;
	border-radius: 4px; 
	border: 2px solid #2f8dcb;
    }
    </style>
</head>
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

	$flyer = '<td align="left" class="flyer">
		<img src="images/logo.png" width="289" height="62"></img>
		<p align="center"><b>'.$silo->getTitle().'</b></p>
		<p>We are having a community fundraiser on <i>'.SITE_NAME.'.com</i>, from '.$start_date.' - '.$end_date.'. The silo will close at '.$end_time.' on that '.$end_dayofweek.'.</p>
		<p>Cause: '.$silo->getPurpose().'
		<p>Please help us by donating items to our fundraiser (silo); these items then sell to the local public.</p>
		<p>'.ACTIVE_URL.'silos/'.$silo->shortname.'</p>
	</td>';

	$flyer = '<td align="left" class="flyer">
		<img src="images/logo.png" width="289" height="62"></img>
		<p align="center"><b>'.$silo->getTitle().'</b></p>
		<p>We are holding a \'silo\' and are asking for donated items, which sell to help our cause!</p>
		<p>Think of it as a rummage sale on the Internet.</p>
		<p align="center"><b>How to Help Us!</b></p>
		<p>1) Go to: '.ACTIVE_URL.'silos/'.$silo->shortname.'</p>
		<p>2) Join our silo by listing items that sell around '.$silo->admin->getLocation().'</p>
		<p>3) After our silo ends, on '.$end_date.', we keep 90% of the money raised!</p>
		<p>You can also help by spreading the word!</p>
	</td>';
?>

<head>
	<link rel="stylesheet" type="text/css" media="print" href="print.css" />
</head>

<table style="text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 15pt;" width="100%" height="100%;">
<tr>
	<?=$flyer?>
	<td width="15px"></td>
	<?=$flyer?>

</tr>
<tr><td style="padding-bottom: 15px"></td></tr>
<tr>
	<?=$flyer?>
	<td></td>
	<?=$flyer?>
</tr>
</table>