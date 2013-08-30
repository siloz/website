<?php
$updNotif = mysql_query("DELETE FROM notifications WHERE user_id = '$user_id'");

if ($_SESSION['is_logged_in'] != 1) { ?>
	<script type="text/javascript">
		$(document).ready(function () {
			$("#login").fancybox().trigger('click');
		})
	</script>
<?php 
	die; }

//** UPDATED --> Voucher == PayKey, Voucher Key == PayLock **//

if (param_post('item') == 'Delete') {
	$item_id = param_post('item_id');
	$user_id = param_post('user_id');
	$decline = mysql_query("UPDATE items SET status = 'deleted' WHERE item_id = '$item_id' AND user_id = '$user_id'");
	$rmFeed = mysql_query("DELETE FROM feed WHERE item_id = '$item_id' AND user_id = '$user_id'");

	$updatemsg = "Item has been deleted.";
}

if (param_post('item') == 'Decline') {
	$item_id = param_post('item_id');
	$user_id = param_post('user_id');
	$decline = mysql_query("UPDATE item_purchase SET status = 'declined' WHERE item_id = '$item_id' AND user_id = '$user_id'");

	include('braintree/refunds.php');

	$updatemsg = "Item has been declined.";
}

if (param_post('item') == 'Clear') {
	$id = param_post('id');
	$clear = mysql_query("UPDATE buyer SET cleared = '1' WHERE id = '$id'");

	$updatemsg = "Item cleared from transaction console.";
}

if (param_post('item') == 'Seller Clear') {
	$user_id = param_post('user_id');
	$item_id = param_post('item_id');
	$clear = mysql_query("INSERT INTO seller_cleared (user_id, item_id) VALUES ('$user_id', '$item_id')");

	$updatemsg = "Item cleared from transaction console.";
}

if (param_post('offer') == 'Accept') {
	$item_id = param_post('item_id');
	$seller_id = param_post('seller_id');
	$accept = mysql_query("UPDATE offers SET status = 'accepted', expired_date = DATE_ADD(NOW(), INTERVAL 1 day) WHERE item_id = '$item_id' AND seller_id = '$seller_id' AND expired_date != 0");
		
		$Notification = new Notification();
		$Notification->seller_id = $seller_id;
		$Notification->item_id = $item_id;
		$Notification->type = "Accept Offer";
		$Notification->Send();

	$updatemsg = "Offer accepted!";
}

if (param_post('offer') == 'Decline') {
	$item_id = param_post('item_id');
	$seller_id = param_post('seller_id');
	$decline = mysql_query("UPDATE offers SET status = 'declined', expired_date = '0' WHERE item_id = '$item_id' AND seller_id = '$seller_id'");
	$updItem = mysql_query("UPDATE items SET status = 'pledged' WHERE item_id = '$item_id'");

	$buyer = mysql_fetch_row(mysql_query("SELECT buyer_id FROM offers WHERE item_id = '$item_id' AND seller_id = '$seller_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$buyer[0]' AND item_id = '$item_id' AND (favorite = '1' OR purchase = '1')"));
	if ($check) {
		$query = mysql_query("UPDATE buyer SET offer = '0' WHERE user_id = '$buyer[0]' AND item_id = '$item_id'");
	} else {
		$query = mysql_query("DELETE FROM buyer WHERE user_id = '$buyer[0]' AND item_id = '$item_id'");
	}

		$Notification = new Notification();
		$Notification->seller_id = $seller_id;
		$Notification->item_id = $item_id;
		$Notification->type = "Decline Offer";
		$Notification->Email();

	$updatemsg = "Offer declined";
}

if (param_post('offer') == 'Make') {
	$item_id = param_post('item_id');
	$buyer_id = param_post('buyer_id');
	$amount = param_post('amount');

		$Item = new Item();
		$Item->item_id = $item_id;
		$Item->buyer_id = $buyer_id;
		$Item->amount = $amount;
		$Item->NewOffer();

	$updatemsg = "Offer sent!";
}

if (param_post('offer') == 'Cancel') {
	$item_id = param_post('item_id');
	$buyer_id = param_post('buyer_id');
			
		$Item = new Item();
		$Item->item_id = $item_id;
		$Item->buyer_id = $buyer_id;
		$Item->RemoveOffer();

	$updatemsg = "Offer canceled";
}

