<?php
	$item_id = param_get('id');

	require("include/autoload.class.php");
	if ($item_id == '') {
		echo "<script>window.location = 'index.php';</script>";			
	}
	else {
		$item = new Item($item_id);
		$item->Update();
		$seller = $item->owner;
		$silo = $item->silo;
		$item = new Item($item_id);

		$silo_id = urlencode($item->silo_id);
		$item_long = urlencode($item->longitude);
		$item_lat = urlencode($item->latitude);
		$user_long = urlencode($user['longitude']);
		$user_lat = urlencode($user['latitude']);
		$distBuyerSeller = $item->getDistance($item_long, $item_lat, $user_long, $user_lat);
		$checkClosed = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE silo_id = '$silo_id' AND status != 'active'"));
		if ($checkClosed > 0) { echo "<script>window.location = 'index.php?task=view_silo&id=".$silo->id."';</script>"; }
		if ($item->status == "deleted" || $item->status == "flagged") { echo "<script>window.location = 'index.php';</script>"; }

	if (param_post('fav') == 'add to favorites') {
		$user_id = param_post('user_id');
		$item_id = param_post('item_id');

			$Item = new Item();
			$Item->user_id = $user_id;
			$Item->item_id = $item_id;
			$Item->AddFav();
	}

	if (param_post('fav') == 'remove from favorites') {
		$user_id = param_post('user_id');
		$item_id = param_post('item_id');

			$Item = new Item();
			$Item->user_id = $user_id;
			$Item->item_id = $item_id;
			$Item->RemoveFav();
	}

	if (param_post('offer') == 'send offer') {
		$item_id = param_post('item_id');
		$buyer_id = param_post('buyer_id');
		$seller_id = param_post('seller_id');
		$amount = param_post('amount');

			$Item = new Item();
			$Item->item_id = $item_id;
			$Item->buyer_id = $buyer_id;
			$Item->seller_id = $seller_id;
			$Item->amount = $amount;
			$Item->NewOffer();
	}

	if (param_post('offer') == 'cancel') {
		$item_id = param_post('item_id');
		$buyer_id = param_post('buyer_id');
		$seller_id = param_post('seller_id');

			$Item = new Item();
			$Item->item_id = $item_id;
			$Item->buyer_id = $buyer_id;
			$Item->seller_id = $seller_id;
			$Item->RemoveOffer();
	}

	$user_id = $_SESSION['user_id'];
	$isSeller = mysql_num_rows(mysql_query("SELECT * FROM items WHERE user_id = '$user_id' AND item_id = '$item->item_id'"));
	$isAdmin = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = '$user_id' AND silo_id = '$silo->silo_id'"));

	$fav = mysql_num_rows(mysql_query("SELECT * FROM favorites WHERE user_id = '$user_id' AND item_id = '$item->item_id'"));
	$itemOffer = mysql_num_rows(mysql_query("SELECT * FROM items WHERE item_id = '$item->item_id' AND status = 'offer'"));
	$itemFlagged = mysql_num_rows(mysql_query("SELECT * FROM flag_item WHERE user_id = '$user_id' AND item_id = '$item->item_id'"));

	$offerUser = mysql_fetch_array(mysql_query("SELECT status, amount FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id'"));
	$offerStatus = $offerUser['status'];
	$offerAmount = $offerUser['amount'];

	$offerItem = mysql_fetch_array(mysql_query("SELECT status, expired_date FROM offers WHERE item_id = '$item->item_id' ORDER BY id DESC"));
	$statusO = $offerItem[0];
	$expO = strtotime($offerItem[1]); $offerExp = date('g:i a F j, Y', $expO);

	if ($offerStatus == 'accepted') { $price = $offerAmount; } else { $price = $item->price; }

	if (param_post('item') == 'Update') {			
		$id_item = param_post('item_id');
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

						$name = "uploads/".$id_item."_".$i.".jpg";
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
			$sql = mysql_query("UPDATE items SET title = '$title', price = '$price', item_cat_id = '$item_cat_id', description = '$description' WHERE id = '$item->id'");

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
		echo "<script>window.location = 'index.php?task=view_item&id=".$item->id."';</script>";			
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

<div class="login" id="offer" style="width: 300px;">
	<div id="offer_drag" style="float:right">
		<img id="offer_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item->item_id?>">
			<input type="hidden" name="buyer_id" value="<?=$user_id?>">
			<input type="hidden" name="seller_id" value="<?=$item->user_id?>">
			<h2>Enter your offer below:</h2>
			$<input onclick=this.value="" type="text" value="0.00" name="amount">
			<br/><br>	
			<button type="submit" name="offer" value="send offer">Send offer</button>
		</form>
	</div>
</div>

<div class="login" id="offerp" style="width: 300px;">
	<div id="offerp_drag" style="float:right">
		<img id="offerp_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item->item_id?>">
			<input type="hidden" name="buyer_id" value="<?=$user_id?>">
			<input type="hidden" name="seller_id" value="<?=$item->user_id?>">
			<h2><font color="FF642F">Offer: $<?=$offerAmount?></font></h2>
			<h2>You are only allowed one offer per item. Are you sure you want to cancel it?</h2>
			<br/><br>	
			<button type="submit" name="offer" value="cancel">Yes, cancel my offer</button>
		</form>
	</div>
</div>

<div class="login" id="ioffer" style="width: 300px;">
	<div id="ioffer_drag" style="float:right">
		<img id="ioffer_exit" src="images/close.png"/>
	</div>
	<div>
		<?php if ($statusO == "pending") { ?>
		<h2>Another user has already sent an offer for this item. If the seller doesn't take any action, it will expire on <?=$offerExp?>. <br><br>
		You can still purchase this item for the original asking price at any time.</h2>
		<?php } elseif ($statusO == "accepted") { ?>
		<h2>Another user has made an offer and the seller has accepted it. No more offers will be able to be made for this item. <br><br>
		You can still purchase this item at the seller's original asking price. Buy it soon!</h2>
		<?php } ?>
	</div>
</div>

<div class="login" id="dist" style="width: 300px;">
	<div id="dist_drag" style="float:right">
		<img id="dist_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>You are too far away from the seller. Please find an item closer to your current location.</h2>
	</div>
</div>

<div class="login" id="flagged" style="width: 300px;">
	<div id="flagged_drag" style="float:right">
		<img id="flagged_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>You have flagged this item already. You can only flag each item once.</h2>
	</div>
</div>

<div class="login" id="closed_silo" style="width: 300px;">
	<div id="closed_silo_drag" style="float:right">
		<img id="closed_silo_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>This function has been disabled because the silo is no longer active.</h2>
		<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('closed_silo').style.display='none';">Okay</button>
	</div>
</div>

<div class="contact_seller" id="contact_admin">
	<div id="contact_admin_drag" style="float: right">
		<img id="contact_admin_exit" src="images/close.png"/>
	</div>
	<div>
		<form name="contact_admin_form" id="contact_admin_form" method="POST">
			<h2>Contact Admin</h2>
			<p>Silo <b><?php echo $silo->name; ?></b></p>
			<div id="contact_admin_status"></div>			
			<table>
				<tr>
					<td valign="top">
						<b>Email</b>
					</td>
					<td>
						<input type="text" name="contact_email" id="contact_email" onfocus="select();" style="width:300px;" 
						value=<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?> >
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Subject</b>
					</td>
					<td>
						<input type="text" name="contact_subject" id="contact_subject" onfocus="select();" style="width:300px;"/>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Inquiry<br/>/Offer</b>
					</td>
					<td>
						<textarea style='width: 300px; height: 200px' name="inquiry" id="inquiry"></textarea>
					</td>
				</tr>
			</table>
			<br/>			
			<button type="button" id="contact_admin_button">Send</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('contact_admin').style.display='none';">Cancel</button>
		</form>
		<script>
			$("#contact_admin_button").click(function(event) {	
				document.getElementById('overlay').style.display='none';
				document.getElementById('contact_admin').style.display='none';
				$.post(<?php echo "'".API_URL."'"; ?>, 
					{	
						request: 'email_silo_admin',
						silo_id: <?php echo $silo->silo_id; ?>,
						email: document.getElementById('contact_email').value,
						subject: document.getElementById('contact_subject').value,
						content: document.getElementById('inquiry').value
					}, 
					function (xml) {
						$(xml).find('response').each(function (){
							if ($(this).text() == 'successful') 
								alert("Your inquiry has been sent!");
							else
								alert("Failed to send your inquiry!");
							document.getElementById('contact_email').value = "<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?>";
							document.getElementById('contact_subject').value = "";
							document.getElementById('inquiry').value = "";							
						});
					}
				);
			});
		</script>		
	</div>
</div>

<div class="contact_seller" id="contact_seller">
	<div id="contact_seller_drag" style="float: right">
		<img id="contact_seller_exit" src="images/close.png"/>
	</div>
	<div>
		<form name="contact_seller_form" id="contact_seller_form" method="POST">
			<h2>Contact Seller</h2>
			<div id="contact_seller_status"></div>			
			<table>
				<tr>
					<td valign="top">
						<b>Email</b>
					</td>
					<td>
						<input type="text" id="contact_email" style="width:300px;" 
						value=<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?> >
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Subject</b>
					</td>
					<td>
						<input type="text" id="contact_subject" style="width:300px;"/>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Inquiry<br/>/Offer</b>
					</td>
					<td>
						<textarea style='width: 300px; height: 200px' id="inquiry"></textarea>
					</td>
				</tr>
			</table>
			<br/>			
			<button type="button" id="contact_seller_button">Send</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('contact_seller').style.display='none';">Cancel</button>
		</form>
		<script>
			$("#contact_seller_button").click(function(event) {	
				$.post(<?php echo "'".API_URL."'"; ?>, 
					{	
						request: 'email_seller',
						item_id: <?php echo "'$item_id'"; ?>,
						email: $('#contact_email').val(),
						subject: $('#contact_subject').val(),
						content: $('#inquiry').val()
					}, 
					function (xml) {
						$(xml).find('response').each(function (){
							if ($(this).text() == 'successful') { 
								document.getElementById('overlay').style.display='none';
								document.getElementById('contact_seller').style.display='none';
								
								alert("Your inquiry has been sent!");								
							}
							else
								alert("Failed to send your inquiry!");
							document.getElementById('contact_email').value = "<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?>";
							document.getElementById('contact_subject').value = "";
							document.getElementById('inquiry').value = "";							
						});
					}
				);
			});
		</script>		
	</div>
</div>

<div class="spacer"></div>

<table>
	<tr>
		<td valign='top' width="610px">
			<div id="items" style="width: 610px;">	
				<table class="item-page">
					<tr>
						<td style="padding-bottom: 5px" colspan="2">
							<b style="font-size: 18pt;"><?php echo $item->title; ?>, $<?php echo $price; ?></b>
						</td>
					</tr>
					<tr>
						<td valign="top" width="290px" class="item-details">
							<?php
								if ($item->photo_file_1 != '')
									echo "<img src='uploads/items/".$item->photo_file_1."?".$item->last_update."' width=280px height=210px id='current_item_photo' /> &nbsp;&nbsp;";
							?>
							<div style="padding-top: 5px;"></div>
							<?php
								for ($i = 1; $i <=4; $i++) {
									$fn = $item->getPhoto($i);
									if ($fn == "'no_image.jpg'")
										$fn = "no_image.jpg";
									if ($fn != '' && $fn != "no_image.jpg")
										echo "<img src='uploads/items/$fn' width=65px height=49px onclick=\"document.getElementById('current_item_photo').src='uploads/items/$fn';\" /> &nbsp;";
								}									
							?>							
						</td>
						<td valign="top">
						<div class="item-page-box">
							<?php 
								$desc = preg_replace("/\n/","<br>",html_entity_decode($item->description));
								echo $desc;
							?><br><br>
							<b>Seller Availability:</b> <?php if($item->avail == "") { echo "The seller did not list specific availability times"; }
							else { echo "<i>\"$item->avail\"</i>"; } ?><br>
							<?php $checkFlag = new Flag(); $flagCount = $checkFlag->GetItemFlaggedCount($item->item_id); if ($flagCount == 1) { $flagPlural = "flag"; } else { $flagPlural = "flags"; } ?>
							<b>Flags</b>: this item has <?=$flagCount?> <?=$flagPlural?> <br>
						</div>
						<div style="padding: 5px;"></div>
						<div class="item-page-box">
							Item ID: <?php echo $item->id;?> <br>
							Listing <?php if ($closed_silo) { echo "expired"; } else { echo "expires"; } ?>:
								<?php $end = strtotime($silo->end_date); $ended_date = date("M jS, Y", $end); echo $ended_date; ?> <br>
							Seller: <a href="index.php?task=view_user&id=<?php echo $seller->id;?>"><font color="#2f8dcb"><?php echo $seller->fname?> (view other items)</font></a>
						</div>

				<?php if ($closed_silo) { echo "<div style='margin-top: 30px; text-align: center;'>The silo this item belongs to is closed. <br> Items in a closed silo are not interactive.</div>"; } 
				elseif ($isSeller) { echo "<img height='40' width='40' src='images/facebook.jpg' class='fbHover' style='float: left; padding-top: 7px; padding-right: 5px;' onclick='postToFeed();'/> <a onclick=\"javascript:popup_show('mail', 'mail_drag', 'mail_exit', 'screen-center', 0, 0);\"><img src='images/mail-icon.png' width='55' height='55' style='float: left; padding-right: 5px;'></a> <span id='success' class='error'>".$updatemsg."</span> <div class='itemText' style='padding-top: 10px; text-align: center;'>You are the seller of this item. <br> Some functions are hidden.</div> <br> <button class='buttonBuyItem' onclick=\"popup_show('editItem', 'editItem_drag', 'editItem_exit', 'screen-center', 0, 0);\">edit your item</button>"; } else { ?>

						<div style="padding: 5px;"></div>
							<?php if (!$_SESSION['is_logged_in']) {?>
								<button class="buttonBuyItem" onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">buy this item</button>
							<?php } elseif ($isSeller) {} elseif (!$distBuyerSeller) { ?>
								<button class="buttonBuyItem" onclick="popup_show('dist', 'dist_drag', 'dist_exit', 'screen-center', 0, 0);">buy this item</button>
							<?php } elseif ($addInfo_full) { ?>
								<button class="buttonBuyItem" onclick="popup_show('addInfo_item', 'addInfo_item_drag', 'addInfo_item_exit', 'screen-center', 0, 0);">buy this item</button>
							<?php } else { ?>
								<button class="buttonBuyItem" onclick="window.location = 'index.php?task=payment&id=<?php echo $item->id;?>'">buy this item</button>
							<?php } ?>
						<table width="100%" style="padding-top: 5px"><tr>
						<td width="155px">
							<?php if (!$_SESSION['is_logged_in']) { ?>
								<button class="buttonItemPage" onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">make other offer
							<?php } elseif ($addInfo_full) { ?>
								<button class="buttonItemPage" onclick="popup_show('addInfo_item', 'addInfo_item_drag', 'addInfo_item_exit', 'screen-center', 0, 0);">make other offer
							<?php } elseif ($isSeller) {} elseif ($offerStatus == 'declined' || $offerStatus == 'canceled') { ?>
								<button class="buttonItemPage" style="color: red;">offer <?=$offerStatus?>
							<?php } elseif ($offerStatus == 'pending') { ?>
								<button class="buttonItemPage offer" onclick="javascript:popup_show('offerp', 'offerp_drag', 'offerp_exit', 'screen-center', 0, 0);">offer pending
							<?php } elseif ($offerStatus == 'accepted') { ?>
								<button class="buttonItemPage" style="color: green">offer accepted!
							<?php } elseif ($itemOffer) { ?>
								<button class="buttonItemPage" onclick="javascript:popup_show('ioffer', 'ioffer_drag', 'ioffer_exit', 'screen-center', 0, 0);">another offer pending
							<?php } elseif (!$distBuyerSeller) { ?>
								<button class="buttonItemPage" onclick="javascript:popup_show('dist', 'dist_drag', 'dist_exit', 'screen-center', 0, 0);">make other offer
							<?php } else { ?>
								<button class="buttonItemPage" onclick="javascript:popup_show('offer', 'offer_drag', 'offer_exit', 'screen-center', 0, 0);">make other offer
							<?php } ?></button>
						</td>
						<td align="center" valign="middle" rowspan="2">
							<?php if (!$_SESSION['is_logged_in']) { ?>
								<div class="click_me flagItem" onclick="javascript:popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><img height="35px" src="img/flag.png" alt="Flag this item" />
							<?php } elseif (!$distBuyerSeller) { ?>
								<div class="click_me flagItem" onclick="javascript:popup_show('dist', 'dist_drag', 'dist_exit', 'screen-center', 0, 0);"><img height="35px" src="img/flag.png" alt="Flag this item" />
							<?php } elseif ($itemFlagged) { ?>
								<div class="click_me flagItem" onclick="javascript:popup_show('flagged', 'flagged_drag', 'flagged_exit', 'screen-center', 0, 0);"><img height="35px" src="img/flag.png" alt="Flag this item" />
							<?php } elseif (!$isSeller || $closed_silo) { ?>
								<div class="click_me flagItem" onclick="javascript:popup_show('flag_box', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><img height="35px" src="img/flag.png" alt="Flag this item" />
							<?php } ?></div>
						</td>
						<td style="padding-left: 7px" align="center" valign="middle" rowspan="2">
							<a onclick="javascript:popup_show('mail', 'mail_drag', 'mail_exit', 'screen-center', 0, 0);"><img src="images/mail-icon.png" width="55" height="55"></a>
						</td>
						<td align="center" valign="middle" rowspan="2">
							<img height="40" width="40" src="images/facebook.jpg" class="fbHover" onclick='postToFeed();'/>
						</td></tr>
						<tr><td>
							<?php if ($fav) { ?>
								<form method="post" action="">
									<input type="hidden" name="user_id" value="<?=$user_id?>">
									<input type="hidden" name="item_id" value="<?=$item->item_id?>">
									<button class="buttonItemPage" style="color: red;" type="submit" name="fav" value="remove from favorites">remove from favorites
								</form>
							<?php } elseif (!$distBuyerSeller) { ?>
								<button class="buttonItemPage" onclick="javascript:popup_show('dist', 'dist_drag', 'dist_exit', 'screen-center', 0, 0);">add to favorites
							<?php } else { ?>
								<form method="post" action="">
									<input type="hidden" name="user_id" value="<?=$user_id?>">
									<input type="hidden" name="item_id" value="<?=$item->item_id?>">
									<button class="buttonItemPage" type="submit" name="fav" value="add to favorites">add to favorites
								</form>
							<?php } ?></button>
						</td>
						</tr></table>
				<?php } ?>
					</div>
					<tr><td><br></td></tr>
					<tr>
						<td colspan="2">
							<div id="map_canvas" style="width: 600px; height: 345px;" class="map-canvas"></div>
							<div id='fb-root'></div>
							<script src='https://connect.facebook.net/en_US/all.js'></script>
							<?php
								$url = ACTIVE_URL."index.php?task=view_item&id=".$item->id;
								$photo_url = ACTIVE_URL.'uploads/items/'.$item->photo_file_1.'?'.$item->last_update;
								$name = $item->title.": $".$item->price;
								$caption = "Help Silo: ".$silo->name;
								$description = substr($item->description, 0, 200)."...";
							?>

							<script> 
							 	FB.init({
							            appId      : <?php echo "'".FACEBOOK_ID."'"; ?>,
							            status     : true, 
							            cookie     : true,
							            xfbml      : true
							          });
							  	function postToFeed() {
							    	FB.ui({
							      		method: 'feed',
							      		link: "<?php echo $url; ?>",
							      		picture: "<?php echo $photo_url; ?>",
							      		name: "<?php echo $name; ?>",
										caption: "<?php echo $caption; ?>",
							      		description: "<?php echo $description; ?>"
							    	});
							  	}
							</script>																
						</td>
					</tr>				
				</table>
				<table>
					<tr>
						<td><?php include("include/UI/flag_box.php"); ?></td>
					</tr>
				</table>	
			</div>
		</td>
		<td style='width: 4%'>
		</td>
		<td width="340px" align="left">
			<?php include("include/silo_div.php"); ?>
		</td>
	</tr>
</table>
					
<?php
	}
?>

<script  type="text/javascript">

function initialize() {
var styles = [
	{
		featureType: 'water',
		elementType: 'all',
		stylers: [
			{ hue: '#84BFE5' },
			{ saturation: 37 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'landscape.man_made',
		elementType: 'all',
		stylers: [
			{ hue: '#FFFFFF' },
			{ saturation: -100 },
			{ lightness: 100 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.highway',
		elementType: 'all',
		stylers: [
			{ hue: '#FFC92F' },
			{ saturation: 100 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.arterial',
		elementType: 'all',
		stylers: [
			{ hue: '#FFE18C' },
			{ saturation: 100 },
			{ lightness: 2 },
			{ visibility: 'on' }
		]
	}
];
lat = <?=$item->latitude?>;
long = <?=$item->longitude?>;

var myLocation = new google.maps.LatLng(lat, long);
var options = {
	mapTypeControlOptions: {
		mapTypeIds: [ 'Styled']
	},
	center: myLocation,
	zoom: 11,
	maxZoom: 13,
	mapTypeId: 'Styled'
};

var div = document.getElementById('map_canvas');
var map = new google.maps.Map(div, options);
var styledMapType = new google.maps.StyledMapType(styles, { name: 'Item Location' });
map.mapTypes.set('Styled', styledMapType);

<?php
	$plate = $item->getItemCell($silo_id, $c_user_id);
	$plate = str_replace("<td>", "",$plate);
	$plate = str_replace("</td>", "",$plate);
?>

    var infowindow = new InfoBubble({
		maxWidth: 200,
		shadowStyle: 1,
		padding: 0,
		borderRadius: 4,
		arrowSize: 10,
		arrowPosition: 10,
      		arrowStyle: 2,	          
		borderWidth: 0,
		borderColor: '#2c2c2c'
    });

    var marker = new google.maps.Marker({
	       map: map,
		animation: google.maps.Animation.DROP,
		icon: 'images/map-marker.png',
	      	position: myLocation
    });
    markers.push(marker);

    infowindow.setOptions({
        content: "<?=$plate?>",
        position: myLocation,
    });

}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<div class="login" id="addInfo_item" style="width: 300px;">
	<div id="addInfo_item_drag" style="float:right">
		<img id="addInfo_item_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>Please complete your profile.</h2>
		You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
		<button type="button" onclick="document.location='index.php?task=my_account&redirect=view_item&id=<?=$item->id?>'">Finish it now</button>
		<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo_item').style.display='none';">Later</button>
	</div>
</div>

<div class="login" id="mail" style="width: 300px;">
	<div id="mail_drag" style="float:right">
		<img id="mail_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>Select your mail client:</h2>
		<a href="http://webmail.aol.com/mail/compose-message.aspx?&subject=Check out this item on sìloz.com - its sale helps a cause (silo) in the community!&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?>.com is a marketplace for items donated for community (as well as private) causes, or silos.  I thought you'd be interested in this item.%0D%0A%0D%0AItem: <?=ACTIVE_URL?>index.php?task=view_item%26id=<?=$item->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-aol"><span style="padding-left: 20px">AOL</span></div></a>
		<a href="https://mail.google.com/mail/?view=cm&fs=1&su=Check out this item on <?=SITE_NAME?>.com - its sale helps a cause (silo) in the community!&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?>.com is a marketplace for items donated for community (as well as private) causes, or silos.  I thought you'd be interested in this item.%0D%0A%0D%0AItem: <?=ACTIVE_URL?>index.php?task=view_item%26id=<?=$item->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-gmail"><span style="padding-left: 20px">Gmail</span></div></a>
		<a href="https://mail.live.com/default.aspx?rru=compose&subject=Check out this item on <?=SITE_NAME?>.com - its sale helps a cause (silo) in the community!&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?>.com is a marketplace for items donated for community (as well as private) causes, or silos.  I thought you'd be interested in this item.%0D%0A%0D%0AItem: <?=ACTIVE_URL?>index.php?task=view_item%26id=<?=$item->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-hotmail"><span style="padding-left: 20px">Hotmail, Live Mail, or Outlook</span></div></a>
		<a href="http://compose.mail.yahoo.com/?&subject=Check out this item on <?=SITE_NAME?>.com - its sale helps a cause (silo) in the community!&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?>.com is a marketplace for items donated for community (as well as private) causes, or silos.  I thought you'd be interested in this item.%0D%0A%0D%0AItem: <?=ACTIVE_URL?>index.php?task=view_item%26id=<?=$item->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-yahoo"><span style="padding-left: 20px">Yahoo Mail</span></div></a>
	</div>
</div>

<div class="edit_item" id="editItem" style="width: 800px">
	<div id="editItem_drag" style="float: right">
		<img id="editItem_exit" src="images/close.png"/>
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
			<table width="100%" cellpadding="10px" align="center">
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td><b>Listing Title</b> </td>
								<td><input type="text" name="title" style="width : 300px" value='<?php echo $item->title; ?>'/></td>
							</tr>		
							<tr>
								<td><b>Price</b> </td>
								<td><input type="text" name="price" style="width : 100px" value='<?php echo $item->price; ?>'/></td>
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
												if ($row['item_cat_id'] == $item->item_cat_id) {
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
								<td><textarea name="description" style="width: 200px; height: 100px"><?php echo $item->description; ?></textarea></td>
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
						<input type="hidden" name="item_id" value="<?=$item->id?>">
						<button type="submit" name="item" value="Update">Update</button>
					</td>
				</tr>	
			</table>	
		</form>		
	</div>
</div>