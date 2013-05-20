<?php
	if ($_SESSION['is_logged_in'] != 1) {
		echo "<script>window.location = 'index.php';</script>";
	}

	$feedPerPage = "4";
	$itemsPerPage = "12";
	$membersPerPage = "12";

	$id = mysql_fetch_row(mysql_query("SELECT id FROM silos WHERE admin_id = '$user_id'"));
	
	$view = param_get('view');
	if ($view == '')
		$view = 'feed';

		$Silo = new Silo($id[0]);
		$silo_id = $Silo->silo_id;
		$today = date('Y-m-d')."";
		$silo_ended = $Silo->end_date < $today;
		$admin = $Silo->admin;
		$checkClosed = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE silo_id = '$silo_id' AND status != 'active'"));
		if ($checkClosed > 0) { $closed_silo = "_closed"; }
	
	if (param_post('delete_item') != '') {
		$item_id = param_post('item_id');
		$item = mysql_fetch_array(mysql_query("SELECT silo_id, user_id FROM items WHERE item_id = $item_id"));	

		$updItem = mysql_query("UPDATE items SET status = 'deleted', deleted_date = CURRENT_TIMESTAMP WHERE item_id = $item_id");
		$delFeed = mysql_query("DELETE FROM feed WHERE item_id = '$item_id'");
		$checkItems = mysql_num_rows(mysql_query("SELECT * FROM items WHERE user_id = '$item[user_id]' AND silo_id = '$item[silo_id]' AND status != 'deleted' OR status != 'flagged'"));
		if (!$checkItems) { $delMembship = mysql_query("DELETE FROM silo_membership WHERE user_id = '$item[user_id]' AND silo_id = '$item[silo_id]'"); }

		$Notification = new Notification();
		$Notification->DeleteEmail($item_id, $item['user_id'], $item['silo_id'], 'item');
	}

	if (param_post('delete_user') != '') {
		$silo_id = param_post('silo_id');
		$user_id = param_post('user_id');

		$updItem = mysql_query("UPDATE items SET status = 'deleted', deleted_date = CURRENT_TIMESTAMP WHERE silo_id = $silo_id AND user_id = $user_id AND status = 'pledged' OR status = 'offer'");
		$delFeed = mysql_query("DELETE FROM feed INNER JOIN items USING (item_id) WHERE user_id = '$user_id' AND silo_id = '$silo_id' AND status = 'deleted'");
		$delMembship = mysql_query("DELETE FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id'");

		$Notification = new Notification();
		$Notification->DeleteEmail('', $user_id, $silo_id, 'user');
	}

		$err = "";
		$admin_id = $_SESSION['user_id'];	
		$admin = new User($admin_id);
		$silo = $admin->getCurrentSilo();
		if (param_post('update') == 'Update') {		
			$name = param_post('name');
			$shortname = trim(param_post('shortname'));
			$address = param_post('address');
			$org_name = param_post('org_name');
			$purpose = param_post('purpose');
			$phone_number = param_post('phone_number');
			
			if (strlen(trim($name)) == 0) {
				$err = 'Silo name must not be empty.<br/>';		
			}
			if (strlen(trim($name)) > 50) {
				$err = 'Your new silo name is too long. Please shorten it.<br/>';		
			}
			if (strlen($shortname) == 0) {
				$err = "Silo's short name must not be empty.<br/>";
			}
			if (strpos('|'.$shortname, ' ') > 0) {
				$err = "Silo's short name must not contain space.<br/>";
			}
			else {
				if (strlen($shortname) > 30) {
					$err = "Silo's short name cannot be more than 30 characters.<br/>";
				}
			}
			$sql = "SELECT * FROM silos WHERE shortname = '$shortname' AND silo_id <> $silo_id";
			if (mysql_num_rows(mysql_query($sql)) > 0) {
				$err = "Silo's short name is already used by another silo. <br/>";
			}
			
			if (strlen(trim($address)) == 0) {
				$err = 'Address must not be empty.<br/>';		
			}

			$adr = urlencode($address);
			$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=".$adr."&sensor=false");
			$loc = json_decode($json);

			if ($loc->status == 'OK') {
				$address = $loc->results[0]->formatted_address;
				$latitude = $loc->results[0]->geometry->location->lat;
				$longitude = $loc->results[0]->geometry->location->lng;
			}
			else { $err = "Invalid Location! <br>"; }

			if (strlen($err) == 0) {

				$success = "true";							
				include("include/set_silo_params.php");
				
				if ($_FILES['silo_photo']['name'] != '') {
					$allowedExts = array("png", "jpg", "jpeg", "gif");							
					$ext = end(explode('.', strtolower($_FILES['silo_photo']['name'])));
					if (!in_array($ext, $allowedExts)) {
						$err .= $_FILES['silo_photo']['name']." is invalid file type.";
					}
					else {
							$filename = $_FILES['silo_photo']['name'];
							$temporary_name = $_FILES['silo_photo']['tmp_name'];
							$mimetype = $_FILES['silo_photo']['type'];
							$filesize = $_FILES['silo_photo']['size'];

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

							$name = "uploads/".$Silo->id.".jpg";
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

	if (param_post('crop') == 'Crop') {
		$id = trim(param_post('silo_id'));
		$crop = true;
		$targ_w = 300;
		$targ_h = 225;
		$jpeg_quality = 90;

		$src = 'uploads/'.$id.'.jpg';
		$name = 'uploads/silos/'.$id.'.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		imagejpeg($dst_r, $name, $jpeg_quality);
		unlink($src);

		mysql_query("UPDATE silos SET photo_file = '$id.jpg' WHERE id = '$id'");
	}

	$updatemsg = "Your silo has been updated!";
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
		$checkUser = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id' AND removed_date > 0"));
		$showU = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id' AND removed_date = 0"));

//Determine number of pages
	if ($view == 'feed') {
		$count = "SELECT COUNT(*) as num FROM feed WHERE silo_id = '$silo_id'";
		$countRow = mysql_fetch_array(mysql_query($count));
		$total_records = $countRow['num'];
		$total_pages = ceil($total_records / $feedPerPage);
		$param = "view=feed";
		$getCount = $feedPerPage;
	}
	elseif ($view == 'items') {
		$sold_items = param_get('items');
		$param = "view=items";
		$getCount = $itemsPerPage;

		if ($sold_items == "") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE silo_id = $silo_id AND (items.status = 'pledged' OR items.status = 'offer')";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);
		}
		elseif ($sold_items == "sold") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'sold' AND silo_id = $silo_id";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);
			$param .= "&sold_items=sold";
		}
		elseif ($sold_items == "pending") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'pending' AND silo_id = $silo_id";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);
			$param .= "&sold_items=pending";
		}
	}
	elseif ($view == 'members' && $_SESSION['is_logged_in']) {
		$count = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM silo_membership WHERE silo_id = $silo_id AND removed_date = 0)";
		$total_records = mysql_num_rows(mysql_query($count));
		$total_pages = ceil($total_records / $membersPerPage);
		$param = "view=members";
		$getCount = $membersPerPage;
	}

	if (param_get('page')) { $page  = param_get('page'); } else { $page = 1; };
	$start_from = ($page-1) * $getCount;

	$user_id = $_SESSION['user_id'];
	$isAdmin = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = '$user_id' AND silo_id = '$Silo->silo_id'"));
