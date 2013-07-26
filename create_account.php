<br>
<?php

if ($_SESSION['is_logged_in'] == 1) {
	echo "<script>window.location = 'items';</script>";
}

include("include/timeout.php");

	$err = '';
	$success = false;

	if (param_post('register') == 'Register') {	
		$email = trim(param_post('email'));			
		$password = param_post('password');
		$retype_password = param_post('retype_password');		
	
		if (strlen($email) == 0) {
			$err .= 'Please enter an e-mail address.<br/>';		
		}
		else if (!is_valid_email($email)) {
			$err .= "E-mail address '$email' is invalid.<br/>";		
		}
		else {
			$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE email = '$email'"));
			if ($tmp[0] > 0) {
				$err .= "E-mail '$email' is already assosciated with another account.<br/>";
			}
		}
		if (strlen(trim($password)) == 0) {
			$err .= 'Password must not be empty.<br/>';		
		}
		if ($password != $retype_password) {
			$err .= 'Passwords do not match.<br/>';		
		}
		
		$captcha = param_post('ct_captcha');
      		$securimage = new Securimage();

      		if ($securimage->check($captcha) == false) {        
			$err .= "Incorrect security code entered.<br/>";
		}

		$code = rand(100000, 999999);
		if (strlen($err) == 0) {
			$User = new User();
			$User->email = $email;
			$User->password = md5($password);
			$User->city = $userCity;
			$User->state = $userState;
			$User->longitude = $userLong;
			$User->latitude = $userLat;
			$User->validation_code = $code;
			$User->Save();						
		}

		if (strlen($err) != 0) {			
			mysql_query("DELETE FROM users WHERE id = '".$User->id.".");			
		}
		else {
			$success = true;
			$reglink .= ACTIVE_URL."index.php?task=validate_registration&id=".$User->id."&code=".$code;
			$subject = "New user registration at ".SHORT_URL." - Validation code";
			$message = "<h3>Account Validation</h3>";
			$message .= "You created an account on ".SITE_NAME.".com! Please click on the link below to verify your email address.<br/><br/>";
			$message .= "<a href='".$reglink."'>".$reglink."</a><br><br>";
			$message .= "Welcome to ".SITE_NAME."!";
			email_with_template($email, $subject, $message);

			// If admin setting is on
			if (ADMIN_NOTIF == 'on') {
				$adminSub = "New user on siloz.com!";
				$adminMsg = "<h3>A new user has been created on ".SITE_NAME."!</h3>";
				$adminMsg .= "Remember, this user hasn't been activated (yet).<br/><br/>";
				$adminMsg .= "New user's e-mail: <b>".$email."</b><br><br>";
				$adminMsg .= "This e-mail is sent everytime a new user is created through the 'create_account.php' page. To turn off these notifications, look in the config.php file.";
				
				$admin_emails = explode(',', ADMIN_NOTIF_EMAILS);
				foreach ($admin_emails as $email) {
					email_with_template($email, $adminSub, $adminMsg);
				}
			}
			
		}
	}
?>

<?php
if (!$success) {
?>

<span class="greyFont">

<div class="headingPad"></div>

<div class="userNav" align="center">
	<span class="accountHeading">Create a <?=SITE_NAME?> Account</span>
</div>

<div class="headingPad"></div><br>

<form enctype="multipart/form-data"  name="create_account" class="create_account_form" method="POST">
	<input type="hidden" name="task" value="create_account"/>
	<input type="hidden" name="action" value="<?php echo param_get('action');?>">
	<table align="center" style="margin:auto; width: 400px">
		<tr>
			<td colspan=2 align="center">
				<?php
					if (strlen($err) > 0) {
						echo "<font color='red'><b>".$err."</b></font>";
					}
				?>
			</td>
		</tr>
		<tr>
			<td>E-mail <font color='red'>*</font></td>
			<td><input type="text" name="email" style="width : 200px" value='<?php echo $email; ?>'/></td>
		</tr>
		<tr>
			<td>Password <font color='red'>*</font></td>
			<td><input type="password" name="password" style="width : 200px"/></td>			
		</tr>
		<tr>
			<td>Retype Password <font color='red'>*</font></td>
			<td><input type="password" name="retype_password" style="width : 200px"/></td>			
		</tr>
		<tr>
			<td colspan=2><br></td>
		</tr>
		<tr>
			<td colspan="2">
				<b>Security Code:</b><br/>
			    <img id="siimage" style="border: 1px solid #000; margin-right: 15px" src="include/captcha/securimage_show.php?sid=<?php echo md5(uniqid()) ?>" alt="CAPTCHA Image" align="left" />
			    <object type="application/x-shockwave-flash" data="include/captcha/securimage_play.swf?bgcol=#ffffff&amp;icon_file=include/captcha/images/audio_icon.png&amp;audio_file=include/captcha/securimage_play.php" height="32" width="32">
			    <param name="movie" value="include/captcha/securimage_play.swf?bgcol=#ffffff&amp;icon_file=include/captcha/images/audio_icon.png&amp;audio_file=include/captcha/securimage_play.php" />
			    </object>
			    &nbsp;
			    <a tabindex="-1" style="border-style: none;" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = 'include/captcha/securimage_show.php?sid=' + Math.random(); this.blur(); return false"><img src="include/captcha/images/refresh.png" alt="Reload Image" height="32" width="32" onclick="this.blur()" align="bottom" border="0" /></a>
				<br />
				<br/>
			</td>
		<tr>
			<td>
				<strong>Enter Code <font color='red'>*</font>:</strong>
			</td>
			<td>
				<input type="text" name="ct_captcha" style="width : 100px"/>							    
			</td>
		</tr>				
		<tr>
			<td colspan="2" align="center">
				<br/>
				<button type="submit" name="register" value="Register">Register</button>
			</td>
		</tr>
	</table>
</form>

<?php
}
if ($success && $filename) {
?>
	<center>
		To complete you account creation, please crop the image you uploaded below:<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$User->id?>.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="user_id" value="<?=$User->id?>" />
			<button type="submit" name="crop" value="Crop">Crop</button>
		</form>
	</center>
<?php
}
elseif ($success && !$filename) { $crop = "true"; }
if ($crop == "true") {
?>
	<table align="center" style="margin:auto; width: 400px">
		<tr>
			<td>
				<center><span class='blue'><b>An activation link has been sent to your e-mail address. Please click on it to finish your registration!</b></font></center>
			</td>
		</tr>
	</table>
<?php
}
?>

</span>
