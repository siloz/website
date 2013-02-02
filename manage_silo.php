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
?>



<div class="heading" style="padding-bottom:5px;">
	<table width="940px" style="border-spacing: 0px;">
		<tr>
			<td width="700px">
				<span style='font-size: 16px; font-weight: bold'><?php echo $Silo->name ?></span>
			</td>
			<td align="center">
				<a href="index.php?task=manage_silo_admin" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">Administrate</a>
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
				<a href="index.php?task=manage_silo" style="font-size: 12px; font-weight: bold; background: transparent; border: 0px; color: #fff">Home</a>
		</tr>
	</table>
</div>

<table width="100%">
	<tr>
		<td>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'home' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=home&id=<?php echo $Silo->id;?>'">Home</button>			
			<button type="button" style="font-size: 12px; <?php echo ($view == 'feed' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=feed'">Feed</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'notifications' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=notifications'">Notifications <?php if ($num_notifications > 0) echo "(".$num_notifications.")"; ?></button>				
			<button type="button" style="font-size: 12px; <?php echo ($view == 'members' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=members'">Members</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'items' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=items'">Items</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'map' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=manage_silo&view=map'">Map</button>
		</td>
		<td valign="center" align="right">
			<?php
				$new_sort_order = '';
				$sort_order = param_get('sort_order');
				$img1_path = 'images/none.png';
				$img2_path = 'images/none.png';			
				$order_by = param_get('sort_by');	
				if ($sort_order == 'asc') {
					$new_sort_order = '&sort_order=desc';
					if ($order_by == 'date')
						$img2_path = 'images/up.png';
					else if ($order_by != '')
						$img1_path = 'images/up.png';
				}
				else {
					$new_sort_order = '&sort_order=asc';										
					if ($order_by == 'date')
						$img2_path = 'images/down.png';
					else if ($order_by != '')
						$img1_path = 'images/down.png';
				}
				if ($view == 'items') {
					echo "<b>sort by <a href=index.php?task=manage_silo&view=items&sort_by=price$new_sort_order&id=$silo_id class=simplebluelink>price <img src=$img1_path></a> or <a href=index.php?task=manage_silo&view=items&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>list date <img src=$img2_path></a></b>";
				}
				else if ($view == 'members') {
					echo "<b>sort by <a href=index.php?task=manage_silo&view=members&sort_by=name$new_sort_order&id=$silo_id class=simplebluelink>username <img src=$img1_path></a> or <a href=index.php?task=manage_silo&view=members&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>join date <img src=$img2_path></a></b>";
				}
				else if ($view == 'donations') {
					echo "<b>sort by <a href=index.php?task=manage_silo&view=donations&sort_by=goal$new_sort_order&id=$silo_id class=simplebluelink>goal <img src=$img1_path></a> or <a href=index.php?task=manage_silo&view=donations&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>date <img src=$img2_path></a></b>";
				}
			?>
		</td>
	</tr>
</table>
<hr/>				

