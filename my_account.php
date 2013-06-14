<?php
	$redirect = param_get('redirect');
	$redirect_id = param_get('id');
	$linkedin_upd = param_get('connect');

if ($_SESSION['is_logged_in'] != 1) { ?>
	<script type="text/javascript">
		$(document).ready(function () {
        		javascript:popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);
		})
	</script>
<?php 
	die; }

	if (param_post('crop') == 'Crop') {
		$id = trim(param_post('user_id'));
		$fb = param_post('fb');
		if ($fb) { $fb_upd = true; }
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

		$sql = "UPDATE users SET photo_file = '$id.jpg' WHERE id = $id";
		mysql_query($sql);
	}

//If account info is updated
	if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'on') { 
    		echo "<script>window.location = 'https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "';</script>";
    		exit();
	}
 	else {
		$err = '';
		if (param_post('task') == 'update_account') {				
			$old_password = param_post('old_password');
			$new_password = param_post('new_password');
			$retype_new_password = param_post('retype_new_password');
		
			$fname = param_post('fname');
			$lname = param_post('lname');
			$email = trim(param_post('email'));
			$address = param_post('address');
			$zip_code = param_post('zip_code');
			$phone = param_post('phone');
			$fb_photo = param_post('fb_photo');
			$friend_count = param_post('friend_count');

			$sql = "SELECT * FROM users WHERE email='$email'";
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			$photo_file = $row['photo_file'];
			$id = $row['id'];
			
			if ($new_password != $retype_new_password) {
				$err .= 'Passwords do not match.<br/>';		
			}
			if (strlen(trim($fname)) == 0) {
				$err .= 'First name must not be empty.<br/>';	
			}
			if (strlen(trim($lname)) == 0) {
				$err .= 'Last name must not be empty.<br/>';		
			}
			if (strlen(trim($phone)) == 0) {
				$err .= 'Please enter a phone number.<br/>';		
			}
			if (strlen(trim($email)) == 0) {
				$err .= 'Email must not be empty.<br/>';		
			}
			else if (!is_valid_email($email)) {
				$err .= 'Email address is invalid.<br/>';		
			}
			else {
				$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE email = '$email' AND user_id != '$user_id'"));
				if ($tmp[0] > 0) {
					$err .= "Email '$email' is already registered.<br/>";
				}
			}

			$filesize = $_FILES['member_photo']['size'];
			if ($filesize > 2097152) {
				$err .= "Image file is too large. Please scale it down.";
			}

			$adr = urlencode($address);
			$zip = urlencode($zip_code);

		$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$adr."+".$zip."&sensor=false");
		$loc = json_decode($json);

		if ($loc->status == 'OK') {
    			foreach ($loc->results[0]->address_components as $address) {
        			if (in_array("locality", $address->types)) {
            				$city = $address->long_name;
        			}
        			if (in_array("administrative_area_level_1", $address->types)) {
            				$state = $address->short_name;
        			}
        			if (in_array("postal_code", $address->types)) {
            				$zip_code = $address->short_name;
        			}
    			}

			$address = $loc->results[0]->formatted_address;
			$lat = $loc->results[0]->geometry->location->lat;
			$long = $loc->results[0]->geometry->location->lng;
		}
		
		if ($fb_photo) {
			$img = 'uploads/'.$id.'.jpg';
			$img_url = 'https://graph.facebook.com/'.$fb_photo.'/picture?width=500';
			file_put_contents($img, file_get_contents($img_url));
			$targ_w = "900";
			$img_w = getimagesize($img);

			if ($img_w[0] > $targ_w) {
      				$image = new Photo();
      				$image->load($img);
      				$image->resizeToWidth($targ_w);
				$image->save($img);
			}
		}
		
			if ($zip_code == '') {
				$err .= "Please enter a more detailed address.";
			}

			elseif (strlen($err) == 0) {				
				if ($old_password != '' || $new_password != '' || $retype_new_password != '') {

					$enc_old = md5($old_password);
					$enc_new = md5($new_password);
					$enc_retype = md5($retype_new_password);

					if ($enc_old != $row['password']) {
						$err .= "Current password is not correct.";
					}					
					else if ($enc_new == '') {
					 	$err .= "New password must not be empty.";
					}
					else {
						$sql = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', address = '$address', city = '$city', state = '$state',
								zip_code = '$zip_code', phone = '$phone', password='$enc_new', longitude = '$long', latitude = '$lat', friend_count = '$friend_count' 
								WHERE id = $id";
						mysql_query($sql);

						if ($_FILES['member_photo']['name'] != '') {
							$allowedExts = array("png", "jpg", "jpeg", "gif");
							$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
							if (!in_array($ext, $allowedExts)) {
								$err .= $_FILES['member_photo']['name']." is invalid file type.";
							} else {
								$filename = $_FILES['member_photo']['name'];
								$temporary_name = $_FILES['member_photo']['tmp_name'];
								$mimetype = $_FILES['member_photo']['type'];

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

							$name = "uploads/".$id.".jpg";
							$targ_w = "900";
							$img_w = getimagesize($temporary_name);

							if ($img_w[0] > $targ_w) {
      								$image = new Photo();
      								$image->load($temporary_name);
      								$image->resizeToWidth($targ_w);
								$image->save($name);
							} else {
								imagejpeg($i,$name,80);
							}

							unlink($temporary_name);

							}
						}						
					}
				}				
				else {
					$sql = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', address = '$address', city = '$city', state = '$state', 
							zip_code = '$zip_code', phone = '$phone', longitude = '$long', latitude = '$lat', friend_count = '$friend_count' 
							WHERE id = $id";
					mysql_query($sql);
					if ($_FILES['member_photo']['name'] != '') {
						$allowedExts = array("png", "jpg", "jpeg", "gif");							
						$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
						if (!in_array($ext, $allowedExts)) {
							$err .= $_FILES['member_photo']['name']." is invalid file type.";
						} else {
							$filename = $_FILES['member_photo']['name'];
							$temporary_name = $_FILES['member_photo']['tmp_name'];
							$mimetype = $_FILES['member_photo']['type'];

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

							$name = "uploads/".$id.".jpg";
							$targ_w = "900";
							$img_w = getimagesize($temporary_name);

							if ($img_w[0] > $targ_w) {
      								$image = new Photo();
      								$image->load($temporary_name);
      								$image->resizeToWidth($targ_w);
								$image->save($name);
							} else {
								imagejpeg($i,$name,80);
							}

							unlink($temporary_name);
						}
					}					
				}		
			}
		}
		else {
			$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			$id = $row['id'];
			$fname = $row['fname'];
			$lname = $row['lname'];
			$email = $row['email'];
			$address = $row['address'];
			$zip_code = $row['zip_code'];
			$phone = $row['phone'];
			$last_update = strtotime($row['last_update']);
			$photo_file = $row['photo_file'];
			$valid_code = $row['validation_code'];
		}

		if ($redirect && (!param_post('task') == 'update_account')) { ?>
			<script type="text/javascript">
				$(document).ready(function () {
        				javascript:popup_show('addInfo_message', 'addInfo_message_drag', 'addInfo_message_exit', 'screen-center', 0, 0);
				})
			</script>
<?php 		} elseif ($valid_code == -1) { mysql_query("UPDATE users SET validation_code = -2 WHERE user_id = '$user_id'"); ?>
			<script type="text/javascript">
				$(document).ready(function () {
        				javascript:popup_show('new_account', 'new_account_drag', 'new_account_exit', 'screen-center', 0, 0);
				})
			</script>
<?php 		}

			$data = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = '$user_id'"));
			$jdate = $data['joined_date'];
			$msince = date("m/d/Y", strtotime($jdate));

			$funds = mysql_fetch_array(mysql_query("SELECT SUM(price) AS total FROM items WHERE user_id = '$user_id' AND status = 'sold'"));
			$total_raised = $funds['total'];

	$updatemsg = "Your account has been updated!";

	if (!$fname || !$lname || !$address || !$phone || !$photo_file) { $addInfo_account = true; }

	if ($fb_upd && !$addInfo_account) { $updatemsg = "Your account has been updated using Facebook!"; } 
	elseif ($fb_upd && $redirect) { $updatemsg = "Your account has been updated using Facebook. You'll be redirected once the rest of your information is completed."; }
	if ($linkedin_upd && !$addInfo_account) { $updatemsg = "Your account has been updated using LinkedIn!"; }
	elseif ($linkedin_upd && $redirect) { $updatemsg = "Your account has been updated using LinkedIn. You'll be redirected once the rest of your information is completed."; }

	if ($redirect && !$addInfo_account && !$filename) {
		if ($redirect_id) { $redirect_id = "&id=".$redirect_id; }
		echo "<script>window.location = 'index.php?task=".$redirect."".$redirect_id."';</script>";
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

<div class="spacer"></div>

<span class="greyFont">

<div class="userNav">
	<table width="940px" style="border-spacing: 0px;">
		<tr><form action="">
			<td>
				<span class="accountHeading">User Account</span>
			</td>
			<td align="center" width="500px">
				<?php
					if (strlen($err) > 0 && !param_post('delete')) {
						echo "<span id='success' class='error'>".$err."</span>";
					}

					if ((param_post('task') == 'update_account') && !$filename && strlen($err) == 0) { 
						echo "<span id='success' class='error'>".$updatemsg."</span>";
					}
					elseif ($crop == "true" || $linkedin_upd) { 
						echo "<span id='success' class='error'>".$updatemsg."</span>";
					}

				?>
			</td>
		</form></tr>
	</table>
</div>

<div class="spacer"></div>

<?php
//If account info updated
if ((param_post('task') == 'update_account') && (strlen($err) == 0) && ($filename || $fb_photo)) {
?>
		<center>
				<h1>New Profile Photo - My Account</h1>
		To finish uploading your new photo, please crop the image you uploaded below:<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$id?>.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="user_id" value="<?=$id?>" />
			<?php if ($fb_photo) { echo '<input type="hidden" name="fb" value="true" />'; } ?>
			<button type="submit" name="crop" value="Crop">Crop</button>
		</form>
		</center>
		<br>
		<br>
<?php
die;
}
?>

<?php
//If account deleted
if (param_post('delete') == 'Delete account') {
	$id = param_post('id');
	$user_id = param_post('user_id');

	$User = new User();
	$User->id = $id;
	$User->user_id = $user_id;
	$User->DeleteUser();
?>
		<center>
			<font color="red"><b>Your account has been deleted. You will not receive any further notifications from us. 
			If you have any questions, please call or e-mail our support team.<br><br>
			Thanks for using <?=SITE_NAME?>.com!</b></font>
		</center>
		<br>
		<br>
<?php
session_destroy();
die;
}
?>

	<form enctype="multipart/form-data"  name="my_account_form" id="account_form" class="my_account_form" method="POST">
		<input type="hidden" name="task" value="update_account"/>		
		<table cellpadding="10px">
			<tr>
				<td valign="top" width="250px">
					<table>						
						<tr>			
							<td align="center"><img id="prof_pic" src="<?php echo 'uploads/members/'.$photo_file.'?'.$last_update?>" width="250px"></td>
						</tr>
						<tr>
							<td><input name="member_photo" type="file" style="height: 24px"/></td>
								<input type="hidden" name="fb_photo" id="fb_photo" value="" />
								<input type="hidden" name="friend_count" id="friend_count" value="" />
						</tr>		
					</table>
				</td>
				
				
				<td valign="top">
					<table>
						<tr>
							<td><b>Email</b> </td>
							<td><input type="text" name="email" id="email" style="width : 200px" value='<?php echo $email; ?>'/></td>
						</tr>
						<tr>
							<td><b>First name</b> </td>
							<td><input type="text" name="fname" id="fname" style="width : 200px" value='<?php echo $fname; ?>'/></td>
						</tr>
						<tr>
							<td><b>Last name</b> </td>
							<td><input type="text" name="lname" id="lname" style="width : 200px" value='<?php echo $lname; ?>'/></td>
						</tr>
						<tr>
							<td><b>Address</b> </td>
							<td><input type="text" name="address" style="width : 200px" value='<?php echo $address; ?>' /></td>
						</tr>
						<tr>
							<td><b>Zip code</b> </td>
							<td><input type="text" name="zip_code" style="width : 100px" value='<?php echo $zip_code; ?>'/></td>
						</tr>
						<tr>
							<td><b>Phone</b> </td>
							<td><input type="text" name="phone" id="phone" style="width : 200px" value='<?php echo $phone; ?>'/></td>
						</tr>						
					</table>
				</td>
				
				<td valign="top">
					<table>
						<tr>
							<td colspan="2"><b>Member Since:</b> <?=$msince?> </td>
						</tr>
						<tr>
							<td colspan="2"><b>Total funds raised for silos:</b> <?php if ($total_raised) { echo "$".$total_raised; } else { echo "$0.00"; } ?></td>
						</tr>
						<tr>
							<td><b>Current Password</b> </td>
							<td><input type="password" name="old_password" style="width : 200px"/></td>			
						</tr>
						<tr>
							<td><b>New Password</b> </td>
							<td><input type="password" name="new_password" style="width : 200px"/></td>			
						</tr>
						<tr>
							<td><b>Retype Password</b> </td>
							<td><input type="password" name="retype_new_password" style="width : 200px"/></td>			
						</tr>
						<tr>
							<td colspan="2"><br/></td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<button type="submit" name="update" value="Update">Update</button>
							</td>
						</tr>
					</table>					
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2">
					<p>
      						<div id="fb"><fb:login-button perms="email,user_address,user_mobile_phone">Connect your Facebook account with <?=SITE_NAME?>
      						</fb:login-button></div>
						<br><br>
						<img src="images/linkedin_connect.png" id="linkedin" style="margin-left: -3px"></img>
					</p>

					<p><font color="red"><b>E-mail notifications</b></font> 
					<font color="grey">For security reasons, we will notify you of a status changes for items you are buying or selling, 
					when you join a silo, or when a silo administrator does something of consequence (e.g. end a silo).
					<?=SITE_NAME?> is not responsible for email intiated by other users.</font></p>

					<?php 
					$chkItems = mysql_num_rows(mysql_query("SELECT * FROM items WHERE user_id = '$user_id' AND (status = 'pledged' OR status = 'offer' OR status = 'pending')"));
					$chkSilos = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = '$user_id' AND (status = 'active' OR status = 'latent')"));
					if (!$chkItems && !$chkSilos) { ?>
					<a onclick="popup_show('delete_account', 'delete_account_drag', 'delete_account_exit', 'screen-center', 0, 0);"><font color="red"><strong>Delete account</strong></font></a>
					<font color="grey">(this is not reversible, and cannot be performed with items pending or while a silo is open.)</font>
					<?php } else { ?>
					<a onclick="popup_show('keep_account', 'keep_account_drag', 'keep_account_exit', 'screen-center', 0, 0);"><font color="red"><strong>Delete account</strong></font></a>
					<font color="grey">(this is not reversible, and cannot be performed with items pending or while a silo is open.)</font>
					<?php } ?>
				</td>
			</tr>
		</table>
<?php
	}
