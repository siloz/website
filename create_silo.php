<?php
if ($_SESSION['is_logged_in'] != 1) {
	echo "<script>window.location = 'index.php';</script>";
}	
else {
	$err = "";
	$today = date('Y-m-d')."";
	if (mysql_num_rows(mysql_query("SELECT * FROM silos WHERE admin_id = ".$_SESSION['user_id']." AND end_date >= '".$today."'")) > 0) {
		echo "<script>window.location = 'index.php?task=manage_silo';</script>";		
	}	
	if (param_post('publish') == 'Publish') {
			
		$admin_id = $_SESSION['user_id'];
		$name = param_post('name');
		$shortname = trim(param_post('shortname'));		
		$paypal_account = param_post('paypal_account');
		$financial_account_type = param_post('financial_account_type');
		$bank_name = param_post('bank_name');
		$bank_account_number = param_post('bank_account_number');
		$reenter_bank_account_number = param_post('reenter_bank_account_number');
		$bank_routing_number = param_post('bank_routing_number');
		$reenter_bank_routing_number = param_post('reenter_bank_routing_number');
		$ein = param_post('ein');		
		$issue_receipts = param_post('issue_receipts');		
		$org_name = param_post('org_name');		
		$title = param_post('title');
		$phone_number = param_post('phone_number');
		$address = param_post('address');
		$silo_cat_id = param_post('silo_cat_id');
		$start_date = $today;				
		$goal = param_post('goal');
		$purpose = param_post('purpose');
		if($_REQUEST["duration"]){
			$end_date = Common::AddDays($_REQUEST["duration"]);
			
		}
		
		
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
		$sql = "SELECT * FROM silos WHERE shortname = '$shortname'";
		if (mysql_num_rows(mysql_query($sql)) > 0) {
			$err .= "Silo's short name is already used by another silo. <br/>";
		}
		if ($silo_cat_id == '') {
			$err .= 'Please select a Silo type.<br/>';		
		}		
		if (strlen(trim($org_name)) == 0) {
			$err .= "Silo's organization name must not be empty. <br/>";
		}
		
		if ($financial_account_type  == 'PayPal' && strlen(trim($paypal_account)) >0 && !is_valid_email($paypal_account)) {
			$err .= 'PayPal account is invalid.<br/>';		
		}

		if ($financial_account_type != 'PayPal' &&  (strlen(trim($bank_account_number)) == 0 || strlen(trim($bank_routing_number)) == 0)) {
			$err .= "Empty Bank Account/Routing number. <br/>";
		}		
		if ($bank_account_number != $reenter_bank_account_number) {
			$err .= 'Bank Account number does not match.<br/>';		
		}
		if ($bank_routing_number != $reenter_bank_routing_number) {
			$err .= 'Bank Routing number does not match.<br/>';		
		}

		if (strlen(trim($address)) == 0) {
			$err .= 'Address must not be empty.<br/>';		
		}
		if (strlen(trim($title)) == 0) {
			$err .= "Your title must not be empty. <br/>";
		}
		if (strlen(trim($phone_number)) == 0) {
			$err .= "Phone number must not be empty. <br/>";
		}
		if (strlen(trim($purpose)) > 250) {
			$err .= "The organization purpose is more than 250 characters.<br/>";
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
		if (strlen(trim($goal)) == 0) {
			$err .= "Silo's goal must be set.<br/>";		
		}	
		else if (!is_numeric($goal)) {
			$err .= "Silo's goal is not a valid number.<br/>";
		}
		else if (floatval($goal) < 0) {
			$err .= "Silo's goal is negative.<br/>";
		}
		else if (floatval($goal) > 100000000) {
			$err .= "Silo's goal exceeds the allowed maximum.<br/>";
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
			$status = "active";
			$Silo = new Silo();
			$Silo->admin_id = $admin_id;
			
			include("include/set_silo_params.php");
			$actual_id = $silo_id;
			
			
			
			$allowedExts = array("png", "jpg", "jpeg", "gif");
			
			
			if ($_FILES['silo_photo']['name'] != '') {
				$ext = end(explode('.', strtolower($_FILES['silo_photo']['name'])));
				if (!in_array($ext, $allowedExts)) {
					$err .= $_FILES['silo_photo']['name']." is invalid file.<br/>";
				}
				else {
					$photo = new Photo();
					$photo->upload($_FILES['silo_photo']['tmp_name'], 'silos', $id.".jpg");
					$Silo->photo_file = $id.".jpg";
					$Silo->Save();
				}
			}					
		}
		
		if (strlen($err) == 0) {
			?>
			<script>
				alert("shot");
				window.location = 'index.php?task=manage_silo';
				
				
			</script>
			<?php
		}
	}
	
	$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$_SESSION['user_id']));
	//die(print_r($user));