if (param_post('paykey') == 'Enter') {
	$user_id = param_post('user_id');
	$item_id = param_post('item_id');
	$paylock = param_post('paylock');
	$paykey = trim(param_post('key'));
	$check = mysql_num_rows(mysql_query("SELECT * FROM item_purchase WHERE item_id = '$item_id' AND paylock = '$paylock' AND paykey = '$paykey'"));

	if ($check) {
		$updPurchase = mysql_query("UPDATE item_purchase SET status = 'sold' WHERE item_id = '$item_id' AND paykey = '$paykey'");
		$updItem = mysql_query("UPDATE items SET status = 'sold' WHERE item_id = '$item_id'");
		
			$Feed = new Feed();
			$Feed->user_id = $user_id;
			$Feed->item_id = $item_id;
			$Feed->status = "Sold";
			$Feed->Save();

			$Notification = new Notification();
			$Notification->seller_id = $user_id;
			$Notification->item_id = $item_id;
			$Notification->type = "Item Sold";
			$Notification->Send();

		$updatemsg = "Your item has been sold and your donation has been added to the silo!";
	}
	else {
		$upd = mysql_query("UPDATE item_purchase SET attempts = attempts + 1 WHERE item_id = '$item_id' AND paylock = '$paylock'");
		$updatemsg = "You have entered the wrong Voucher code.";
	}
}

if (param_post('fav') == 'Remove') {
	$user_id = param_post('user_id');
	$item_id = param_post('item_id');

		$Item = new Item();
		$Item->user_id = $user_id;
		$Item->item_id = $item_id;
		$Item->RemoveFav();

	$updatemsg = "Item removed from favorites";
}

