<?php
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

		$sql = "UPDATE users SET photo_file = '$id.jpg' WHERE id = $id";
		mysql_query($sql);
	}

//If account info is updated
	if ($_SESSION['is_logged_in'] != 1) {
		echo "<script>window.location = 'index.php';</script>";
	}	
 	else {
		$username = $_SESSION['username'];		
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

			$sql = "SELECT * FROM users WHERE username='$username'";
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			$photo_file = $row['photo_file'];
			$id = $row['id'];
			
			if (strlen(trim($username)) == 0) {
				$err .= 'Username must not be empty.<br/>';		
			}
			if ($new_password != $retype_new_password) {
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
				$err .= 'Email address is invalid.<br/>';		
			}
			else {
				$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users WHERE email = '$email' AND username <> '$username'"));
				if ($tmp[0] > 0) {
					$err .= "Email '$email' is already registered.<br/>";
				}
			}
		
			if (strlen($err) == 0) {				
				if ($old_password != '' || $new_password != '' || $retype_new_password != '') {
					if ($old_password != $row['password']) {
						$err .= "Current password is not correct.";
					}					
					else if ($new_password == '') {
					 	$err .= "New password must not be empty.";
					}
					else {
						$sql = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', address = '$address', zip_code = '$zip_code', phone = '$phone', password='$new_password' WHERE id = $id";
						mysql_query($sql);

						if ($_FILES['member_photo']['name'] != '') {
							$allowedExts = array("png", "jpg", "jpeg", "gif");							
							$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
							if (!in_array($ext, $allowedExts)) {
								$err .= $_FILES['member_photo']['name']." is invalid file type.";
							}
							else {
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
						}						
					}
				}				
				else {
					$sql = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', address = '$address', zip_code = '$zip_code', phone = '$phone' WHERE id = $id";
					mysql_query($sql);
					if ($_FILES['member_photo']['name'] != '') {
						$allowedExts = array("png", "jpg", "jpeg", "gif");							
						$ext = end(explode('.', strtolower($_FILES['member_photo']['name'])));
						if (!in_array($ext, $allowedExts)) {
							$err .= $_FILES['member_photo']['name']." is invalid file type.";
						}
						else {
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
							imagejpeg($i,"uploads/".$id.".jpg",80);
						}
					}					
				}		
			}
		}
		else {
			$sql = "SELECT * FROM users WHERE username='$username'";
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			$id = $row['id'];
			$user_id = $row['user_id'];
			$fname = $row['fname'];
			$lname = $row['lname'];
			$email = $row['email'];
			$address = $row['address'];
			$zip_code = $row['zip_code'];
			$phone = $row['phone'];
			$jdate = $row['joined_date'];
			$msince = date("m/d/Y", strtotime($jdate));
			$photo_file = $row['photo_file'];

			$funds = mysql_fetch_array(mysql_query("SELECT SUM(price) AS total FROM items WHERE user_id = '$user_id' AND status = 'sold'"));
			$total_raised = $funds['total'];
		}

	$updatemsg = "Your account has been updated!";
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

<div class="heading" style="padding-bottom:5px;">
	<table width="940px" style="border-spacing: 0px;">
		<tr>
			<td width="700px">
				<b>Your Account</b><?php echo " (".$fname." ".$lname.")"?>
			</td>
			<td align="center">
				<a href="index.php?task=transaction_console" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">Transaction Console</a>
			</td>	
			<td align="center">
				<span style="color: #fff">|</span>
			</td>					
			<td align="center">
				<a href="index.php?task=my_listings" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">My Listings</a>
			</td>
			<td align="center">
				<span style="color: #fff">|</span>
			</td>
			<td align="center">
				<a href="index.php?task=my_account" style="font-size: 12px; font-weight: bold; background: transparent; border: 0px; color: #fff">Home</a>
		</tr>
	</table>
</div>
<br/>

<?php
//If account info updated
if ((param_post('task') == 'update_account') && (strlen($err) == 0) && ($filename)) {
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
			Thanks for using siloz.com!</b></font>
		</center>
		<br>
		<br>
<?php
session_destroy();
die;
}
?>

	<hr/>
	<font size="4"><b>My Account Information</b></font>
	<?php
		if (strlen($err) > 0) {
			echo "<div style='float: right'><font color='red'><b>".$err."</b></font><br/></div>";
		}
		
		if ((param_post('task') == 'update_account') && !$filename && strlen($err) == 0) { 
			echo "<div style='float: right'><font color='red'><b>".$updatemsg."</b></font><br/></div>";
		}
		elseif ($crop == "true") { 
			echo "<div style='float: right'><font color='red'><b>".$updatemsg."</b></font><br/></div>";
		}

	?>
	<hr/>

	<form enctype="multipart/form-data"  name="my_account_form" class="my_account_form" method="POST">
		<input type="hidden" name="task" value="update_account"/>		
		<table cellpadding="10px">
			<tr>
				<td valign="top" width="250px">
					<table>						
						<tr>			
							<td align="center"><img src=<?php echo 'uploads/members/'.$photo_file;?> width="250px"></td>
						</tr>
						<tr>
							<td><input name="member_photo" type="file" style="height: 20px"/></td>
						</tr>		
					</table>
				</td>
				
				
				<td valign="top">
					<table>
						<tr>
							<td><b>First name</b> </td>
							<td><input type="text" name="fname" style="width : 200px" value='<?php echo $fname; ?>'/></td>
						</tr>
						<tr>
							<td><b>Last name</b> </td>
							<td><input type="text" name="lname" style="width : 200px" value='<?php echo $lname; ?>'/></td>
						</tr>
						<tr>
							<td colspan="2"><b>Member Since:</b> <?=$msince?> </td>
						</tr>
						<tr>
							<td><b>Email</b> </td>
							<td><input type="text" name="email" style="width : 200px" value='<?php echo $email; ?>'/></td>
						</tr>
						<tr>
							<td><b>Address</b> </td>
							<td><input type="text" name="address" style="width : 200px" value='<?php echo $address; ?>'/></td>
						</tr>
						<tr>
							<td><b>Zip code</b> </td>
							<td><input type="text" name="zip_code" style="width : 100px" value='<?php echo $zip_code; ?>'/></td>
						</tr>
						<tr>
							<td><b>Phone</b> </td>
							<td><input type="text" name="phone" style="width : 200px" value='<?php echo $phone; ?>'/></td>
						</tr>						
					</table>
				</td>
				
				<td valign="top">
					<table>
						<tr>
							<td><b>Username</b> </td>
							<td><input type="text" name="username" style="width : 200px" value='<?php echo $username; ?>' disabled="disabled"/></td>			
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
					<p><font color="red"><b>E-mail notifications</b></font> 
					<font color="grey">For security reasons, we will notify you of a status changes for items you are buying or selling, 
					when you join a silo, or when a silo administrator does something of consequence (e.g. end a silo).
					siloz is not responsible for email intiated by other users.</font></p>

					<form method="post" action="">
					<input type="hidden" name="id" value="<?=$id?>">
					<input type="hidden" name="user_id" value="<?=$user_id?>">
					<input style="color: red; font-weight: bold; background: #fff;" type="submit" name="delete" value="Delete account" onClick="return confirmSubmit()">
					<font color="grey">(this is not reversible, and cannot be performed with items pending or while a silo is open.)</font>
				</td>
			</tr>
		</table>
	</form>
<?php
	}
?>

<script>
function confirmSubmit()
{
var agree=confirm("Are you sure you want to delete your account? ALL DELETIONS ARE FINAL!");
if (agree)
	return true ;
else
	return false ;
}
</script>