?>

<div class="login" id="sold" style="width: 300px;">
	<div id="sold_drag" style="float:right">
		<img id="sold_exit" src="images/close.png"/>
	</div>
	<div>
		This Item Has Been Sold!
	</div>
</div>

<div class="login" id="pending" style="width: 300px;">
	<div id="pending_drag" style="float:right">
		<img id="pending_exit" src="images/close.png"/>
	</div>
	<div>
		This Item Is Pending To Be Sold!
	</div>
</div>

<div class="contact_seller" id="contact_admin">
	<div id="contact_admin_drag" style="float: right">
		<img id="contact_admin_exit" src="images/close.png"/>
	</div>
	<div>
		<form name="contact_admin_form" id="contact_admin_form" method="POST">
			<h2>Contact Admin</h2>
			<p>Silo <b><?php echo $Silo->name; ?></b></p>
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
						<b>Body</b>
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
						silo_id: <?php echo $Silo->silo_id; ?>,
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

<div class="headingPad"></div>

<div class="siloHeading_manage">
	<table width="100%" style="border-spacing: 0px;">
		<tr>
			<td>
				<?php echo $Silo->getTitle(); ?>
			</td>
			<td align="center" style="font-size: 8pt; font-weight: bold">
				<?php
					if (strlen($err) > 0) {
						echo "<span id='success' class='error'>".$err."</span>";
					}

					if ((param_post('update') == 'Update') && !$filename && strlen($err) == 0) { 
						echo "<span id='success' class='error'>".$updatemsg."</span>";
					}
					elseif ($crop == "true") { 
						echo "<span id='success' class='error'>".$updatemsg."</span>";
					}

				?>
			</td>
			<td width="450px" style="font-size: 10pt; font-weight: bold" align="right">
			<a href="index.php?task=manage_silo" class="<?php if (param_get('task') == 'manage_silo') { echo "orange"; } else { echo "blue"; } ?>">manage members and items</a>
			<span style="padding: 0 5px;">|</span>
			<a href="index.php?task=manage_silo_admin" class="<?php if (param_get('task') == 'manage_silo_admin') { echo "orange"; } else { echo "blue"; } ?>">view statistics and promote</a>
			<span style="padding: 0 5px;">|</span>
			<a onclick="popup_show('edit_silo', 'edit_silo_drag', 'edit_silo_exit', 'screen-center', 0, 0);populate_silo_info('<?=$silo_id?>');" class="blue">edit silo</a>
			</td>
		</tr>
	</table>
