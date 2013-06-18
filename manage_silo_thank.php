<?php

	if (param_post('thank') == 'send') {
		$posted = "true";
		$id = param_post('id');
		$silo_id = param_post('silo_id');
		for ($i=0; $i<count($_FILES['thank_photo']['name']); $i++) {
			if (!empty($_FILES['thank_photo']['name'][$i])) {
				$totalFiles = $totalFiles + 1;
			}
		}
		$comments = mysql_real_escape_string(param_post('thank_comments'));
		echo $totalFiles;
	if ($totalFiles > 4) { $err = "You can only upload 4 images."; }
	elseif (!$totalFiles) { $err = "Please upload at least one photo."; }
	elseif (strlen($comments) > 250) { $err = "Please limit your silo comments to 250 characters."; }
	else {
		$allowedExts = array("png", "jpg", "jpeg", "gif");
		mkdir("uploads/thank-you/".$id, 0777, true);
		for ($i=1; $i<=$totalFiles; $i++) {
				$filesize = $_FILES['thank_photo']['size'][$i];
				if ($filesize > 2097152) {
					$err = "Image file ".$i." is too large. Please scale it down.";
				}
				elseif ($_FILES['thank_photo']['name'][$i] != '') {
					$ext = end(explode('.', strtolower($_FILES['thank_photo']['name'][$i])));
					if (!in_array($ext, $allowedExts)) {
						$err = $_FILES['thank_photo']['name'][$i]." is invalid file type.<br/>";
					}
					else {
						$filename = $_FILES['thank_photo']['name'][$i];
						$temporary_name = $_FILES['thank_photo']['tmp_name'][$i];
						$mimetype = $_FILES['thank_photo']['type'][$i];
						$photo = "photo_".$i;
						$$photo = "true";

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

							$name = "uploads/thank-you/".$id."_".$i.".jpg";
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

	if (strlen($err) == 0) {
		mysql_query("INSERT INTO silo_thank (silo_id) VALUES ('$silo_id')");

		$silo = mysql_fetch_array(mysql_query("SELECT id, name FROM silos WHERE silo_id = '$silo_id'"));
		$getUser = mysql_query("SELECT user_id FROM silo_membership WHERE silo_id = '$silo_id' AND removed_date = 0");
		while ($user = mysql_fetch_array($getUser)) {
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$user[0]'"));

			$subject = "You have a personal thanks from your silo's administrator!";
			$message = "<h3>Thank you for helping the silo titled <a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo['id']."'><b>".$silo['name']."</b></a>!</h3>";
			$message .= "Your silo's administrator has been paid, and provided an opportunity to thank you, a member of this silo, with a brief message and uploaded photos, completing the lifecycle of this silo, which would not have been possible without you!<br><br>";
			$message .= "<a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo['id']."'>Click here to view the silo administrator’s thank you message and any uploaded photos.</a><br><br>";
			$message .= "Thank You,<br><br>".SITE_NAME." Staff";

			email_with_template($email[0], $subject, $message);
		}

		mysql_query("UPDATE silo_thank SET comments = '".$comments."' WHERE silo_id = '$silo_id'");
		mysql_query("UPDATE silos SET thanked = 1 WHERE silo_id = '$silo_id'");

		$success = "true"; 
	}
	}
	}

	if (param_post('crop') == 'Crop1') {
		$posted = "true";
		$id = trim(param_post('id'));
		$silo_id = trim(param_post('silo_id'));
		$targ_w = 600;
		$targ_h = 450;
		$jpeg_quality = 90;

		$src = 'uploads/thank-you/'.$id.'_1.jpg';
		$name = 'uploads/thank-you/'.$id.'/'.$id.'_1.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE silo_thank SET photo_1 = '".$id."_1.jpg' WHERE silo_id = '$silo_id'");

		if ($_POST['upload2']) { $crop = "2"; } else { $posted = ""; }
		if ($_POST['upload3']) { $upl3 = "3"; }
		if ($_POST['upload4']) { $upl4 = "4"; }

	}
	elseif (param_post('crop') == 'Crop2') {
		$posted = "true";
		$id = trim(param_post('id'));
		$silo_id = trim(param_post('silo_id'));
		$targ_w = 600;
		$targ_h = 450;
		$jpeg_quality = 90;

		$src = 'uploads/thank-you/'.$id.'_2.jpg';
		$name = 'uploads/thank-you/'.$id.'/'.$id.'_2.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE silo_thank SET photo_2 = '".$id."_2.jpg' WHERE silo_id = '$silo_id'");

		if ($_POST['upload3']) { $crop = "3"; } else { $posted = ""; }
		if ($_POST['upload4']) { $upl4 = "4"; }

	}
	elseif (param_post('crop') == 'Crop3') {
		$posted = "true";
		$id = trim(param_post('id'));
		$silo_id = trim(param_post('silo_id'));
		$targ_w = 600;
		$targ_h = 450;
		$jpeg_quality = 90;

		$src = 'uploads/thank-you/'.$id.'_3.jpg';
		$name = 'uploads/thank-you/'.$id.'/'.$id.'_3.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE silo_thank SET photo_3 = '".$id."_3.jpg' WHERE silo_id = '$silo_id'");

		if ($_POST['upload4']) { $crop = "4"; } else { $posted = ""; }
	}
	elseif (param_post('crop') == 'Crop4') {
		$posted = "";
		$id = trim(param_post('id'));
		$silo_id = trim(param_post('silo_id'));
		$targ_w = 600;
		$targ_h = 450;
		$jpeg_quality = 90;

		$src = 'uploads/thank-you/'.$id.'_4.jpg';
		$name = 'uploads/thank-you/'.$id.'/'.$id.'_4.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE silo_thank SET photo_4 = '".$id."_4.jpg' WHERE silo_id = '$silo_id'");
	}

	$Silo = new Silo();
	$silo_id = $Silo->GetUserSiloId($_SESSION['user_id']);
	$Silo->Populate($silo_id);
		
	if ($_SESSION['is_logged_in'] != 1) {
		echo "<script>window.location = 'index.php';</script>";
	}	
		$admin_id = $_SESSION['user_id'];	
		$admin = new User($admin_id);
		$silo = $admin->getCurrentSilo();

	$user_id = $_SESSION['user_id'];

	$id = mysql_fetch_row(mysql_query("SELECT id FROM silos WHERE admin_id = '$user_id'"));

		$Silo = new Silo($id[0]);
		$silo_id = $Silo->silo_id;
		$silo_status = $Silo->status;

		if ($silo_status == "active") {
			echo "<script>window.location = 'index.php?task=manage_silo';</script>";
		}

		if (!$posted && ($Silo->thanked == 1 || $Silo->paid == "no")) {
			echo "<script>window.location = 'index.php?task=manage_silo_admin';</script>";
		}

		$today = date('Y-m-d')."";
		$silo_ended = $Silo->end_date < $today;
		$admin = $Silo->admin;

		$admin_id = $_SESSION['user_id'];
		$admin = new User($admin_id);
		$silo = $admin->getCurrentSilo();
?>

<div class="headingPad"></div>

<div class="siloHeading_manage">
	<table width="100%" style="border-spacing: 0px;">
		<tr>
			<td>
				<?php echo $Silo->getShortTitle(45); ?>
			</td>
			<td width="450px" style="font-size: 10pt; font-weight: bold" align="right">
				<a href="index.php?task=manage_silo_thank" class="<?php if (param_get('task') == 'manage_silo_thank') { echo "orange"; } else { echo "blue"; } ?>">thank members</a>
				<span style="padding: 0 5px;">|</span>
				<a href="index.php?task=manage_silo_admin" class="<?php if (param_get('task') == 'manage_silo_admin') { echo "orange"; } else { echo "blue"; } ?>">view statistics</a>
			</td>
		</tr>
	</table>
</div>

<div class="headingPad"></div>

<div class="greyFont">

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

$(document).keypress(function(e) {
  if(e.which == 13) {
    $("#cropSubmit").click();
  }
});
		</script>

<?php
if ($success) {
?>
		<center>
				<h1>Thank the members</h1>
		To finish thanking your members, please crop all of the images you uploaded below (Image 1):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/thank-you/<?=$silo->id?>_1.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" id="thankForm" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="id" value="<?=$Silo->id?>" />
			<input type="hidden" name="silo_id" value="<?=$Silo->silo_id?>" />

		<?php if ($photo_2) { echo '<input type="hidden" name="upload2" value="2" />'; } ?>
		<?php if ($photo_3) { echo '<input type="hidden" name="upload3" value="3" />'; } ?>
		<?php if ($photo_4) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>

			<button type="submit" name="crop" id="cropSubmit" value="Crop1">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
?>

<?php
if ($crop == "2") {
?>
		<center>
				<h1>Thank the members</h1>
		To finish thanking your members, please crop all of the images you uploaded below (Image 2):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/thank-you/<?=$Silo->id?>_2.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" id="thankForm" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="id" value="<?=$Silo->id?>" />
			<input type="hidden" name="silo_id" value="<?=$Silo->silo_id?>" />
		<?php if ($upl3) { echo '<input type="hidden" name="upload3" value="3" />'; } ?>
		<?php if ($upl4) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>
			<button type="submit" name="crop" id="cropSubmit" value="Crop2">Crop</button>
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
				<h1>Thank the members</h1>
		To finish thanking your members, please crop all of the images you uploaded below (Image 3):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/thank-you/<?=$Silo->id?>_3.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" id="thankForm" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="id" value="<?=$Silo->id?>" />
			<input type="hidden" name="silo_id" value="<?=$Silo->silo_id?>" />
		<?php if ($upl4) { echo '<input type="hidden" name="upload4" value="4" />'; } ?>
			<button type="submit" name="crop" id="cropSubmit" value="Crop3">Crop</button>
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
				<h1>Thank the members</h1>
		To finish thanking your members, please crop all of the images you uploaded below (Image 4):<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/thank-you/<?=$Silo->id?>_4.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" id="thankForm" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="id" value="<?=$Silo->id?>" />
			<input type="hidden" name="silo_id" value="<?=$Silo->silo_id?>" />
			<button type="submit" name="crop" id="cropSubmit" value="Crop4">Crop</button>
		</form>
		</center>
		<br><br>
<?php
die;
}
	$siloInfo = mysql_fetch_array(mysql_query("SELECT id, admin_id, collected, end_date FROM silos WHERE silo_id = '$silo_id'"));
	$siloAdmin = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$siloInfo[admin_id]'"));
	$siloThank = mysql_fetch_array(mysql_query("SELECT * FROM silo_thank WHERE silo_id = '$silo_id'"));
	$end = strtotime($siloInfo['end_date']); $ended_date = date("F jS, Y", $end);
	$thanked_date = date("F jS, Y");
?>

<div class="thank-heading">
	<h3 align="center">Congratulations!</h3> 
	Your silo has ended, and, as the silo administrator, you have been issued a payment. <br><br>
	It's time to thank those who pledged and sold items, and to validate your expenditure of the money they raised for your silo. Please upload PHOTOS of goods and services your silo's funds paid for. When complete, select, 'Notify Members', to send an email to all of the members in the silo.<br><br>
	<span style="font-weight: normal;">Silo info shown on thank you page:</span> <br> <blockquote> This silo raised $<?=$siloInfo['collected']?>, and ended on <?=$ended_date?>. The silo administrator, <span class="blue"><?=$siloAdmin['fname']?> <?=$siloAdmin['lname']?></span>, started the 'Thank You' phase of this silo on <?=$thanked_date?>. Thanks to all who pledged items and donated funds!</blockquote>
	<span style="font-weight: normal; font-size: 9pt;">**Please Note: Only .gif, .jpg, .jpeg, and .png image file extentions are allowed.</span><br><br>
	<center>Upload up to 4 photos below:</center>
</div>


<?php if($err) { ?>
	<div align="center" style="font-size: 10pt; color: red; font-weight: bold;">
		<?=$err?><br><br>
	</div>
<?php }?>


<form enctype="multipart/form-data" name="thank_members" id="thankForm" method="POST">
<table width="595px" align="center" style="font-size: 10pt; font-weight: bold;">
<input name="id" value="<?=$Silo->id?>" type="hidden">
<input name="silo_id" value="<?=$Silo->silo_id?>" type="hidden">
<center><div id="morePhotos"></div></center>
<tr>
	<td align="center">
		<table class="thank-photo"><tr>
			<td width="200px">Silo photo:</td>
			<td><input id="thankPhoto" name="thank_photo[]" type="file" style="height: 24px" /></td>
		</tr></table>
	</td>
</tr>
<tr>
	<td align="center">
		<table class="thank-photo"><tr>
			<td width="200px">Additional silo comments:</td>
			<td><textarea name="thank_comments" value="" /></textarea></td>
		</tr></table>
	</td>
</tr>
<tr>
	<td align="center" style="padding-top: 10px;">
		<button type="submit" name="thank" value="send">Notify Members</button>
	</td>
</tr>
</table>
</div>

<script>
var i = 1;
$('#thankPhoto').live('change', function() {
    	i++;
	if (i < 5) {
    	  $('#morePhotos').prepend('<tr><td align="center"><table class="thank-photo"><tr><td width="200px" style="font-size: 10pt; font-weight: bold;">Silo photo:</td><td><input id="thankPhoto" name="thank_photo[]" type="file" style="height: 24px" /></td></tr></table></td></tr>');
	}
});

</script>

</form>