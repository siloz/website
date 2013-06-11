<br><br>
<?php
	$id = param_get('id');
	$token = param_get('token');
	$validation_code = param_get('validation_code');
	$refer_id = param_get('refer_id');

	$validReset = mysql_num_rows(mysql_query("SELECT * FROM password_reset WHERE user_id = '$id' AND token = '$token' AND exp_date > NOW()"));

if ($_SESSION['is_logged_in'] == 1) {
	echo "<script>window.location = 'items';</script>";
}
elseif (!$validReset && ($id || $token || $validation_code || $refer_id)) {
	echo "<script type='text/javascript'>window.location = 'index.php?task=reset_password';</script>";
}

	$err = '';
	$success = false;

	if (param_post('createpass') == 'Submit') {
		$user_id = param_post('user_id');	
		$refer_id = param_post('refer_id');	
		$password = md5(mysql_real_escape_string(param_post('password')));
		$confirm_password = md5(mysql_real_escape_string(param_post('confirm_password')));

		$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE id = '$user_id'"));
	
		if (strlen($password) == 0) {
			$err .= 'New password must not be empty.<br/>';		
		}
		elseif (strlen($confirm_password) == 0) {
			$err .= 'Please confirm your new password.<br/>';		
		}
		elseif ($password != $confirm_password) {
			$err .= "The two passwords do not match. Please confirm they are both the same.<br/>";		
		}

		if (strlen($err) != 0) {}
		else {
			$pwUpd = mysql_query("UPDATE users SET password = '$password' WHERE id = '$user_id'");
			$resetDel = mysql_query("DELETE FROM password_reset WHERE user_id = '$id'");

			$success = true;
			$subject = "Password created on ".SHORT_URL;
			$message = "<h3>Your password has been updated on ".SITE_NAME."</h3>";
			$message .= "<br/>This message is simply to inform you that your password has been updated on your ".SITE_NAME." account. If you did not intitate this, please contact our support team right away.";
			email_with_template($email[0], $subject, $message);

    			echo "<script>window.location = '".ACTIVE_URL."index.php?task=my_account&redirect=create_silo&id=".$refer_id."';</script>";
    			exit();
		}
	}

	if (param_post('newpass') == 'Submit') {
		$user_id = param_post('user_id');		
		$password = md5(mysql_real_escape_string(param_post('password')));
		$confirm_password = md5(mysql_real_escape_string(param_post('confirm_password')));

		$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE id = '$user_id'"));
	
		if (strlen($password) == 0) {
			$err .= 'New password must not be empty.<br/>';		
		}
		elseif (strlen($confirm_password) == 0) {
			$err .= 'Please confirm your new password.<br/>';		
		}
		elseif ($password != $confirm_password) {
			$err .= "The two passwords do not match. Please confirm they are both the same.<br/>";		
		}

		if (strlen($err) != 0) {}
		else {
			$pwUpd = mysql_query("UPDATE users SET password = '$password' WHERE id = '$user_id'");
			$resetDel = mysql_query("DELETE FROM password_reset WHERE user_id = '$id'");

			$err = "Your password has been updated! Please login above to access your account.";
			$success = true;
			$subject = "Password changed on ".SHORT_URL;
			$message = "<h3>Your password has been updated on ".SITE_NAME."</h3>";
			$message .= "<br/>This message is simply to inform you that your password has been updated on your ".SITE_NAME." account. If you did not intitate this, please contact our support team right away.";
			email_with_template($email[0], $subject, $message);
		}
	}

	if (param_post('reset') == 'Reset') {				
		$email = trim(strtolower(param_post('email')));
		$retype_email = trim(strtolower(param_post('retype_email')));
	
		if (strlen($email) == 0) {
			$err .= 'E-mail must not be empty.<br/>';		
		}
		elseif (strlen($retype_email) == 0) {
			$err .= 'Please retype your e-mail address.<br/>';		
		}
		elseif (!is_valid_email($email)) {
			$err .= "Email address '$email' is invalid.<br/>";		
		}
		elseif ($email != $retype_email) {
			$err .= "The e-mails don't match. Please make sure both e-mails match.<br/>";		
		}
		else {
			$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE email = '$email'"));
			if ($tmp[0] < 1) {
				$err .= "Your e-mail address ('$email') is not registered yet on ".SITE_NAME.".<br/>";
			}
		}
		
		$captcha = param_post('ct_captcha');
      		$securimage = new Securimage();

      		if ($securimage->check($captcha) == false) {        
			$err .= "Incorrect security code entered.<br/>";
		}

		$user = mysql_fetch_array(mysql_query("SELECT id, fname, email FROM users WHERE email = '$email'"));
		$id = $user['id'];
		$fname = $user['fname'];
		$email = $user['email'];
		if (strtolower($email) == strtolower($retype_email)) {
			$retype_email = $user['email'];
		}

		$token = genRandomString(15);
		
		if (strlen($err) != 0) {}
		else {
			$dbReset = mysql_query("INSERT INTO password_reset (user_id, token, exp_date) VALUES ('$id', '$token', DATE_ADD(NOW(), INTERVAL 1 DAY))");

			$err = "An e-mail has been sent with a password reset link. Please create a new password right away because the link expires after 24 hours!";
			$success = true;
			$subject = "Username/Password Reset - ".SHORT_URL."";
			$message = "<h3>Password Reset Link for ".SITE_NAME."</h3>";
			$message .= "<br/>Hello ".$fname.",<br><br>";
			$message .= "A request has been made to reset your password. Please click on the link below.<br><br/>";
			$message .= "<a href='".ACTIVE_URL."index.php?task=reset_password&id=".$id."&token=".$token."'>".ACTIVE_URL."index.php?task=reset_password&id=".$id."&token=".$token."</a><br/><br/>";
			$message .= "**Remember, this link is only active for 24 hours.";
			email_with_template($email, $subject, $message);
		}
	}