</div>

<div class="headingPad"></div>

<?php
if ($success && $filename) {
?>
	<center>
		<h1>New Silo Photo</h1>
		To finish updating your silo, please crop the image you uploaded below:<br><br>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="uploads/<?=$Silo->id?>.jpg" id="cropbox" />
		
		<br>

		<!-- This is the form that our event handler fills -->
		<form action="" method="post" onsubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" name="silo_id" value="<?=$Silo->id?>" />
			<button type="submit" name="crop" value="Crop">Crop</button>
		</form>
	</center>
	<br><br>
<?php
die;
}
?>

<table align="right" width="700px" style="padding-top: 5px;">
	<tr>
		<td height="35px" valign="top">
			<a class="<?php if ($view == "feed") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="index.php?task=manage_silo">feed</a>
			<a class="<?php if ($view == "items") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="index.php?task=manage_silo&view=items">items</a>
			<a class="<?php if ($view == "members") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="index.php?task=manage_silo&view=members">people</a>
			<a class="<?php if ($view == "map") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="index.php?task=manage_silo&view=map">map</a>
		</td>
		<td align="right" valign="top">
		<?php
			if ($total_pages == 1) {
				echo '<span class="nb_siloSelected">1</span>';
			}
			elseif (!$total_pages) {}
			else	{
				if ($page != "1") {
					$prev = $page - 1;
						echo '<a href="index.php?task=manage_silo&'.$param.'&page='.$prev.'" class="nb_silo">< Prev</a> <span class="navPad"></span>';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=manage_silo&'.$param.'&page='.$i.'" class="nb_silo">' . $i . '</a> <span class="navPad"></span>';
					} 
					else {
						echo '<span class="nb_siloSelected">'.$i.'</span> <span class="navPad"></span>';
					}
				};
				if ($page != $total_pages) {
					$next = $page + 1;
					echo '<a href="index.php?task=manage_silo&'.$param.'&page='.$next.'" class="nb_silo">>Next</a>';
				}
			}
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2" <?php if ($total_records || $view == map) { echo 'class="info-container"'; } ?> valign="top">
<?php

