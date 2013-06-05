<?php
// Refund include when transaction time has expired or buyer declines an item

			$itemPur = mysql_fetch_array(mysql_query("SELECT amount, user_id, trans_id FROM item_purchase WHERE item_id = '$item_id'"));
			$itemInfo = mysql_fetch_array(mysql_query("SELECT title FROM items WHERE item_id = '$item_id'"));
			$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$itemPur[user_id]'"));
			$refund_amount = $itemPur['amount'] * .95;
			$trans_id = $itemPur['trans_id'];

			require_once 'braintree/lib/Braintree.php';
			Braintree_Configuration::environment('production');
			Braintree_Configuration::merchantId('sf9ngrv2pjv7vmcm');
			Braintree_Configuration::publicKey('4xywwjd3vzdz2b73');
			Braintree_Configuration::privateKey('tsqksyy97dck5gyf');

			$transaction = Braintree_Transaction::find($trans_id);
			if ($transaction->status == Braintree_Transaction::AUTHORIZED) {
				$result = Braintree_Transaction::void($trans_id);
			} elseif ($transaction->status == Braintree_Transaction::SETTLED) {
				$result = Braintree_Transaction::refund($trans_id, $refund_amount);
			}

			if ($result->success) { 
				$subject = "You have been issued a refund";
				$message = "<h3>You have been refunded for your purchase for the item titled <b>".$itemInfo['title']."</b></h3>";
				$message .= "The seller never completed the transaction (they didn't confirm your Voucher), therefore your money has been refunded, less a 5% transaction fee.<br><br>";
				$message .= "Refunded amount: <b>".$refund_amount."</b><br><br>";
				$message .= "If you have any further questions, please contact ".SITE_NAME." support right away.";
				email_with_template($user['email'], $subject, $message);
			} else {
				mysql_query("INSERT INTO braintree (item_id, user_id, error) VALUES ('$item_id', '$itemPur[user_id]', '$result->message')");
			}
?>