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
				if (status == "Funds Received") {					
					document.getElementById("dropdown_" + item_id).innerHTML = status;				
				}
			}
		);
   	}
}
</script>

<?php
	function mask($number) {
		$s = substr($number, -4);
		for ($i = 0; $i < strlen($number) - 4; ++$i)
			$s = "*".$s;
		return $s;
	}
	function getStatusDropDown($item_id, $current) {
		if ($current == "Funds Sent") {
			$out = "<span id='dropdown_$item_id'><select style='font-size: 10px; width: 70%' onchange='updateStatus(this, $item_id)'>";
			$out .= "<option value='Funds Sent' selected>Funds Sent</option>";
			$out .= "<option value='Funds Received'>Funds Received</option>";			
			$out .= "</select></span>";
			return $out;
		}
		else {
			return $current;
		}		
	}
	$Silo = new Silo();
	$silo_id = $Silo->GetUserSiloId($_SESSION['user_id']);
	$Silo->Populate($silo_id);
	
	
	
	if (param_post('delete_item') != '') {
		$item_id = param_post('item_id');
		$item = mysql_fetch_array(mysql_query("SELECT * FROM items WHERE item_id = $item_id"));		
		$silo = mysql_fetch_array(mysql_query("SELECT * FROM silos WHERE silo_id = ".$item['silo_id']));		
		$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));	
		$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$silo['admin_id']));	
		
		$sql = "UPDATE items SET deleted_date = CURRENT_TIMESTAMP WHERE item_id = $item_id";
		mysql_query($sql);
		
		//EMAIL - MEMBER REMOVED
		$subject = "You item has been removed from Silo - ".$silo['name'];
		$message = $user['username'].", your item number #".$item['item_id'].": ".$item['title']." has been removed from silo ".$silo['name']." by the silo’s administrator.  Only items whose status was not ‘funds sent’ or funds received’ are able to be removed by members or silo administrators.  Therefore, you are not obliged to follow-through with payment to your silo administrator for your item.<br/><br/>";
		$message .= "Silo administrators have the prerogative to remove items at their discretion, for just cause.  See our Terms of Use and FAQ for more information on what constitutes ‘just cause’.  If you feel your item waswrongfully removed you can contact the silo administrator at ".$admin['email'].".  If you feel your silo administrator was not abiding our tenets of legality, respect, goodwill and inclusiveness, you can report inappropriate action to us by using the ‘contact us’ link in the footer of the website.<br/><br/>";
		$message .= "Thanks,<br/><br/>
					Siloz Staff.";			
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: noreply@siloz.com" . "\r\n" ."X-Mailer: PHP/" . phpversion();
	    $sent = mail($user['email'], $subject, $message, $headers);		
						
	}
	if (param_post('delete_user') != '') {
		$silo_id = param_post('silo_id');
		$user_id = param_post('user_id');
		
		$silo = mysql_fetch_array(mysql_query("SELECT * FROM silos WHERE silo_id = $silo_id"));		
		$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = $user_id"));	
		$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$silo['admin_id']));	

		$sql = "UPDATE items SET deleted_date = CURRENT_TIMESTAMP WHERE silo_id = $silo_id AND user_id = $user_id AND (status <> 'Funds Sent' AND status <> 'Funds Received')";
		mysql_query($sql);		
		$sql = "DELETE FROM silo_membership WHERE silo_id = $silo_id AND user_id = $user_id";
		mysql_query($sql);
			
		//EMAIL - MEMBER REMOVED
		$subject = "You have been removed from Silo - ".$silo['name'];
		$message = $user['username'].", you have been removed from silo ".$silo['name']." by the silo’s administrator.  Any items whose status was not ‘funds sent’ or funds received’ are active on the silo.  Any donations are active on the silo.  Please honor your pledge or donation promise by sending any outstanding amounts.<br/><br/>";
		$message .= "Silo administrators have the prerogative to remove members at their discretion, for just cause - or upon request.  See our Terms of Use and FAQ for more information on what constitutes ‘just cause’.  If you feel you were wrongfully removed you can contact the silo administrator at ".$admin['email'].".  If you feel your silo administrator was not abiding our tenets of legality, respect, goodwill and inclusiveness, you can report inappropriate action to us by using the ‘contact us’ link in the footer of the website.<br/><br/>";
		$message .= "Thanks,<br/><br/>
					Siloz Staff.";			
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: noreply@siloz.com" . "\r\n" ."X-Mailer: PHP/" . phpversion();
	    $sent = mail($user['email'], $subject, $message, $headers);		
	}
		
	if ($_SESSION['is_logged_in'] != 1) {
		echo "<script>window.location = 'index.php';</script>";
	}	
	else {
		$err = "";
		$admin_id = $_SESSION['user_id'];	
		$admin = new User($admin_id);
		$silo = $admin->getCurrentSilo();
		if (param_post('update') == 'Update') {		
			$name = param_post('name');
			$shortname = trim(param_post('shortname'));
			$address = param_post('address');
			$org_name = param_post('org_name');
			$silo_cat_id = param_post('silo_cat_id');
			$start_date = param_post('start_date');		
			$end_date = param_post('end_date');				
			$goal = param_post('goal');
			$purpose = param_post('purpose');
			$description = param_post('description');
			$admin_notice = param_post('admin_notice');
			$paypal_account = param_post('paypal_account');
			$financial_account_type = param_post('financial_account_type');
			$bank_name = param_post('bank_name');
			$bank_account_number = param_post('bank_account_number');
			$reenter_bank_account_number = param_post('reenter_bank_account_number');
			$bank_routing_number = param_post('bank_routing_number');
			$reenter_bank_routing_number = param_post('reenter_bank_routing_number');
			$phone_number = param_post('phone_number');
			
			if (strlen(trim($name)) == 0) {
				$err .= 'Silo name must not be empty.<br/>';		
			}
			if (strlen($shortname) == 0) {
				$err .= "Silo's short name must not be empty.<br/>";
			}
			if (strpos('|'.$shortname, ' ') > 0) {
				$err .= "Silo's short name must not contain space.<br/>";
			}
			else {
				if (strlen($shortname) > 30) {
					$err .= "Silo's short name cannot be more than 30 characters.<br/>";
				}
			}
			$sql = "SELECT * FROM silos WHERE shortname = '$shortname' AND silo_id <> $silo_id";
			if (mysql_num_rows(mysql_query($sql)) > 0) {
				$err .= "Silo's short name is already used by another silo. <br/>";
			}			
			if ($financial_account_type  == 'PayPal' && strlen(trim($paypal_account)) >0 && !is_valid_email($paypal_account)) {
				$err .= 'PayPal account is invalid.<br/>';		
			}

			if (!$paypal_account &&  (strlen(trim($bank_account_number)) == 0 || strlen(trim($bank_routing_number)) == 0)) {
				$err .= "Empty Bank Account/Routing number. <br/>";
			}		
			if ($bank_account_number != $reenter_bank_account_number && $bank_account_number != $Silo->bank_account_number) {
				$err .= 'Bank Account number does not match.<br/>';		
			}
			if ($bank_routing_number != $reenter_bank_routing_number && $bank_routing_number != $Silo->bank_routing_number) {
				$err .= 'Bank Routing number does not match.<br/>';		
			}
			
			if (strlen(trim($address)) == 0) {
				$err .= 'Address must not be empty.<br/>';		
			}
			if (strlen(trim($end_date)) > 0 && strlen(trim($start_date)) > 0) {
				if (strtotime($end_date) - strtotime($start_date) > 30*24*60*60) {
					$err .= "A Silo can only run up to 30 days. <br/>";
				}
				if (strtotime($end_date) - strtotime($start_date) < 0) {
					$err .= "End date has to be after start date. <br/>";
				}
			}
			else {
				$err .= "End date must not be empty. <br/>";
			}
			$adr = urlencode($address);
			$url = "http://maps.google.com/maps/geo?q=".$adr;
			$xml = file_get_contents($url);
			$geo_json = json_decode($xml, TRUE);
			if ($geo_json['Status']['code'] == '200') {
				$precision = $geo_json['Placemark'][0]['AddressDetails']['Accuracy'];
				$latitude = $geo_json['Placemark'][0]['Point']['coordinates'][0];
				$longitude = $geo_json['Placemark'][0]['Point']['coordinates'][1];
			} else {
				$err .= 'Invalid address.<br/>';
			}

			if (strlen($err) == 0) {
							

				include("include/set_silo_params.php");
				
				if ($_FILES['silo_photo']['name'] != '') {
					$allowedExts = array("png", "jpg", "jpeg", "gif");							
					$ext = end(explode('.', strtolower($_FILES['silo_photo']['name'])));
					if (!in_array($ext, $allowedExts)) {
						$err .= $_FILES['silo_photo']['name']." is invalid file type.";
					}
					else {
						$photo_file = $Silo->id.".jpg";
						$photo = new Photo();
						$photo->upload($_FILES['silo_photo']['tmp_name'], 'silos', $photo_file);
						$sql = "UPDATE silos SET photo_file = '$photo_file' WHERE id = '".$Silo->id."'";
						mysql_query($sql);
					}
				}				
			}
		}
		$Silo = new Silo();
		$silo_id = $Silo->GetUserSiloId($_SESSION['user_id']);
		$Silo = new Silo($silo_id);
		if ($Silo == null) {
			
			echo "<script> window.location = 'index.php?task=create_silo';</script>";			
		}
		$sql = "SELECT * FROM items WHERE deleted_date = 0 AND status = 'Funds Sent' AND silo_id = ".$Silo->silo_id." ORDER BY sent_date DESC";
		$sent_items = mysql_query($sql);
		$num_notifications = mysql_num_rows($sent_items);
		$view = param_get('view');		
		if ($view == '')
			$view = 'home';	
}
?>



