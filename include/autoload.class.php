<?php
	
	session_start();
	require_once('utils.php');
	require_once('config.php');
	require_once('classes/silo.class.php');
	require_once('classes/item.class.php');	
	require_once('classes/user.class.php');
	require_once('classes/photo.class.php');
	
	require_once('classes/feed.class.php');
	require_once('classes/notification.class.php');
	require_once('classes/paycodes.class.php');
	require_once("classes/Vouch.class.php");
	require_once("classes/VouchType.class.php");
	
	# mysql data classes
	require_once("classes/Flag.class.php");
	require_once("classes/FlagItem.class.php");
	require_once("classes/FlagSilo.class.php");
	require_once("classes/FlagRadar.class.php");
	
	# the radar functions
	require_once("classes/Radar.class.php");
	require_once("classes/ItemPurchase.class.php");
	
	require_once("classes/Formatter.class.php");
	require_once("classes/Common.class.php");
	
	
	require_once('include/geoplugin.class.php');
	require_once('include/captcha/securimage.php');
	require_once('include/OpenInviter/openinviter.php');

	setlocale(LC_MONETARY, 'en_US');
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	
	$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
?>
