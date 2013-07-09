<?php
	$feedPerPage = "4";
	$itemsPerPage = "12";
	$membersPerPage = "12";

	$id = param_get('id');
	$shortname = param_get('name');
	
	$view = param_get('view');
	if ($view == '') {
		$view = 'feed';
		$view_track = "";
	} else { $view_track = "set"; }

	if (!$id && !shortname) {
		echo "<script>window.location = '".ACTIVE_URL."index.php';</script>";	
	}	
	else {
		if ($shortname) {
			$getSilos = mysql_fetch_row(mysql_query("SELECT id FROM silos WHERE shortname = '$shortname'"));
			$id = $getSilos[0];
		}
		$silo = new Silo($id);
		$silo_id = $silo->silo_id;
	if (!$silo_id) {
		echo "<script>window.location = '".ACTIVE_URL."index.php';</script>";	
	}
		$today = date('Y-m-d')."";
		$silo_ended = $silo->end_date < $today;
		$admin = $silo->admin;

	if ($silo->silo_type == "private") {
		$check = mysql_num_rows(mysql_query("SELECT * FROM silo_private WHERE silo_id = '$silo->silo_id' AND user_id = '$user_id'"));
		if (!$check)
			$upd = mysql_query("INSERT INTO silo_private (silo_id, user_id) VALUES ('$silo->silo_id', '$user_id')");
	}

		$checkUser = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id' AND removed_date > 0"));
		$showU = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id' AND removed_date = 0"));
		$checkClosed = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE silo_id = '$silo_id' AND status != 'active'"));
		if ($checkClosed > 0) { $closed_silo = "_closed"; }
		elseif ($silo->silo_cat_id == "99") { $disaster_silo = "_disaster"; $disaster_silo_text = "Relief"; }

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
	$isAdmin = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = '$user_id' AND silo_id = '$silo->silo_id'"));
?>

<div class="login" id="sold" style="width: 300px;">
	This Item Has Been Sold!
</div>

<div class="login" id="pending" style="width: 300px;">
	This Item Is Pending To Be Sold!
</div>

<div class="login" id="closed_silo" style="width: 300px;">
	<h2>This function has been disabled because the silo is no longer active.</h2>
</div>


<div class="contact_seller" id="contact_admin">
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
					<b>Body</b>
				</td>
				<td>
					<textarea style='width: 300px; height: 200px' name="inquiry" id="inquiry"></textarea>
				</td>
			</tr>
		</table>
		<br/>			
		<button type="button" id="contact_admin_button">Send</button>
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

<div class="headingPad"></div>

<table width="100%" id="nav" class="siloHeading<?=$closed_silo?><?=$disaster_silo?>">
<tr>
	<td style="padding-left: 10px">
		<?php echo $silo->getTitle(); ?>
		<?php if ($closed_silo) { echo "(Closed - This silo is no longer active)"; } elseif ($tax_ded) { echo "(".$disaster_silo_text.")"; }  elseif ($silo->silo_type == "private") { echo "(Private silo)"; } ?>
	</td>
	<td width="200px" style="padding-top: 5px;">
		<?php if (!$closed_silo) { ?>
			<a class='fancybox' href='#mail'><img src="<?=ACTIVE_URL?>images/mail-icon.png" width="32" height="32"></a>
			<img src="<?=ACTIVE_URL?>images/facebook.jpg" onclick='postToFeed();'/>

		<?php
			} if ($closed_silo) {
		?>
			<a class='fancybox' href='#closed_silo'><button type="submit" style="margin-bottom: -6px" class="buttonDonations" id="sell_on_siloz">donate items</button></a>
		
		<?php
			}
			elseif ($addInfo_full && $_SESSION['is_logged_in']) {
		?>
			<a class='fancybox' href='#addInfo_donate'><button type="submit" class="buttonDonations" id="sell_on_siloz">donate items</button></a>
	
		<?php
			}
			elseif ($checkUser && $_SESSION['is_logged_in']) {
		?>
			<a class='fancybox' href='#rmSilo'><button type="submit" class="buttonDonations" id="sell_on_siloz">donate items</button></a>

		<?php
			}
			elseif ($_SESSION['is_logged_in']) {			
		?>
			<button type="submit" class="buttonDonations" onclick="window.open('<?=ACTIVE_URL?>index.php?task=sell_on_siloz<?php echo "&id=".$silo->id;?>', '_parent');" id="sell_on_siloz">donate items</button>						

			<?php
				$ref = "S".$silo->id."-U".$current_user['id']."-".date('m/d/Y H:i:s');	
			?>

		<?php
			}
			else {
		?>
	<td>
			<a class='fancybox' href='#login'><button type="submit" class="buttonDonations" id="sell_on_siloz">donate items</button></a>					
		<?php
			}
		?>
	</td>
</tr>
</table>

<div class="headingPad"></div>

<table align="right" width="700px" style="padding-top: 5px;">
	<tr>
		<td height="35px" valign="top">
			<a class="<?php if ($view == "feed") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="<?=ACTIVE_URL?>index.php?task=view_silo&id=<?php echo $silo->id;?>">feed</a>
			<a class="<?php if ($view == "items") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="<?=ACTIVE_URL?>index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>">items</a>
			<a class="<?php if ($view == "members") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="<?=ACTIVE_URL?>index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>">people</a>
			<a class="<?php if ($view == "map") { echo "siloNav_sel"; } else { echo "siloNav"; } ?>" href="<?=ACTIVE_URL?>index.php?task=view_silo&view=map&id=<?php echo $silo->id;?>">map</a>
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
						echo '<a href="'.ACTIVE_URL.'index.php?task=view_silo&'.$param.'&id='.$silo->id.'&page='.$prev.'" class="nb_silo">< Prev</a> <span class="navPad"></span>';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="'.ACTIVE_URL.'index.php?task=view_silo&'.$param.'&id='.$silo->id.'&page='.$i.'" class="nb_silo">' . $i . '</a> <span class="navPad"></span>';
					} 
					else {
						echo '<span class="nb_siloSelected">'.$i.'</span> <span class="navPad"></span>';
					}
				};
				if ($page != $total_pages) {
					$next = $page + 1;
					echo '<a href="'.ACTIVE_URL.'index.php?task=view_silo&'.$param.'&id='.$silo->id.'&page='.$next.'" class="nb_silo">>Next</a>';
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
			$siloCell = $silo->getPlate($silo_id);

			$date = date('M d, Y', strtotime($result['date']));
			$time = date('h:i A T', strtotime($result['date']));
			$type = $result['type'];
			$goal_reached = $result['goal_reached'];
			$total_raised = floatval($collected);

			if ($showU) { $user_name = $user->fname; $user_name .= "&nbsp;".$user->lname; } else { $user_name = $user->fname; };

			if ($type == "Joined") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'><img src='".ACTIVE_URL."uploads/members/".$user->photo_file."?".$user->last_update."'></a></td>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'><img src='".ACTIVE_URL."uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>New member <a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'>".$user_name."</a> has joined this silo by pledging <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'>".$item->title."</a> for $".$item->price.".</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			if ($type == "Pledged") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'><img src='".ACTIVE_URL."uploads/members/".$user->photo_file."?".$user->last_update."'></a></td>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'><img src='".ACTIVE_URL."uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'><a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'>".$user_name."</a> has pledged an additional item, <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'>".$item->title."</a> for $".$item->price.".</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			elseif ($type == "Sold") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'><img src='".ACTIVE_URL."uploads/members/".$user->photo_file."?".$user->last_update."'></a></td>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'><img src='".ACTIVE_URL."uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>Sold! <br><br> <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'>".$item->title."</a> has been sold for $".$item->price.". <br><br> Thanks, <a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'>".$user->fname."</a>!</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			elseif ($type == "Goal") {
				$cellInfo = "<div class='nicebox'><table width='675px'><tr>";
				$cellInfo .= "<td width='15%'><button class='buttonGoal'>We reached ".$goal_reached."% of our Goal!</button></td>";
				$cellInfo .= "<td width='15%'><a href='".ACTIVE_URL."index.php?task=view_item&id=".$item->id."'><img src='".ACTIVE_URL."uploads/items/".$item->photo_file_1."?".$item->last_update."'></a></td>";
				$cellInfo .= "<td valign='top' style='padding: 10px 15px'>With <a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'>".$user_name."</a>'s sale, we reached ".$goal_reached."% of our fundraising goal. <br><br> Thanks, <a href='".ACTIVE_URL."index.php?task=view_user&id=".$user->id."'>".$user->fname."</a>!</td>";
				$cellInfo .= "</tr></table></div>";
				if ($i) { echo "<hr class='sInfo'>"; }
			}
			echo $cellInfo;
			$i++;
		}
	}
		