//VIEW FEED
	if ($view == 'feed') {
		$feed = mysql_query("SELECT * FROM feed WHERE silo_id = '$silo_id' ORDER BY id DESC LIMIT $start_from, $feedPerPage");
		$num = mysql_num_rows($feed);

		if (!$num) {
    			echo "<br><br><center>This silo feed is empty. Any activity for this silo will be posted here to keep everyone involved up to date.</center>";
  		}

		$i=0;
		while ($result = mysql_fetch_array($feed)) {

			//Get and set info for feed
			$user = new User($result['user_id']);
			$userCell = $user->getMemberCell($silo_id, $c_user_id);
			$item = new Item($result['item_id']);
			$itemCell = $item->getItemCell($silo_id, $c_user_id);
			$siloCell = $Silo->getPlate($silo_id);

			$date = date('M d, Y', strtotime($result['date']));
			$time = date('h:i A T', strtotime($result['date']));
			$type = $result['type'];
			$goal_reached = $result['goal_reached'];
			$total_raised = floatval($collected);

			if ($showU) { $user_name = $user->fname; $user_name .= "&nbsp;".$user->lname; } else { $user_name = $user->fname; };

			if ($type == "Pledged") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><a href='index.php?task=view_user&id=".$user->id."'><img src='uploads/members/".$user->photo_file."?".$user->last_update."'></a></td>";
				$cellInfo .= "<td width='15%'><a href='index.php?task=view_item&id=".$item->id."'><img src='uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>New member <a href='index.php?task=view_user&id=".$user->id."'>".$user_name."</a> has joined this silo by pledging <a href='index.php?task=view_item&id=".$item->id."'>".$item->title."</a> for $".$item->price.".</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			elseif ($type == "Sold") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><a href='index.php?task=view_user&id=".$user->id."'><img src='uploads/members/".$user->photo_file."?".$user->last_update."'></a></td>";
				$cellInfo .= "<td width='15%'><a href='index.php?task=view_item&id=".$item->id."'><img src='uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>Sold! <br><br> <a href='index.php?task=view_item&id=".$item->id."'>".$item->title."</a> has been sold for $".$item->price.". <br><br> Thanks, <a href='index.php?task=view_user&id=".$user->id."'>".$user->fname."</a>!</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			elseif ($type == "Goal") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><button class='buttonGoal'>We reached ".$goal_reached."% of our Goal!</button></td>";
				$cellInfo .= "<td width='15%'><a href='index.php?task=view_item&id=".$item->id."'><img src='uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>With <a href='index.php?task=view_user&id=".$user->id."'>".$user_name."</a>'s sale, we reached ".$goal_reached."% of our fundraising goal. <br><br> Thanks, <a href='index.php?task=view_user&id=".$user->id."'>".$user->fname."</a>!</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			echo $cellInfo;
			$i++;
		}
	}
		
//VIEW ITEMS
	if ($view == "items" && $sold_items == "") {
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <font class='orange'><u>Pledged</u></font> &nbsp; | &nbsp; <a href='index.php?task=manage_silo&view=items&items=sold' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <a href='index.php?task=manage_silo&view=items&items=pending' style='text-decoration: none' class='blue'>Pending Sales</a></div>";

		$limit = "LIMIT $start_from, $itemsPerPage";
		$items = Item::getItems($silo_id, $order_by, $limit);
		$n = 0;
		echo "<table cellpadding='10px'>";			
		foreach ($items as $item) {
			if ($n == 0)
				echo "<tr>";
				echo $item->getItemCellAdmin($silo_id, $c_user_id);					
				$n++;
			if ($n == 4) {
				echo "</tr>";
				$n = 0;
			}					
		}
		echo "</table>";
		if (!$total_records) { echo "<br><br><center>There are currently no items being pledged in this silo. Once an item is pledged to this silo, it will be added to this list.</center>"; }
	}
	elseif ($sold_items == "sold") {
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='index.php?task=manage_silo&view=items' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <font class='orange'><u>Sold</u></font> &nbsp; | &nbsp; <a href='index.php?task=manage_silo&view=items&items=pending' style='text-decoration: none' class='blue'>Pending Sales</a> </div>";

		$limit = "LIMIT $start_from, $itemsPerPage";
		$items = Item::getSoldItems($silo_id, $order_by, $limit);
		$n = 0;
		echo "<table cellpadding='10px'>";			
		foreach ($items as $item) {
			if ($n == 0)
				echo "<tr>";							
				echo $item->getSoldItemCell($silo_id, $c_user_id);					
				$n++;
			if ($n == 4) {
				echo "</tr>";
				$n = 0;
			}	
		}
		echo "</table>";
		if (!$total_records) { echo "<br><br><center>There are currently no items that have sold in this silo. Once an item has sold for this silo, it will be added to this list.</center>"; }
	}
	elseif ($sold_items == "pending") {
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='index.php?task=manage_silo&view=items' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <a href='index.php?task=manage_silo&view=items&items=sold' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <font class='orange'><u>Pending Sales</u></font> </div>";

		$limit = "LIMIT $start_from, $itemsPerPage";
		$items = Item::getPendingItems($silo_id, $order_by, $limit);
		$n = 0;
		echo "<table cellpadding='10px'>";			
		foreach ($items as $item) {
			if ($n == 0)
				echo "<tr>";							
				echo $item->getPendingItemCell($silo_id, $c_user_id);					
				$n++;
			if ($n == 4) {
				echo "</tr>";
				$n = 0;
			}	
		}
		echo "</table>";
		if (!$total_records) { echo "<br><br><center>There are currently no items that have sold in this silo. Once an item has sold for this silo, it will be added to this list.</center>"; }
	}