<div class="heading" style="padding-bottom:5px;">
	<table align="right" width="300px" style="border-spacing: 0px; padding-right: 50px;">
		<tr>
			<td align="center">
				<a href="index.php?task=manage_silo_admin" style="font-size: 12px; font-weight: bold; background: transparent; border: 0px; color: #fff">Administrate</a>
			</td>	
			<td align="center">
				<span style="color: #fff">|</span>
			</td>					
			<td align="center">
				<a href="index.php?task=view_silo&id=<?php echo $Silo->id; ?>" target="_blank" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">Preview</a>
			</td>
			<td align="center">
				<span style="color: #fff">|</span>
			</td>
			<td align="center">
				<a href="index.php?task=manage_silo" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">Home</a>
		</tr>
	</table>
</div>

<table width="100%" style="padding-right: 50px;">
<tr>
<td rowspan="2">
<table cellpadding="5px">
	<tr>
		<td valign="top" align="middle" width="260px">
					<?php
						$collected = $silo->getCollectedAmount();
						$pct = round($collected*100.0/floatval($silo->goal),1);
						$pct_day = round(100*max(0,(time() - strtotime($Silo->start_date))*1.0/(strtotime($Silo->end_date) - strtotime($Silo->start_date))));
						$end_date = $silo->end_date;
						$end = strtotime("$end_date");
						$now = time();
						$timeleft = $end-$now;
						$daysleft = ceil($timeleft/86400);
						
						if ($daysleft > 1) { $dayplural = "Days"; } else { $dayplural = "Day"; }
					?>
		<div class='siloInfo'>
			<button type='button' class='buttonTitleInfo'><?php echo $silo->getTitle(); ?></button>
			<img src=<?php echo 'uploads/silos/'.$silo->photo_file;?> width='250px'/>
			<div class='bio'>

			<div class='floatL'><b>Goal:</b> <?php echo money_format('%(#10n', floatval($silo->goal));?> (<?=$pct?>%)</div>
			<div class='floatR'><b><?=$daysleft?> <?=$dayplural?> Left</b></div>
			<div class='floatL'><div class='padding'><b>Progress:</b> &nbsp;&nbsp;&nbsp;<div style='float: right; width: 160px; height: 12px; border: 1px solid #2F8ECB;'><div style='float: left; width: <?=$pct;?>%; height:12px; background: #2F8ECB;'></div></div></div></div>
			<div class='padding'>&nbsp;</div>
			<p><a href='index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalMembers();?> Members</a>, <a href='index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalItems();?> Items Pledged</a></p>
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
				<b>Silo Admin:</b> <br> <?php echo $admin->fullname; ?><br>
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
		
		<div id='fb-root'></div>
		<script src='http://connect.facebook.net/en_US/all.js'></script>
		<?php
			$url = ACTIVE_URL."/index.php?task=view_silo&id=$silo->id";
			$photo_url = ACTIVE_URL.'/uploads/silos/300px/'.$silo->photo_file;
			$name = $silo->name;
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
		      		name: "<?php echo $silo->type.' Silo: '.$name; ?>",
					caption: "Siloz.com - Commerce thats count",
		      		description: "<?php echo $description; ?>"
		    	});
		  	}
		</script>
		</td>
	</tr>