//VIEW ITEMS
	if ($view == "items" && $sold_items == "") {
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <font class='orange'><u>Pledged</u></font> &nbsp; | &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&items=sold&id=".$silo->id."' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&items=pending&id=".$silo->id."' style='text-decoration: none' class='blue'>Pending Sales</a></div>";

		$limit = "LIMIT $start_from, $itemsPerPage";
		$items = Item::getItems($silo_id, $order_by, $limit);
		$n = 0;
		echo "<table cellpadding='10px'>";			
		foreach ($items as $item) {
			if ($n == 0)
				echo "<tr>";							
				echo $item->getItemCell($silo_id, $c_user_id);					
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
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&id=".$silo->id."' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <font class='orange'><u>Sold</u></font> &nbsp; | &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&items=pending&id=".$silo->id."' style='text-decoration: none' class='blue'>Pending Sales</a> </div>";

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
		echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&id=".$silo->id."' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <a href='".ACTIVE_URL."index.php?task=view_silo&view=items&items=sold&id=".$silo->id."' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <font class='orange'><u>Pending Sales</u></font> </div>";

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
				echo $user->getMemberCell($silo_id, $user_id);					
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
	
}
?>
		</td>
	</tr>
</table>

<div style="margin-top: -5px;"></div>
<?php $showDiv = "true"; include("include/silo_div.php"); ?>

		<div id='fb-root'></div>
		<script src='https://connect.facebook.net/en_US/all.js'></script>
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
		      		link: "<?=$item->getUrl();?>",
		      		picture: "<?=$item->getPhotoUrl();?>",
		      		name: "<?php echo $silo->type.' Silo: '.$silo->name; ?>",
					caption: "<?=TAG_LINE?>",
		      		description: "<?php echo $description; ?>"
		    	});
		  	}
		</script>

