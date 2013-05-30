<?php
	require_once("include/autoload.class.php");
	require_once("include/check_updates.php");

if ($_SESSION['is_logged_in']) {
	$cur_user = mysql_fetch_array(mysql_query("SELECT city, state, longitude, latitude FROM users WHERE user_id = '".$_SESSION['user_id']."'"));
		$userCity = $cur_user['city'];
		$userState = $cur_user['state'];
		$userLong = $cur_user['longitude'];
		$userLat = $cur_user['latitude'];

	if (!isset($_COOKIE['LoggedIn'])) {
		setcookie( "UserCity", $userCity, strtotime( '+1 year' ) );
		setcookie( "UserState", $userState, strtotime( '+1 year' ) );
		setcookie( "UserLong", $userLong, strtotime( '+1 year' ) );
		setcookie( "UserLat", $userLat, strtotime( '+1 year' ) );
		setcookie( "LoggedIn", true, strtotime( '+1 year' ) );
	}
}
else {
	if ((!isset($_COOKIE['UserCity'])) || (!isset($_COOKIE['UserState']))) {
		$geoplugin = new geoPlugin();
		$geoplugin->locate();
		$userCity = $geoplugin->city;
		$userState = $geoplugin->region;
		$userLong = $geoplugin->longitude;
		$userLat = $geoplugin->latitude;

		setcookie( "UserCity", $userCity, strtotime( '+1 year' ) );
		setcookie( "UserState", $userState, strtotime( '+1 year' ) );
		setcookie( "UserLong", $userLong, strtotime( '+1 year' ) );
		setcookie( "UserLat", $userLat, strtotime( '+1 year' ) );
		setcookie( "LoggedIn", false, strtotime( '0 day' ) );
	}
	else {
		$userCity = $_COOKIE['UserCity'];
		$userState = $_COOKIE['UserState'];
		$userLong = $_COOKIE['UserLong'];
		$userLat = $_COOKIE['UserLat'];
	}

	if (param_post('location') == 'Update') {
		$zip = urlencode(param_post('zip'));

		$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$zip."&sensor=false");
		$loc = json_decode($json);

		if ($loc->status == 'OK') {

    			foreach ($loc->results[0]->address_components as $address) {
        			if (in_array("locality", $address->types)) {
            				$userCity = $address->long_name;
        			}
        			if (in_array("administrative_area_level_1", $address->types)) {
            				$userState = $address->short_name;
        			}
    			}
			$userLat = $loc->results[0]->geometry->location->lat;
			$userLong = $loc->results[0]->geometry->location->lng;
		}
		else { echo "Invalid Location!"; die; }

		setcookie( "UserCity", $userCity, strtotime( '+1 year' ) );
		setcookie( "UserState", $userState, strtotime( '+1 year' ) );
		setcookie( "UserLong", $userLong, strtotime( '+1 year' ) );
		setcookie( "UserLat", $userLat, strtotime( '+1 year' ) );
		setcookie( "LoggedIn", false, strtotime( '0 day' ) );

		header("Location:".$_SERVER['REQUEST_URI']);
		exit;
	}
}
	$userLocation = $userCity.", ".$userState;

	$sqlDist = " ( 3959 * acos( cos( radians($userLong) ) * cos( radians( longitude ) ) * cos( radians( latitude ) - radians($userLat) ) + sin( radians($userLong) ) * sin( radians( longitude ) ) ) ) ";
	
	$headline = "";
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	
	$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
	$stmt = $db->stmt_init();