?>
<form enctype="multipart/form-data"  name="create_silo" class="create_account_form" method="POST">
	<input type="hidden" name="task" value="create_silo"/>
		
	<div class="create_account_form" style="width: 500px">
		<table style="margin:auto">
			<tr>
				<td colspan="2" align="center">
					<h1>Create a Silo</h1>
				</td>
			</tr>
			<tr>
				<td colspan=2 align="left">
					<?php
						if (strlen($err) > 0) {
							echo "<font color='red'><b>".$err."</b></font>";
						}
					?>
				</td>
			</tr>		
			<tr>
				<td>Silo name <font color='red'>*</font></td>
				<td><input type="text" name="name" style="width : 300px" value='<?php echo $name; ?>'/></td>			
			</tr>
			<tr>
				<td>Silo short name <font color='red'>*</font></td>
				<td><input type="text" name="shortname" style="width : 300px" value='<?php echo $shortname; ?>'/></td>			
			</tr>
			<tr>
				<td valign="top">Silo Type <font color='red'>*</font></td>
				<td>
					<select id="silo_cat_id" name="silo_cat_id">
						<option value="">-- Please Select --</option>
						<option value="1">Education</option>
						<option value="2">Local Youth Sports</option>
						<option value="3">Neighborhood and Civic</option>
						<option value="4">Other</option>
						<option value="5">Non-Profit Organizations</option>
						<option value="6">Religious</option>
					</select>
				</td>			
			</tr>
			<tr>
				<td align="left">
					<table>
						<tr>
							<td width="30px">
								<input type="radio" name="financial_account_type" value="PayPal"/>
							</td>
							<td>
								<b>PayPal Account</b>
							</td>
						</tr>
					</table>
				</td>
				<td><input type="text" name="paypal_account" id="paypal_account" style="width : 300px; font-weight: bold" value='<?php echo $paypal_account; ?>'/></td>			
			</tr>
			<tr>
				<td align="left">
					<table>
						<tr>
							<td width="30px" valign="top">
							</td>
							<td>
								<b>Bank Account</b><br/>
								Bank Name <br/>
								Account Number <br/>
								Routing Number <br/>
							</td>
						</tr>
					</table>					
				</td>
				<td valign="top" align="left">					
					Checking <input type="radio" name="financial_account_type" value="Checking Account" style="width: 50px" checked/>
					Saving <input type="radio" name="financial_account_type" value="Saving Account" style="width: 50px"/>					
					<br/><input type="text" name="bank_name" id="bank_name" style="width : 300px; font-weight: bold" value='<?php echo $bank_name; ?>'/>
					<br/><input type="text" name="bank_account_number" id="bank_account_number" style="width : 120px; font-weight: bold; margin-top:2px;" value='<?php echo $bank_account_number; ?>'/> Re-enter <input type="text" name="reenter_bank_account_number" id="reenter_bank_account_number" style="width : 120px; font-weight: bold; margin-top: 2px;" value='<?php echo $reenter_bank_account_number; ?>'/>
					<br/><input type="text" name="bank_routing_number" id="bank_routing_number" style="width : 120px; font-weight: bold; margin-top: 2px;" value='<?php echo $bank_routing_number; ?>'/> Re-enter <input type="text" name="reenter_bank_routing_number" id="reenter_bank_routing_number" style="width : 120px; font-weight: bold; margin-top: 2px;" value='<?php echo $reenter_bank_routing_number; ?>'/>
					
				</td>			
			</tr>												
			<tr>
				<td>EIN (Emp. ID) for Tax-Ded.</td>
				<td>
					<input type="text" name="ein" id="ein" style="width : 200px" value='<?php echo $ein; ?>'/>
					<button type="button" onclick="verify_ein();">Verify EIN</button>
					<script>
					function verify_ein() {
						$.post(<?php echo "'".API_URL."'"; ?>, 
							{	
								request: 'verify_ein',
								ein: document.getElementById('ein').value 
							}, 
							function (data) {
								if ($(data).find('status').text() == 'ok') {
									alert("EIN is valid");
									document.getElementById('org_name').value = $(data).find('CompanyName').text();
									document.getElementById('address').value = $(data).find('Address').text() + ", " +  $(data).find('City').text() + ", " +  $(data).find('State').text() + " " +  $(data).find('Zip').text();
								}
								else {
									alert($(data).find('npres\\:errmsg').text());
								}
							}
						);
						
					}
					</script>
				</td>			
			</tr>
			<tr>
				<td colspan="2">
					Issue Emailed Tax-Ded. Receipts w/Donations/Sales? 
					&nbsp;&nbsp;&nbsp;
					Yes <input type="radio" name="issue_receipts" value="1" style="width: 50px;" checked />
					No <input type="radio" name="issue_receipts" value="0" style="width: 50px;"/>
				</td>
			</tr>
			<tr>
				<td>Name of the organization <font color='red'>*</font></td>
				<td><input type="text" name="org_name" id="org_name" style="width : 300px" value='<?php echo $org_name; ?>'/></td>			
			</tr>			
			<tr>
				<td>Official address <font color='red'>*</font></td>
				<td><input type="text" name="address" id="address" style="width : 300px" value='<?php echo $address; ?>'/></td>			
			</tr>						
			<tr>
				<td>Your title <font color='red'>*</font></td>
				<td><input type="text" name="title" id="title" style="width : 150px" value='<?php echo $title; ?>'/></td>			
			</tr>						
			<tr>
				<td>Official telephone number <font color='red'>*</font></td>
				<td><input type="text" name="phone_number" id="phone_number" style="width : 150px" value='<?php echo $phone_number; ?>'/></td>			
			</tr>						
			<tr>
				<td>Duration <font color='red'>*</font></td>
				<td>
					<?php
						if(!$_REQUEST["duration"]){$_REQUEST["duration"] = 3;} 
						for($i=1; $i<4; $i++){
							$days = ($i * 7); 
							if($_REQUEST["duration"] == $days){$checked = "checked=\"checked\"";}
							else{$checked = '';}
					?>
						<input type="radio" name="duration" id="duration"  value="<?= $days ;?>" <?= $checked ;?> /><?= $i ;?> Week
					 <?php } ?>
				</td>			
			</tr>
			<tr>
				<td>Goal <font color='red'>*</font></td>
				<td><input type="text" name="goal" style="width : 150px" value='<?php echo $goal; ?>'/> USD</td>			
			</tr>
			<tr>
				<td>Organization and fundraiser purpose <font color='red'>*</font></td>
				<td><textarea name="purpose" style="width : 300px; height: 50px"/><?php echo $purpose; ?></textarea></td>			
			</tr>
			<tr>			
				<td>Photo </td>
				<td><input name="silo_photo" type="file"  style="width : 300px"/></td>
			</tr>		
			<tr>
				<td colspan="2"><br/></td>
			</tr>				
			<tr>
				<td colspan="2" align="center">
					<button type="submit" value="Publish" name="publish">Publish this Silo</button>
				</td>
			</tr>
		</table>
	</div>
</form>
<?php
}
?>