<?php
if ($silo->silo_type == "public") {
	$mapLat = $silo->latitude;
	$mapLong = $silo->longitude;
} else {
	$user = new User($admin->user_id);
	$mapLat = $user->latitude;
	$mapLong = $user->longitude;
}

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

<script src="https://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js"></script>
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
	
	mapLat = <?=$mapLat?>;
	mapLong = <?=$mapLong?>;

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

	var mapLocation = new google.maps.LatLng(mapLat, mapLong);
	var options = {
		mapTypeControlOptions: {
			mapTypeIds: [ 'Styled']
		},
		center: mapLocation,
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
	bounds.extend(mapLocation);
	<?php
	foreach ($plates as $item_id => $plate) { $item = new Item($item_id);
		?>
		var pos<?=$item_id?> = new google.maps.LatLng(<?=$item->latitude?> + (2*Math.random()-1)*0.005, <?=$item->longitude?> + (2*Math.random()-1)*0.005);				
		
	   	var marker<?=$item_id?> = new google.maps.Marker({
	       	map: map,
			animation: google.maps.Animation.DROP,
			icon: 'images/map-marker.png',
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
  script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<div style="padding-bottom: 10px;"></div>

<div class="login" id="addInfo_donate" style="width: 300px;">
	<h2>Please complete your profile.</h2>
	You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
	<button type="button" onclick="document.location='<?=ACTIVE_URL?>index.php?task=my_account&redirect=sell_on_siloz&id=<?=$silo->id?>'">Finish it now</button>
</div>


<?php
	$checkThanked = mysql_num_rows(mysql_query("SELECT * FROM silos WHERE silo_id = '$silo_id' AND status != 'active' AND thanked = '1'"));
	if ($checkThanked && $view_track == "") {
	$siloInfo = mysql_fetch_array(mysql_query("SELECT id, admin_id, collected, end_date FROM silos WHERE silo_id = '$silo_id'"));
	$siloAdmin = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$siloInfo[admin_id]'"));
	$siloThank = mysql_fetch_array(mysql_query("SELECT * FROM silo_thank WHERE silo_id = '$silo_id'"));
	$end = strtotime($siloInfo['end_date']); $ended_date = date("F jS, Y", $end);
	$thanked = strtotime($siloThank['date']); $thanked_date = date("F jS, Y", $thanked);
?>
		<script type="text/javascript">
			$(document).ready(function () {
        			$("#thank_you").fancybox().trigger('click');
			})
		</script>

<div class="greyFont" style="font-weight: bold">
<div class="login" id="thank_you" style="width: 650px;">
		This silo raised $<?=$siloInfo['collected']?>, and ended on <?=$ended_date?>. The silo administrator, <span class="blue"><?=$siloAdmin['fname']?> <?=$siloAdmin['lname']?></span>, started the 'Thank You' phase of this silo on <?=$thanked_date?>. Thanks to all who pledged items and donated funds!<br><br>
<div class="row">
	<div id="slider_frame">
		<div id="slider">
			<?php if ($siloThank['photo_1']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_1']?>" alt="" />
			<?php } if ($siloThank['photo_2']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_2']?>" alt="" />
			<?php } if ($siloThank['photo_3']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_3']?>" alt="" />
			<?php } if ($siloThank['photo_4']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_4']?>" alt="" />
			<?php } if ($siloThank['photo_5']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_5']?>" alt="" />
			<?php } if ($siloThank['photo_6']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_6']?>" alt="" />
			<?php } if ($siloThank['photo_7']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_7']?>" alt="" />
			<?php } if ($siloThank['photo_8']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_8']?>" alt="" />
			<?php } if ($siloThank['photo_9']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_9']?>" alt="" />
			<?php } if ($siloThank['photo_10']) { ?>
				<img src="uploads/thank-you/<?=$siloInfo['id']?>/<?=$siloThank['photo_10']?>" alt="" />
			<?php } ?>
		</div>
	</div>
</div>

<br><br>

<?php if ($siloThank['comments']) { ?>
	<span style="font-weight: normal">Silo administrator comments:</span> "<?=$siloThank['comments']?>"
<?php } ?>
</div>
</div>

<?php } ?>

<div class="login" id="mail" style="width: 300px;">
	<h2>Select your mail client:</h2>
	<a href="http://webmail.aol.com/mail/compose-message.aspx?&subject=Here's a worthy cause (silo) I thought you might want to help&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?> is a marketplace for items donated for community (as well as private) causes, or silos. I found a silo I thought you'd be interested in. Please donate or buy an item to help the cause!%0D%0A%0D%0A silo: <?=ACTIVE_URL?>index.php?task=view_silo%26id=<?=$silo->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-aol"><span style="padding-left: 20px">AOL</span></div></a>
	<a href="https://mail.google.com/mail/?view=cm&fs=1&su=Here's a worthy cause (silo) I thought you might want to help&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?> is a marketplace for items donated for community (as well as private) causes, or silos.  I found a silo I thought you'd be interested in. Please donate or buy an item to help the cause!%0D%0A%0D%0A silo: <?=ACTIVE_URL?>index.php?task=view_silo%26id=<?=$silo->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-gmail"><span style="padding-left: 20px">Gmail</span></div></a>
	<a href="https://mail.live.com/default.aspx?rru=compose&subject=Here's a worthy cause (silo) I thought you might want to help&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?> is a marketplace for items donated for community (as well as private) causes, or silos. I found a silo I thought you'd be interested in. Please donate or buy an item to help the cause!%0D%0A%0D%0A silo: <?=ACTIVE_URL?>index.php?task=view_silo%26id=<?=$silo->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-hotmail"><span style="padding-left: 20px">Hotmail, Live Mail, or Outlook</span></div></a>
	<a href="http://compose.mail.yahoo.com/?&subject=Here's a worthy cause (silo) I thought you might want to help&body=Hey!%0D%0A%0D%0A<?=SITE_NAME?> is a marketplace for items donated for community (as well as private) causes, or silos.  I found a silo I thought you'd be interested in. Please donate or buy an item to help the cause!%0D%0A%0D%0A silo: <?=ACTIVE_URL?>index.php?task=view_silo%26id=<?=$silo->id?>" target="_blank" style="text-decoration: none" class="greyFont"><div class="mail-yahoo"><span style="padding-left: 20px">Yahoo Mail</span></div></a>
</div>

<div class="login" id="rmSilo" style="width: 300px;">
	You cannot pledge anymore items to this silo because the administrator has removed you. Please find a different silo!
</div>