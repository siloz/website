<?php
	$err = '';
	$success = false;

	if (param_post('register') == 'Register') {				
		$username = trim(param_post('username'));
		$password = param_post('password');
		$retype_password = param_post('retype_password');		
		$fullname = param_post('fullname');
		$email = trim(param_post('email'));
		$address = param_post('address');
		$zipcode = param_post('zipcode');
		$phone = param_post('phone');
	
		if (strlen($username) == 0) {
			$err .= 'Username must not be empty.<br/>';		
		}
		else {
			$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE username = '$username'"));
			if ($tmp[0] > 0) {
				$err .= "Username '$username' is not available.<br/>";
			}
		}
		if (strlen(trim($password)) == 0) {
			$err .= 'Password must not be empty.<br/>';		
		}
		if ($password != $retype_password) {
			$err .= 'Passwords do not match.<br/>';		
		}
		if (strlen(trim($fullname)) == 0) {
			$err .= 'Full name must not be empty.<br/>';		
		}
		if (strlen(trim($email)) == 0) {
			$err .= 'Email must not be empty.<br/>';		
		}
		else if (!is_valid_email($email)) {
			$err .= "Email address '$email' is invalid.<br/>";		
		}
		else {
			$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE email = '$email'"));
			if ($tmp[0] > 0) {
				$err .= "Email '$email' is already registered.<br/>";
			}			
		}
		
		$captcha = param_post('ct_captcha');
      	$securimage = new Securimage();

      	if ($securimage->check($captcha) == false) {        
			$err .= "Incorrect security code entered.<br/>";
		}
					
		$code = rand(100000, 999999);
		if (strlen($err) == 0) {
			$User = new User();
			$User->username = $username;
			$User->password = $password;
			$User->fullname = $fullname;
			$User->email = $email;
			$User->address = $address;
			$User->zip_code = $zipcode;
			$User->phone = $phone;
			$User->validation_code = $code;
			$User->Save();
			
			
			
			
			$allowedExts = array("png", "jpg", "jpeg", "gif");
			if ($_FILES['member_photo']['name'] != '') {
				$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
				if (!in_array($ext, $allowedExts)) {
					$err .= $_FILES['member_photo']['name']." is invalid file.<br/>";
				}
				else {
					$photo = new Photo();
					$photo->upload($_FILES['member_photo']['tmp_name'], 'members', $User->id.".jpg");
					$User->photo_file = $User->id.".jpf";
					$User->Save();
				}
			}
						
		}
		
		if (strlen($err) != 0) {			
			mysql_query("DELETE FROM users WHERE id = '".$User->id.".");			
		}
		else {
			$err = "Please check your email for validation code!";
			$success = true;
			$subject = "New user registration at siloz.com - Validation code";
			$message = "<h3>Account Validation</h3>";
			$message .= "You created an account on siloz.com!  Please click on the link below to verify your email address.<br/><br/>";
			$message .= "http://www.siloz.com/alpha/index.php?task=validate_registration&id=".$User->id."&code=".$code." <br/><br/>";
			$message .= "Welcome to siloz!";
			email_with_template($email, $subject, $message);
		}
	}
?>
<form enctype="multipart/form-data"  name="create_account" class="create_account_form" method="POST">
	<input type="hidden" name="task" value="create_account"/>
	<input type="hidden" name="action" value="<?php echo param_get('action');?>">
	<div class="create_account_form" style="width: 400px">
	<table style="margin:auto">
		<tr>
			<td colspan="2" align="center">
				<h1>Create a Siloz Account</h1>
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
			<td>Username <font color='red'>*</font></td>
			<td><input type="text" name="username" style="width : 200px" value='<?php echo $username; ?>'/></td>			
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
			<td colspan=2><hr/></td>
		</tr>
		<tr>
			<td>Fullname <font color='red'>*</font></td>
			<td><input type="text" name="fullname" style="width : 200px" value='<?php echo $fullname; ?>'/></td>
		</tr>		
		<tr>
			<td>Email <font color='red'>*</font></td>
			<td><input type="text" name="email" style="width : 200px" value='<?php echo $email; ?>'/></td>
		</tr>
		<tr>
			<td>Address </td>
			<td><input type="text" name="address" style="width : 200px" value='<?php echo $address; ?>'/></td>
		</tr>
		<tr>
			<td>Zip code <font color='red'>*</font></td>
			<td><input type="text" name="zipcode" style="width : 100px" value='<?php echo $zipcode; ?>'/></td>
		</tr>
		<tr>
			<td>Phone </td>
			<td><input type="text" name="phone" style="width : 200px" value='<?php echo $phone; ?>'/></td>
		</tr>
		<tr>			
			<td>Photo </td>
			<td><input name="member_photo" type="file"  style="width : 200px; height: 20px;"/></td>
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
<?php
}
?>
	</table>
	</div>
</form>
