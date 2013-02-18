<?php
	if (param_post('location') == 'Update-header') {
		$zip = urlencode(param_post('zip'));

		$url = "http://maps.google.com/maps/geo?q=".$zip;
		$xml = file_get_contents($url);
		$geo_json = json_decode($xml, TRUE);
		if ($geo_json['Status']['code'] == '200') {
			$precision = $geo_json['Placemark'][0]['AddressDetails']['Accuracy'];
			$new_adr = $geo_json['Placemark'][0]['address'];
			$userCity = $geo_json['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['Locality']['LocalityName'];
			$userState = $geo_json['Placemark'][0]['AddressDetails']['Country']['AdministrativeArea']['AdministrativeAreaName'];
			$userLong = $geo_json['Placemark'][0]['Point']['coordinates'][0];
			$userLat = $geo_json['Placemark'][0]['Point']['coordinates'][1];
		} else {
			$err .= 'Invalid Zip Code.<br/>';
		}

		setcookie( "UserCity", $userCity, strtotime( '+1 year' ) );
		setcookie( "UserState", $userState, strtotime( '+1 year' ) );
		setcookie( "UserLong", $userLong, strtotime( '+1 year' ) );
		setcookie( "UserLat", $userLat, strtotime( '+1 year' ) );

		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
?>

<script type="text/javascript">
	function create_silo_need_login() {
		popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);
		document.forms['login_form'].elements['purpose'].value = 'create_silo';
	}
	
	function create_account_with_purpose() {
		if (document.forms['login_form'].elements['purpose'].value == '') {
			window.location.replace('index.php?task=create_account')
		}
		else {
			window.location.replace('index.php?task=create_account&action='+document.forms['login_form'].elements['purpose'].value);	
		}
	}
</script>
<div id="top_menu">
	<!-- <div class="row">
			<div id="logo_container">
				<a href="index.php" style="text-decoration:none"><img src="images/logo_main.png"/></a>			
			</div>
			<div id="logged_out_supernav">
				
				
				<span class="blue">login/create account</span>
				<span class="blue separator">|</span>
				<span class="blue">start a silo</span>
			</div>
		</div> -->
	<!--<a href="index.php?task=stories" class="bold_text">silozstories</a>-->					
	
	<span class="gray">Your location:</span> 
	<span class="blue"><?=$userLocation?></span>
	<span class="blue separator">|</span>
	<?php
		if (isset($_SESSION['user_id'])) {
			$user_id = $_SESSION['user_id'];
			$sql = "SELECT * FROM silos WHERE admin_id = $user_id AND end_date >= '".date('Y-m-d')."'";
			$res = mysql_query($sql);
			if (mysql_num_rows($res) == 0) {
	?>
				<a href="index.php?task=create_silo"><span class="blue">start a silo</span></a>
				<span class="blue separator">|</span>
	<?php
			}
			else {
	?>
				<a href="index.php?task=manage_silo"><span class="blue">manage your silo</span></a>		
				<span class="blue separator">|</span>
	<?php
			}
	?>
			<a href="index.php?task=my_account"><span class="blue">my account</span></a>	
	<?php
		} else {
	?>
			<a href="javascript:popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><span class="blue">login/create account</span></a>
			<span class="blue separator">|</span>	
			<a href="javascript:create_silo_need_login();"><span class="blue">start a silo</span></a>
	<?php
		}
	?>
</div>
<div class="login" id="login">
	<div id="login_drag" style="float:right">
		<img id="login_exit" src="images/close.png"/>
	</div>
	<div>
		<form name="login_form" id="login_form" method="POST">
			<input type="hidden" name="purpose" value=""/>
			<h2>Login to your account</h2>
			<table>
				<tr>
					<td>
						<input type="text" name="username" id="username" onfocus="select();" placeholder="Username"/>
					</td>
					<td>
						<input type="password" name="password" id="password" onfocus="select();"  placeholder="Password"/>
					</td>
				</tr>
			</table>
			<input type="checkbox" name="remember_me" value="yes" /> Keep me logged in <br/>
			<div id="login_status"></div>
			<br/>			
			<button type="button" id="login_button">Login</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('login').style.display='none';">Cancel</button>
		</form>
		<script>
			$("#login_button").click(function(event) {	
				var un = document.getElementById('username').value;
				var pw = document.getElementById('password').value;							
				if (un == '' || pw == '') {
					document.getElementById("login_status").innerHTML = "<font color='red'><b><br/>Error: Empty username/password</b></font>";
				}
				else {
					$.post(<?php echo "'".API_URL."'"; ?>, 
						{	
							request: 'login',
							username: un, 
							password: pw
						}, 
						function (data) {
							if (parseInt($(data).find('authenticated').text(), 10) == 1) {
								document.getElementById('overlay').style.display='none';
								document.getElementById('login').style.display='none';
								document.getElementById("login_status").innerHTML = "";								
								document.forms['login_form'].submit();							
							}						
							else {
								document.getElementById("login_status").innerHTML = "<font color='red'><b><br/>Error: Invalid username/password</b></font>";
								document.getElementById('password').value = "";								
							}
						}
					);
					
				}
			});
		</script>
		<br/>
		<hr/>
<!--
		<h2>Login with an Existing Account </h2>
		<img src="images/facebook_logo.png"/>
		<img src="images/google_logo.jpg"/>
		<img src="images/amazon_logo.png"/>
		<img src="images/paypal_logo.png"/>
		<hr/>
-->		
		<h2>Forgot your username/password?</h2>
		<a href="index.php?task=reset_password"><button>Reset Password</button></a>			
		<br/><br/>
		<hr/>
		<h2>Create a Siloz Account</h2>
		<button type="button" onclick="create_account_with_purpose()">Create Account</button>			
	</div>
</div>

<div class="login" id="location" style="width: 300px;">
	<div id="location_drag" style="float:right">
		<img id="location_exit" src="images/close.png"/>
	</div>
	<div>
	<?php if (!$_SESSION['is_logged_in']) { ?>
		<form action="" method="POST">
			<input type="hidden" name="purpose" value=""/>
			<h2>Update your location below:</h2>
			<input onclick=this.value="" type="text" value="Enter Zip Code" name="zip">
			<br/><br>	
			<button type="submit" name="location" value="Update-header">Update</button>
		</form>
	<?php } else { echo "<b>Your location was obtained through your account when you signed up. <br><br> To change your location, please update your account information.</b>"; } ?>
	</div>
</div>

<form id="search_form" name="search_form">
<div id="logo_container">
	<a href="index.php" style="text-decoration:none"><img src="images/logo.png"/></a>			
</div>
<!-- <div align="right" style="margin-top: -30px; margin-right: 10px; font-size: 12px; line-height: 25px;">
	Raise money by accepting items, pledged items - all online. Start a silo now!
</div> -->
<div id="status" align="right" style="width: 965px; margin-top: -22px; position: absolute;">
<?php
	if ($_SESSION['admin_access']) {
		$header = "<a href='administrator/' target='_blank' style='padding-right: 20px; text-decoration: none'><font color='red'><b>Admin Login</b></font></a>";
	}
	if ($_SESSION['is_logged_in']) {
		$header .= "Hello <b>".$_SESSION['username'].",</b> you are logged in! <a href='index.php?task=logout' class='status'>Logout</a>";
	}
	echo $header;
	$is_search = array_key_exists('keywords', $_GET) || array_key_exists('zip_code', $_GET) || array_key_exists('category', $_GET) || array_key_exists('amount_min', $_GET) || array_key_exists('amount_max', $_GET);
?>
</div>
<div>

<?php if ((param_get('search') == 'item') || (param_get('search') == 'silo')) {
?>

	<table width="975px" style="">
		<tr>
			
			<td align="left" class="main_menu" id="search_bar">
				Search: &nbsp;&nbsp;&nbsp;
				<a href="index.php?search=item" <?php if (param_get('search') == 'item') echo "class=main_menu_current"; ?>>Items</a> &nbsp;&nbsp;&nbsp;
				<a href="index.php?search=silo" <?php if (param_get('search') == 'silo') echo "class=main_menu_current"; ?>>Silos</a>	&nbsp;&nbsp;&nbsp;	
				Near: <a href="javascript:popup_show('location', 'location_drag', 'location_exit', 'screen-center', 0, 0);" class="bold_text"><?=$userLocation?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;				
					<?php
						if (!$is_search || strlen(trim(param_get('keywords'))) == 0)
							echo "<input type='text' name='keywords' placeholder='Keyword' style='width: 200px;; background: #fff;'/>";
						else
							echo "<input type='text' name='keywords' value='".param_get('keywords')."' style='width: 200px; background: #fff;'/>";
					?>				
					<select name="category" size="1" placeholder="Categories" style="width: 150px; background: #fff;">
						<?php
							if (param_get('search') == 'silo') {
								$sql = "SELECT * FROM silo_categories ORDER BY silo_cat_id";
								$s = mysql_query($sql);
								echo "<option value=''>Category</option>";											
								while ($row = mysql_fetch_array($s)) {
									if ($is_search && param_get('category') == $row['silo_cat_id'])												
										echo "<option value=".$row['silo_cat_id']." selected>".$row['type']."</option>";
									else
										echo "<option value=".$row['silo_cat_id'].">".$row['type']."</option>";												
								}
							}
							else if (param_get('search') == 'item') {
								echo "<option value=''>Category</option>";																						
								$sql = "SELECT * FROM item_categories ORDER BY category";
								$s = mysql_query($sql);
								while ($row = mysql_fetch_array($s)) {
									if ($is_search && param_get('category') == $row['item_cat_id'])																								
										echo "<option value=".$row['item_cat_id']." selected>".$row['category']."</option>";
									else
										echo "<option value=".$row['item_cat_id'].">".$row['category']."</option>";
								}											
							}
							else {
								echo "<option value=''>Category</option>";																						
							}
						?>
					</select>
					
					<?php
						$amount_min = max(floatval(param_get('amount_min')), 0);
						if (!$is_search || strlen(trim(param_get('amount_min'))) == 0)
							echo "<input type='text' name='amount_min' placeholder='Low $' style='width: 40px; background: #fff;'/>";
						else
							echo "<input type='text' name='amount_min' value='$amount_min' style='width: 40px; background: #fff;'/>";										
					?>
					<?php
						$amount_max = max(floatval(param_get('amount_max')), 0);					
						if (!$is_search || strlen(trim(param_get('amount_max'))) == 0)
							echo "<input type='text' name='amount_max' placeholder='Hi $' style='width: 40px; background: #fff;'/>";
						else
							echo "<input type='text' name='amount_max' value='$amount_max' style='width: 40px; background: #fff;'/>";							
					?>
					
					
					<?php
						if (param_get('search') == 'silo') 
							echo "<button type='submit' value='silo' name='search'>Search</button>";
						else if (param_get('search') == 'item')
							echo "<button type='submit' value='item' name='search'>Search</button>";
						else
							echo "<button type='submit'>Search</button>";																
					?>		
			
			</td>
			
		</tr>
	</table>

<?php
	}
else { }
?>
</div>

</form>

<script>
function changeLocation()
{
  var str = '<form action="" method="POST"><input onclick=this.value=""; type="text" value="Enter Zip Code" name="zip"> <button type="submit" name="location" value="Update">Update</button></form>';
  $('#enterLocation').append( str );
  userLocation.style.display = 'none';
  enterLocation.style.display = 'inline-block';

}
</script>