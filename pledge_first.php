<?php
//include("include/timeout.php");

if ($_SESSION['is_logged_in'] != 1) { ?>
	<script type="text/javascript">
		$(document).ready(function () {
   		 	if (!runCode) {
        			$("#login").fancybox().trigger('click');
				var runCode = true;
			}
		})
	</script>
<?php 
} elseif ($addInfo_full) {
	echo "<script>window.location = 'index.php?task=my_account&redirect=1';</script>";
}
else {
	$user_id = $_SESSION['user_id'];	
	$user = new User($user_id);
	$title = "";
	$avail = "";
	$item_cat_id = "0";
	$description = "";

	$err = "";
	if (param_post('submit') == 'Finish') {
		$title = param_post('title');
		$avail = param_post('avail');
		$price = param_post('price');
		if ($price) { $price = round($price); }
		$item_cat_id = param_post('item_cat_id');
		$description = param_post('description');
		$address = param_post('address');
		$zip = param_post('zip');
		$admin_email = param_post('admin_email');
		
		//test for form errors //comment added sept 8th 2012 james kenny
		if (strlen(trim($title)) == 0) {
			$err .= "Item title must not be empty. <br/>";
		}
		if (strlen(trim($title)) > 40) {
			$err .= "Your item title is too long. Please shorten it. <br/>";
		}
		if (strlen(trim($price)) == 0) {
			$err .= "Item's price must not be empty. <br/>";
		}
		else if (!is_numeric($price)) {
			$err .= "Item's price is not a valid number.<br/>";
		}
		else if (floatval($price) < 5) {
			$err .= "The minimum price for an item is $5.<br/>";
		}
		else if (floatval($price) > 100000000) {
			$err .= "Item's price exceeds the allowed maximum. For more information, contact siloz through the link in our footer. <br/>";
		}
		if (strlen(trim($avail)) > 30) {
			$err .= "Please limit your availablitiy to 30 characters.<br>";
		}
		if (strlen(trim($description)) > 500) {
			$err .= "Please limit your description to 500 characters.<br>";
		}
		if ( (!$_FILES['item_photo_1']['name']) && (!$_FILES['item_photo_2']['name']) && (!$_FILES['item_photo_3']['name']) && (!$_FILES['item_photo_4']['name']) ) {
			$err .= "Please submit at least one image.<br>";
		}
		if ( ($_FILES['item_photo_1']['name']) && ($_FILES['item_photo_3']['name']) && (!$_FILES['item_photo_2']['name']) ) {
			$err .= "Please submit a second image for your item or remove the third image.<br>";
		}
		if ( ($_FILES['item_photo_1']['name']) && ($_FILES['item_photo_4']['name']) && ((!$_FILES['item_photo_2']['name']) || (!$_FILES['item_photo_3']['name']))  ) {
			$err .= "Please submit a second and third image for your item or remove the fourth image.<br>";
		}
		if ( (!$_FILES['item_photo_1']['name']) && (($_FILES['item_photo_2']['name']) || ($_FILES['item_photo_3']['name']) || ($_FILES['item_photo_4']['name']))  ) {
			$err .= "Please submit your image in the first slot before adding more images.<br>";
		}
		if (strlen($admin_email) == 0) {
			$err .= "Please enter the admininstator's e-mail address.<br/>";		
		}
		else if (!is_valid_email($admin_email)) {
			$err .= "The address '$admin_email' is not a valid e-mail address.<br/>";		
		}
		else {
			$checkAdmin = mysql_fetch_array(mysql_query("SELECT user_id FROM users WHERE email = '$admin_email'"));
			$checkAdmin_id = $checkAdmin[0];
			$checkSilo = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = '$checkAdmin_id'"));
			if ($checkSilo > 0 && $checkAdmin_id) {
				$err .= "The e-mail address '$admin_email' is already assosciated with another account that has already began a silo.<br/>";
			}
		}

		$adr = urlencode($address);
		$zip_code = urlencode($zip);
		$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$adr."+".$zip_code."&sensor=false");
		$loc = json_decode($json);

		if ($loc->status == 'OK') {
			$new_adr = $loc->results[0]->formatted_address;
			$address = $new_adr;
			$lat = $loc->results[0]->geometry->location->lat;
			$long = $loc->results[0]->geometry->location->lng;
		}
		 else {
			$err .= 'Invalid address.<br/>';
		}
		
		$joined = true;
		if (strlen($err) == 0) {

			$sql = "INSERT INTO items(silo_id, user_id, title, price, item_cat_id, description, status) VALUES (?,?,?,?,?,?,?);";
			$stmt->prepare($sql);			
			$status = "Requested";
			$stmt->bind_param("sssssss", $silo->silo_id, $user_id, $title, $price,$item_cat_id, htmlentities($description, ENT_QUOTES), $status);
			$stmt->execute();
			$stmt->close();
			$allowedExts = array("png", "jpg", "jpeg", "gif");
			$actual_id = $db->insert_id;
			$id = "02".time()."0".$actual_id;
			
			$sql = "UPDATE items SET id = '$id', avail = '$avail', longitude = '$long', latitude = '$lat', added_date = NOW() WHERE item_id = $actual_id";
			mysql_query($sql);

			$sqladr = "UPDATE users SET address = '$new_adr' WHERE user_id = $user_id";
			mysql_query($sqladr);
						
			for ($i=1; $i<=4; ++$i) {
				$filesize = $_FILES['item_photo_'.$i]['size'];
				if ($filesize > 2097152) {
					$err .= "Image file is too large. Please scale it down.";
				}
				elseif ($_FILES['item_photo_'.$i]['name'] != '') {
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

							$name = "uploads/".$id."_".$i.".jpg";
							$targ_w = "900";
							$img_w = getimagesize($temporary_name);

							if ($img_w[0] > $targ_w) {
      								$image = new Photo();
      								$image->load($temporary_name);
      								$image->resizeToWidth($targ_w);
								$image->save($name);
							} else {
								imagejpeg($img,$name,80);
							}

							unlink($temporary_name);
					}
				}							
			}

			if (strlen($err) > 0) {
				$sql = "DELETE FROM items WHERE id = $id";
				mysql_query($sql);
				$err = "Something went wrong..";
			}			
		}
		if (strlen($err) == 0) {
				$subjectItem = "Thank you for joining ".$silo->name;
				$messageItem = "<br/>Congratulations on joining silo <b>".$silo->name."</b> with your item - <b>$title</b> ($$price).<br/><br/>";
				$messageItem .= "<h3>Getting Started</h3>";
				$messageItem .= "Remember: you can share the silo you joined on <b>Facebook</b>, or, use our address book tool to generate an email to your frequent contacts to notify them of your fund-raiser's need for member.  Click <a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo->id."'>here</a> to notify your contacts.<br/><br/>";
				$messageItem .= "We thank you for participating in your silo and for using siloz.com.<br/><br/>
							Sincerely,<br/><br/>
							".SITE_NAME." Staff.";			
			    email_with_template($user->email, $subjectItem, $messageItem);
				
		//Add user that will start the silo if there is no account assos. with the e-mail provided
			$getAdmin = mysql_fetch_array(mysql_query("SELECT user_id FROM users WHERE email = '$admin_email'"));
			if (!$getAdmin) {
				$code = rand(100000, 999999);
				$adminUser = new User();
				$adminUser->email = $admin_email;
				$adminUser->validation_code = $code;
				$admin_id = $adminUser->Save();
				$adminUser = new User($admin_id);
				$userStage = "Password";			
			} else {
				$admin_id = $getAdmin[0];
				$adminUser = new User($admin_id);

				if (!$adminUser->fname || !$adminUser->lname || !$adminUser->address || !$adminUser->phone || !$adminUser->photo_file) {
					$userStage = "Info";
				} else {
					$userStage = "Complete";
				}
			}

		//Create a silo in the database to link up the user and new admin user
			$silo = new Silo();
			$silo->admin_id = $adminUser->user_id;
			$silo->end_date = date('Y-m-d H:i:s', strtotime('+2 week'));
			$silo->status = "pending";
			$silo_id = $silo->Save();
			mysql_query("UPDATE items SET silo_id = '$silo_id' WHERE item_id = '$actual_id'");

		//Send silo user info about creating a silo and activating their account
			$subject = "Silo request from ".$user->fname." ".$user->lname."!";
			$message = "<h3>".$user->fname." ".$user->lname." is requesing that you create a silo on ".SHORT_URL.".</h3>";
			$message .= "<b>What is ".SITE_NAME."?</b> You could think of a silo as a rummage sale, online, to benefit a local cause, whether private or public. We think ".SITE_NAME." is transparent, safe, and fun. Everybody wins: Buyers get merchandise while helping local causes. Those donating items often receive a tax-deduction for a sold item and silo administrators raise money without asking for cash! For more information, please go to ".SHORT_URL." or click <a href='".ACTIVE_URL."#quick-start'>here</a>. <br><br>";
			$message .= "To pursue your silo, you will need to respond within <b>2</b> weeks. You will be walked along the process the whole way. Please look below for instructions on how to start your first silo!<br><br>";

			if ($userStage == "Password") {
				$token = genRandomString(15);
				$dbReset = mysql_query("INSERT INTO password_reset (user_id, token, exp_date) VALUES ('$adminUser->id', '$token', DATE_ADD(NOW(), INTERVAL 14 DAY))");
				$link = ACTIVE_URL."index.php?task=reset_password&id=".$adminUser->id."&token=".$token."&validation_code=".$code."&refer_id=".$user->id;				
				$message .= "We have determined, from your e-mail address, that you do not have an account on ".SITE_NAME." yet. We have already created an account for you. You will need to enter a password to gain access to this account. Once you have entered a password, you will be redirected to your new account page. All users are required to enter a certain amount of information to start a silo. User information is never shared with third-party websites and is only used for site-wide purposes. After you have filled out your profile and account information, you can proceed to create your public or private silo. You can start this quick proccess by clicking on the link below: <br><br>";
				$message .= "<a href='".$link."'>".$link."</a> <br><br>";
				$message .= "Welcome to ".SITE_NAME."!";
			} elseif ($userStage == "Info") {
				$link = ACTIVE_URL."index.php?task=my_account&redirect=create_silo&id=".$user->id;
				$message .= "We have found an account with the e-mail address provided, but the account has not been fully filled out quite yet. In order to create a silo on ".SITE_NAME.", you will need to complete your profile information. User information is never shared with third-party websites and is only used for site-wide purposes. After completion of your profile, you will be redirected to a page to create your silo! You can begin right now by click on the link below: <br><br>";
				$message .= "<a href='".$link."'>".$link."</a> <br><br>";
				$message .= "We hope you enjoy ".SITE_NAME.".com and we wish your new silo the absolute best!";
			} elseif ($userStage == "Complete") {
				$link .= ACTIVE_URL."index.php?task=create_silo&id=".$user->id;
				$message .= "We have determined that you have an account with ".SITE_NAME." and you do not have any active silos. All of the hard work is over! To start a new silo with an item already pledged towards your cause, click on the link below: <br><br>";
				$message .= "<a href='".$link."'>".$link."</a> <br><br>";
				$message .= "We hope you enjoy ".SITE_NAME.".com and we wish your new silo the absolute best!";
			}

			email_with_template($admin_email, $subject, $message);

		//Proceed adding item, like normal
			if(!$Vouch){$Vouch = new Vouch();}
			$Vouch->Save($silo_id, $user_id, '76');

			$joined = false;
			$member = "INSERT INTO silo_membership (silo_id, user_id) VALUES (".$silo_id.",".$user_id.")";
			mysql_query($member);
			$status = "Joined";

			$Feed = new Feed();
			$Feed->silo_id = $silo->silo_id;
			$Feed->user_id = $user_id;
			$Feed->item_id = $actual_id;
			$Feed->status = $status;
			$Feed->Save();

			$success = "true";
		}
	}

	if (param_post('crop') == 'Crop1') {
		$id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'_1.jpg';
		$name = 'uploads/items/'.$id.'_1.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_1 = '".$id."_1.jpg' WHERE id = '$id'");

		if ($_POST['upload2']) { $crop = "2"; } else { $crop = "true"; }
		if ($_POST['upload3']) { $upl3 = "3"; }
		if ($_POST['upload4']) { $upl4 = "4"; }

	}
	elseif (param_post('crop') == 'Crop2') {
		$id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'_2.jpg';
		$name = 'uploads/items/'.$id.'_2.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_2 = '".$id."_2.jpg' WHERE id = '$id'");

		if ($_POST['upload3']) { $crop = "3"; } else { $crop = "true"; }
		if ($_POST['upload4']) { $upl4 = "4"; }
	}
	elseif (param_post('crop') == 'Crop3') {
		$id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'_3.jpg';
		$name = 'uploads/items/'.$id.'_3.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_3 = '".$id."_3.jpg' WHERE id = '$id'");

		if ($_POST['upload4']) { $crop = "4"; } else { $crop = "true"; }
	}
	elseif (param_post('crop') == 'Crop4') {
		$id = trim(param_post('item_id'));
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'_4.jpg';
		$name = 'uploads/items/'.$id.'_4.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE items SET photo_file_4 = '".$id."_4.jpg' WHERE id = '$id'");

		$crop = "true";
	}

	if ($crop == "true") {
		echo "<script>window.location = 'index.php?task=view_item&id=".$id."';</script>";
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

<span class="greyFont">

<div class="headingPad"></div>

<div class="userNav" align="center">
	<span class="accountHeading">
		<b>Donate an item to a silo that hasn't been created yet. Be the first to pledge an item!</b>
	</span>
</div>

<div class="headingPad"></div>

<?php
if ($success && $_FILES['item_photo_1']['name']) {
?>
		<center>
				<h1>Donate to a Silo</h1>
		To finish pledging your item, please crop all of the images you uploaded below (Image 1):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$id?>_1.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$id?>" />

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
elseif ($success && !$filename) { echo "<script>window.location = 'index.php?task=view_item&id=".$id."';</script>"; }
?>

<?php
if ($crop == "2") {
?>
		<center>
				<h1>Donate to a Silo</h1>
		To finish pledging your item, please crop the image you uploaded below (Image 2):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$id?>_2.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$id?>" />
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
if ($crop == "3") {
?>
		<center>
				<h1>Donate to a Silo</h1>
		To finish pledging your item, please crop the image you uploaded below (Image 3):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$id?>_3.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$id?>" />
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
if ($crop == "4") {
?>
		<center>
				<h1>Donate to a Silo</h1>
		To finish pledging your item, please crop the image you uploaded below (Image 4):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$id?>_4.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="item_id" value="<?=$id?>" />
			<button type="submit" name="crop" value="Crop4">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

		<p>Please enter your item details below, and upload up to 4 images for your item.</p>
		<form enctype="multipart/form-data"  name="sell_on_siloz" class="my_account_form" method="POST">
			<input type="hidden" name="task" value="sell_on_siloz"/>
			<input type="hidden" name="address" value="<?php echo $user->address;?>"/>					
			<input type="hidden" name="zip" value="<?php echo $user->zip_code;?>"/>
			<table width="80%" cellpadding="10px" align="center">
				<tr>
					<td colspan="2" align="center">
						<?php
							if (strlen($err) > 0) {
								echo "<font color='red'><b>".$err."</b></font>";
							}
						?>						
					</td>
				</tr>		
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td><b>Listing Title</b> </td>
								<td><input type="text" name="title" style="width : 300px" value='<?php echo $title; ?>'/></td>
							</tr>
							<tr>
								<td><b>Seller Availability</b> </td>
								<td><input type="text" name="avail" style="width : 300px" value='<?php echo $avail; ?>'/></td>
							</tr>
							<tr>
								<td><b>Price</b> </td>
								<td><input type="text" name="price" style="width : 100px" value='<?php echo $price; ?>'/></td>
							</tr>
							<tr>
								<td><b>Category</b> </td>
								<td>
									<select name="item_cat_id" style="width: 300px">
										<?php
											$sql = "SELECT * FROM item_categories";
											$res = mysql_query($sql);
											while ($ic = mysql_fetch_array($res)) {
												if ($ic['item_cat_id'] == $item_cat_id) {
													echo "<option value='".$ic['item_cat_id']."' selected>".$ic['category']."</option>";
												}
												else {
													if ($item_cat_id == 0 && $ic['category'] == 'Everything (default)')
														echo "<option value='".$ic['item_cat_id']."' selected>".$ic['category']."</option>";
													else
														echo "<option value='".$ic['item_cat_id']."'>".$ic['category']."</option>";
												}
											}							
										?>							
									</select>
								</td>
							</tr>
							<tr>
								<td><b>Description</b> </td>
								<td><textarea name="description" style="width: 300px; height: 50px; resize: none;"><?php echo $description; ?></textarea></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td><b>Photo </b> </td>
								<td><input name="item_photo_1" type="file" style="width: 200px; height: 24px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo </b> </td>
								<td><input name="item_photo_2" type="file" style="width: 200px;height: 24px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo </b> </td>
								<td><input name="item_photo_3" type="file" style="width: 200px;height: 24px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo </b> </td>
								<td><input name="item_photo_4" type="file" style="width: 200px;height: 24px;"/></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Please enter the e-mail address of the person you want to start this silo below. They will be sent an e-mail with information about how to create an account and begin their silo. Once they finish the whole process, your item will be the first to be pledged towards that silo!
					</td>
				</tr>
				<tr>
					<td align="center"><b>Admininistator's E-mail Address: </b> <input name="admin_email" type="text" style="width: 300px;" value="<?=$admin_email?>" /></td>
				</tr>
				<tr>
					<td align="center">
						<button type="submit" name="submit" value="Finish">Finish</button>
					</td>
				</tr>
			</table>
		</form>
<?php
}
?>

</span>


<script type="text/javascript">
	$("#famIndex_75").click(function () {
		$('#famIndex_76, #famIndex_77, #famIndex_78').removeClass('buttonFamIndexSel').addClass('buttonFamIndex');
		$(this).addClass('buttonFamIndexSel');
		$('#famIndex').val('75');
	});

	$("#famIndex_76").click(function () {
		$('#famIndex_75, #famIndex_77, #famIndex_78').removeClass('buttonFamIndexSel').addClass('buttonFamIndex');
		$(this).addClass('buttonFamIndexSel');
		$('#famIndex').val('76');
	});

	$("#famIndex_77").click(function () {
		$('#famIndex_75, #famIndex_76, #famIndex_78').removeClass('buttonFamIndexSel').addClass('buttonFamIndex');
		$(this).addClass('buttonFamIndexSel');
		$('#famIndex').val('77');
	});

	$("#famIndex_78").click(function () {
		$('#famIndex_75, #famIndex_76, #famIndex_77').removeClass('buttonFamIndexSel').addClass('buttonFamIndex');
		$(this).addClass('buttonFamIndexSel');
		$('#famIndex').val('78');
	});
</script>