//VIEW MEMBERS
	if ($view == 'members' && $_SESSION['is_logged_in']) {
		$limit = "LIMIT $start_from, $membersPerPage";
		$users = User::getMembers($silo_id, $order_by, $limit);
		echo "<table cellpadding='10px'>";
		$n = 0;
		foreach ($users as $user) {
			if ($n == 0)
				echo "<tr>";
				echo $user->getMemberCellAdmin($silo_id, $user_id);					
				$n++;
			if ($n == 4) {
				echo "</tr>";
				$n = 0;
				}
			}
		echo "</table>";
		if (!$total_records) { echo "<br><br><center>There are currently no members in this silo. Only users who pledge an item to this silo are considered members.</center>"; }
	}
	elseif ($view == 'members') {
			echo "<br><br><center>Please create an account or login to an existing account to view silo members.";
	}

//VIEW Map
	if ($view == 'map') {
		echo "<div id='map_canvas' style='width: 700px; height: 576px;'></div>";
	}
?>
		</td>
	</tr>
</table>

<table class='siloInfo' style="margin-top: -5px;">
	<tr>
		<td>
					<?php
						$collected = $Silo->getCollectedAmount();
						$pct = round($collected*100.0/floatval($Silo->goal));
						if ($pct == 100) { $radius = "border-radius: 4px;"; } else { $radius = "border-top-left-radius: 4px; border-bottom-left-radius: 4px"; }
						
						$c_user_id = $current_user['user_id'];
						$showA = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$Silo->silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
						if ($showA) { $admin_name = $admin->fname; $admin_name .= "&nbsp;".$admin->lname; } else { $admin_name = $admin->fname; };
					?>
			<img src="<?php echo 'uploads/silos/'.$Silo->photo_file.'?'.$Silo->last_update;?>" width='250px' class="siloImg"/>
			<div class="siloImgOverlay">
			<div class="progress-bg"><div class="progress-bar" style="width: <?=$pct?>%; <?=$radius?>"></div></div>
			goal: $<?=number_format($Silo->goal)?> (<?=$pct?>%)
			</div>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo">
			<a href='index.php?task=manage_silo&view=members'><?=$Silo->getTotalMembers();?></a>
			<a href='index.php?task=manage_silo&view=items'><?=$Silo->getTotalItems();?></a>
			<?=$Silo->getDaysLeft()?>
			<div style="padding-top: 10px;"></div>
		<?php if (!$tax_ded) { $tax = "<b><u>not</u></b>"; } ?>
			<div class="voucherText" style="font-size: 10pt; text-align: left"><b>Purpose:</b> <?=$silo->getPurpose();?></div>
			<div class="voucherText" style="font-size: 10pt; text-align: left">This Administrator has <?=$tax?> provided an EIN number for this fundraiser, and donations are <?=$tax?> tax-deductable.</div>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo">
			<span class="floatL">
				<img src="<?php echo 'uploads/members/'.$admin->photo_file.'?'.$admin->last_update;?>" class="siloImg" width='100px'/><br>
				<a class='buttonEmail' href="javascript:popup_show('contact_admin', 'contact_admin_drag', 'contact_admin_exit', 'screen-center', 0, 0);">Email Admin.</a>
			</span>
			<div align="left">
			<span class="infoDetails">
				Administrator:<br>
				<span class="notBold"><?=$admin_name?></span><br>
				Official Address:<br>
				<span class="notBold"><?=$Silo->address?></span><br>
				Telephone:<br>
				<span class="notBold"><?=$Silo->phone_number?></span>
			</span>
			</div>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo">
			<div align="left">
			<span class='voucher'>Donate only to local causes that you know or have researched!</span><br><br>
			<?php include('include/UI/flag_box.php'); ?>
			<center>Silo ID: <?=$silo->id?></center>
		<div id='fb-root'></div>
		<script src='http://connect.facebook.net/en_US/all.js'></script>
		<?php
			$url = ACTIVE_URL."index.php?task=view_silo&id=$Silo->id";
			$photo_url = ACTIVE_URL.'uploads/silos/'.$Silo->photo_file.'?'.$Silo->last_update;
			$name = $Silo->name;
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
		      		name: "<?php echo $Silo->type.' Silo: '.$name; ?>",
					caption: "<?=TAG_LINE?>",
		      		description: "<?php echo $description; ?>"
		    	});
		  	}
		</script>
		</div>
		</td>
	</tr>
</table>

<?php
//Get items in silo for map

