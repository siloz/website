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
		$end_date = param_post('end_date');				
		$goal = param_post('goal');
		$purpose = param_post('purpose');
		$description = param_post('description');
		$admin_notice = param_post('admin_notice');

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
		if ($silo_cat_id == '-1') {
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
			$Silo = new Silo();
			$Silo->admin_id = $admin_id;
			$Silo->name = $name;
			$Silo->shortname = $shortname;
			$Silo->paypal_account = $paypal_account;
			$Silo->financial_account = $financial_account;
			$Silo->bank_name = $bank_name;
			$Silo->bank_account_name = $bank_account_name;
			$Silo->bank_account_number = $bank_account_number;
			$Silo->bank_routing_number = $bank_routing_number;
			$Silo->org_name = $org_name;
			$Silo->ein = $ein;
			$Silo->issue_receipts = $issue_receipts;
			$Silo->title = $title;
			$Silo->phone_number = $phone_number;
			$Silo->address = $address;
			$Silo->longitude = $longitude;
			$Silo->latitude = $latitude;
			$Silo->silo_cat_id = $silo_cat_id;
			$Silo->start_date = $start_date;
			$Silo->schedule_end_date = $end_date;
			$Silo->goal = $goal;
			$Silo->admin_notice = $admin_notice;
			
			$actual_id = $Silo->Save();
			
			
			
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
			else {
				$sql = "UPDATE silos SET id = '$id' WHERE silo_id = $actual_id";
				mysql_query($sql);				
			}					
		}
		
		if (strlen($err) == 0) {
			?>
			<script>
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
					<?php
						echo "<script>var categories = new Array();";
						$sql = "SELECT * FROM silo_categories ORDER BY type, subtype, subsubtype";
						$res = mysql_query($sql);
						while ($row = mysql_fetch_array($res)) {
							echo "var cat = new Array('".$row['type']."', '".$row['subtype']."', '".$row['subsubtype']."', '".$row['silo_cat_id']."');";
							echo "categories.push(cat);";
						}							
						echo "</script>";
					?>													
					<input name="silo_cat_id" id="silo_cat_id" type="hidden" value="<?php if (intval($silo_cat_id) > 0) echo $silo_cat_id; else echo '-1';?>" />
					<?php
						$silo_cat = array();
						if (intval($silo_cat_id) > 0) {					
							$silo_cat = mysql_fetch_array(mysql_query("SELECT * FROM silo_categories WHERE silo_cat_id = $silo_cat_id"));
						}
					?>
					
					<select name="type" id="type" style="width: 300px">
						<option value="">Select Silo type</option>
						<option value="Community" <?php if ($silo_cat['type'] == 'Community') echo "selected";?> >Community</option>
						<option value="Personal" <?php if ($silo_cat['type'] == 'Personal') echo "selected";?>>Personal</option>
					</select>
					<br/>
					<select name="subtype" id="subtype" style="width: 300px; margin-top: 2px;">
						<?php 
							if (intval($silo_cat_id) > 0) {
								$subtypes = mysql_query("SELECT DISTINCT subtype FROM silo_categories WHERE type = '".$silo_cat['type']."'");
								while ($subtype = mysql_fetch_array($subtypes)) {
									if ($subtype[0] == $silo_cat['subtype'])
										echo "<option value='$subtype[0]' selected>$subtype[0]</option>";
									else
										echo "<option value='$subtype[0]'>$subtype[0]</option>";									
								}
							}
							else {
						?>
						<option value="">---</option>
						<?php
							}
						?>
					</select>
					<br/>
					<select name="subsubtype" id="subsubtype" style="width: 300px; margin-top: 2px;">
						<?php 
							if (intval($silo_cat_id) > 0) {
								$subsubtypes = mysql_query("SELECT DISTINCT subsubtype FROM silo_categories WHERE type = '".$silo_cat['type']."' AND subtype='".$silo_cat['subtype']."'");
								while ($subsubtype = mysql_fetch_array($subsubtypes)) {
									if ($subsubtype[0] == $silo_cat['subsubtype'])
										echo "<option value='$subsubtype[0]' selected>$subsubtype[0]</option>";
									else
										echo "<option value='$subsubtype[0]'>$subsubtype[0]</option>";									
								}
							}
							else {
						?>
						<option value="">---</option>
						<?php
							}
						?>
					</select>					
					<script>
						$("#type").change(function() {
							var current_type = document.getElementById("type").value;
							if (current_type == 'Personal') {
								document.getElementById("org_name").value = "Personal Silo by <?php echo $user['fullname']; ?>";
								document.getElementById("address").value = "<?php echo $user['address'];?>";
								document.getElementById("paypal_account").value = "<?php echo $user['email'];?>";
								document.getElementById("title").value = "Administrator";
								document.getElementById("phone_number").value = "<?php echo $user['phone'];?>";

								document.getElementById("org_name").disabled = true;
								document.getElementById("address").disabled = true;
								document.getElementById("title").disabled = true;
								document.getElementById("phone_number").disabled = true;
							}
							else {
								// document.getElementById("org_name").value = "";
								// document.getElementById("paypal_account").value = "";
								// document.getElementById("address").value = "";
								// document.getElementById("title").value = "";
								// document.getElementById("phone_number").value = "";

								document.getElementById("org_name").disabled = false;
								document.getElementById("address").disabled = false;
								document.getElementById("title").disabled = false;
								document.getElementById("phone_number").disabled = false;								
							}
							var subtype = document.getElementById("subtype");
							subtype.options.length = 0;
							var i;
							var j = 0;
							subtype.options[0] = new Option("Select a Category", "", true);
							j++;
							var current_subtype = "";
							for (i = 0; i < categories.length; i++) {
								if (categories[i][0] == current_type && categories[i][1] != current_subtype) {
									subtype.options[j] = new Option(categories[i][1], categories[i][1]);
									current_subtype = categories[i][1];
									j += 1;
								}
							}
							document.getElementById("subsubtype").options.length = 1;
							document.getElementById("subsubtype").options[0] = new Option("---", "");							
						});

						$("#subtype").change(function() {
							var current_type = document.getElementById("type").value;
							var current_subtype = document.getElementById("subtype").value;
							var subsubtype = document.getElementById("subsubtype");
							subsubtype.options.length = 0;
							var i;
							var j = 0;
							subsubtype.options[0] = new Option("Select...", "", true);
							j++;							
							var current_subsubtype = "";
							for (i = 0; i < categories.length; i++) {
								if (categories[i][0] == current_type && categories[i][1] == current_subtype && categories[i][2] != current_subsubtype) {
									subsubtype.options[j] = new Option(categories[i][2], categories[i][2]);
									current_subsubtype = categories[i][2];
									j += 1;
								}
							}
						});
						
						$("#subsubtype").change(function() {
							var current_type = document.getElementById("type").value;
							var current_subtype = document.getElementById("subtype").value;
							var current_subsubtype = document.getElementById("subsubtype").value;
							for (i = 0; i < categories.length; i++) {
								if (categories[i][0] == current_type && categories[i][1] == current_subtype && categories[i][2] == current_subsubtype) {
									document.getElementById("silo_cat_id").value = categories[i][3];
								}
							}
						});						
							
					</script>
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
				<td>End date <font color='red'>*</font></td>
				<td><input type="text" name="end_date" id="end_date" style="width : 150px" value='<?php echo $end_date; ?>' class=".ui-datepicker"/></td>			
				
				<script>
					$(function() {
						$( "#end_date" ).datepicker();
					});
				</script>
			</tr>
			<tr>
				<td>Goal <font color='red'>*</font></td>
				<td><input type="text" name="goal" style="width : 150px" value='<?php echo $goal; ?>'/> USD</td>			
			</tr>
			<tr>
				<td>Purpose <font color='red'>*</font></td>
				<td><textarea name="purpose" style="width : 300px; height: 50px"/><?php echo $purpose; ?></textarea></td>			
			</tr>
			<tr>
				<td>Description <font color='red'>*</font></td>
				<td><textarea name="description" style="width : 300px; height: 100px"/><?php echo $description; ?></textarea></td>			
			</tr>
			<tr>
				<td>Admin notice <font color='red'>*</font></td>
				<td><textarea name="admin_notice" style="width : 300px; height: 100px"/><?php echo $admin_notice; ?></textarea></td>			
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
