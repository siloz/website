<div class="edit_item" id="editItem_<?=$item_id?>" style="width: 800px">
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
								<td><input type="text" name="title" style="width : 300px" value='<?php echo $title_edit; ?>'/></td>
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
</div>

<div class="login" id="delItem_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="user_id" value="<?=$user_id?>">
		<h2>Are you sure you want to delete this item? All actions are final and cannot be reversed.</h2>
		<br/><br>	
		<button type="submit" name="item" value="Delete">Yes, delete this item</button>
	</form>
</div>

<div class="login" id="decItem_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="user_id" value="<?=$user_id?>">
		<h2>Are you sure you want to decline this item?</h2>
		<br/><br>	
		<button type="submit" name="item" value="Decline">Yes, decline this item</button>
	</form>
</div>

<div class="login" id="clearItem_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="id" value="<?=$id?>">
		<h2>Clear this item from your transaction console?</h2>
		<br><br>	
		<button type="submit" name="item" value="Clear">Yes, clear it</button>
	</form>
</div>


<div class="login" id="seller_clearItem_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="user_id" value="<?=$user_id?>">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<h2>Clear this item from your transaction console?</h2>
		<br><br>	
		<button type="submit" name="item" value="Seller Clear">Yes, clear it</button>
	</form>
</div>

<div class="login" id="acceptOffer_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="seller_id" value="<?=$user_id?>">
		<h2>Accept this offer? Once you accept it, this price will show up only for the user</h2>
		<br/><br>	
		<button type="submit" name="offer" value="Accept">Accept offer</button>
	</form>
</div>

<div class="login" id="decOffer_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="seller_id" value="<?=$user_id?>">
		<h2>Decline this offer?</h2>
		<br/><br>	
		<button type="submit" name="offer" value="Decline">Yes, decline offer</button>
	</form>
</div>

<div class="login" id="mkOffer_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="buyer_id" value="<?=$user_id?>">
		<h2>Enter your offer below:</h2>
		$<input onclick=this.value="" type="text" value="0.00" name="amount">
		<br/><br>	
		<button type="submit" name="offer" value="Make">Make offer</button>
	</form>
</div>

<div class="login" id="cancelOffer_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<input type="hidden" name="buyer_id" value="<?=$user_id?>">
		<h2>Are you sure you want to cancel your offer? Once you cancel this offer, you cannot make another one for this item.</h2>
		<br/><br>
		<button type="submit" name="offer" value="Cancel">Yes, cancel my offer</button>
	</form>
</div>

<div class="login" id="enterPK_<?=$item_id?>" style="width: 300px;">
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

<div class="login" id="rmFav_<?=$item_id?>" style="width: 300px;">
	<form action="" method="POST">
		<input type="hidden" name="user_id" value="<?=$user_id?>">
		<input type="hidden" name="item_id" value="<?=$item_id?>">
		<h2>Remove this item from your favorites?</h2>
		<br/><br>	
		<button type="submit" name="fav" value="Remove">Yes, remove item</button>
	</form>
</div>

<div class="login" id="addInfo_<?=$item_id?>" style="width: 300px;">
	<h2>Please complete your profile.</h2>
	You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
	<button type="button" onclick="document.location='index.php?task=my_account&redirect=payment&id=<?=$id_item?>'">Finish it now</button>
</div>

<div class="login" id="addInfo_offer_<?=$item_id?>" style="width: 300px;">
	<h2>Please complete your profile.</h2>
	You have some information in your profile that has not been filled out yet. Please complete your profile. This will allow you to use the rest of <?=SITE_NAME?>.com <br><br>
	<button type="button" onclick="document.location='index.php?task=my_account&redirect=transaction_console'">Finish it now</button>
</div>

<div class="login" id="buy-notif_<?=$item_id?>" style="width: 300px;">
	<h3>Buying reminder</h3>
	**Remember:  After your purchase has been completed, if the item gets declined, you will receive 95% of your money back. Click 'I agree' below to continue. <br><br>
	<button onclick="window.location='index.php?task=payment&id=<?=$item_link;?>'">I agree</button>
</div>

<div class="login" id="craigslist_<?=$item_id?>" style="width: 600px;">

<script type="text/javascript" src="js/zClip/jquery.zclip.min.js"></script>
<script>
$(document).ready(function(){
    $('button#craigslist-copy').zclip({
        path:'js/zClip/ZeroClipboard.swf',
	 copy:$('textarea#craigslist-html_<?=$item_id?>').val(),
 	 beforeCopy:function(){
	 	$('#craigslist-html_<?=$item_id?>').trigger('click');
	 },
 	 afterCopy:function(){
	 	$('#copy-status_<?=$item_id?>').fadeIn().html('<a href="http://www.craigslist.org" style="text-decoration: none; color: green;" target="_blank">Copied to clipboard! Click here to go to Craigslist!');
	 }
    });
});

$('textarea#craigslist-html_<?=$item_id?>').focus(function() {
    var $this = $(this);

    $this.select();

    window.setTimeout(function() {
        $this.select();
    }, 1);

    // Work around WebKit's little problem
    function mouseUpHandler() {
        // Prevent further mouseup intervention
        $this.off("mouseup", mouseUpHandler);
        return false;
    }

    $this.mouseup(mouseUpHandler);
});

$('textarea#craigslist-html_<?=$item_id?>').keypress(function(e) {
	return false;
});
</script>

	<h2 style='color: #fe5300;'><?=$item->title?>, $<?=$item->price?></h2>
	<h3>Copy and paste the code, below, to your local Craigslist.org listing</h3>
	<textarea rows="10" cols="60" name="craigslist-html_<?=$item_id?>" id="craigslist-html_<?=$item_id?>">
<a href="<?=$item->getUrl();?>">
<?php
	if ($item->photo_file_1 != '')
		echo "<img src='".$item->getPhotoUrl()."' width=280px height=210px id='current_item_photo' /> &nbsp;&nbsp;";
?>
</a>
<br><br>
<font color="blue">
	<b>Description:</b> <?=$item->description?> <br>
	<b>Listing Expires:</b> <?=$item->getFormattedEndDate();?>
</font>
<br><br>
<a href="<?=$item->getUrl();?>">
	Click here to buy this item now via <?=SITE_NAME?>!
</a>
</textarea> 

<br><br>
	<button id="craigslist-copy">Copy to Clipboard</button> <br><br>
	Remember to fill out the appropriate item title and price! <br><br>
	<div id="copy-status_<?=$item_id?>" style="color: green;"></div>
</div>