?>


<form enctype="multipart/form-data"  name="reset_password" class="create_account_form" method="POST">
	<input type="hidden" name="task" value="create_account"/>
	<input type="hidden" name="action" value="<?php echo param_get('action');?>">
	<div class="create_account_form" style="width: 400px">
	<table style="margin:auto">

<?php
if ($validation_code && $refer_id) { $User = new User($id); $activate = $User->ValidateRegistration($id, $validation_code); }
if ($activate == "success") {
?>
		<tr>
			<td colspan="2" align="center">
				<h1>Create your password</h1>
			</td>
		</tr>
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
			<td>New password <font color='red'>*</font></td>
			<td><input type="password" name="password" style="width : 200px" /></td>
		</tr>
		<tr>
			<td>Confirm new password <font color='red'>*</font></td>
			<td><input type="password" name="confirm_password" style="width : 200px" /></td>
		</tr>			
		<tr>
			<td colspan="2" align="center">
				<br/>
				<input type="hidden" name="user_id" value="<?=$id?>" />
				<input type="hidden" name="refer_id" value="<?=$refer_id?>" />
				<button type="submit" name="createpass" value="Submit">Submit</button>
			</td>
		</tr>

<?php
} elseif ($validReset > 0 && !$success) {
?>
		<tr>
			<td colspan="2" align="center">
				<h1>Forgot Password</h1>
			</td>
		</tr>
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
			<td>New password <font color='red'>*</font></td>
			<td><input type="password" name="password" style="width : 200px" /></td>
		</tr>
		<tr>
			<td>Confirm new password <font color='red'>*</font></td>
			<td><input type="password" name="confirm_password" style="width : 200px" /></td>
		</tr>			
		<tr>
			<td colspan="2" align="center">
				<br/>
				<input type="hidden" name="user_id" value="<?=$id?>" />
				<button type="submit" name="newpass" value="Submit">Submit</button>
			</td>
		</tr>

<?php
} elseif (!$success) {
?>
		<tr>
			<td colspan="2" align="center">
				<h1>Forgot Password</h1>
			</td>
		</tr>
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
			<td colspan="2" align="center">
				<h2>Please enter your e-mail below:</h2>
			</td>
		</tr>
		
		<tr>
			<td>Email <font color='red'>*</font></td>
			<td><input type="text" name="email" style="width : 200px" value='<?php echo $email; ?>'/></td>
		</tr>
		<tr>
			<td>Retype Email <font color='red'>*</font></td>
			<td><input type="text" name="retype_email" style="width : 200px" value='<?php echo $retype_email; ?>'/></td>
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
				<button type="submit" name="reset" value="Reset">Reset</button>
			</td>
		</tr>
<?php
}
?>
	</table>
	</div>
</form>