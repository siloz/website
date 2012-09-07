<?php
	$process = param_post('process');
	$id = param_get('id');
	if ($id == '')
		$id = param_post('id');
	$item = new Item($id);
	if ($process == '') {
?>
<form action="index.php" method="post">
	<input type="hidden" name="task" value="payment"/>
	<input type="hidden" name="process" value="true"/>
	<input type="hidden" name="amount" value="<?php echo $item->price;?>"/>
	<input type="hidden" name="id" value="<?php echo $id;?>"/>
	<input type="hidden" name="credit_card[customer_id]" value="<?php echo $current_user->id;?>"/>
	
	<table cellpadding="10px">
		<tr>
			<td valign="top" width="200px">
			<h3>Item Detail</h3>
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
					<td><b><?php echo "$".$item->price;?></b></td>
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
					<td><input type="text" style="width: 150px" name="credit_card[number]" value="5105105105105100"/></td>
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
					<td>Expiration Year<td>
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
	else {
		require_once 'braintree/lib/Braintree.php';
		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('3g3ms64nnp4jthgj');
		Braintree_Configuration::publicKey('b7pqj735f7zpv843');
		Braintree_Configuration::privateKey('wq85yksj4vp6zdfq');
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
			    'storeInVault' => true
			)
		));

		if ($result->success) {
		    print_r("success!: " . $result->transaction->id);
		} else if ($result->transaction) {
		    print_r("Error processing transaction:");
		    print_r("\n  message: " . $result->message);
		    print_r("\n  code: " . $result->transaction->processorResponseCode);
		    print_r("\n  text: " . $result->transaction->processorResponseText);
		} else {
		    print_r("Message: " . $result->message);
		    print_r("\nValidation errors: \n");
		    print_r($result->errors->deepAll());
		}
	}
?>