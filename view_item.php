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
?>
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
			<div style="width: 610px;" class="heading">
				Home > <?php echo $item->category; ?>
			</div>
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
									echo "<img src='uploads/items/300px/".$item->photo_file_1."' width=280px id='current_item_photo'/> &nbsp;&nbsp;";
							?>
							<br/>
							<?php
								for ($i = 1; $i <=4; $i++) {
									$fn = $item->getPhoto($i);
									if ($fn == "'no_image.jpg'")
										$fn = "no_image.jpg";
									if ($fn != '' && $fn != "no_image.jpg")
										echo "<img src='uploads/items/100px/$fn' width=65px onclick=\"document.getElementById('current_item_photo').src='uploads/items/300px/$fn';\" /> &nbsp;";
								}									
							?>							
						</td>
						<td valign="top" width="340px">
							<div style="text-align:justify; margin-left: 5px; height: 240px; overflow: hidden;">
								<b>Description: </b><br/><br/>
								<?php 
									$desc = preg_replace("/\n/","<br>",html_entity_decode($item->description));
									echo $desc;
								?>								
							</div>
							<div style='height: 20px;line-height: 20px;padding: 5px;'>
								<table width='100%'>
									<tr>
										<td align=left>
											<?php
											if ($_SESSION['is_logged_in']) {
											?>
											<button onclick="window.location = 'index.php?task=payment&id=<?php echo $item->id;?>'">Buy This Item</button>
											<?php
											} else {
											?>
											<button onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);">Buy This Item</button>
											
											<?php
											}
											?>
										</td>
										<td align=right>
											<button>Offer Another Amount</button>
										</td>
									</tr>
								</table>
							</div>									
						</td>						
					</tr>
					<tr>
						<td colspan=2><hr/></td>
					</tr>
					<tr>
						<td valign="top">
							<table cellpadding="2px">
								<tr>
									<td><b>ID:</b></td>
									<td><?php echo $item->id;?></td>
								</tr>
								<tr>
									<td><b>Seller:</b></td>
									<td><a href="index.php?task=view_user&id=<?php echo $seller->id;?>"><font color="#2f8dcb"><?php echo $seller->username?></font></a></td>
								</tr>
								<tr>
									<td><b>Listed on:</b></td>
									<td><?php echo $item->added_date;?></td>
								</tr>
							</table>
						</td>
						<td>
							<div id='fb-root'></div>
							<script src='http://connect.facebook.net/en_US/all.js'></script>
							<p><img src="images/facebook.jpg" onclick='postToFeed();'/></p>
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
					<tr>
						<td colspan=2><hr/></td>
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
		<td valign='top' width="340px" align="center">
			<div style="width: 340px;" class="heading">
				Proceeds from this item help...
			</div>			
			<div class="silo_preview" id="silo_preview"><?php echo $item->getSiloPreview();?></div>
			<div style="color: #000; padding-top: 5px; font-weight: bold;">Silo location</div>
			<div id="map_canvas" style="width: 340px; height: 170px;"></div>			
			<script>
				var myOptions = {
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						disableDefaultUI: true,
				        navigationControl: true,
				        navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
				        draggable: true,
				        scaleControl: false,
						scrollwheel: true
						};
				var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);		    
				var bounds = new google.maps.LatLngBounds();

				item_id = <?php echo $item->item_id;?>;
				longitude = <?php echo $item->silo->longitude;?>;
				latitude = <?php echo $item->silo->latitude;?>;	
				pos = new google.maps.LatLng(longitude, latitude);
				bounds.extend(pos);

		       	var item_marker = new google.maps.Marker({
		           	map: map,
					animation: google.maps.Animation.DROP,
					icon: 'images/orange_circle.png',
		           	position: pos
		       	});
			 	google.maps.event.addListener(item_marker, 'click', (function(marker, item_id) {
			        return function() {
			        }
			      })(item_marker, item_id));								

				map.fitBounds(bounds);
				zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
				            if (this.getZoom() > 14) // Change max/min zoom here
				                this.setZoom(14);	
				});
			
			    // google.maps.event.trigger(item_marker,"click");
			    item_marker.setAnimation(google.maps.Animation.BOUNCE);			
			</script>
		</td>
	</tr>
</table>
					
<?php
	}
?>