// If item is updated
	$err = "";

	if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'on') { 
    		echo "<script>window.location = 'https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "';</script>";
    		exit();
	}
	
	if (param_post('item') == 'Update') {			
		$item_id = param_post('item_id');
		$title = param_post('title');
		$price = param_post('price');
		$item_cat_id = param_post('item_cat_id');
		$description = param_post('description');
	
		if (strlen(trim($title)) == 0) {
			$err .= "Item title must not be empty. <br/>".$item_id;
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
				$filesize = $_FILES['item_photo_'.$i]['size'];
				if ($filesize > 2097152) {
					$err .= "Image file is too large. Please scale it down.";
				}
				elseif ($_FILES['item_photo_'.$i]['name'] != '') {
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

						$name = "uploads/".$item_id."_".$i.".jpg";
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
		echo "<script>window.location = 'index.php?task=transaction_console';</script>";			
	}
	if ($icrop == "true" || $updmsg == "true") { 
		$updatemsg = "Your item has been updated!";
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

<div class="userNav">
	<table width="100%" style="border-spacing: 0px;">
		<tr><form action="">
			<td>
				<span class="accountHeading">Transaction Console</span>
			</td>
			<td align="center" width="500px">
				<?php echo "<span id='success' class='error'>".$updatemsg."</span>"; ?>
			</td>
		</form></tr>
	</table>
</div>

<div class="spacer"></div>

<span class="greyFont">

<?php
if ($success && $_FILES['item_photo_1']['name']) {
?>
		<center>
				<h1>Edit Item Image</h1>
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
				<h1>Edit Item Image</h1>
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
				<h1>Edit Item Image</h1>
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
				<h1>Edit Item Image</h1>
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

<table width="100%">
<tr><td style="padding: 5px;">
	<span class="accountHeading">Selling</span>
</td>
<td width="2%"></td>
<td style="padding: 5px;">
	<span class="accountHeading">Buying</span>
</td></tr>

<tr>
<td width="49%" valign="top">

<?php
$qry = mysql_query("SELECT * FROM items WHERE user_id = '$user_id' ORDER BY id DESC");
if (mysql_num_rows($qry)) {
while ($item = mysql_fetch_array($qry)) {
	$id_item = $item['id'];
	$item_id = $item['item_id'];
	$silo_id = $item['silo_id'];
	$title = (strlen($item['title']) > 22) ? substr($item['title'], 0, 22) . '...' : $item['title'];
	$title_edit = $item['title'];
	$price = $item['price'];
	$item_cat_id = $item['item_cat_id'];
	$last_update = strtotime($item['last_update']);
	$photo = $item['photo_file_1'];
	$status = $item['status'];
	$description = $item['description'];

	$silo = new Silo($silo_id);
	$item = new Item($item_id);
	$end_date = strtotime($silo->end_date); $end = date('g:i a F j, Y', $end_date);

	$cleared = mysql_num_rows(mysql_query("SELECT * FROM seller_cleared WHERE user_id = '$user_id' AND item_id = '$item_id'"));

	if ($cleared > 0) { continue; } else { $sellingItem = true; }

	if ($status == "pledged") { $cStatus = "Listed"; $notif = "Silo ends on ".$end; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#editItem_".$item_id."'>Edit Item</a> | <a class='fancybox' href='#delItem_".$item_id."'>Delete Item</a>"; }
	elseif ($status == "offer") {
		$offer = mysql_fetch_array(mysql_query("SELECT buyer_id, amount, status, expired_date FROM offers WHERE seller_id = '$user_id' AND item_id = '$item_id' AND status != 'canceled' ORDER BY id DESC"));
		$buyer_id = $offer['buyer_id'];
		$amt = $offer['amount'];
		$oStatus = $offer['status'];
		$exp_date = strtotime($offer['expired_date']); $exp = date('g:i a F j, Y', $exp_date);
		$user = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$buyer_id'"));
		$buyer_name = $user['fname']." ".$user['lname'];

		if ($oStatus == "pending") { $cStatus = "<span style='color: red; font-weight: bold;'>You received an offer of $".$amt." from ".$buyer_name.".</span>"; $notif = "This offer will expire on ".$exp." if you do not take any action."; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#aceeptItem_".$item_id."'>Accept $".$amt." Offer</a> | <a class='fancybox' href='#decOffer_".$item_id."'\">Decline $".$amt." Offer</a> <br> <a class='fancybox' href='#editItem_".$item_id."'>Edit Item</a>| <a class='fancybox' href='#delItem_".$item_id."'>Delete Item</a>"; }
		elseif ($oStatus == "accepted") { $cStatus = "Listed, you accepted a $".$amt." offer from ".$buyer_name."."; $notif = "Buyer has until ".$exp." to make payment, or this offer will be canceled."; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#editItem_".$item_id."'>Edit Item</a> | <a class='fancybox' href='#delItem_".$item_id."'>Delete Item</a>"; }
		elseif ($oStatus == "declined") { $cStatus = "Listed, you declined a $".$amt." offer from ".$buyer_name."."; $notif = "Silo ends on ".$end; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#editItem_".$item_id."'>Edit Item</a> | <a class='fancybox' href='#delItem_".$item_id."'>Delete Item</a>"; }
	}
	elseif ($status == "pending") {
		$buyer = mysql_fetch_array(mysql_query("SELECT fname, phone, email, address, paylock, attempts FROM users INNER JOIN item_purchase USING (user_id) WHERE item_id = '$item_id' AND item_purchase.status = 'pending'"));
		$buyerInfo = "'".$buyer['fname']."', ".$buyer['phone'].", <a href='mailto: ".$buyer['email']."'>".$buyer['email']."</a>, ".$buyer['address'];
		$paylock = $buyer['paylock'];
		$attempts = $buyer['attempts'];

		$cStatus = "Seller paid, you are awaiting the Voucher code!"; $notif = "You have until ".$exp." to enter the buyer's Voucher into the site and close the sale.<br> <b>Your Voucher Key:</b> ".$paylock." <a href='index.php?task=faq'>(instructions)</a>"; $contact = $buyerInfo; $actions = "<a class='fancybox' href='#enterPK_".$item_id."'>Enter Voucher</a>"; }
	elseif ($status == "requested") { $cStatus = "Silo requested"; $notif = "Awaiting silo creation from administrator"; $contact = "Pending silo administator"; $actions = "<a class='fancybox' href='#editItem_".$item_id."'>Edit Item</a> | <a class='fancybox' href='#delItem_".$item_id."'>Delete Item</a>"; }
	elseif ($status == "sold") { $cStatus = "<b>Sold!</b>"; $notif = "This sale was tax-deductible for you! Check your email!"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#seller_clearItem_".$item_id."'>Clear Item</a>"; }
	elseif ($status == "inert") { $cStatus = "Inert"; $notif = "Your item is no longer being listed because the silo has expired."; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#seller_clearItem_".$item_id."'>Clear Item</a>"; }
	elseif ($status == "deleted") { $cStatus = "Item deleted"; $notif = "Your item has been deleted."; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#seller_clearItem_".$item_id."'>Clear Item</a>"; }
	elseif ($status == "flagged") { $cStatus = "Flagged"; $notif = "Your item has been flagged and is no longer listed on the site"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#seller_clearItem_".$item_id."'>Clear Item</a>"; }
?>
<div class="plateTConsoleSell">
	<table width="100%" height="100%">
	<tr valign="top">
		<td valign="top" width="40%" rowspan="2">
			<div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_item&id=<?=$id_item?>'>
			<img src=uploads/items/<?=$photo?>?<?=$last_update?>>
			<div style='padding-bottom: 0px;'><?=$title?></div>  <span class='blue'>$<?=$price?></span>
			</a></div>
			<span class="notBold" style="font-size: 9pt;"><a href="index.php?task=view_silo&id=<?=$silo->id?>">Silo: <?=$silo->getTitle()?></a></span>
		</td>
		<td>
			Status: <span class="greyFont"><?=$cStatus?></span><br>
			Notifications: <span class="greyFont"><?=$notif?></span><br>
			Other Party Contact: <span class="greyFont"><?=$contact?></span><br>
<br><br>
			<span class="greyFont"><b>Actions:</b></span><br>
			<?=$actions?>
			<div class="headingPad"></div>
			<a class='fancybox' href='#craigslist_<?=$item_id?>'><div style='font-size: 10pt;'>Copy/Paste code to list on Craigslist!</div></a>
		</td>
	</tr>
	</table>
</div>

<?php
	include ("include/UI/transaction_console.php");
	}
}

if (!mysql_num_rows($qry) || $sellingItem != "true") { echo "There is currently no selling activity for your account."; }
?>

</td>

<td width="2%"></td>

<td width="49%" valign="top">

<?php
$qry = mysql_query("SELECT *, buyer.id AS buyer_id FROM buyer INNER JOIN items USING (item_id) WHERE buyer.user_id = '$user_id' AND buyer.cleared = '0' ORDER BY date DESC");
if (mysql_num_rows($qry)) {
while ($item = mysql_fetch_array($qry)) {
	$id = $item['buyer_id'];
	$item_link = $item['id'];
	$id_item = $item['id'];
	$item_id = $item['item_id'];
	$silo_id = $item['silo_id'];
	$title = (strlen($item['title']) > 22) ? substr($item['title'], 0, 22) . '...' : $item['title'];

	$offerUser = mysql_fetch_array(mysql_query("SELECT status, amount FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item_id'"));
	$offerStatus = $offerUser['status'];
	$offerAmount = $offerUser['amount'];
	if ($offerStatus == 'accepted') { $price = $offerAmount; } else { $price = $item['price']; }

	$photo = $item['photo_file_1'];

	$pur = $item['purchase'];
	$offer = $item['offer'];

	$silo = new Silo($silo_id);

	if ($pur) {
		$purchase = mysql_fetch_array(mysql_query("SELECT amount, status, paykey, expired_date FROM item_purchase WHERE user_id = '$user_id' AND item_id = '$item_id'"));
		$amt = $purchase['amount'];
		$status = $purchase['status'];
		$paykey = $purchase['paykey'];
		$exp_date = strtotime($purchase['expired_date']); $exp = date('g:i a F j, Y', $exp_date);

		$seller = mysql_fetch_array(mysql_query("SELECT fname, phone, email, address FROM users INNER JOIN items USING (user_id) WHERE item_id = '$item_id'"));
		$sellerInfo = "'".$seller['fname']."', ".$seller['phone'].", <a href='mailto: ".$seller['email']."'>".$seller['email']."</a>, ".$seller['address'];

		if ($status == "pending") { $cStatus = "You made payment and have an option to buy this item."; $notif = "You and the seller have until ".$exp." for the seller to enter your Voucher code into the site. <b>Your Voucher code is '".$paykey."'. Don't provide a false Voucher code or share unless you collect your item."; $contact = $sellerInfo; $actions = "<a class='fancybox' href='#decItem_".$item_id."'>Decline Item</a>"; }
		elseif ($status == "sold") { $cStatus = "You bought this item for $".$amt."."; $notif = "No notifications"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#clearItem_".$item_id."'>Clear Item</a>"; }
		elseif ($status == "declined") { $cStatus = "You declined this item."; $notif = "No notifications"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#clearItem_".$item_id."'>Clear Item</a> | <a class='fancybox' href='#cancelOffer_".$item_id."'>Cancel Offer</a>"; }
	}
	elseif ($offer) {
		$off = mysql_fetch_array(mysql_query("SELECT status, amount, expired_date FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item_id'"));
		$status = $off['status'];
		$amount = $off['amount'];
		$exp_date = strtotime($off['expired_date']); $exp = date('g:i a F j, Y', $exp_date);

		if ($status == "pending") { $cStatus = "You made an offer of $".$amount."."; $notif = "Seller has until ".$exp." to accept this offer or it will expire."; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#buy-notif_".$item_id."'>Buy</a> | <a class='fancybox' href='#cancelOffer_".$item_id."'>Cancel Offer</a>"; }
		elseif ($status == "accepted") { $cStatus = "Your offer of $".$amount." was accepted!"; $notif = "<span style='color: red; font-weight: bold;'>You have until ".$exp." to buy this item at the offered price, or this sale will cancel.</span>"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#buy-notif_".$item_id."'>Buy</a> | <a class='fancybox' href='#cancelOffer_".$item_id."'>Cancel Offer</a>"; }
		elseif ($status == "declined") { $cStatus = "Watching (your $".$amount." offer was declined)"; $notif = "No notifications"; $contact = "No other party at this time"; $actions = "<a class='fancybox' href='#buy-notif_".$item_id."'>Buy</a> | <a class='fancybox' href='#rmFav_".$item_id."'>Clear Item</a>"; }
	} 
	else { $cStatus = "Watching"; $notif = "No notifications"; $contact = "No other party at this time";
		if ($addInfo_full) {
			$actions = "<a class='fancybox' href='#addInfo_".$item_id."'>Buy</a> | <a class='fancybox' href='#rmFav_".$item_id."'>Clear Item</a> | <a class='fancybox' href='#addInfo_offer_".$item_id."'>Make Offer</a>";
		} else {
			$actions = "<a class='fancybox' href='#buy-notif_".$item_id."'>Buy</a> | <a class='fancybox' href='#rmFav_".$item_id."'>Clear Item</a> | <a class='fancybox' href='#mkOffer_".$item_id."'>Make Offer</a>"; 
		}
	}
?>
<div class="plateTConsoleBuy">
	<table width="100%" height="100%">
	<tr valign="top">
		<td valign="top" width="40%" rowspan="2">
			<div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_item&id=<?=$id_item?>'>
			<img src=uploads/items/<?=$photo?>?<?=$last_update?>>
			<div style='padding-bottom: 0px;'><?=$title?></div>  <span class='blue'>$<?=$price?></span>
			</a></div>
			<span class="notBold" style="font-size: 9pt;"><a href="index.php?task=view_silo&id=<?=$silo->id?>">Silo: <?=$silo->getTitle()?></a></span>
		</td>
		<td>
			Status: <span class="greyFont"><?=$cStatus?></span><br>
			Notifications: <span class="greyFont"><?=$notif?></span><br>
			Other Party Contact: <span class="greyFont"><?=$contact?></span><br>
		</td>
	</tr>
	<tr>
		<td valign="middle">
			<span class="greyFont"><b>Actions:</b></span><br>
			<?=$actions?>
		</td>
	</tr>
	</table>
</div>

<?php
	include ("include/UI/transaction_console.php");
} 
} else { echo "There is currently no buying activity for your account."; }
?>

</td>

</tr>
</table>
</div>

</td>
</tr>
</table>

</span>

<br>

<script>
      $(document).ready( function() {
        $('#notification').delay(1000).fadeOut();
      });
</script>