$items = Item::getItems($silo_id, $order_by, "");
$plates = array();
foreach ($items as $item) {
	//die(print_r($item));
	$plate = $item->getItemCell($silo_id, $c_user_id);
	$plate = str_replace("<td>", "",$plate);
	$plate = str_replace("</td>", "",$plate);
	$plates[$item->item_id] = $plate;		
}
?>

<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js"></script>
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

	siloLat = <?=$silo->latitude?>;
	siloLong = <?=$silo->longitude?>;
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

	var siloLocation = new google.maps.LatLng(siloLat, siloLong);
	var options = {
		mapTypeControlOptions: {
			mapTypeIds: [ 'Styled']
		},
		center: siloLocation,
		zoom: 5,
		maxZoom: 13,
		mapTypeId: 'Styled'
	};

	var div = document.getElementById('map_canvas');
	var map = new google.maps.Map(div, options);
	var styledMapType = new google.maps.StyledMapType(styles, { name: 'Silo Map' });
	map.mapTypes.set('Styled', styledMapType);

	var bounds = new google.maps.LatLngBounds();
	var markers = [];
	bounds.extend(siloLocation);
	<?php
	foreach ($plates as $item_id => $plate) {
		?>
		var pos<?=$item_id?> = new google.maps.LatLng(<?=$item->latitude?> + (2*Math.random()-1)*0.005, <?=$item->longitude?> + (2*Math.random()-1)*0.005);				
		
	   	var marker<?=$item_id?> = new google.maps.Marker({
	       	map: map,
			animation: google.maps.Animation.DROP,
			icon: 'images/red_square.png',
	       	position: pos<?=$item_id?>
	   	});
		markers.push(marker<?=$item_id?>);
		bounds.extend(pos<?=$item_id?>);	    
		google.maps.event.addListener(marker<?=$item_id?>, 'click', (function(marker) {
	        return function() {
	          infowindow.setContent(<?="\"$plate\""?>);
	          infowindow.open(map, marker);
	        }
	      })(marker<?=$item_id?>));
		
		<?php
	}
	?>
	map.fitBounds(bounds);
	var markerCluster = new MarkerClusterer(map, markers, {maxZoom: 13, gridSize:10});
}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<div style="padding-bottom: 10px;"></div>

<div class="edit_item" id="edit_silo">
	<div id="edit_silo_drag" style="float: right">
		<img id="edit_silo_exit" src="images/close.png"/>
	</div>

	<div>

<form enctype="multipart/form-data"  name="manage_silo_form" class="manage_silo_form" method="POST">
		<input type="hidden" name="task" value="manage_silo"/>
		
		<table cellpadding="10px">
			<tr>
				<td align="center" valign="top" width="650px">
					<img src="<?php echo 'uploads/silos/'.$Silo->photo_file.'?'.$Silo->last_update;?>" width="300px"/>
					<br/><br/>
					<b>Upload new photo: </b><input name="silo_photo" type="file" style="height: 24px" />
					<br/><br/>

					<table>
						<tr>
							<td valign="center" style="width: 120px;"><b>Silo Full Name: </b></td>
							<td><input type="text" name="name" style="width : 300px" value='<?php echo $Silo->name; ?>'/></td>
						</tr>
						<tr>
							<td valign="center"><b>Silo Short Name: </b></td>
							<td><input type="text" name="shortname" style="width : 300px" value='<?php echo $Silo->shortname; ?>'/></td>
						</tr>						
						<tr>
							<td>
								<b>Address:</b>
							</td>
							<td>
								<input type="text" name="address" style="width : 300px" value='<?php echo $Silo->address; ?>'/>
							</td>
						</tr>
						<tr>
							<td>
								<b>Organization:</b><br/>
							</td>
							<td>
								<input type="text" name="org_name" style="width : 300px" value='<?php echo $Silo->org_name; ?>'/>
							</td>
						</tr>						
						<tr>
							<td>
								<b>Phone Number:</b>
							</td>
							<td>
								<input type="text" name="phone_number" style="width : 150px" value='<?php echo $Silo->phone_number; ?>'/>
							</td>
						</tr>

						<tr>
							<td colspan=2><br/></td>
						</tr>
						
						<tr>
							<td colspan=2><b>Organization and fundraiser purpose: </b>
							<?php
								echo $Silo->purpose;
							?>
							</td>
						</tr>
					</table>
					<br><br>

					<button type="submit" name="update" value="Update">Update Silo</button>				
				</td>				
			</tr>
		</table>
	</form>

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