?>

<div class="login" id="delete_account" style="width: 300px;">
	<div id="delete_account_drag" style="float:right">
		<img id="delete_account_exit" src="images/close.png"/>
	</div>
	<div>
		<form method="post" action="">
			<h2>Are you sure you want to delete your account?</h2>
			**All deletions are <strong>final</strong> and <strong>cannot</strong> be reversed!<br><br>
			<input type="hidden" name="id" value="<?=$id?>">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<button type="submit" name="delete" value="Delete account">Delete account</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('delete_account').style.display='none';">Cancel</button>
		</form>
	</div>
</div>

<div class="login" id="keep_account" style="width: 300px;">
	<div id="keep_account_drag" style="float:right">
		<img id="keep_account_exit" src="images/close.png"/>
	</div>
	<div>
		<form method="post" action="">
			<h2>You cannot delete your account due to active items and/or silos.</h2>
			You are only allowed to delete your account once you have no items pledged or pending. You also cannot be assosciated with an active silo.<br><br>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('keep_account').style.display='none';">Okay</button>
		</form>
	</div>
</div>

<div class="login" id="new_account" style="width: 500px;">
	<div id="new_account_drag" style="float:right">
		<img id="new_account_exit" src="images/close.png"/>
	</div>
	<div>
			<h2>Welcome to your <?=SITE_NAME?> account page!</h2>
			We have worked around the clock to make your user experience as fast and fluent as possible. We try to make everything simple and easy as well.<br><br>
			When you signed up, we only asked for your e-mail address and a password. By doing this, you can navigate through the site and view certain things you wouldn't be able to without creating an account.<br><br>
			If you would like to create a silo, donate an item, or buy an item on <?=SITE_NAME?>, you will need to provide some more information about yourself. We will not ask for this information until we absolutely need it. At any time, you can complete this by filling out your profile or by connecting with Facebook or LinkedIn. All of this can be located under your account page.<br><br>
			Our hope is to have each member help benefit a silo in some way, but we don't want to make it required, by any means.<br><br>
			In the meantime, we hope you enjoy <?=SITE_NAME?>! If you have any questions, please look at the bottom of the page to help get started or to view the frequently asked questions.<br><br>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('new_account').style.display='none';">Go to my account</button>
	</div>