if (!isset($_SESSION['is_logged_in'])) {
	if ((isset($_COOKIE['remember_me_id'])) && (isset($_COOKIE['remember_me_token']))) {
		$user_id = $_COOKIE['remember_me_id'];
		$old_token = $_COOKIE['remember_me_token'];
		$check = mysql_num_rows(mysql_query("SELECT * FROM user_sessions WHERE user_id = '$user_id' AND token = '$old_token'"));
		if ($check) {
			$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = '$user_id'"));

			$_SESSION['username'] = $user['username'];
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['is_logged_in'] = 1;

			if ($user['admin'] == yes) {
				$_SESSION['admin_access'] = true;
			}

			$token = new User();
			$token = $token->randString(32);
			$ip = getenv('REMOTE_ADDR');

			$updSess = mysql_query("UPDATE user_sessions SET token = '$token', ip = '$ip' WHERE user_id = '$user_id'");
			setcookie( "remember_me_id", $user_id, strtotime( '+1 month' ) );
			setcookie( "remember_me_token", $token, strtotime( '+1 month' ) );
		}
		else {
			$delSess = mysql_query("DELETE FROM user_sessions WHERE user_id = '$user_id' OR token = '$old_token'");
			setcookie( "remember_me_id", false, strtotime( '-1 month' ) );
			setcookie( "remember_me_token", false, strtotime( '-1 month' ) );
			setcookie( "LoggedIn", false, strtotime( '0 day' ) );
		}
	}
}
	
	if (isset($_POST['email']) && isset($_POST['password'])) {
		$email = $_POST['email'];
		$password = md5($_POST['password']);
		$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_array($res);
			if ($row['validation_code'] < 0) {
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['is_logged_in'] = 1;
			}
			else {
				$headline = "ERROR: Your account is not activated yet. Please check your email!";
			}
			if ($row['admin'] == yes) {
				$_SESSION['admin_access'] = true;
			}
			if (isset($_POST['remember_me'])) {
				$check = mysql_num_rows(mysql_query("SELECT * FROM user_sessions WHERE user_id = '$row[user_id]'"));
				$token = new User();
				$token = $token->randString(32);
				$ip = getenv('REMOTE_ADDR');

				if (!$check) {
					$newSess = mysql_query("INSERT INTO user_sessions (user_id, token, ip) VALUES ('$row[user_id]', '$token', '$ip')");
					setcookie( "remember_me_id", $row['user_id'], strtotime( '+1 month' ) );
					setcookie( "remember_me_token", $token, strtotime( '+1 month' ) );
				}
				else {
					$updSess = mysql_query("UPDATE user_sessions SET token = '$token', ip = '$ip' WHERE user_id = '$row[user_id]'");
					setcookie( "remember_me_id", $row['user_id'], strtotime( '+1 month' ) );
					setcookie( "remember_me_token", $token, strtotime( '+1 month' ) );
				}
			}
			else {
				$delSess = mysql_query("DELETE FROM user_sessions WHERE user_id = '$row[user_id]'");
				setcookie( "remember_me_id", false, strtotime( '-1 month' ) );
				setcookie( "remember_me_token", false, strtotime( '-1 month' ) );
			}
		} 
	}

	if (param_get('task') == 'logout') {
		$delSess = mysql_query("DELETE FROM user_sessions WHERE user_id = '$_SESSION[user_id]'");
		setcookie( "remember_me_id", false, strtotime( '-1 month' ) );
		setcookie( "remember_me_token", false, strtotime( '-1 month' ) );
		setcookie( "LoggedIn", false, strtotime( '0 day' ) );

		$_SESSION = array();
		session_destroy();
		echo "<script type='text/javascript'>window.location = 'index.php';</script>";		
	}

	if (param_get('name')) {
		$rewrite = "../";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="https://www.facebook.com/2008/fbml">
	<head>
		<title><?=TAG_LINE?></title>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
		<link rel="shortcut icon" href="<?=ACTIVE_URL?>favicon.ico?v=6" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/bootstrap.custom.css" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/siloz.css" />	
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/siloz_header.css" />	
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/siloz_footer.css" />	
		<link rel="stylesheet" tyle="text/css" href="<?=ACTIVE_URL?>css/jquery-ui-1.8.16.css"/>
    		<link href="<?=ACTIVE_URL?>themes/1/js-image-slider.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="<?=ACTIVE_URL?>include/fancybox/source/jquery.fancybox.css?v=2.1.0" type="text/css" media="screen" />
		<link rel='stylesheet' type='text/css' href='<?=ACTIVE_URL?>include/OpenInviter/more_examples/css/jquery.fancybox-1.3.4.css' media='screen' />
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery-1.9.0.min.js"></script>	
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery-ui-1.8.16.min.js"></script> 
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/source/jquery.fancybox.pack.js?v=2.1.0"></script>			
		<script type='text/javascript' src='<?=ACTIVE_URL?>include/OpenInviter/more_examples/js/jquery.fancybox-1.3.4.pack.js'></script>							
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/popup-window.js"></script>	  
	    <script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery.placeholder.js"></script>		
	    <script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery.jconfirmation.js"></script>				
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery.truncator.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/infobubble-compiled.js"></script>	  								
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/util.js"></script>	
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/change_location.js"></script>
	  	<script type="text/javascript">
			$(document).ready(function() {
		      	$('.max1500').truncate({max_length: 1500});			    
		      	$('.max800').truncate({max_length: 800});			    
		      	$('.max600').truncate({max_length: 600});			    
		      	$('.max400').truncate({max_length: 400});			    		
				$('.confirmation').jConfirmAction({question : "Are you sure to delete?", yesAnswer : "Yes", cancelAnswer : "No"});		
			});
	  	</script>

		<script src="<?=ACTIVE_URL?>jCrop/js/jquery.min.js"></script>
		<script src="<?=ACTIVE_URL?>jCrop/js/jquery.Jcrop.js"></script>
		<script src="<?=ACTIVE_URL?>js/md5.js"></script>
		<link rel="stylesheet" href="<?=ACTIVE_URL?>jCrop/css/jquery.Jcrop.css" type="text/css" />
		<link rel="stylesheet" href="<?=ACTIVE_URL?>demo_files/demos.css" type="text/css" />

    		<script src="<?=ACTIVE_URL?>themes/1/js-image-slider.js" type="text/javascript"></script>

		<?php
			//SPECIAL REDIRECT CASES
			$task = param_get('task');
			$silo_shortname = param_get('shortname');						
			if ($task == 'view_silo' && $silo_shortname != '') {
				$sql = "SELECT * FROM silos WHERE shortname = '$silo_shortname';";		
				$res = mysql_query($sql);
				$row = mysql_fetch_array($res);	
				$id = $row['id'];
				echo "<script>window.location = 'index.php?task=view_silo&id=$id';</script>";
			}		
		?>

    <SCRIPT language=Javascript>
       <!--
       function isNumberKey(evt)
       {
          var charCode = (evt.which) ? evt.which : event.keyCode;
          if (charCode != 46 && charCode > 31 
            && (charCode < 48 || charCode > 57))
             return false;

          return true;
       }
       //-->
    </SCRIPT>

	<meta name="google-site-verification" content="Yx4Ns5zGDP4tabuxvwAtGTY10jyw_CC4NjvBTISPcqc" />
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
				<img id="notice_exit" src="<?=ACTIVE_URL?>images/close_white.png" onclick="document.getElementById('notice').style.display = 'none';document.getElementById('overlay').style.display='none';"/>
			</div>						
			<img src="<?=ACTIVE_URL?>images/notice.png"/>
		</div>
		<?php
			setcookie('notice', '1');
		}
		?>
		<div id="main">
			<?php
			if ((count($_GET) == 0 && count($_POST) == 0) && (!$_SESSION['is_logged_in']) || param_get('allow') == "yes") {
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
							$subject = "Make a difference in your community, as a shopper, item donor, or silo administrator!";
							$message = "<h2>Welcome to ".SITE_NAME."!</h2>";
							$message .= "We want to thank you for creating an account with ".SITE_NAME."! We wanted to briefly tell you what you can expect as a user. ".SITE_NAME." allows local organizations to raise money by accepting donated items from local supporters.  Those items then appear for sale to the general public. <br><br>";
							$message .= "We believe ".SITE_NAME." is, quite simply, the best way for a community – private or public – to raise money for a cause.  Here are some reasons why: <br><br>";
							$message .= "<ul>
									<li>It's not shaking a collection jar; it's asking for items.</li>
									<li>Whether private or public, causes are local, and assist people you know, involve features you drive by every day, and organizations that make a real-world difference in the life of your community.</li>
									<li>Everybody wins – the silo administrator, the donor (who often receives a tax-deduction), and the buyer, who not only gets an item, but the knowledge that he or she is helping a local cause of their choosing.</li>
									<li>It's designed for viral promotion. There is no limit to a fundraising goal, and no limit to how many members can be part of a given silo.</li>
									<li>It's safe, it's transparent, and it's 90% efficient for public silos, and 95% efficient for private silos.</li>
									</ul> <br>";
							$message .= "We invite you to communicate your questions and concerns with us.<br><br>";
							$message .= "Thank You, and Happy Fundraising, <br><br><br>";
							$message .= "Zackery West <br><br> CEO, ".SITE_NAME." LLC";
							email_with_template($User->email, $subject, $message);
							mysql_query("UPDATE users SET info_emails = 1 WHERE user_id = '$User->user_id'");
						}
						else if ($activate === "active") {
							$headline = "Your account was already activated, please login!";							
						}
						if ($headline != "")
							echo "<div style='font-size: 14px; font-weight: bold; color: red; text-align: center'>$headline</div>";
						include('search_item.php');
					}
					else if ($task != '') {
						if ($headline != "") {
							echo "<div style='font-size: 14px; font-weight: bold; color: red; text-align: center'>$headline</div>";
						}
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
								window.location = "items";
							</script>
							<?
						}
					}
				?>				
			</div>
			<?php
			}
			if (count($_GET) == 0 && count($_POST) == 0 && (!$_SESSION['is_logged_in']) || param_get('allow') == "yes"){} else { echo '<div id="push"></div>'; }
			?>
		</div>
		</div>
			<div id="new-footer">
				<?php
					if (count($_GET) == 0 && count($_POST) == 0 && (!$_SESSION['is_logged_in']) || param_get('allow') == "yes") {
					} 
					else {
						include('footer.php'); 
					}
				?>
			</div>
	<script>
	    $('input[placeholder], textarea[placeholder]').placeholder();
	</script>		
	</body>
</html>

    <script type="text/javascript"> 
      $(document).ready( function() {
        $('#success').delay(2000).fadeOut();
      });

$('#email, #password').keypress(function(event){
  if(event.keyCode == 13){
    $('#login_button').click();
  }
});
    </script>

<script type="text/javascript"> 
	$("#category").change(function () {
    		if($(this).val() == "") $(this).addClass("empty");
    		else $(this).removeClass("empty")
	});

	$("#category").change();
</script>