</table>
</td>
<td align="center" valign="top" style="padding-top: 10px;">

<table class="aconsole-metrics">
<tr>
<td rowspan="2" align="center">
<b><font size="4">Metrics</font></b>
</td>
<td valign="top">
<div class='floatL'><div class='padding'><b>Progress:</b> &nbsp; <div style='float: right; width: 100px; height: 15px; border: 1px solid #000;'><div style='float: left; width: <?=$pct;?>%; height:15px; background: #000;'></div></div></div></div>
</td>
<td rowspan="2" align="center">
Average Item Price: <?php $avgprice = $silo->getAvgItemPrice(); echo money_format('%(#10n', floatval($avgprice));?><br><br>
Listings per Member: <?php echo $silo->getAvgListings();?>
</td>
<td width="100px" rowspan="3">
</td>
</tr>
<tr>
<td align="center">
<div class='floatL'><div class='padding'><b>Deadline:</b> &nbsp;&nbsp; <div style='float: right; width: 100px; height: 15px; border: 1px solid #000;'><div style='float: left; width: <?=$pct_day;?>%; height:15px; background: #000;'></div></div></div></div>
</td>
</tr>
<tr>
<td align="center">
<b>Goal:</b> <?php echo money_format('%(#10n', floatval($silo->goal));?>
</td>
<td align="center">
<b>Pledged:</b> <?php echo money_format('%(#10n', $Silo->getPledgedAmount());?>
</td>
<td align="center">
<b>Collected:</b> <?php $collected = $Silo->getCollectedAmount(); $pct = round($collected*100.0/floatval($Silo->goal),1);	echo money_format('%(#10n', $collected);?>
</td>
</tr>
<tr>
<td colspan="3">
<?php
include "include/charts/charts.php";
echo InsertChart ( "include/charts/charts.swf", "include/charts/charts_library", "include/charts/aconsole-chart.php", 450, 125 );
?>
</td>
<td valign="top">
View:<br><br>
<div class="metrics-text-blue">
Members<br><br>
Money Raised
</div>
</td>
</tr>
</table>

<br>

<table class="aconsole-promote">
<tr>
<td width="15%" align="center">
<font size="4">Promote</font>
</td>
<td width="50%" align="center">
http://www.siloz.com/<?=$silo->getShortName(30);?>
</td>
<td rowspan="3" align="center" valign="top" class="promote-text-blue" style="padding-top: 30px;">
Share your silo on Facebook<br><br>
<img src="images/f_share.gif">
</td>
</tr>
<tr>
<td colspan="2" align="center" class="promote-text-blue" style="padding: 0px 60px;">
<form action="business_card.php" method="post" target="_blank">
	<input type="hidden" name="id" value="<?php echo $Silo->id;?>" />
	<input type="submit" class="promote-text-blue" style="font-size: 12px; font-weight: bold; background: transparent; border: 0px;" value="Print Promotional Business Cards" />
</form>
</td>
</tr>
<tr>
<td colspan="2" align="center" class="promote-text-blue" style="padding: 0px 60px;">
<a href="index.php?task=invite_promote&id=<?php echo $Silo->id; ?>" target="_blank">
Link to your e-mail address book via OpenInviter (to tell your friends)<br><br>
<img src="images/mail-icon.png"></a>
</td>
</tr>
</table>

</td>
</tr>
</table>
