<?php
	$err = '';
	$success = false;

	if (param_post('register') == 'Register') {				
		$username = trim(param_post('username'));
		$password = param_post('password');
		$retype_password = param_post('retype_password');		
		$fname = param_post('fname');
		$lname = param_post('lname');
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
		if (strlen(trim($fname)) == 0) {
			$err .= 'First name must not be empty.<br/>';		
		}
		if (strlen(trim($lname)) == 0) {
			$err .= 'Last name must not be empty.<br/>';		
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

		$adr = urlencode($address);
		$zip = urlencode($zipcode);
		$url = "http://maps.google.com/maps/geo?q=".$adr."".$zip;
		$xml = file_get_contents($url);
		$geo_json = json_decode($xml, TRUE);
		if ($geo_json['Status']['code'] == '200') {
			$precision = $geo_json['Placemark'][0]['AddressDetails']['Accuracy'];
			$new_adr = $geo_json['Placemark'][0]['address'];
			$long = $geo_json['Placemark'][0]['Point']['coordinates'][0];
			$lat = $geo_json['Placemark'][0]['Point']['coordinates'][1];
		} else {
			$err .= 'Invalid address.<br/>';
		}
	
		$code = rand(100000, 999999);
		if (strlen($err) == 0) {
			$User = new User();
			$User->username = $username;
			$User->password = md5($password);
			$User->fname = $fname;
			$User->lname = $lname;
			$User->email = $email;
			$User->address = $new_adr;
			$User->zip_code = $zipcode;
			$User->longitude = $long;
			$User->latitude = $lat;
			$User->phone = $phone;
			$User->validation_code = $code;
			$User->Save();
			
			
			$allowedExts = array("png", "jpg", "jpeg", "gif");
			if ($_FILES['member_photo']['name'] != '') {
				$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
				if (!in_array($ext, $allowedExts)) {
					$err .= $_FILES['member_photo']['name']." is an invalid file type.<br/>";
				}
			}

			$filename = $_FILES['member_photo']['name'];
			$temporary_name = $_FILES['member_photo']['tmp_name'];
			$mimetype = $_FILES['member_photo']['type'];
			$filesize = $_FILES['member_photo']['size'];

			switch($mimetype) {

    				case "image/jpg":

    				case "image/jpeg":

        			$i = imagecreatefromjpeg($temporary_name);

       			break;

    				case "image/gif":

        			$i = imagecreatefromgif($temporary_name);

        			break;

    				case "image/png":

        			$i = imagecreatefrompng($temporary_name);

        			break;
			}

			unlink($temporary_name);
			imagejpeg($i,"uploads/".$User->id.".jpg",80);
						
		}
		
		if (strlen($err) != 0) {			
			mysql_query("DELETE FROM users WHERE id = '".$User->id.".");			
		}
		else {
			$success = true;
			$subject = "New user registration at siloz.com - Validation code";
			$message = "<h3>Account Validation</h3>";
			$message .= "You created an account on siloz.com!  Please click on the link below to verify your email address.<br/><br/>";
			$message .= "http://www.siloz.com/website/index.php?task=validate_registration&id=".$User->id."&code=".$code." <br/><br/>";
			$message .= "Welcome to siloz!";
			email_with_template($email, $subject, $message);
		}
	}

	if (param_post('crop') == 'Crop') {
		$id = trim(param_post('user_id'));
		$crop = true;
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'.jpg';
		$name = 'uploads/members/'.$id.'.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE users SET photo_file = '$id.jpg' WHERE id = '$id'");
	}
?>

		<script language="Javascript">

			$(function(){

				$('#cropbox').Jcrop({
					aspectRatio: 4/3,
					onSelect: updateCoords
				});

			});

			function updateCoords(c)
			{
				$('#x').val(c.x);
				$('#y').val(c.y);
				$('#w').val(c.w);
				$('#h').val(c.h);
			};

			function checkCoords()
			{
				if (parseInt($('#w').val())) return true;
				alert('Please select a crop region then press submit.');
				return false;
			};

		</script>

<?php
if (!$success && !$crop) {
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
			<td>First name <font color='red'>*</font></td>
			<td><input type="text" name="fname" style="width : 200px" value='<?php echo $fname; ?>'/></td>
		</tr>
		<tr>
			<td>Last name <font color='red'>*</font></td>
			<td><input type="text" name="lname" style="width : 200px" value='<?php echo $fname; ?>'/></td>
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
	</table>
	</div>
</form>

<?php
}
if ($success && $filename) {
?>
	<div class="create_account_form" style="width: 800px; margin: auto;">
		<center>
				<h1>Create a Siloz Account</h1>
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
	</div>
<?php
}
elseif ($success && !$filename) { $crop = "true"; }
if ($crop == "true") {
?>
	<div class="create_account_form" style="width: 400px">
	<table style="margin:auto">
		<tr>
			<td colspan="2" align="center">
				<h1>Create a Siloz Account</h1>
			</td>
		</tr>
		<tr>
			<td>
				<center><font color='red'><b>An activation link has been sent to your e-mail address. Please click on it to finish your registration!</b></font></center>
			</td>
		</tr>
	</table>
	</div>
<?php
}
?>