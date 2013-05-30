<?php

	if (param_post('thank') == 'send') {
		$id = param_post('id');
		$silo_id = param_post('silo_id');
		$allowedExts = array("png", "jpg", "jpeg", "gif");
		for ($i=0; $i<count($_FILES['thank_photo']['name']); $i++) {

			if ($_FILES['thank_photo']['name'][$i] != '') {
				$filename = $_FILES['thank_photo']['name'][$i];
				$filename = str_replace(' ', '_', $filename);
   				$filename_array[] = $filename;
				$temporary_name = $_FILES['thank_photo']['tmp_name'][$i];
				$target = "uploads/thank-you/".$id."/".$filename;

				mkdir("uploads/thank-you/".$id, 0777, true);
				move_uploaded_file($temporary_name, $target);
			}
		}

		$silo = mysql_fetch_array(mysql_query("SELECT name FROM silos WHERE silo_id = '$silo_id'"));
		$getUser = mysql_query("SELECT user_id FROM silo_membership WHERE silo_id = '$silo_id' AND removed_date = 0");
		while ($user = mysql_fetch_array($getUser)) {
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$user[0]'"));

			$subject = "Thank you for helping!";
			$message = "<h3>Thank you for helping the silo titled <b>".$silo['name']."</b>!</h3>";
			$message .= "We are proud to inform you that you have made a difference through ".SHORT_URL.". This silo, which you had pledged an item to, has been fully paid out. The administrator has obtained the earnings and they have used the money to help their organization.<br><br>";
			$message .= "This notification is sent out to every user who has pledged or sold an item to this silo. The silo administrator is given the option, and is encouraged, to upload files (receipts, images, documents, etc.) to show the people that pledged an item what they have helped accomplished. All of the files that the silo administrator has uploaded can be viewed as an attachment in this e-mail.<br><br>";
			$message .= "If you have any questions about this silo, please contact ".SITE_NAME." or the silo administator to address your concern.<br><br>";
			$message .= "Thank you for helping this silo and we hope you will pledge some more items on ".SITE_NAME." soon! Every item counts!";

			email_with_attachment($email[0], $subject, $message, $id, $filename_array);

			mysql_query("UPDATE silos SET thanked = 1 WHERE silo_id = '$silo_id'");
		}
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

		if ($Silo->thanked == 1 || $Silo->paid == "no") {
			echo "<script>window.location = 'index.php?task=manage_silo_admin';</script>";
		}

		$today = date('Y-m-d')."";
		$silo_ended = $Silo->end_date < $today;
		$admin = $Silo->admin;

		$err = "";
		$admin_id = $_SESSION['user_id'];
		$admin = new User($admin_id);
		$silo = $admin->getCurrentSilo();
?>

<div class="headingPad"></div>

<div class="siloHeading_manage">
	<table width="100%" style="border-spacing: 0px;">
		<tr>
			<td>
				<?php echo $silo->getTitle(); ?>
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

<div class="thank-heading">
	<h3 align="center">Congratulations!</h3> 
	Your silo has ended, and, as the silo administrator, you have been issued a payment. <br><br>
	It's time to thank those who pledged and sold items, and to validate your expenditure of the money they raised for your silo. Please upload .pdf scans of DOCUMENTS (receipts, purchase orders, shipping orders, etc.) and PHOTOS of goods and services your silo's funds paid for. When complete, select, 'Notify Members', to send an email to all of the members in the silo.<br><br>
	<center>Upload as many photos as you would like to below:</center>
</div>

<form enctype="multipart/form-data" name="thank_members" id="thankForm" method="POST">
<table width="595px" align="center" style="font-size: 10pt; font-weight: bold;">
<input name="id" value="<?=$Silo->id?>" type="hidden">
<input name="silo_id" value="<?=$Silo->silo_id?>" type="hidden">
<center><div id="morePhotos"></div></center>
<tr>
	<td align="center">
		<div class="thank-photo">
			<input name="thank_photo[]" type="file" style="height: 24px"/>
		</div>
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
$('.thank-photo').live('change', function() {
    $('#morePhotos').prepend('<tr><td align="center"><div class="thank-photo"><input name="thank_photo[]" type="file" style="height: 24px"/></div></td></tr>');
});
</script>

</form>