</div>

<div class="login" id="addInfo_message" style="width: 500px;">
	<div id="addInfo_message_drag" style="float:right">
		<?php if ($valid_code != -1) { ?>
			<img id="addInfo_message_exit" src="images/close.png"/>
		<?php } ?>
	</div>
	<div>
			<h2>Completing your <?=SITE_NAME?> profile</h2>
			In order to use <?=SITE_NAME?> at its fullest, you will need to fill out your profile completely.<br><br>
			Below is a list of the specific details that you will need to complete:
			<blockquote><b>
			- First name <br>
			- Last name <br>
			- Address <br>
			- Phone <br>
			- Profile picture <br>
			</b></blockquote>
			Remember, you can always connect with Facebook or LinkedIn to get started right away without the hassle!<br><br>
			Once you have finished, we will redirect you back to where you were.<br><br>
		<?php if ($valid_code == -1) { mysql_query("UPDATE users SET validation_code = -2 WHERE user_id = '$user_id'"); ?>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo_message').style.display='none'; javascript:popup_show('new_account', 'new_account_drag', 'new_account_exit', 'screen-center', 0, 0);">Next message</button>
		<?php } else { ?>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo_message').style.display='none';">Continue to my account</button>
		<?php } ?>
	</div>
</div>

<div class="login" id="fb_connect" style="width: 500px;">
	<div id="fb_connect_drag" style="float:right">
		<img id="fb_connect_exit" src="images/close.png"/>
	</div>
	<div>
			<h2>You are connected with Facebook!</h2>
			You have successfully linked your Facebook profile with <?=SHORT_URL?>. If you would like to update your profile information, click the button below. Don't forget to click 'Update' afterwords.<br><br>
			<button type="button" id="fb-update" onclick="document.getElementById('overlay').style.display='none';document.getElementById('fb_connect').style.display='none';">Update profile now</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('fb_connect').style.display='none';">Later</button>
	</div>
