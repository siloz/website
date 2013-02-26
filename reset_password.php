<?php
	$err = '';
	$success = false;

	if (param_post('reset') == 'Reset') {				
		$email = trim(param_post('email'));
		$retype_email = trim(param_post('retype_email'));
	
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
				$err .= "Your e-mail address ('$email') is not registered yet on siloz .<br/>";
			}
		}
		
		$captcha = param_post('ct_captcha');
      		$securimage = new Securimage();

      	if ($securimage->check($captcha) == false) {        
			$err .= "Incorrect security code entered.<br/>";
		}

		$user = mysql_fetch_array(mysql_query("SELECT id, username, email FROM users WHERE email = '$email'"));
		$id = $user['id'];
		$username = $user['username'];
		$email = $user['email'];

		function genRandomString() {
    			$length = 10;
    			$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvwxyz";
    			$string = "";    

    			for ($p = 0; $p < $length; $p++) {
        		$string .= $characters[mt_rand(0, strlen($characters))];
    			}

    			return $string;
			}
		$new_pw = genRandomString();
		$enc_pw = md5($new_pw);

		$dbpw = mysql_fetch_array(mysql_query("UPDATE users SET password = '$enc_pw' WHERE email = '$email'"));
		
		if (strlen($err) != 0) {}
		else {
			$err = "An e-mail has been sent with your username and your password has been reset.";
			$success = true;
			$subject = "Username/Password Reset - siloz";
			$message = "<h3>Your new login information for siloz is below:</h3>";
			$message .= "<br/>Username: ".$username." <br><br/>";
			$message .= "Password: ".$new_pw." <br/><br/>";
			$message .= "Welcome to siloz!";
			email_with_template($email, $subject, $message);
		}
	}
?>
<form enctype="multipart/form-data"  name="reset_password" class="create_account_form" method="POST">
	<input type="hidden" name="task" value="create_account"/>
	<input type="hidden" name="action" value="<?php echo param_get('action');?>">
	<div class="create_account_form" style="width: 400px">
	<table style="margin:auto">
		<tr>
			<td colspan="2" align="center">
				<h1>Forgot Username/Password</h1>
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
<?php
if (!$success) {
?>

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