<?php
	
	if ($view == 'home') {
		if (strlen($err) > 0) {
			echo "<br/><font color='red'><b>ERROR: ".$err."</b></font>";
		}	
?>
<form enctype="multipart/form-data"  name="manage_silo_form" class="manage_silo_form" method="POST">
		<input type="hidden" name="task" value="manage_silo"/>
		
		<table cellpadding="10px">
			<tr>
				<td valign="top" width="300px">
					<img src="<?php echo 'uploads/silos/300px/'.$Silo->photo_file;?>" width="300px"/>
					<br/><br/>
					<b>Upload new photo: </b><input name="silo_photo" type="file"/>
					<br/><br/>
					<table>											
						<tr>
							<td><b>Start Date: </b></td>
							<td>
							<?php
								echo $Silo->start_date;
								echo "<input type=hidden name=start_date value='".$Silo->start_date."' />";										
							?>
							</td>
						</tr>
						<tr>
							<td><b>End Date: </b></td><td><input type="text" name="end_date" id="end_date" style="width : 100px" value='<?php echo $Silo->end_date; ?>' class=".ui-datepicker"/></td>
							<script>
								$(function() {
									$( "#end_date" ).datepicker();
								});
							</script>
						</tr>
						<tr>
							<td colspan=2><br/></td>
						</tr>
						<tr>
							<td><b>Goal: </b></td>
							<td>
							<?php 
								echo money_format('%(#10n', floatval($Silo->goal));
							?>
							</td>
						</tr>
						<tr>
							<td><b>Pledged: </b></td>
							<td>
								<?php
									echo money_format('%(#10n', $Silo->getPledgedAmount());
								?>
							</td>
						</tr>
						<tr>
							<td><b>Collected: </b></td>
							<td>
								<?php
									$collected = $Silo->getCollectedAmount();
									$pct = round($collected*100.0/floatval($Silo->goal),1);															
									echo money_format('%(#10n', $collected)." (reached $pct %)";
								?>								
							</td>
						</tr>
						<tr>
							<td><b>Progress: </b></td>
							<td>
								<?php
									echo "<div style='width: 220px; height: 15px; border: 1px solid #2F8ECB;'><div style='width: $pct%; height:12px; background: #2F8ECB;'></div></div>"									
								?>								
							</td>
						</tr>		
						<tr>
							<td><b>Deadline: </b></td>
							<td>
								<?php
									$pct_day = round(100*max(0,(time() - strtotime($Silo->start_date))*1.0/(strtotime($Silo->end_date) - strtotime($Silo->start_date))));

									echo "<div style='width: 220px; height: 15px; border: 1px solid #2F8ECB;'><div style='width: $pct_day%; height:15px; background: #2F8ECB;'></div></div>"
								?>
							</td>
						</tr>				
										
					</table>
				</td>
				
				
				<td valign="top" width="650px">
					<table>
						<tr>
							<td colspan=2 valign="center">
								<?php 
									echo "<span style='font-size:14px; font-weight: bold;'>".$Silo->type." > ".$Silo->subtype." > ".$Silo->subsubtype."</span>";
								?>
								<button type="submit" name="update" value="Update" style="float:right">Update Silo</button>
							</td>	
						</tr>
						<tr>
							<td valign="center" style="width: 120px;"><b>Silo Full Name: </b></td>
							<td><input type="text" name="name" style="width : 480px" value='<?php echo $Silo->name; ?>'/></td>
						</tr>
						<tr>
							<td valign="center"><b>Silo Short Name: </b></td>
							<td><input type="text" name="shortname" style="width : 480px" value='<?php echo $Silo->shortname; ?>'/></td>
						</tr>						
						<tr>
							<td>
								<b>Address:</b>
							</td>
							<td>
								<input type="text" name="address" style="width : 480px" value='<?php echo $Silo->address; ?>'/>
							</td>
						</tr>
						<tr>
							<td>
								<b>Organization:</b><br/>
							</td>
							<td>
								<input type="text" name="org_name" style="width : 480px" value='<?php echo $Silo->org_name; ?>'/>
							</td>
						</tr>
						<tr>
							<td>
								<b>PayPal Account:</b>
							</td>
							<td>
								<input type="text" name="paypal_account" style="width : 150px" value='<?php echo $Silo->paypal_account; ?>'/>
							</td>
						</tr>	
						<tr>
							<td>
								<b>Bank Account:</b>
							</td>
							<td>
								Checking <input type="radio" name="financial_account_type" value="Checking" style="width: 50px" <?php if ($Silo->financial_account=='Checking')  echo 'checked';?>/>
								Saving <input type="radio" name="financial_account_type" value="Saving" style="width: 50px" <?php if ($Silo->financial_account=='Saving')  echo 'checked';?>/>													
							</td>
						</tr>
						<tr>
							<td>
								<b>Bank Name:</b>
							</td>
							<td>
								<input type="text" name="bank_name" id="bank_name" style="width : 300px; font-weight: bold" value='<?php echo $Silo->bank_name; ?>'/>
							</td>
						</tr>
						<tr>
							<td>
								<b>Account Number:</b>
							</td>
							<td>
								<input type="text" name="bank_account_number" id="bank_account_number" style="width : 120px; font-weight: bold; margin-top:2px;" value='<?php echo mask($Silo->bank_account_number); ?>'/> Re-enter <input type="text" name="reenter_bank_account_number" id="reenter_bank_account_number" style="width : 120px; font-weight: bold; margin-top: 2px;"/>
							</td>
						</tr>
						<tr>
							<td>
								<b>Routing Number:</b>
							</td>
							<td>
								<input type="text" name="bank_routing_number" id="bank_routing_number" style="width : 120px; font-weight: bold; margin-top: 2px;" value='<?php echo mask($Silo->bank_routing_number); ?>'/> Re-enter <input type="text" name="reenter_bank_routing_number" id="reenter_bank_routing_number" style="width : 120px; font-weight: bold; margin-top: 2px;"/>
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
							<td colspan=2><b>Purpose: </b>
							<?php
								echo $Silo->purpose;
							?>
							</td>
						</tr>																		
					</table>
					<br/>
					<table>
						<tr>
							<td valign=top>
								<b>Description:</b>
								<br/>
								<textarea style="width: 390px; height: 150px" name="description" ><?php echo $Silo->description;?></textarea>					
							</td>
							<td valign=top>
								<b>Admin Notices:</b>
								<br/>
								<textarea style="width: 200px; height: 150px" name="admin_notice" ><?php echo $Silo->admin_notice;?></textarea>					
							</td>
						</tr>
					</table>						
				</td>				
			</tr>	
		</table>
	</form>
	
		<?php
	}
			if ($view == 'feed') {
				//NEW MEMBER
				$r = mysql_fetch_array(mysql_query("SELECT user_id, joined_date FROM silo_membership WHERE silo_id = $silo_id ORDER BY joined_date DESC LIMIT 1"));
				$s = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$r['user_id']));			
				$t = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silo_membership WHERE silo_id = $silo_id"));			
				if ($r != null) {
					$new_mem_photo = "<img src='uploads/members/100px/".$s['photo_file']."' width='100px'>";
					$new_mem_info = $s['fullname']." has joined this Silo. Thanks ".$s['fullname']." for joining! This Silo now has ".$t[0]." members.";
					$date = date('M d, Y', strtotime($r['joined_date']));
					$time = date('h:i A T', strtotime($r['joined_date']));
				}
				else {
					$new_mem_photo = "";
					$new_mem_info = "No member has joined yet";
					$date = date('M d, Y');
					$time = date('h:i A T');
				}
				$new_mem_date = "$date<br/>$time<br/>";
				echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>New Member</b></font></td><td width=120px valign=top>$new_mem_photo</td><td width=120px valign=top></td><td width=460px valign=top>$new_mem_info</td><td width=120px valign=top align=right>$new_mem_date</td></tr></table></div><br/>";
			
			
				//FUNDS COLLECTED
				$sql = "SELECT * FROM users WHERE user_id=$user_id";
				$res = mysql_query($sql);
				$user = mysql_fetch_array($res);
				$admin_photo = "<img src='uploads/members/100px/".$user['photo_file']."' width='100px'/>";
				$tmp = mysql_query("SELECT user_id, SUM(price) as total_received FROM items WHERE silo_id = $silo_id AND status = 'Funds Received' GROUP BY user_id ORDER BY added_date DESC LIMIT 3");
				$n = 0;
				$funds_info = "";
				while ($r = mysql_fetch_array($tmp)) {
					$s = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$r['user_id']));
					$funds_info .= $s['fullname']." has sent ".money_format('%(#10n', floatval($r['total_received'])).", ";				
				}
				if ($funds_info == "")
					$funds_info .= "No fund has been collected yet.";
				else
					$funds_info .= "... bringing the <b>Silo total to ".money_format('%(#10n', floatval($s2[0]))." collected</b>. Thanks everyone!";
				$date = date('M d, Y');
				$time = date('h:i A T');
				$funds_date = "$date<br/>$time<br/>";			
				echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>Funds Collected</b></font></td><td width=120px valign=top>$admin_photo</td><td width=120px valign=top></td><td width=460px valign=top>$funds_info</td><td width=120px valign=top align=right>$funds_date</td></tr></table></div><br/>";

				$milestone_info = "No milestone has been reached!";
				$pct_round = "";
				if ($pct >= 10) {
					$pct_round = (round($pct/10)*10)."%";
					$milestone_info = "<b>Silo has reached $pct_round of its Goal!</b><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum";
				}

				echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>Milestone Reached</b></font></td><td width=120px valign=top>$admin_photo</td><td width=120px valign=center align=center><font size=6><b>$pct_round</b></font></td><td width=460px valign=top>$milestone_info</td><td width=120px valign=top align=right>$funds_date</td></tr></table></div>";
			
			}
		
			//VIEW MEMBERS
			if ($view == 'members') {
				$order_by_clause = "";
				if ($order_by == 'name')
					$order_by_clause = " ORDER BY username $sort_order ";
				if ($order_by == 'date')
					$order_by_clause = " ORDER BY joined_date $sort_order ";			
				$sql = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM silo_membership WHERE silo_id = $silo_id) $order_by_clause";				
				$users = mysql_query($sql);
				echo "<table cellpadding='3px'>";
				$n = 0;
				while ($row = mysql_fetch_array($users)) {	
					if ($n == 0)
						echo "<tr>";
					$user_id = $row['user_id'];
					$tmp = mysql_query("SELECT SUM(price) FROM items WHERE deleted_date = 0 AND status = 'Pledged' AND silo_id = $silo_id AND user_id = ".$row['user_id']);					
					$pledged = mysql_fetch_array($tmp);
					if ($pledged[0] != null)
						$pledged = $pledged[0];
					else
						$pledged = 0;
					$tmp = mysql_query("SELECT SUM(price) FROM items WHERE deleted_date = 0 AND status <> 'Pledged' AND silo_id = $silo_id AND user_id = ".$row['user_id']);					
					$collected = mysql_fetch_array($tmp);
					if ($collected[0] != null)
						$collected = $collected[0];
					else
						$collected = 0;
					$date = substr($row['joined_date'],5,2).'/'.substr($row['joined_date'],8,2).'/'.substr($row['joined_date'],2,2);
					$cell = "<td><div class=plate id='user_".$row['user_id']."' style='color: #000; font-size: 11px;'><table width=100% height=100%><tr valign=top><td>";
					$cell .= "<form name='f$user_id' id='f$user_id' method='post' action=''><input type='hidden' name='user_id' value='$user_id'><input type='hidden' name='silo_id' value='$silo_id'><input type='hidden' name='delete_user' value='delete_$user_id'><a href='javascript:document.f$user_id.submit()' class='confirmation'><img src=images/delete.png style='margin-top: -5px; margin-left:-5px; margin-right: 5px;'></a><a href='index.php?task=view_user&id=$user_id'> <b>".$row['username']."</b></a></form><b>Member Since:</b>".$date."<br/><img height=100px width=135px src=uploads/members/100px/".$row['photo_file']." style='margin-bottom: 5px; margin-top: 5px;'><br/><b>Pledged: </b><span style='color: #f60'>$".$pledged."</span><br/><b>Sold/Donated: </b><span style='color: #f60'>$".$collected."</span><br/>View Items: <a href='index.php?task=view_user&id=".$row['user_id']."&silo_id=$silo_id'>This</a> | <a href=#><a href='index.php?task=view_user&id=$user_id'>All Silos</a></td></table></div></td>";
					echo $cell;					
					$n++;
					if ($n == 6) {
						echo "</tr>";
						$n = 0;
					}					
				}
				echo "</table>";
			}
			
			//VIEW ITEMS
			if ($view == 'items') {
				$order_by_clause = "";
				if ($order_by == 'price')
					$order_by_clause = " ORDER BY price $sort_order ";
				if ($order_by == 'date')
					$order_by_clause = " ORDER BY added_date $sort_order ";						
				$sql = "SELECT * FROM items INNER JOIN users USING (user_id) WHERE deleted_date = 0 AND silo_id = $silo_id $order_by_clause";
				$items = mysql_query($sql);
				$n = 0;
				echo "<table cellpadding='3px'>";			
				while ($row = mysql_fetch_array($items)) {
					if ($n == 0)
						echo "<tr>";
					$item_id = $row['item_id'];
					$delete_html = '';
					if ($row['status'] != 'Funds Sent' && $row['status'] != 'Funds Received')
						$delete_html = "<form name='f$item_id' id='f$item_id' method='post' action=''><input type='hidden' name='item_id' value='$item_id'><input type='hidden' name='delete_item' value='delete_$item_id'><a href='javascript:document.f$item_id.submit()' class='confirmation'><img src=images/delete.png style='margin-top: -5px; margin-left:-5px; margin-right: 5px;'></a>";
					$cell = "<td><div class=plate id='item_$item_id' style='color: #000; font-size: 11px;'>";
					$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'>$delete_html<a href='index.php?task=view_item&id=$item_id'><b>".substr($row['title'], 0, 40)."</b></a></form></div><img height=100px width=135px src=uploads/items/100px/".$row['photo_file_1']." style='margin-bottom: 3px'><div style='font-size: 11px; color: #000;'><b>Status: </b>".getStatusDropDown($item_id, $row['status'])."<br/><b>Member: </b><a href='index.php?task=view_user&id=".$row['user_id']."'>".$row['username']."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$row['price']."</b></span></td><td align=right><a href='index.php?task=view_item&id=$item_id'><i><b>more...</b></i></a></td></tr></table></div></td>";							
					echo $cell;					
					$n++;
					if ($n == 6) {
						echo "</tr>";
						$n = 0;
					}					
				}
				echo "</table>";		
			}
			
			//VIEW DONATIONS
			if ($view == 'donations') {
				$order_by_clause = "";
				if ($order_by == 'amount')
					$order_by_clause = " ORDER BY amount $sort_order ";
				if ($order_by == 'date')
					$order_by_clause = " ORDER BY sent_date $sort_order ";						
				$sql = "SELECT * FROM donations INNER JOIN users USING (user_id) WHERE deleted_date = 0 AND silo_id = $silo_id $order_by_clause";
				$donations = mysql_query($sql);
				$n = 0;
				echo "<table cellpadding='3px'>";			
				while ($don = mysql_fetch_array($donations)) {
					if ($n == 0)
						echo "<tr>";
					$donation_id = $don['donation_id'];
					$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$don['user_id']));
					$cell = "<td><div class=plate id='item_".$don['donation_id']."' style='color: #000;'>";
					$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><b>Donation Plate Title???</b></div><img height=100px width=135px src=uploads/members/100px/".$user['photo_file']." style='margin-bottom: 3px'><div style='color: #000;line-height: 120%;'><b>Status: </b>".$don['status']."<br/><b>Member: </b><a href='index.php?task=view_user&id=".$user['user_id']."'>".$user['username']."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$don['amount']."</b></span></td><td align=right><i><b>more...</b></i></td></tr></table></div></td>";							
					echo $cell;					
					$n++;
					if ($n == 6) {
						echo "</tr>";
						$n = 0;
					}					
				}
				echo "</table>";		
			}
			
			
			//NOTIFICATION
			if ($view == 'notifications') {		
				if ($num_notifications > 0) {
					echo "<br/><div style='color: #f60; font-size: 16px; font-weight: bold;'>There are $num_notifications funds sent but NOT yet received</div><br/>";		
					$html = "<table id='alternate_table'><tr><th>Item</th><th>Title</th><th>Price</th><th>Member</th><th>Sent date</th></tr>";
					while ($item = mysql_fetch_array($sent_items)) {
						$item_id = $item['item_id'];
						$mem = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));						
						$html .= "<tr><td><a class='bluelink' href='index.php?task=view_item&id=$item_id'>#$item_id</a></td><td><a class='bluelink'  href='index.php?task=view_item&id=$item_id'>".$item['title']."</a></td><td>".money_format('%(#10n', floatval($item['price']))."</td><td>".$mem['username']."</td><td>".$item['sent_date']."</td></tr>";
					}
					$html .= "</table>";
					echo $html;
				}
				else {
					echo "<div style='color: #2F8DCB; font-size: 16px; font-weight: bold;'>There is NO notification</div>";							
				}
			}
		?>
	
<?php
	}
?>
