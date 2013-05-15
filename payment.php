<div class="headingPad"></div>

<div class="userNav" align="center">
	<span class="accountHeading">Payment Form</span>
</div>

<div class="headingPad"></div>

<?php

$id = param_get('id');
if ($id == '')
	$id = param_post('id');

if ($_SESSION['is_logged_in'] != 1 || (!$id)) {
	echo "<script>window.location = 'index.php';</script>";
}
elseif ($addInfo_full) {
	echo "<script>window.location = 'index.php?task=my_account&redirect=1';</script>";
}

include("include/timeout.php");

	$process = param_post('process');
	$item = new Item($id);
	$current_user = new User($user_id);
	if ($process != '') {
		require_once 'braintree/lib/Braintree.php';
		Braintree_Configuration::environment('production');
		Braintree_Configuration::merchantId('sf9ngrv2pjv7vmcm');
		Braintree_Configuration::publicKey('4xywwjd3vzdz2b73');
		Braintree_Configuration::privateKey('tsqksyy97dck5gyf');
		$cc = $_POST['credit_card'];
		$billing = $cc['billing_address'];
		//die(print_r($billing));
		$result = Braintree_Transaction::sale(array(
		    'amount' => $_POST['amount'],
		    'creditCard' => array(
		        'number' => $cc['number'],
		        'expirationDate' => $cc['expiration_month']."/".$cc['expiration_year'],
				'cardholderName' => $cc['cardholder_name'],
				'cvv' => $cc['cvv']
		    ),
			'customer' => array(
				'id' => $cc['customer_id']
			),
			'billing' => array (
				'firstName' => $billing['first_name'],
				'lastName' => $billing['last_name'],
				'streetAddress' => $billing['street_address'],
				'region' => $billing['region'],
				'postalCode' => $billing['postal_code']
			),
			'options' => array(
			    'storeInVault' => false
			)
		));

		if ($result->success) {
		    	$codes = $item->GetPayCode($_SESSION["user_id"], $item->owner->user_id);
		    	$paylock = $codes[0];
			$paykey = $codes[1];

		    	$ItemPurchase = new ItemPurchase();
		    	$ItemPurchase->paylock = $paylock;
		    	$ItemPurchase->paykey = $paykey;
		    	$ItemPurchase->item_id = $item->item_id;
		    	$ItemPurchase->silo_id = $item->silo_id;
		    	$ItemPurchase->user_id = $user_id;
		    	$ItemPurchase->ip = Common::RemoteIp();
		    	$ItemPurchase->amount = $_POST["amount"];
		    	$ItemPurchase->status = "pending";
		    	if($ItemPurchase->Save()){
		    		$item->status = "pending";
				$item->price = $_POST["amount"];
		    		$item->Save();
				$item->user_id = $user_id;
				$item->SaveBuyer();
				$Notification = new Notification();
				$Notification->user_id = $item->owner->user_id;
				$Notification->Send();
		    	}

			$delRecords = mysql_query("DELETE FROM braintree WHERE item_id = '$item->item_id' AND user_id = '$user_id'");

			$buyer_subject = "Your purchase is not complete! Provide your Voucher to the seller to make payment.";
			$buyer_email .= "<img src='".ACITVE_URL."/images/items-sold.png' width='100%' height='100%'></img>";
			$buyer_email = "<h2>What Happens Now?</h2>";
			$buyer_email .= "Congratulations! You have made payment for <b>".$item->title."</b>, which helps the silo ".$item->silo->name.". Now you have one week to meet the seller and collect your item, completing the purchase. Your Voucher, below, acts as cash, and is provided to, or withheld from, the seller, to make or decline a purchase.<br/><br/>";
			$buyer_email .= "<span style='color: red'><b>The Voucher for item ".$item->title." is the word \"".$paykey."\"</b></span><br/><br/>";
			$buyer_email .= "<h2>STEP 1: Buyer (you) made payment.</h2>";
			$buyer_email .= "<h2>STEP 2: Contact the seller and arrange a time and place to meet to inspect the item.</h2>";
			$buyer_email .= "<h3 align='center'>Seller Contact Information:</h3>";
			$buyer_email .= "<center>Item Name: <b>".$item->title."</b><br/>";
			$buyer_email .= "Full name: <b>".$item->owner->fname." ".$item->owner->lname."</b><br/>";
			$buyer_email .= "Email Address: <b>".$item->owner->email."</b><br/>";
			$buyer_email .= "Telephone Number: <b>".$item->owner->phone."</b></center> <br/><br/>";
			$buyer_email .= "<h2>STEP 3: Meet, inspect the item. Decline or accept it. Bring your Voucher!</h2>";
			echo "<span class='greyFont'>".$buyer_email."</span>";
			email_with_template($current_user->email, $buyer_subject, $buyer_email);
			
			$seller_subject = "Congratulations! Your item sold. You must enter the buyer's Voucher to complete the sale.";
			$seller_email = "<img src='".ACITVE_URL."/images/items-sold.png' width='100%' height='100%'></img>";
			$seller_email = "<h2>What Happens Now?</h2>";
			$seller_email .= "Congratulations! ".$current_user->fname." ".$current_user->lname." has made payment for <b>".$item->title."</b>, which helps the silo ".$item->silo->name.". You now have one week in which to meet the buyer and collect his/her Voucher (which is like cash). Entering their Voucher into our site proves the buyer received the item. You should only accept a voucher that conforms to your Voucher Key.<br/><br/>";
			$seller_email .= "<span style='color: red'><b>Voucher Key: All the letters in the Voucher appear in the word \"".$paylock."\" </b></span><br/><br/>";
			$seller_email .= "<h2>STEP 1: Already done! Buyer has made payment.</h2>";
			$seller_email .= "<h2>STEP 2: Contact the buyer and arrange a time and place to meet to show him/her the item.</h2>";
			$seller_email .= "<h3 align='center'>Buyer Contact Information:</h3>";
			$seller_email .= "<center>Item Name: <b>".$item->title."</b><br/>";
			$seller_email .= "Full name: <b>".$current_user->fname." ".$current_user->lname."</b><br/>";
			$seller_email .= "Email Address: <b>".$current_user->email."</b><br/>";
			$seller_email .= "Telephone Number: <b>".$current_user->phone."</b></center> <br/><br/>";
			$seller_email .= "<h2>STEP 3: Meet, present the item. <span style='color: red'>Remember to ask for a Voucher Key (effectively, cash) AND to enter it into the site, so your silo receives the money you raised!</span></h2>";
			email_with_template($item->owner->email, $seller_subject, $seller_email);
			
		} else if ($result->transaction) {
			echo "<h2 align='center' style='color: red'>There was an error proccessing your transaction. Please make sure all of your information is correct.</h2>";
			//echo "<h2 style='color: red'>Error: ".$result->message."</h2>";
			//echo "<h2 style='color: red'>Code: ".$result->transaction->processorResponseCode."</h2>";
			//echo "<h2 style='color: red'>Text: ".$result->transaction->processorResponseText."</h2>";

			$pay_error = mysql_real_escape_string($result->message);
			$error_code = $result->transaction->processorResponseCode;
			$error_text = mysql_real_escape_string($result->transaction->processorResponseText);

			$check = mysql_num_rows(mysql_query("SELECT * FROM braintree WHERE item_id = '$item->item_id' AND user_id = '$user_id'"));
			if ($check < 1) {
				$recordError = mysql_query("INSERT INTO braintree (item_id, user_id, error, error_code, error_text) 
				VALUES ('$item->item_id', '$user_id', '$pay_error', '$error_code', '$error_text')");
			} else {
				$updError = mysql_query("UPDATE braintree 
				SET item_id = '$item->item_id', user_id = '$user_id', error = '$pay_error', error_code = '$error_code', error_text = '$error_text'
				WHERE item_id = '$item->item_id' AND user_id = '$user_id'");
			}

			$process = '';
		} else {			
			echo "<h2 align='center' style='color: red'>".$result->message."</h2>";
			$process = '';
		}
	}
	if ($process == '') {

	$user_id = $_SESSION["user_id"];
	$offerUser = mysql_fetch_array(mysql_query("SELECT status, amount FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id'"));
	$offerStatus = $offerUser['status'];
	$offerAmount = $offerUser['amount'];

	if ($offerStatus == 'accepted') { $price = $offerAmount; } else { $price = $item->price; }
?>
<form action="index.php" method="post">
	<input type="hidden" name="task" value="payment"/>
	<input type="hidden" name="process" value="true"/>
	<input type="hidden" name="amount" value="<?php echo $price;?>"/>
	<input type="hidden" name="id" value="<?php echo $id;?>"/>
	<input type="hidden" name="credit_card[customer_id]" value="<?php echo $current_user->id;?>"/>
	
	<table cellpadding="10px">
		<tr>
			<td valign="top" width="200px">
			<h3>Item Details</h3>
			<table>
				<tr>
					<td>ID<td>
					<td><b><?php echo $id;?></b></td>
				</tr>
				<tr>
					<td>Name<td>
					<td><b><?php echo $item->title;?></b></td>
				</tr>
				<tr>
					<td>Price<td>
					<td><b><?php echo "$".$price;?></b></td>
				</tr>
			</table>				
			</td>
			<td valign="top">
			<h3>Credit Card</h3>		
			<table>
				<tr>
					<td>Credit Holder Name<td>
					<td><input type="text" style="width: 150px" name="credit_card[cardholder_name]"/></td>
				</tr>
				<tr>
					<td>Credit Number<td>
					<td><input type="text" style="width: 150px" name="credit_card[number]" value=""/></td>
				</tr>
				<tr>
					<td>CVV Code<td>
					<td><input type="text" style="width: 30px" name="credit_card[cvv]"/></td>
				</tr>
				<tr>
					<td>Expiration Month<td>
					<td>
						<select name="credit_card[expiration_month]">
							<option value="01">01</option>
							<option value="02">02</option>
							<option value="03">03</option>
							<option value="04">04</option>
							<option value="05">05</option>
							<option value="06">06</option>
							<option value="07">07</option>
							<option value="08">08</option>
							<option value="09">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Expiration Year here<td>
					<td>
						<select name="credit_card[expiration_year]">
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
						</select>
					</td>
				</tr>
			</table>
			</td>
			<td valign="top">
				<h3>Billing Address</h3>
				<table>
					<tr>
						<td>First Name</td>
						<td><input type="text" style="width: 200px" name="credit_card[billing_address][first_name]"/></td>
					</tr>
					<tr>
						<td>Last Name</td>
						<td><input type="text" style="width: 200px" name="credit_card[billing_address][last_name]"/></td>
					</tr>
					<tr>
						<td>Street Address</td>
						<td><input type="text" style="width: 200px" name="credit_card[billing_address][street_address]"/></td>
					</tr>
					<tr>
						<td>State</td>
						<td><input type="text" style="width: 50px" name="credit_card[billing_address][region]"/></td>
					</tr>
					<tr>
						<td>Postal Code</td>
						<td><input type="text" style="width: 50px" name="credit_card[billing_address][postal_code]"/></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<input type="submit" name="submit" value="Submit"/>
			</td>
		</tr>
	</table>
</form>
<?php
}
?>
