<?php
if ($_SESSION['is_logged_in'] != 1) {
	echo "<script>create_silo_need_login();</script>";
}	
else {
	$user_id = $_SESSION['user_id'];	
	$user = new User($user_id);
	$title = "";
	$price = "";
	$item_cat_id = "0";
	$description = "";

	$err = "";
	if (param_post('submit') == 'Finish') {	
		$silo_id = param_post('silo_id');
		$silo = new Silo($silo_id);
		$title = param_post('title');
		$price = param_post('price');
		$item_cat_id = param_post('item_cat_id');
		$description = param_post('description');
		
		if (strlen(trim($title)) == 0) {
			$err .= "Item title must not be empty. <br/>";
		}
		if (strlen(trim($price)) == 0) {
			$err .= "Item price must not be empty. <br/>";
		}
		if (strlen($err) == 0) {
			$sql = "INSERT INTO items(silo_id, user_id, title, price, item_cat_id, description, status) VALUES (?,?,?,?,?,?,?);";
			$stmt->prepare($sql);			
			$status = "Pledged";
			$stmt->bind_param("sssssss", $silo->silo_id, $user_id, $title,$price,$item_cat_id, htmlentities($description, ENT_QUOTES), $status);
			$stmt->execute();
			$stmt->close();
			$allowedExts = array("png", "jpg", "jpeg", "gif");
			$actual_id = $db->insert_id;
			$id = "02".time()."0".$actual_id;
			
			for ($i=1; $i<=4; ++$i) {
				if ($_FILES['item_photo_'.$i]['name'] != '') {
					$ext = end(explode('.', strtolower($_FILES['item_photo_'.$i]['name'])));
					if (!in_array($ext, $allowedExts)) {
						$err .= $_FILES['item_photo_'.$i]['name']." is invalid file type.<br/>";
						break;
					}
					else {
						$photo = new Photo();
						$photo->upload($_FILES['item_photo_'.$i]['tmp_name'], 'items', $id."_".$i.".jpg");
						$sql = "UPDATE items SET photo_file_$i = '$id"."_"."$i.jpg', id = '$id' WHERE item_id = $actual_id";
						mysql_query($sql);

						$sql = "SELECT * FROM silo_membership WHERE silo_id = $silo_id AND user_id = $user_id";
						if (mysql_num_rows(mysql_query($sql)) == 0) {
							$sql = "INSERT INTO silo_membership(silo_id, user_id) VALUES (".$silo->silo_id.",".$user_id.")";
							mysql_query($sql);
						}
					}
				}							
			}
			if (strlen($err) > 0) {
				$sql = "DELETE FROM items WHERE id = $id";
				mysql_query($sql);
			}			
		}
		if (strlen($err) == 0) {
			$subject = "Thank you for joining ".$silo->name;
			$message = "<br/>Congratulations on joining silo <b>".$silo->name."</b> with your item - <b>$title</b> ($$price).<br/><br/>";
			$message .= "<h3>Getting Started</h3>";
			$message .= "Remember: you can share the silo you joined on <b>Facebook</b>, or, use our address book tool to generate an email to your frequent contacts to notify them of your fund-raiser’s need for member.  Click <a href='".ACTIVE_URL."/index.php?task=view_silo&id=".$silo->id."'>here</a> to notify your contacts.<br/><br/>";
			$message .= "We thank you for participating in your silo and for using siloz.com.<br/><br/>
						Sincerely,<br/><br/>
						Siloz Staff.";			
		    email_with_template($user->email, $subject, $message);
			
			echo "<script>window.location = 'index.php?task=view_silo&id=$silo_id';</script>";			
		}
	}
	else {
		$id = param_get('id');
		if ($id == '') {
			echo "<script>window.location = 'index.php';</script>";		
		}
		else {
			$silo = new Silo($id);
		}
	}		
?>
		<div class="heading">
			<b>Join this Silo: <?php echo "<a href=".ACTIVE_URL."/index.php?task=view_silo&id=".$silo->id.">".$silo->name."</a></b> (".$silo->type." Silo)";?>
		</div>
		<p>Please enter your item details below, and upload up to 4 images for your item.</p>
		<form enctype="multipart/form-data"  name="sell_on_siloz" class="my_account_form" method="POST">
			<input type="hidden" name="task" value="sell_on_siloz"/>
			<input type="hidden" name="silo_id" value="<?php echo $silo->id;?>"/>						
			<table width="80%" cellpadding="10px" align="center">
				<tr>
					<td colspan="2" align="center">
						<?php
							if (strlen($err) > 0) {
								echo "<font color='red'><b>".$err."</b></font>";
							}
						?>						
					</td>
				</tr>		
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td><b>Listing Title</b> </td>
								<td><input type="text" name="title" style="width : 300px" value='<?php echo $title; ?>'/></td>
							</tr>		
							<tr>
								<td><b>Price</b> </td>
								<td><input type="text" name="price" style="width : 100px" value='<?php echo $price; ?>'/></td>
							</tr>
							<tr>
								<td><b>Category</b> </td>
								<td>
									<select name="item_cat_id" style="width: 300px">
										<?php
											$sql = "SELECT * FROM item_categories";
											$res = mysql_query($sql);
											while ($ic = mysql_fetch_array($res)) {
												if ($ic['item_cat_id'] == $item_cat_id) {
													echo "<option value='".$ic['item_cat_id']."' selected>".$ic['category']."</option>";
												}
												else {
													if ($item_cat_id == 0 && $ic['category'] == 'Everything (default)')
														echo "<option value='".$ic['item_cat_id']."' selected>".$ic['category']."</option>";
													else
														echo "<option value='".$ic['item_cat_id']."'>".$ic['category']."</option>";
												}
											}							
										?>							
									</select>
								</td>
							</tr>
							<tr>
								<td><b>Description</b> </td>
								<td><textarea name="description" style="width: 300px; height: 50px"><?php echo $description; ?></textarea></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td><b>Photo file 1</b> </td>
								<td><input name="item_photo_1" type="file" style="width: 200px; height: 20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 2</b> </td>
								<td><input name="item_photo_2" type="file" style="width: 200px;height: 20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 3</b> </td>
								<td><input name="item_photo_3" type="file" style="width: 200px;height: 20px;"/></td>
							</tr>		
							<tr>
								<td><b>Photo file 4</b> </td>
								<td><input name="item_photo_4" type="file" style="width: 200px;height: 20px;"/></td>
							</tr>
						</table>
					</td>
				</tr>	
				<tr>
					<td colspan="2" align="center">
						<button type="submit" name="submit" value="Finish">Finish</button>
					</td>
				</tr>			
			</table>	
		</form>
		<!-- <div style='width: 75%; margin: auto'>
			<br/>
			<b>Note: </b> While we think we're the easiest site to use, regrettably, we don't always have the traffic to sell items.  We invite you to - in addition to our site - list and sell your item on any website or through off-line means (yard sale, etc.) you wish!  
			<ul>
				<li>If your item sells here first, remove those ads!</li>
				<li>If it sells elsewhere, change the status to 'Item Sold' or 'Payment Sent', as the case may be!</li>
			</ul>
		</div> -->
<?php
}
?>