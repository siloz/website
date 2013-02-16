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
	
		$username = $_SESSION['username'];
		$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE username = '$username'"));

		$item_long = urlencode($item->longitude);
		$item_lat = urlencode($item->latitude);
		$user_long = urlencode($user['longitude']);
		$user_lat = urlencode($user['latitude']);
		$distBuyerSeller = $item->getDistance($item_long, $item_lat, $user_long, $user_lat);

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
		$amount = param_post('amount');

			$Item = new Item();
			$Item->item_id = $item_id;
			$Item->buyer_id = $buyer_id;
			$Item->seller_id = $seller_id;
			$Item->RemoveOffer();
	}

		$user_id = $user['user_id'];
		$fav = mysql_num_rows(mysql_query("SELECT * FROM favorites WHERE user_id = '$user_id' AND item_id = '$item->item_id'"));

		$checkOffer = mysql_num_rows(mysql_query("SELECT * FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id' AND avail = 'no'"));
		$offer = mysql_num_rows(mysql_query("SELECT * FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id' AND avail = 'yes'"));
		$amt = mysql_fetch_array(mysql_query("SELECT amount FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id' AND avail = 'yes'"));
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
			<h2><font color="FF642F">Offer: $<?=$amt['amount']?></font></h2>
			<h2>You are only allowed one offer per item. Are you sure you want to cancel it?</h2>
			<br/><br>	
			<button type="submit" name="offer" value="cancel">Yes, cancel my offer</button>
		</form>
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
<table>
	<tr>
		<td valign='top' width="610px">
			<div id="items" style="width: 610px;">	
				<table>
					<tr>
						<td colspan=2>
							<hr/>							
							<table width="100%">
								<tr>
									<td>
										<b style="font-size: 14px;"><?php echo $item->title; ?></b>
									</td>
									<td align="right">
										<b style="font-size: 14px; color:#f60; text-align:right">$<?php echo $item->price; ?></b>							
									</td>
								</tr>
							</table>
							<hr/>		
							<br/>					
						</td>
					</tr>
					<tr>
						<td valign="top" width="290px">
							<?php
								if ($item->photo_file_1 != '')
									echo "<img src='uploads/items/".$item->photo_file_1."' width=280px id='current_item_photo'/> &nbsp;&nbsp;";
							?>
							<br/>
							<?php
								for ($i = 1; $i <=4; $i++) {
									$fn = $item->getPhoto($i);
									if ($fn == "'no_image.jpg'")
										$fn = "no_image.jpg";
									if ($fn != '' && $fn != "no_image.jpg")
										echo "<img src='uploads/items/$fn' width=65px onclick=\"document.getElementById('current_item_photo').src='uploads/items/$fn';\" /> &nbsp;";
								}									
							?>							
						</td>
						<td valign="top" width="340px">
							<div style="text-align:justify; margin-left: 5px; height: 240px;">
								<?php 
									$desc = preg_replace("/\n/","<br>",html_entity_decode($item->description));
									echo $desc;
								?>
							<div style="margin-top: 15px;">
							<table cellpadding="2px">
								<tr>
									<td><b>ID:</b></td>
									<td><?php echo $item->id;?></td>
								</tr>
								<tr>
									<td><b>Seller:</b></td>
									<td><a href="index.php?task=view_user&id=<?php echo $seller->id;?>"><font color="#2f8dcb"><?php echo $seller->fname?></font></a></td>
								</tr>
								<tr>
									<td><b>Listed on:</b></td>
									<td><?php echo $item->added_date;?></td>
								</tr>
							</table>
							</div>
					<div style="margin-top: 15px; z-index: 200;">
					<table width="100%">
						<tr>
							<td rowspan="2"><a href="mailto:?Subject=www.siloz.com/index.php?task=view_item%26<?php echo $item->id;?>&Body=Check out this item on siloz!"><img src="images/mail-icon.png"></a></td>
							<td rowspan="2"><img src="images/facebook.jpg" onclick='postToFeed();'/></td>
							<td class="click_me" onclick="javascript:popup_show('flag_box', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><div class="voucherText"><font size="1">Flag this item</font></div></td>
									<td align="center">
											<?php if (!$_SESSION['is_logged_in']) {
											?>
											<button onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">Buy This Item</button>
											<?php
											} elseif (!$distBuyerSeller) {
											?>
											<button onclick="alert('You are too far away from the buyer. Please find an item closer to you!')">Buy This Item</button>
											<?php
											} else {
											?>
											<button onclick="window.location = 'index.php?task=payment&id=<?php echo $item->id;?>'">Buy This Item</button>
											<?php
											}
											?>
									</td>
						</tr>
						<tr>
							<td class="click_me" onclick="javascript:popup_show('flag_box', 'login_drag', 'login_exit', 'screen-center', 0, 0);"><img height="40px" width="auto" src="img/flag.png" alt="Flag this item" /></td>
							<td align="center">
								<div class="voucherText"><b>
								<?php if (!$_SESSION['is_logged_in']) { ?>
									<a onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">offer another amount
									<?php } elseif ($checkOffer) { ?>
									<font color="red">offer declined</font>
									<?php } elseif ($offer) { ?>
									<div class="offer"><a href="javascript:popup_show('offerp', 'offerp_drag', 'offerp_exit', 'screen-center', 0, 0);"><span>offer pending</span></div>
									<?php } else { ?>
									<a href="javascript:popup_show('offer', 'offer_drag', 'offer_exit', 'screen-center', 0, 0);">offer another amount
									<?php } ?></div></a></b><br>

								<div class="voucherText">
								<?php if (!$_SESSION['is_logged_in']) { ?>
									<a onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">add to favorites
									<?php } elseif ($fav) { ?>
									<form method="post" action="">
										<input type="hidden" name="user_id" value="<?=$user_id?>">
										<input type="hidden" name="item_id" value="<?=$item->item_id?>">
										<input style="color: red; background: #fff;" type="submit" name="fav" value="remove from favorites">
									</form>
									<?php } else { ?>
									<form method="post" action="">
										<input type="hidden" name="user_id" value="<?=$user_id?>">
										<input type="hidden" name="item_id" value="<?=$item->item_id?>">
										<input class="voucherText" type="submit" name="fav" value="add to favorites">
									</form>
									<?php } ?>
								</div></a><br>
							</td>
						</tr>
					</table>
							</div>						
							</div>

						</td>						
					</tr>
					<tr><td><br></td></tr>
					<tr>
						<td colspan="2">
							<b>Seller Availability:</b> <?php if($item->avail == "") { echo "The seller did not list specific availability times"; }
							else { echo "</i>$item->avail</i>"; } ?>
							<div id="map_canvas" style="width: 600px; height: 200px;"></div>
							<div id='fb-root'></div>
							<script src='http://connect.facebook.net/en_US/all.js'></script>
							<?php
								$url = ACTIVE_URL."/index.php?task=view_item&id=".$item->id;
								$photo_url = ACTIVE_URL.'/uploads/items/300px/'.$item->photo_file_1;
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
		<td style='width: 10px'>
		</td>
		<td width="340px" align="left">
				<div class="voucherText" align="left">You purchasing this item helps:</div><br>
					<?php
						$admin = $silo->getAdmin();
						$collected = $silo->getCollectedAmount();
						$pct = round($collected*100.0/floatval($silo->goal),1);
						$end_date = $silo->end_date;
						$end = strtotime("$end_date");
						$now = time();
						$timeleft = $end-$now;
						$daysleft = ceil($timeleft/86400);
						
						if ($daysleft > 1) { $dayplural = "Days"; } else { $dayplural = "Day"; }
					?>
	<a href='index.php?task=view_silo&id=<?php echo $silo->id;?>'>
		<div class='siloInfo'>
			<button type='button' class='buttonTitleInfo'><?php echo $silo->getTitle(); ?></button>
			<div align="center"><img src=<?php echo 'uploads/silos/'.$silo->photo_file;?> width='250px'/></div></a>
			<div class='bio'>

			<div class='floatL'><b>Goal:</b> <?php echo money_format('%(#10n', floatval($silo->goal));?> (<?=$pct?>%)</div>
			<div class='floatR'><b><?=$daysleft?> <?=$dayplural?> Left</b></div>
			<div class='floatL'><div class='padding'><b>Progress:</b> &nbsp;&nbsp;&nbsp;<div style='float: right; width: 160px; height: 12px; border: 1px solid #2F8ECB;'><div style='float: left; width: <?=$pct;?>%; height:12px; background: #2F8ECB;'></div></div></div></div>
			<div class='padding'>&nbsp;</div>
			<p align="center"><a href='index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalMembers();?> Members</a>, <a href='index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalItems();?> Items Pledged</a></p>
			<div class='floatL'><b>Organization:</b> <?php echo $silo->name; ?></div>
			<div class='floatL'><b>Purpose:</b> <?php echo $silo->getPurpose();?></div>
			<div class='floatL'><b>Category:</b> <?php echo $silo->type;?></div>
			<div class='padding2'>&nbsp;</div>
			<table class='floatL'>
			<tr>
			<td>
				<img src=<?php echo 'uploads/members/'.$admin->photo_file;?> width='90px'/><br>
				<button type='button' class='buttonEmail'>Email Admin.</button>
			</td>
			<td width='6%'></td>
			<td>
				<b>Silo Admin:</b> <br> <?php echo $admin->fname; ?><br>
				<b>Title:</b> <?php echo $silo->title; ?><br>
				<b>Official Address:</b> <br> <?php echo $silo->address; ?><br>
				<b>Telephone:</b> <?php echo $silo->phone_number; ?>
			</td>
			</tr>
			</table>
			<div class='floatL'><div class='voucher'>Research a silo and administrator</div></div>
			<div class='floatL'><?php include('include/UI/flag_box.php'); ?></div>

		</div>
		</div>
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


infoWindow = new google.maps.InfoWindow();
    infoWindow.setOptions({
        content: "<div align='center'><img src='uploads/items/<?=$item->photo_file_1?>' width=100px id='current_item_photo'/></div>",
        position: myLocation,
    });

infoWindow.open(map);
}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>