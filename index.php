<?php 
	require_once("include/autoload.class.php");
	
	$geoplugin = new geoPlugin();
	$geoplugin->locate();
	
	$headline = "";
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	
	$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
	$stmt = $db->stmt_init();
	
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_array($res);
			if ($row['validation_code'] == -1) {
				$_SESSION['username'] = $username;
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['is_logged_in'] = 1;
			}
			else {
				$headline = "ERROR: Your account is not activated yet. Please check your email!";
			}
		} 
	}

	if ($_SESSION['is_logged_in']) {
		$sql = "SELECT * FROM users WHERE username='".$_SESSION['username']."'";
		$res = mysql_query($sql);		
		$current_user = mysql_fetch_array($res);		
	}
	if (param_get('task') == 'logout') {
		$_SESSION = array();
		session_destroy();
		echo "<script type='text/javascript'>window.location = 'index.php';</script>";		
	}		
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="https://www.facebook.com/2008/fbml">
	<head>
		<title>SiloZ - Commerce That Counts</title>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8"> 
		<link rel="stylesheet" type="text/css" href="css/siloz.css" />	
		<link rel="stylesheet" type="text/css" href="css/siloz_header.css" />	
		<link rel="stylesheet" type="text/css" href="css/siloz_footer.css" />	
		<link rel="stylesheet" tyle="text/css" href="css/jquery-ui-1.8.16.css"/>
		<link rel="stylesheet" href="include/fancybox/source/jquery.fancybox.css?v=2.1.0" type="text/css" media="screen" />
		<link rel='stylesheet' type='text/css' href='include/OpenInviter/more_examples/css/jquery.fancybox-1.3.4.css' media='screen' />
		<script type="text/javascript" src="include/fancybox/source/jquery.fancybox.pack.js?v=2.1.0"></script>			
		<script type='text/javascript' src='include/OpenInviter/more_examples/js/jquery.fancybox-1.3.4.pack.js'></script>			
	    <script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>	
		<script type="text/javascript" src="js/jquery-ui-1.8.16.min.js"></script> 				
		<script type="text/javascript" src="js/popup-window.js"></script>	  
	    <script type="text/javascript" src="js/jquery.placeholder.js"></script>		
	    <script type="text/javascript" src="js/jquery.jconfirmation.js"></script>				
		<script type="text/javascript" src="js/jquery.truncator.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false"></script>
		<script type="text/javascript" src="js/infobubble-compiled.js"></script>	  								
		<script type="text/javascript" src="js/util.js"></script>	  								
	  	<script type="text/javascript">
			$(document).ready(function() {
		      	$('.max1500').truncate({max_length: 1500});			    
		      	$('.max800').truncate({max_length: 800});			    
		      	$('.max600').truncate({max_length: 600});			    
		      	$('.max400').truncate({max_length: 400});			    		
				$('.confirmation').jConfirmAction({question : "Are you sure to delete?", yesAnswer : "Yes", cancelAnswer : "No"});		
			});
	  	</script>
	
		<?php
			//SPECIAL REDIRECT CASES
			$task = param_get('task');			
			$silo_shortname = param_get('shortname');						
			if ($task == 'view_silo' && $silo_shortname != '') {
				$sql = "SELECT * FROM silos WHERE shortname = '$silo_shortname';";		
				$res = mysql_query($sql);
				$row = mysql_fetch_array($res);	
				$id = $row['id'];
				echo "<script>window.location = '/alpha/index.php?task=view_silo&id=$id';</script>";
			}		
		?>
		<script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-33231904-1']);
		  _gaq.push(['_setDomainName', 'siloz.com']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
		<meta name="google-site-verification" content="IdsGEo2aFhBP7TVnPEfSzjkF7rDKpsv4RviuTlHTjt8" />				
	</head>
	<body>
		<div id="overlay">	
		</div>	
		<?php
		if (false && (!isset($_COOKIE['notice']) || $_COOKIE['notice'] != '1')) {
		?>
		<script>
			document.getElementById('overlay').style.display='block';	
	  	</script>
		<div id="notice" style="position: fixed; z-index: 1000; top: 50%; left: 50%; margin-left: -300px; margin-top: -200px;">
			<div id="notice_drag" style="float:right;margin-left: -5px;">
				<img id="notice_exit" src="images/close_white.png" onclick="document.getElementById('notice').style.display = 'none';document.getElementById('overlay').style.display='none';"/>
			</div>						
			<img src="images/notice.png"/>
		</div>
		<?php
			setcookie('notice', '1');
		}
		?>
		<div id="main">
			<?php
			if (count($_GET) == 0 && count($_POST) == 0) {
				include('splash.php');
			} 
			else {
			?>
			<div id="header">
				<?php
					include('header.php');
				?>
			</div>
			<div id="main_body">				
				<?php
					$task = param_get('task');
					if ($task == '') 
						$task = param_post('task');
					$search = param_get('search');
					if ($task == 'validate_registration') {
						$User = new User(param_get('id'));

						$code = param_get('code');
						$activate = $User->ValidateRegistration($_REQUEST["id"],$code);
						error_log("ACTIVATE: ".$activate);
						if ($activate === "success"){
							$headline = "Your account has been activated, please login!";
						}
						else if ($activate === "active") {
							$headline = "Your account was already activated, please login!";							
						}
						if ($headline != "")
							echo "<div style='font-size: 14px; font-weight: bold; color: red; text-align: center'>$headline</div>";
						include('search_item.php');
					}
					else if ($task != '') {
						if ($headline != "")
							echo "<div style='font-size: 14px; font-weight: bold; color: red; text-align: center'>$headline</div>";
						include($task.'.php');
					}
					else {
						if ($headline != "")
							echo "<div style='font-size: 14px; font-weight: bold; color: red; text-align: center'>$headline</div>";
						if ($search == 'silo') {
							include('search_silo.php');
						}							
						else if ($search == 'item') {
							include('search_item.php');
						}
						else {
							?>
							<script type="text/javascript">
								window.location = "index.php?search=item";
							</script>
							<?
						}
					}
				?>				
			</div>
			<?php
			}
			?>
			<div id="footer">
				<?php
					include('footer.php');
				?>
			</div>
		</div>
	<script>
	    $('input[placeholder], textarea[placeholder]').placeholder();
	</script>		
	</body>
</html>
