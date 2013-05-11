<div class="edit_item" id="editItem_<?=$item_id?>" style="width: 800px">
	<div id="editItem_<?=$item_id?>_drag" style="float: right">
		<img id="editItem_<?=$item_id?>_exit" src="images/close.png"/>
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
			<input type="hidden" name="item_id" value=""/>						

			<table width="100%" cellpadding="10px" align="center">
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
										<option value="">Select an Item type</option>
										<?php
											$sql = "SELECT * FROM item_categories";
											$res = mysql_query($sql);
											while ($row = mysql_fetch_array($res)) {
												if ($row['item_cat_id'] == $item_cat_id) {
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
								<td><textarea name="description" style="width: 200px; height: 100px"><?php echo $description; ?></textarea></td>
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
						<input type="hidden" name="item_id" value="<?=$id_item?>">
						<button type="submit" name="item" value="Update">Update</button>
					</td>
				</tr>	
			</table>	
		</form>
		<script>
			$("#edit_item_button").click(function(event) {	
				document.getElementById('overlay').style.display='none';
				document.getElementById('editItem_<?=$item_id?>').style.display='none';
			});
		</script>		
	</div>
</div>

<div class="login" id="delItem_<?=$item_id?>" style="width: 300px;">
	<div id="delItem_<?=$item_id?>_drag" style="float:right">
		<img id="delItem_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<h2>Are you sure you want to delete this item? All actions are final and cannot be reversed.</h2>
			<br/><br>	
			<button type="submit" name="item" value="Delete">Yes, delete this item</button>
		</form>
	</div>
</div>

<div class="login" id="decItem_<?=$item_id?>" style="width: 300px;">
	<div id="decItem_<?=$item_id?>_drag" style="float:right">
		<img id="decItem_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<h2>Are you sure you want to decline this item?</h2>
			<br/><br>	
			<button type="submit" name="item" value="Decline">Yes, decline this item</button>
		</form>
	</div>
</div>

<div class="login" id="clearItem_<?=$item_id?>" style="width: 300px;">
	<div id="clearItem_<?=$item_id?>_drag" style="float:right">
		<img id="clearItem_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="id" value="<?=$id?>">
			<h2>Clear this item from your transaction console?</h2>
			<br><br>	
			<button type="submit" name="item" value="Clear">Yes, clear it</button>
		</form>
	</div>
</div>


<div class="login" id="seller_clearItem_<?=$item_id?>" style="width: 300px;">
	<div id="seller_clearItem_<?=$item_id?>_drag" style="float:right">
		<img id="seller_clearItem_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<h2>Clear this item from your transaction console?</h2>
			<br><br>	
			<button type="submit" name="item" value="Seller Clear">Yes, clear it</button>
		</form>
	</div>
</div>

<div class="login" id="acceptOffer_<?=$item_id?>" style="width: 300px;">
	<div id="acceptOffer_<?=$item_id?>_drag" style="float:right">
		<img id="acceptOffer_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="seller_id" value="<?=$user_id?>">
			<h2>Accept this offer? Once you accept it, this price will show up only for the user</h2>
			<br/><br>	
			<button type="submit" name="offer" value="Accept">Accept offer</button>
		</form>
	</div>
</div>

<div class="login" id="decOffer_<?=$item_id?>" style="width: 300px;">
	<div id="decOffer_<?=$item_id?>_drag" style="float:right">
		<img id="decOffer_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="seller_id" value="<?=$user_id?>">
			<h2>Decline this offer?</h2>
			<br/><br>	
			<button type="submit" name="offer" value="Decline">Yes, decline offer</button>
		</form>
	</div>
</div>

<div class="login" id="mkOffer_<?=$item_id?>" style="width: 300px;">
	<div id="mkOffer_<?=$item_id?>_drag" style="float:right">
		<img id="mkOffer_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="buyer_id" value="<?=$user_id?>">
			<h2>Enter your offer below:</h2>
			$<input onclick=this.value="" type="text" value="0.00" name="amount">
			<br/><br>	
			<button type="submit" name="offer" value="Make">Make offer</button>
		</form>
	</div>
</div>

<div class="login" id="cancelOffer_<?=$item_id?>" style="width: 300px;">
	<div id="cancelOffer_<?=$item_id?>_drag" style="float:right">
		<img id="cancelOffer_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="buyer_id" value="<?=$user_id?>">
			<h2>Are you sure you want to cancel your offer? Once you cancel this offer, you cannot make another one for this item.</h2>
			<br/><br>
			<button type="submit" name="offer" value="Cancel">Yes, cancel my offer</button>
		</form>
	</div>
</div>

<div class="login" id="enterPK_<?=$item_id?>" style="width: 300px;">
	<div id="enterPK_<?=$item_id?>_drag" style="float:right">
		<img id="enterPK_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
	<?php if ($attempts < 2) { ?>
		<form action="" method="POST">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<input type="hidden" name="paylock" value="<?=$paylock?>">
			<h2>Your PayLock is: <?=$paylock?><br><br>
			Enter the PayKey below that matches your PayLock to complete this sale:</h2>
			<input onclick=this.value="" type="text" placeholder="enter PayKey here" name="key">
			<br/><br>	
			<button type="submit" name="paykey" value="Enter">Submit sale</button>
		</form>
	<?php } else { echo "<h2>You have entered an incorrect PayKey twice. To complete this sale, please call our offices for further verification."; } ?>
	</div>
</div>

<div class="login" id="rmFav_<?=$item_id?>" style="width: 300px;">
	<div id="rmFav_<?=$item_id?>_drag" style="float:right">
		<img id="rmFav_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<form action="" method="POST">
			<input type="hidden" name="user_id" value="<?=$user_id?>">
			<input type="hidden" name="item_id" value="<?=$item_id?>">
			<h2>Remove this item from your favorites?</h2>
			<br/><br>	
			<button type="submit" name="fav" value="Remove">Yes, remove item</button>
		</form>
	</div>
</div>

<div class="login" id="addInfo_<?=$item_id?>" style="width: 300px;">
	<div id="addInfo_<?=$item_id?>_drag" style="float:right">
		<img id="addInfo_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>Please complete your profile.</h2>
		You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
		<button type="button" onclick="document.location='index.php?task=my_account&redirect=payment&id=<?=$id_item?>'">Finish it now</button>
		<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo_<?=$item_id?>').style.display='none';">Later</button>
	</div>
</div>

<div class="login" id="addInfo_offer_<?=$item_id?>" style="width: 300px;">
	<div id="addInfo_offer_<?=$item_id?>_drag" style="float:right">
		<img id="addInfo_offer_<?=$item_id?>_exit" src="images/close.png"/>
	</div>
	<div>
		<h2>Please complete your profile.</h2>
		You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
		<button type="button" onclick="document.location='index.php?task=my_account&redirect=transaction_console'">Finish it now</button>
		<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('addInfo_offer_<?=$item_id?>').style.display='none';">Later</button>
	</div>
</div>