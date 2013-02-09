<script type="text/javascript">
function updateStatus (e, item_id) {
   	if (e.options[e.selectedIndex].value != "") { 
		$.post(<?php echo "'".API_URL."'"; ?>, 
			{	
				request: 'update_item_status',
				item_id: item_id,
				status: e.options[e.selectedIndex].value
			}, 
			function (xml) {
				var status = e.options[e.selectedIndex].value;
				var arr = new Array("Pledged", "Item Sold", "Funds Sent");
				if (status == "Item Sold")
					arr = new Array("Item Sold", "Funds Sent");
				if (status == "Funds Sent")
					arr = new Array("Funds Sent");
				var out = "<select style='font-size: 10px; width: 70%' onchange='updateStatus(this," + item_id + ")' id='dropdown_" + item_id + "'>";
				var i;
				for (i in arr) {
					var s = arr[i];
					if (s == status)
						out += "<option value='" + s + "' selected>" + s + "</option>";
					else
						out += "<option value='" + s + "'>" + s + "</option>";
				}
				out += "</select>";	
				document.getElementById("dropdown_" + item_id).innerHTML = out;
			}
		);
   	}
}

function populate_item_info(item_id) {
	document.forms['update_item'].elements['item_id'].value = item_id;
	$.post(<?php echo "'".API_URL."'"; ?>, 
		{	
			request: 'get_item_info',
			item_id: item_id
		}, 
		function (xml) {
			$(xml).find('item').each(function (){
				document.forms['update_item'].elements['title'].value = $(this).find('title').text();
				document.forms['update_item'].elements['price'].value = $(this).find('price').text();
				document.forms['update_item'].elements['item_cat_id'].value = $(this).find('item_cat_id').text();
				document.forms['update_item'].elements['description'].value = $(this).find('description').text();				
			});
		}
	);	
}

</script>

<?php
//If account info is updated
	function getStatusDropDown($item_id, $current) {
		if ($current == "Funds Received" || $current == "Funds Sent")
			return $current;
			
		$status_set = array("Pledged", "Item Sold", "Funds Sent");
		if ($current == "Item Sold")		
			$status_set = array("Item Sold", "Funds Sent");
		$out = "<select style='font-size: 10px; width: 80px;' onchange=\"updateStatus(this,'".$item_id."')\" id='dropdown_$item_id'>";
		foreach ($status_set as $s) {
			if ($s == $current)
				$out .= "<option value='".$s."' selected>".$s."</option>";
			else
				$out .= "<option value='".$s."'>".$s."</option>";
		}
		$out .= "</select>";
		return $out;
	}

	if (param_post('delete_item') != '') {
		$item_id = param_post('item_id');
		$item = new Item($item_id);
		$silo = $item->silo;
		$owner = $item->owner;
		$admin = $silo->admin;
		
		$sql = "UPDATE items SET deleted_date = CURRENT_TIMESTAMP WHERE id = $item_id";
		mysql_query($sql);			
		
		$message = "This email is to certify that your item number #".$item_id.": ".$item->title." has been removed from silo ".$silo->name.", on ".date()."<br/><br/>";
		$message .= "Thanks,<br/><br/>
					Siloz Staff.";			
		email_with_template($owner->email, $subject, $message);			
	}
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
			$fullname = param_post('fullname');
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
			if (strlen(trim($fullname)) == 0) {
				$err .= 'Full name must not be empty.<br/>';		
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
						$sql = "UPDATE users SET fullname = '$fullname', email = '$email', address = '$address', zip_code = '$zip_code', phone = '$phone', password='$new_password' WHERE id = $id";
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
					$sql = "UPDATE users SET fullname = '$fullname', email = '$email', address = '$address', zip_code = '$zip_code', phone = '$phone' WHERE id = $id";
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
			$fullname = $row['fullname'];
			$email = $row['email'];
			$address = $row['address'];
			$zip_code = $row['zip_code'];
			$phone = $row['phone'];
			$photo_file = $row['photo_file'];
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
	}

	$updatemsg = "Your account has been updated!";
