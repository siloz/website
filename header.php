<?php
	$user_id = $_SESSION['user_id'];
	$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = '$user_id'"));
	$fname = $user['fname'];
	$lname = $user['lname'];

	if (!$user['fname'] || !$user['lname'] || !$user['address'] || !$user['phone'] || !$user['photo_file']) { $addInfo_full = true; }
?>

<script type="text/javascript">
	function create_silo_need_login() {
		popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);
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
	<span class="gray">Your location:</span> 
	<span class="blue"><?=$userLocation?>

	<?php if (!isset($_SESSION['user_id'])) {
		echo "<span class='change_location'>change</span>";
		}
	?>

	</span>
	<span class="blue separator">|</span>
	<?php
		if (isset($_SESSION['user_id'])) {
			$user_id = $_SESSION['user_id'];
			$sql = "SELECT * FROM silos WHERE admin_id = $user_id";
			$res = mysql_query($sql);
			if ($addInfo_full) {
	?>
				<a href="javascript:popup_show('addInfo', 'addInfo_drag', 'addInfo_exit', 'screen-center', 0, 0);"><span class="blue">start a silo</span></a>
				<span class="blue separator">|</span>
	<?php
			} elseif (mysql_num_rows($res) == 0) {
	?>
				<a href="index.php?task=create_silo"><span class="<?php if (param_get('task') == 'create_silo') { echo "orange"; } else { echo "blue"; } ?>">start a silo</span></a>
				<span class="blue separator">|</span>
	<?php
			} else {
		$sid = mysql_fetch_row(mysql_query("SELECT id FROM silos WHERE admin_id = '$user_id'"));
		$Silo = new Silo($sid[0]);
		$silo_id = $Silo->silo_id;
	?>
				<a href="index.php?task=manage_silo"><span class="<?php if (param_get('task') == 'manage_silo' || param_get('task') == 'manage_silo_admin') { echo "orange"; } else { echo "blue"; } ?>">manage your silo</span></a>
				<span class="blue separator">|</span>
				<a href="index.php?task=view_silo&id=<?=$Silo->id?>"><span  class="<?php if (param_get('task') == 'view_silo' && param_get('id') == $Silo->id) { echo "orange"; } else { echo "blue"; } ?>">view silo as user</span></a>
				<span class="blue separator">|</span>
	<?php
			}
	?>
			<a href="index.php?task=my_account"><span class="<?php if (param_get('task') == 'my_account' || param_get('task') == 'transaction_console') { echo "orange"; } else { echo "blue"; } ?>">my account</span></a>	
	<?php
		} else {
	?>
			<a href="javascript:popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><span class="blue">login/create account</span></a>
			<span class="blue separator">|</span>	
			<a href="javascript:create_silo_need_login();"><span class="blue">start a silo</span></a>
	<?php
		}
	?>
	<div class="enterLocation" style="font-size: 9pt; color: red; padding-top: 7px;"><?=$locErr?></div>
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
						<input type="text" name="email" id="email" onfocus="select();" placeholder="E-mail"/>
					</td>
					<td>
						<input type="password" name="password" id="password" onfocus="select();"  placeholder="Password"/>
					</td>
				</tr>
			</table>
			<input type="checkbox" name="remember_me" value="yes" /> Keep me logged in for 1 month <br/>
			<div id="login_status"></div>
			<br/>
			<button type="button" id="login_button">Login</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('login').style.display='none';">Cancel</button>
		</form>
		<script>
			$("#login_button").click(function(event) {	
				var un = document.getElementById('email').value;
				var pw = document.getElementById('password').value;
				var enc_pw = md5(pw);					
				if (un == '' || pw == '') {
					document.getElementById("login_status").innerHTML = "<font color='red'><b><br/>Error: Empty e-mail/password</b></font>";
				}
				else {
					$.post(<?php echo "'".API_URL."'"; ?>, 
						{	
							request: 'login',
							email: un, 
							password: enc_pw
						}, 
						function (data) {
							if (parseInt($(data).find('authenticated').text(), 10) == 1) {
								document.getElementById('overlay').style.display='none';
								document.getElementById('login').style.display='none';
								document.getElementById("login_status").innerHTML = "";								
								document.forms['login_form'].submit();
							}						
							else {
								document.getElementById("login_status").innerHTML = "<font color='red'><b><br/>Error: Invalid e-mail/password</b></font>";
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
		<h2>Forgot your e-mail/password?</h2>
		<a href="index.php?task=reset_password"><button>Reset Password</button></a>			
		<br/><br/>
		<hr/>
		<h2>Create a <?=SITE_NAME?> Account</h2>
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

<?php 	if (isset($_SESSION['user_id'])) { $logo_redirect = "items"; } else { $logo_redirect = "index.php"; } ?>

<form action='index.php' id="search_form" name="search_form">
<div id="logo_container">
	<a href="<?=$logo_redirect?>" style="text-decoration:none"><img src="images/logo-beta.png" width="289" height="62" /></a>			
</div>
<!-- <div align="right" style="margin-top: 100px; margin-right: 10px; font-size: 12px; line-height: 25px;">
	Raise money by accepting items, pledged items - all online. Start a silo now!
</div> -->
<div id="status" align="right" style="width: 965px; margin-top: 45px; position: absolute; font-size: 8pt;">
<?php
	if ($_SESSION['admin_access']) {
		$header = "<a href='administrator/' target='_blank' style='padding-right: 100px; text-decoration: none; color: grey'><b>Admin Panel</b></a>";
	}

	$qry = mysql_query("SELECT * FROM notifications WHERE user_id = '$user_id'");
	$notif = mysql_fetch_array($qry);

	if ($notif == 1) {
		$header .= "<span id='notification'><a href='index.php?task=transaction_console' style='padding-right: 20px; text-decoration: none'><font color='red'><b>1 new notification!</b></font></a></span>";
	}
	elseif ($notif > 1) {
		$header .= "<span id='notification'><a href='index.php?task=transaction_console' style='padding-right: 20px; text-decoration: none'><font color='red'><b>".$notif['count']." new notifications!</b></font></a></span>";
	}

	if ($_SESSION['is_logged_in']) {
		$header .= "<span style='padding-right: 10px;'>Welcome back <b>".$fname." ".$lname."</b>!</span> <a href='index.php?task=logout' class='status'>Logout</a>";
	}
	echo $header;
	$is_search = array_key_exists('keywords', $_GET) || array_key_exists('zip_code', $_GET) || array_key_exists('category', $_GET) || array_key_exists('amount_min', $_GET) || array_key_exists('amount_max', $_GET);
?>
</div>
<div>
<div style="clear: both;"></div>

<?php 
$sItems = param_get('search') == 'item';
$sSilos = param_get('search') == 'silo';

if ($sItems || $sSilos) {
?>

<div class="spacer"></div>

<table>
<tr>
	<td class="<?php if ($sItems) { echo "sbSelected"; } else { echo "sb"; } ?>" onClick="document.location.href='items';" style="cursor:pointer;cursor:hand">
		<a href="items">shop</a>
	</td>
	<td class="<?php if ($sSilos) { echo "sbSelected"; } else { echo "sb"; } ?>" onClick="document.location.href='silos';" style="cursor:pointer;cursor:hand">
		<a href="silos">donate items</a>
	</td>
	<td class="searchBar">
		<table width="100%">
		<tr>
			<td>
				near: <span class="blue"><span style="font-size: 10pt; font-weight: bold;"><?=$userLocation?></span>

				<?php if (!isset($_SESSION['user_id'])) {
					echo "<span class='change_location'>change</span>";
					}
				?>

				</span>
			</td>
			<td>
				<input type="text" name='keywords' value="<?=param_get('keywords')?>" size="15" onfocus="select();" value="<?=$keywords?>" placeholder="keyword">
			</td>
			<td>			
					<select name="category" id="category" size=1 placeholder="category">
						<?php
							if (param_get('search') == 'silo') {
								$sql = "SELECT * FROM silo_categories ORDER BY silo_cat_id";
								$s = mysql_query($sql);
								echo "<option value=''>category</option>";											
								while ($row = mysql_fetch_array($s)) {
									if ($is_search && param_get('category') == $row['silo_cat_id'])												
										echo "<option value=".$row['silo_cat_id']." selected>".$row['type']."</option>";
									else
										echo "<option value=".$row['silo_cat_id'].">".$row['type']."</option>";												
								}
								echo '<input type="hidden" name="search" value="silo">';
							}
							else if (param_get('search') == 'item') {
								echo "<option value=''>category</option>";																						
								$sql = "SELECT * FROM item_categories ORDER BY category";
								$s = mysql_query($sql);
								while ($row = mysql_fetch_array($s)) {
									if ($is_search && param_get('category') == $row['item_cat_id'])																								
										echo "<option value=".$row['item_cat_id']." selected>".$row['category']."</option>";
									else
										echo "<option value=".$row['item_cat_id'].">".$row['category']."</option>";
								}
								echo '<input type="hidden" name="search" value="item">';											
							}
							else {
								echo "<option value=''>category</option>";																						
							}
						?>
					</select>
			</td>
			<td>
				price
			</td>
			<td>
				<?php $replace = array("$", ","); $low = str_replace($replace, "", param_get('price_low'));?>
				<input type="text" name="price_low" id="price_low" size="5" onfocus="select();" value="<?=money_format('%n', $low)?>" placeholder="low" onkeypress="return isNumberKey(event)">
			</td>
			<td>
				<?php $high = str_replace($replace, "", param_get('price_high'));?>
				<input type="text" name="price_high" id="price_high" size="5" onfocus="select();" value="<?=money_format('%n', $high)?>" placeholder="high" onkeypress="return isNumberKey(event)">
			</td>
			<td align="right">
				<button>search</button>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<script type="text/javascript">
$('form').submit(function(){
    	$('td').children('input[value=""]').attr('disabled', 'disabled');
    	$('select').children('option[value=""]').attr('disabled', 'disabled');
});
</script>

<?php
	}
?>

</form>


<div class="login" id="addInfo" style="width: 300px;">
	<div id="addInfo_drag" style="float:right">
		<img id="addInfo_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>Please complete your profile.</h2>
		You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
		<button type="button" onclick="document.location='index.php?task=my_account&redirect=create_silo'">Finish it now</button>
		<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo').style.display='none';">Later</button>
	</div>
</div>