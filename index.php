<?php
	require_once("include/autoload.class.php");
	require_once("include/check_updates.php");

//Ensure the page is supposed to be secured, otherwise redirect to non-secure link
$secure_pages = array("", "payment", "my_account", "transaction_console");
$current_page = param_get('task');

if (!in_array($current_page, $secure_pages) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') { 
   	echo "<script>window.location = 'http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "';</script>";
    	exit();
}

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

		$json = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=".$zip."&sensor=false");
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
			$user = mysql_fetch_array(mysql_query("SELECT user_id, admin FROM users WHERE user_id = '$user_id'"));

			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['is_logged_in'] = 1;

			if ($user['admin'] == yes) {
				$_SESSION['admin_access'] = true;
			}

			$user = new User();
			$token = $user->randString(32);
			$ip = $user->getUserIP();

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

				if ($row['admin'] == yes) {
					$_SESSION['admin_access'] = true;
				}
				if (isset($_POST['remember_me'])) {
					$check = mysql_num_rows(mysql_query("SELECT * FROM user_sessions WHERE user_id = '$row[user_id]'"));
						$user = new User();
						$token = $user->randString(32);
						$ip = $user->getUserIP();

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
			else {
				$headline = "Your account is not activated. Please check your email!";
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
		<meta name="description" content="siloz or <?=SITE_NAME?> is a marketplace for items donated for causes in your community, private and public. People list items out of their homes, and don't have to move them to a parking lot or field. We allow churches, mosques, synagogues, schools, local non-profits, public university student unions or student groups, civic organizations and neighborhood organizations to raise money. Donations are often tax-deductible. Moreover, a person can hold a private, unlisted, fundraiser (called a 'silo'), and raise money without the public">
		<meta name="keywords" content="silent auction, donate your vehicle to charity, donate my vehicle to charity, donate my boat to charity, donate for a tax-deduction, crowdfunding, crowd-funding, raise money, fundraising, fund-raising, tax-deductible donation, tax-deductible, rummage sale, church rummage sale, school rummage sale, rummage sale fundraiser, rummage sale fundraising, donation to 510 c 3, charitable deduction, irs deduction, car wash fundraiser, bake sale fundraiser, candy bar sale fundraiser, alternative fundraising, charitable concerts, neighborhood block parties, crowd-sourcing, crowdsourcing, wedding fund, policeman's fund, firefighter's fund, civic fundraising, neighborhood fundraising, public university fundraising, youth sports fundraiser, youth soccer fundraiser, youth baseball fundraiser, little league fundraiser, little league fundraising, alternative to indiegogo, alternative to kickstarter, alternative to rockethub, alternative to wepay, alternative to gofundme">
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
		<link rel="shortcut icon" href="<?=ACTIVE_URL?>favicon.ico?v=6" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/bootstrap.custom.css" />
		<link rel="stylesheet" type="text/css" href="css/siloz.css" />
		<link rel="stylesheet" type="text/css" href="css/siloz_header.css" />
		<link rel="stylesheet" type="text/css" href="css/siloz_footer.css" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>css/jquery-ui-1.8.16.css"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Roboto:100"/>

	<?php if (param_get('task') == 'view_silo') { ?> 
		<link href="<?=ACTIVE_URL?>themes/1/silo-slider.css" rel="stylesheet" type="text/css" />
	<?php } else { ?>
		<link href="<?=ACTIVE_URL?>themes/1/js-image-slider.css" rel="stylesheet" type="text/css" />
	<?php } ?>

		<link rel='stylesheet' type='text/css' href='<?=ACTIVE_URL?>include/OpenInviter/more_examples/css/jquery.fancybox-1.3.4.css' media='screen' />
		
		<!-- Fancybox includes -->
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>include/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.6" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>include/fancybox/source/jquery.fancybox.css?v=2.1.0" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?=ACTIVE_URL?>include/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.3" />
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/lib/jquery-1.8.0.min.js"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/source/jquery.fancybox.js?v=2.1.0"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.3"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.6"></script>
		<script type="text/javascript" src="<?=ACTIVE_URL?>include/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.3"></script>
		<!-- End Fancybox includes	-->

		<script type="text/javascript">
			$(".fancybox").fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'elastic',
				'speedIn'		:	100, 
				'speedOut'		:	100, 
				'overlayShow'	:	false
			});
		</script>

	    	<script type="text/javascript" src="<?=ACTIVE_URL?>js/jquery.placeholder.js"></script>		
		<script type="text/javascript" src="<?=ACTIVE_URL?>js/change_location.js"></script>

		<script src="<?=ACTIVE_URL?>jCrop/js/jquery.Jcrop.js"></script>
		<script src="<?=ACTIVE_URL?>js/md5.js"></script>
		<link rel="stylesheet" href="<?=ACTIVE_URL?>jCrop/css/jquery.Jcrop.css" type="text/css" />

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
				if (count($_GET) == 0 && count($_POST) == 0 && (!$_SESSION['is_logged_in']) || param_get('allow') == "yes" || param_get('ref') == "start") {
					$splash = "true";
				}

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
						$headline = "Your account in now activated, please login!";
					}
					elseif ($activate === "active") {
						$headline = "Your account was already activated, please login!";							
					}
					elseif ($activate === "incorrect") {
						$headline = "Wrong confirmation code. Try again!";							
					}
					include('header.php');
					include('search_item.php');
				}
				elseif ($splash) {
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
					if ($task != '') {
						include($task.'.php');
					}
					else {
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
			if ($splash){} else { echo '<div id="push"></div>'; }
			?>
		</div>
		</div>
			<div id="new-footer">
				<?php
					if ($splash) {
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