?>
<?php
// If item is updated
	$err = "";
	if (param_post('item') == 'Update') {			
		$item_id = param_post('item_id');
		$title = param_post('title');
		$price = param_post('price');
		$item_cat_id = param_post('item_cat_id');
		$description = param_post('description');
	
		if (strlen(trim($title)) == 0) {
			$err .= "Item title must not be empty. <br/>";
		}
		if (strlen(trim($title)) > 40) {
			$err .= "Your new item title is too long. Please shorten it. <br/>";
		}
		if (strlen(trim($price)) == 0) {
			$err .= "Item price must not be empty. <br/>";
		}
		if ( ($_FILES['item_photo_1']['name']) && ($_FILES['item_photo_3']['name']) && (!$_FILES['item_photo_2']['name']) ) {
			$err .= "Please submit a second image for your item or remove the third image.";
		}
		if ( ($_FILES['item_photo_1']['name']) && ($_FILES['item_photo_4']['name']) && ((!$_FILES['item_photo_2']['name']) || (!$_FILES['item_photo_3']['name']))  ) {
			$err .= "Please submit a second and third image for your item or remove the fourth image.";
		}
		if ( (!$_FILES['item_photo_1']['name']) && (($_FILES['item_photo_2']['name']) || ($_FILES['item_photo_3']['name']) || ($_FILES['item_photo_4']['name']))  ) {
			$err .= "Please submit your image in the first slot before adding more images.";
		}
	
		if (strlen($err) == 0) {
			for ($i=1; $i<=4; ++$i) {
				if ($_FILES['item_photo_'.$i]['name'] != '') {
					$allowedExts = array("png", "jpg", "jpeg", "gif");
					$ext = end(explode('.', strtolower($_FILES['item_photo_'.$i]['name'])));
					if (!in_array($ext, $allowedExts)) {
						$err .= $_FILES['item_photo_'.$i]['name']." is invalid file type.<br/>";
						break;
					}
					else {
						$filename = $_FILES['item_photo_'.$i]['name'];
						$temporary_name = $_FILES['item_photo_'.$i]['tmp_name'];
						$mimetype = $_FILES['item_photo_'.$i]['type'];
						$filesize = $_FILES['item_photo_'.$i]['size'];
						$uploaded = $i;

						switch($mimetype) {

    							case "image/jpg":

    							case "image/jpeg":

        						$img = imagecreatefromjpeg($temporary_name);

       						break;

    							case "image/gif":

        						$img = imagecreatefromgif($temporary_name);

        						break;

    							case "image/png":

        						$img = imagecreatefrompng($temporary_name);

        						break;
						}

						unlink($temporary_name);
						imagejpeg($img,"uploads/".$item_id."_".$i.".jpg",80);
					}
				}				
			}
			
			$sql = "UPDATE items SET title=?, price=?, item_cat_id=?, description = ? WHERE id = '$item_id';";
			$stmt->prepare($sql);			
			$stmt->bind_param("ssss", $title, $price,$item_cat_id, htmlentities($description, ENT_QUOTES));
			$stmt->execute();
			$stmt->close();

			if ($filename) {
				$success = "true";
			}
			else {
				$updmsg = "true";
			}
		}
	}

	if (param_post('crop') == 'Crop1') {
		$item_id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$item_id.'_1.jpg';
		$name = 'uploads/items/'.$item_id.'_1.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_1 = '".$item_id."_1.jpg' WHERE id = '$item_id'");

		if ($_POST['upload2']) { $icrop = "2"; } else { $icrop = "true"; }
		if ($_POST['upload3']) { $upl3 = "3"; }
		if ($_POST['upload4']) { $upl4 = "4"; }

	}
	elseif (param_post('crop') == 'Crop2') {
		$item_id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$item_id.'_2.jpg';
		$name = 'uploads/items/'.$item_id.'_2.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_2 = '".$item_id."_2.jpg' WHERE id = '$item_id'");

		if ($_POST['upload3']) { $icrop = "3"; } else { $icrop = "true"; }
		if ($_POST['upload4']) { $upl4 = "4"; }
	}
	elseif (param_post('crop') == 'Crop3') {
		$item_id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$item_id.'_3.jpg';
		$name = 'uploads/items/'.$item_id.'_3.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_3 = '".$item_id."_3.jpg' WHERE id = '$item_id'");

		if ($_POST['upload4']) { $icrop = "4"; } else { $icrop = "true"; }
	}
	elseif (param_post('crop') == 'Crop4') {
		$item_id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$item_id.'_4.jpg';
		$name = 'uploads/items/'.$item_id.'_4.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_4 = '".$item_id."_4.jpg' WHERE id = '$item_id'");

		$icrop = "true";
	}


	if ($icrop == "true") {
		echo "<script>window.location = 'index.php?task=my_account';</script>";			
	}

	$itemmsg = "Your item has been updated!";
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

