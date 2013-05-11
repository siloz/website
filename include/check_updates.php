<?php

$silo_latent_check = mysql_query("UPDATE silos SET status = 'latent', end_date = NOW() WHERE status = 'active' AND (end_date <= NOW() OR goal <= collected)");
	if (mysql_affected_rows() > 0) {
		$updItem = mysql_query("UPDATE items, silos SET items.status = 'inert', items.end_date = NOW() WHERE items.silo_id = silos.silo_id AND items.status = 'pledged' AND silos.status = 'latent'");
	}

$offerCheck = mysql_query("UPDATE offers SET status = 'declined' WHERE expired_date < NOW()");
	if (mysql_affected_rows() > 0) {
		$updItem = mysql_query("UPDATE items, offers SET items.status = 'pledged' WHERE offers.status = 'declined' AND offer.expired_date = 0");
	}

$purchaseCheck = mysql_query("UPDATE item_purchase SET status = 'declined' WHERE expired_date < NOW()");
	if (mysql_affected_rows() > 0) {
		$updItem = mysql_query("UPDATE items, item_purchase SET items.status = 'pledged' WHERE item_purchase.status = 'declined'");
	}

$lowVouchCheck = mysql_query("UPDATE flag_radar SET active = 0 WHERE status = 'vouch' AND expired_date < NOW()");
	if (mysql_affected_rows() > 0) {
		$killSilo = mysql_query("UPDATE silos, flag_radar SET silos.status = 'flagged' WHERE flag_radar.status = 'vouch' AND flag_radar.active = 0 AND silos.silo_id = flag_radar.silo_id");
	}

$email24HourCheck = mysql_query("SELECT user_id, email FROM users WHERE info_emails < 2 AND joined_date + INTERVAL 1 DAY <= NOW()");
$num24Hour = mysql_num_rows($email24HourCheck);
	if ($num24Hour > 0) {
		while ($user = mysql_fetch_array($email24HourCheck)) {
		$subject = "How ".SHORT_URL." works";
		$message = "<br> We think people spend too much time learning how to use things that should be simple. So, we want to tell you about ".SITE_NAME.", but we'll keep it short. We know how busy you are. <br><br>";
		$message .= "<b>How ".SITE_NAME." Works</b> <br><br>";
		$message .= "<ol>
				<li>A silo administrator creates a silo. This takes all of 5 minutes.</li>
				<li>That silo administrator promotes the silo, using Facebook and our email contact tools (AOL, Hotmail, Yahoo!, Gmail, etc.), and off-line flyers (printed out from the console). Hint: off-line promotion is best done at an event.</li>
				<li>Supporters spread the word about the silo (to get still more item donors, and shoppers) and donate items themselves, listing them online, from the comfort of their homes, like that other site that sells used stuff in your area. Note: the local public can donate items to a public silo, but private silos are viewable/joinable by invite only.</li>
				<li>Buyers pay online, and are issued a Voucher, which acts as cash. Sellers are given a Voucher Key, which proves a Voucher is authentic. Potential buyers and sellers are provided contact information for each other, and given one week in which to close a sale, which happens <b>when the seller enters the buyer's Voucher into the site</b>.</li>
				<li>The silo ends, we send the money raised to each silo administrator, and ask them to 'Thank' those who donated items.</li>
				</ol>";
		$message .= "We invite you to communicate your questions and concerns with us.<br><br>";
		$message .= "Thank You, and Happy Fundraising, <br><br><br>";
		$message .= "Zackery West <br><br> CEO, ".SITE_NAME." LLC";
		email_with_template($user['email'], $subject, $message);
		mysql_query("UPDATE users SET info_emails = 2 WHERE user_id = '$user[user_id]'");
		}
	}

$email36HourCheck = mysql_query("SELECT user_id, email FROM users WHERE info_emails < 3 AND joined_date + INTERVAL 36 HOUR <= NOW()");
$num36Hour = mysql_num_rows($email36HourCheck);
	if ($num36Hour > 0) {
		while ($user = mysql_fetch_array($email36HourCheck)) {
		$subject = "How to get the most out of ".SHORT_URL;
		$message = "<br> <b>Item Donors</b> <br><br>";
		$message .= "<ul>
				<li>Be sure to investigate your silo and its administrator. Never donate an item to a silo you are unfamiliar with.</li>
				<li>You can donate more than one item! Also, many silos are able to offer tax-deductible receipts. Think of big ticket items when donating.</li>
				<li>Spread the word. The people you tell (via Facebook, or with our email contact tools), can become both shoppers and potential donors to the cause you support.</li>
				</ul>";
		$message .= "<b>silo Administrators</b> <br><br>";
		$message .= "<ul>
				<li>Spread the word, far and wide!</li>
				<li>Log into your ".SITE_NAME." account, and select 'manage silo' to view our on- and off-line promotion tools.</li>
				<li>Manage your silo; respond to inquiries about your organization.</li>
				<li>Remember to upload photos substantiating how your money raised was spent. If it is not a specific purchase or project, photos of your group will do. And of course, offer a warm message to those who helped you raise money.</li>
				</ul>";
		$message .= "<b>Shoppers</b> <br><br>";
		$message .= "<ul>
				<li>Act quickly to pick up your item. Remember your Voucher for a given item. If you have multiple items, you want to be sure to remember which is associated with. Finally: never provide your Voucher over the telephone or agree to receive a shipped item. Your Voucher is like cash. If it goes to the seller, and you don’t collect your item, there’s nothing we can do to help you.</li>
				</ul>";
		$message .= "We invite you to communicate your questions and concerns with us.<br><br>";
		$message .= "Thank You, and Happy Fundraising, <br><br><br>";
		$message .= "Zackery West <br><br> CEO, ".SITE_NAME." LLC";
		email_with_template($user[email], $subject, $message);
		mysql_query("UPDATE users SET info_emails = 3 WHERE user_id = '$user[user_id]'");
		}
	}
?>