</div>

</span>

<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
  FB.init({
    appId      : '<?=FACEBOOK_ID?>', // App ID
    channelUrl : '//<?=ACTIVE_URL?>include/fb.html', // Channel File
    status     : true, // check login status
    cookie     : true, // enable cookies to allow the server to access the session
    xfbml      : true  // parse XFBML
  });

  // Here we subscribe to the auth.authResponseChange JavaScript Event. This event is fired
  // for any auth related change, such as login, logout or session refresh. This means that
  // whenever someone who was previously logged out then logs in, the correct case below 
  // will be handled.
  FB.Event.subscribe('auth.authResponseChange', function(response) {
    // Here we specify what we do with the response anytime this event occurs. 
    if (response.status === 'connected') {
      // The response object is returned with a status field that lets us know what the current
      // login status of the person is. In this case, we're handling the situation where they 
      // have logged in to the app.
	$('#fb').html("<img src='images/fb-connect.png'></img> (Click to update your profile)");
    } else if (response.status === 'not_authorized') {
      // In this case, the person is logged into Facebook, but not into the app, so we call
      // FB.login() to prompt them to do so. 
      // In real-life usage, you wouldn't want to immediately prompt someone to login 
      // like this, for two reasons:
      // (1) JavaScript created popup windows are blocked by most browsers unless they 
      // result from direct user interaction (such as a mouse click)
      // (2) it is a bad experience to be continually prompted to login upon page load.
    } else {
      // In this case, the person is not logged into Facebook, so we call the login() 
      // function to prompt them to do so. Note that at this stage there is no indication
      // of whether they are logged into the app. If they aren't then they'll see the Login
      // Dialog right after they login to Facebook.
      // The same caveats as above apply to the FB.login() call here.
    }
  });
  };

  // Load the SDK Asynchronously
  (function(d){
   var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement('script'); js.id = id; js.async = true;
   js.src = "//connect.facebook.net/en_US/all.js";
   ref.parentNode.insertBefore(js, ref);
  }(document));