<div class="heading">
	<b>Your Account</b><?php echo " (".$_SESSION['username'].")"?>
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
if ($success && $_FILES['item_photo_1']['name']) {
?>
		<center>
				<h1>Create a Silo</h1>
		To finish editing your item, please crop all of the images you uploaded below (Image 1):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$item_id?>_1.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$item_id?>" />

		<?php if ($_FILES['item_photo_2']['name']) { echo '<input type="hidden" name="upload2" value="2" />'; } ?>
		<?php if ($_FILES['item_photo_3']['name']) { echo '<input type="hidden" name="upload3" value="3" />'; } ?>
		<?php if ($_FILES['item_photo_4']['name']) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>

			<button type="submit" name="crop" value="Crop1">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

<?php
if ($icrop == "2") {
?>
		<center>
				<h1>Create a Silo</h1>
		To finish editing your item, please crop the image you uploaded below (Image 2):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$item_id?>_2.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$item_id?>" />
		<?php if ($upl3) { echo '<input type="hidden" name="upload3" value="3" />'; } ?>
		<?php if ($upl4) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>
			<button type="submit" name="crop" value="Crop2">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

<?php
if ($icrop == "3") {
?>
		<center>
				<h1>Create a Silo</h1>
		To finish editing your item, please crop the image you uploaded below (Image 3):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$item_id?>_3.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$item_id?>" />
		<?php if ($upl4) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>
			<button type="submit" name="crop" value="Crop3">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

<?php
if ($icrop == "4") {
?>
		<center>
				<h1>Create a Silo</h1>
		To finish editing your item, please crop the image you uploaded below (Image 4):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$item_id?>_4.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$item_id?>" />
			<button type="submit" name="crop" value="Crop4">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

	<?php
		if (strlen($err) > 0) {
			echo "<font color='red'><b>".$err."</b></font><br/>";
		}
		
		if ((param_post('task') == 'update_account') && !$filename && strlen($err) == 0) { 
			echo "<font color='red'><b>".$updatemsg."</b></font><br/>";
		}
		elseif ($crop == "true") { 
			echo "<font color='red'><b>".$updatemsg."</b></font><br/>";
		}

	?>
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
							<td><b>Fullname</b> </td>
							<td><input type="text" name="fullname" style="width : 200px" value='<?php echo $fullname; ?>'/></td>
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
		</table>
	</form>
	
	<hr/>
	<font size="4"><b>Manage my listings</b></font>
	<?php
	if ($icrop == "true" || $updmsg == "true") { 
		echo "<div style='float: right'><font color='red'><b>$itemmsg</b></font></div>";
	}
	?>
	<hr/>
	<?php
		$user_id = $_SESSION['user_id'];
		$sql = "SELECT id FROM items WHERE deleted_date = 0 AND user_id = $user_id ORDER BY item_id";
		$items = mysql_query($sql);
		$n = 0;
		echo "<table cellpadding='3px'>";			
		while ($row = mysql_fetch_array($items)) {
			if ($n == 0)
				echo "<tr>";
			$item_id = $row['id'];
			$item = new Item($item_id);
			$delete_html = '';
			if ($item->status == 'Pledged')
				$delete_html = "<form name='f$item_id' id='f$item_id' method='post' action=''><input type='hidden' name='item_id' value='$item_id'><input type='hidden' name='delete_item' value='delete_$item_id'><a href='javascript:document.f$item_id.submit()' class='confirmation'><img src=images/delete.png style='margin-top: -5px; margin-left:-5px; margin-right: 5px;'></a>";			
			$cell = "<td><div class=plate id='item_$item_id' style='color: #000; font-size: 11px; height: 200px;'>";			
			$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'>$delete_html<a href='index.php?task=view_item&id=$item_id'><b>".substr($item->title, 0, 40)."</b></a></form></div><img height=100px width=135px src=uploads/items/".$item->photo_file_1." style='margin-bottom: 3px'><div style='font-size: 11px; color: #000;'><button type='button' onclick=\"popup_show('edit_item', 'edit_item_drag', 'edit_item_exit', 'screen-center', 0, 0);populate_item_info('".$item_id."');\" style='margin-left:65px; margin-top: -35px; position: absolute;'>Edit Item</button><b>Status: </b>".getStatusDropDown($item_id, $item->status)."<br/><b>Benefiting: </b><a href='index.php?task=view_silo&id=".$item->silo->id."'>".substr($item->silo->name,0,30)."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$item->price."</b></span></td><td align=right><a href='index.php?task=view_item&id=$item_id'><i><b>more...</b></i></a></td></tr></table></div></td>";							
			echo $cell;					
			$n++;
			if ($n == 6) {
				echo "</tr>";
				$n = 0;
			}		
		}
		echo "</table>";			
	?>
	


<div class="edit_item" id="edit_item" style="width: 800px">
	<div id="edit_item_drag" style="float: right">
		<img id="edit_item_exit" src="images/close.png"/>
	</div>

	<div>
		<h2>Update Item</h2>
		<p><font size="3">Please edit your item details below, and upload up to 4 images for your item.</font></p>
		<?php
			if (strlen($err) > 0) {
				echo "<font color='red'><b>".$err."</b></font>";
			}
		?>						
		<form enctype="multipart/form-data"  name="update_item" class="my_account_form" method="POST">
			<input type="hidden" name="item_id" value=""/>						

			<table width="100%" cellpadding="10px" align="center">
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td><b>Listing Title</b> </td>
								<td><input type="text" name="title" style="width : 300px" value='<?php echo $title; ?>'/></td>
							</tr>		
							<tr>
								<td><b>Price</b> </td>
								<td><input type="text" name="price" style="width : 100px" value='<?php echo $price; ?>'/></td>
							</tr>
							<tr>
								<td><b>Category</b> </td>
								<td>
									<select name="item_cat_id" style="width: 300px">
										<option value="">Select an Item type</option>
										<?php
											$sql = "SELECT * FROM item_categories";
											$res = mysql_query($sql);
											while ($row = mysql_fetch_array($res)) {
												if ($row['item_cat_id'] == $item_cat_id) {
													echo "<option value='".$row['item_cat_id']."' selected>".$row['category']."</option>";
												}
												else {
													echo "<option value='".$row['item_cat_id']."'>".$row['category']."</option>";
												}
											}							
										?>							
									</select>
								</td>
							</tr>
							<tr>
								<td><b>Description</b> </td>
								<td><textarea name="description" style="width: 200px; height: 100px"><?php echo $description; ?></textarea></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td><b>Photo file 1</b> </td>
								<td><input name="item_photo_1" type="file" style="width: 200px;height:20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 2</b> </td>
								<td><input name="item_photo_2" type="file" style="width: 200px;height:20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 3</b> </td>
								<td><input name="item_photo_3" type="file" style="width: 200px;height:20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 4</b> </td>
								<td><input name="item_photo_4" type="file" style="width: 200px;height:20px;"/></td>
							</tr>
						</table>
						<br/>
						<button type="submit" name="item" value="Update">Update</button>
					</td>
				</tr>	
			</table>	
		</form>
		<script>
			$("#edit_item_button").click(function(event) {	
				document.getElementById('overlay').style.display='none';
				document.getElementById('edit_item').style.display='none';
			});
		</script>		
	</div>
</div>
<?php
	}
?>