$("#fb").live('click', function() {
       javascript:popup_show('fb-confirm', 'fb-confirm_drag', 'fb-confirm_exit', 'screen-center', 0, 0);
});
$("#linkedin").live('click', function() {
       javascript:popup_show('linkedin-confirm', 'linkedin-confirm_drag', 'linkedin-confirm_exit', 'screen-center', 0, 0);
});

  // Here we are just running a very simple test of the Graph API after login is successful. 
  // This testAPI() function is only called in those cases. 
  function testAPI() {
    FB.api('/me', function(response) {
	uid = response.id;
	fname = response.first_name;
	lname = response.last_name;
	//email = response.email;
	photo = response.username;

	$("#fname").val(fname);
	$("#lname").val(lname);
	//$("#email").val(email);
       $('#fb_photo').val(photo);
	$("#account_form").submit();
    });
    FB.api(
        {
            method: 'fql.query',
            query: 'SELECT friend_count FROM user WHERE uid = me()'
        },
        function(data) {
		fcount = data[0].friend_count;
    		$("#friend_count").val(fcount);
        }
     );
  }
</script>

<div class="login" id="fb-confirm" style="width: 500px;">
	<div id="fb-confirm_drag" style="float:right">
		<img id="fb-confirm_exit" src="images/close.png"/>
	</div>
	<div>
		<h3>Please Read</h3>
		When you Connect with Facebook, we will grab the information from your Facebook.com account and store your information in our own database. Your information will not be updated until you click the Facebook button again. Each silo needs the user's information at some point. We will <b>never</b> share or distribute any of your information. All of your personal information stays within our site, <?=SHORT_URL?>, at all times. Some of your information will be given to silo administrators and people that you buy an item from or sell an item to. If you click continue, below, you are agreeing and allowing us to store your information in our database for site-wide purposes.<br><br> 
		<button onclick="testAPI();">Continue and Connect with Facebook</button>
		<button onclick="document.getElementById('overlay').style.display='none';document.getElementById('fb-confirm').style.display='none';">Cancel</button>
	</div>
</div>

<div class="login" id="linkedin-confirm" style="width: 500px;">
	<div id="linkedin-confirm_drag" style="float:right">
		<img id="linkedin-confirm_exit" src="images/close.png"/>
	</div>
	<div>
		<h3>Please Read</h3>
		When you Connect with LinkedIn, we will grab the information from your linkedin.com account and store your information in our own database. Your information will not be updated until you click the LinkedIn Connect button again. Each silo needs the user's information at some point. We will <b>never</b> share or distribute any of your information. All of your personal information stays within our site, <?=SHORT_URL?>, at all times. Some of your information will be given to silo administrators and people that you buy an item from or sell an item to. If you click continue, below, you are agreeing and allowing us to store your information in our database for site-wide purposes.<br><br> 
		<button onclick="document.location='linkedin.php?user_id=<?=$user_id?>&redirect=<?=$redirect?>'">Continue and Connect with LinkedIn</button>
		<button onclick="document.getElementById('overlay').style.display='none';document.getElementById('linkedin-confirm').style.display='none';">Cancel</button>
	</div>
</div>