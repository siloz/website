<?php
class Notification {
	public $user_id;
	public $seller_id;
	public $buyer_id;
	public $item_id;
	public $silo_id;
	public $type;

	public function Send(){
		if ($this->seller_id) {
			$buyer = mysql_fetch_array(mysql_query("SELECT buyer_id FROM offers WHERE seller_id = '$this->seller_id' ORDER BY offer_date DESC"));
			$this->user_id = $buyer[0];
		}
		elseif ($this->buyer_id) {
			$seller = mysql_fetch_array(mysql_query("SELECT seller_id FROM item_purchase WHERE buyer_id = '$this->buyer_id'"));
			$this->user_id = $seller[0];
		}

		$notif = mysql_num_rows(mysql_query("SELECT * FROM notifications WHERE user_id = '$this->user_id'"));

		if ($notif) {
			return $this->AddNotification();
		}
		else {
			return $this->InsertNotification();
		}
	}

	public function AddNotification() {
		$add = mysql_query("UPDATE notifications SET count = count + 1 WHERE user_id = '$this->user_id'");

		if ($this->type) {
			return $this->Email();
		} else {
			return true;
		}
	}

	public function InsertNotification() {
		$insert = mysql_query("INSERT INTO notifications (user_id, count) VALUES ('$this->user_id', '1')");

		if ($this->type) {
			return $this->Email();
		} else {
			return true;
		}
	}

	public function Email() {

		if ($this->type == "New Offer") {
			$offer = mysql_fetch_array(mysql_query("SELECT buyer_id, amount FROM offers WHERE seller_id = '$this->user_id' AND item_id = '$this->item_id'"));
			$buyer = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$offer[0]'"));
			$item = mysql_fetch_row(mysql_query("SELECT title FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$this->user_id'"));

			$subject = "New offer on ".SHORT_URL."!";
			$message = "<h3>New offer for ".$item[0]."</h3>";
			$message .= "Buyer's name: <b>".$buyer[0]." ".$buyer[1]."</b><br/><br/>";
			$message .= "Offer amount: <b>$".$offer[1]."</b><br/><br/>";
			$message .= "To accept or reject this offer, click <a href='".ACTIVE_URL."index.php?task=transaction_console'>here</a>. <br> This offer will expire in 24 hours if you do not respond.";
		}
		elseif ($this->type == "Cancel Offer") {
			$offer = mysql_fetch_array(mysql_query("SELECT buyer_id, amount FROM offers WHERE seller_id = '$this->user_id' AND item_id = '$this->item_id'"));
			$buyer = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$offer[0]'"));
			$item = mysql_fetch_row(mysql_query("SELECT title FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$this->user_id'"));

			$subject = "Offer canceled on ".SHORT_URL;
			$message = "<h3>The offer for $".$offer[1]." on ".$item[0]." has been canceled by ".$buyer[0]." ".$buyer[1]."</h3>";
			$message .= "No further action needs to be taken.";
		}
		elseif ($this->type == "Accept Offer") {
			$buyer = mysql_fetch_array(mysql_query("SELECT buyer_id, amount FROM offers WHERE seller_id = '$this->seller_id' AND item_id = '$this->item_id'"));
			$item = mysql_fetch_row(mysql_query("SELECT id, title FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$buyer[0]'"));

			$subject = "Offer accepted on ".SHORT_URL."!";
			$message = "<h3>Your offer of $".$buyer[1]." was accepted for ".$item[1]."!</h3>";
			$message .= "You now have 24 hours to purchase this item at the offered price above, otherwise your offer will be declined and you will have to purhcase the item at the asking price.<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item[0]."'>here</a> to view the item and buy it now.";
		}
		elseif ($this->type == "Decline Offer") {
			$buyer = mysql_fetch_array(mysql_query("SELECT buyer_id, amount FROM offers WHERE seller_id = '$this->seller_id' AND item_id = '$this->item_id'"));
			$item = mysql_fetch_row(mysql_query("SELECT id, title, price FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$buyer[0]'"));

			$subject = "Offer declined on ".SHORT_URL."";
			$message = "<h3>Your offer of $".$buyer[1]." was declined for ".$item[1]."</h3>";
			$message .= "You're offer was declined, but you can still purhcase this item at the asking price of $".$item[2].".<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item[0]."'>here</a> to view the item and buy it now.";
		}
		elseif ($this->type == "Item Sold") {
			$buyer = mysql_fetch_array(mysql_query("SELECT user_id, amount FROM item_purchase WHERE item_id = '$this->item_id' AND status = 'sold'"));
			$item = mysql_fetch_array(mysql_query("SELECT id, title, price, silo_id, user_id FROM items WHERE item_id = '$this->item_id'"));
			$silo = mysql_fetch_array(mysql_query("SELECT org_name, address, title, phone_number, ein, admin_id FROM silos WHERE silo_id = '$item[silo_id]'"));
			$admin = mysql_fetch_array(mysql_query("SELECT email, fname, lname FROM users WHERE user_id = '$silo[admin_id]'"));
			$email = mysql_fetch_array(mysql_query("SELECT email, fname, lname FROM users WHERE user_id = '$item[user_id]'"));
			$emailBuyer = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$buyer[0]'"));
			$s_types = array(2, 5, 6);

			if ($silo['ein'] && $silo['issue_receipts'] == 1) {
				$subject = "Item sold on ".SHORT_URL."!";
				$message = "<h3>You have officially sold an item on ".SITE_NAME."!</h3>";
				$message .= "This email serves as a receipt, for the purpose of claiming a charitable donation tax-deduction, in the amount of $".$item[2].", for ".$item[1].", the proceeds of which were donated by ".$email[fname]." ".$email[lname]." to:<br><br>";
				$message .= $silo['org_name']."<br>";
				$message .= $silo['address']."<br>";
				$message .= $silo['phone_number']."<br><br>";
				$message .= "...for a silo entitled \"".$silo['title']."\", under the fund-raising administration of ".$admin[fname]." ".$admin[lname].", on this day.<br><br>";
				$message .= "Thank You,<br><br>";
				$message .= "<a href='".ACTIVE_URL."'>".SITE_NAME.".com</a>";
			}
			elseif (in_array($silo['silo_cat_id'], $s_types) && $silo['issue_receipts'] == 1) {
				$subject = "Item sold on ".SHORT_URL."!";
				$message = "<h3>You have officially sold an item on ".SITE_NAME."!</h3>";
				$message .= "This silo has <b>not</b> provided an EIN number, but this sale is still tax-deductible<br>";
				$message .= "This email serves as a receipt, for the purpose of claiming a charitable donation tax-deduction, in the amount of $".$item[2].", for ".$item[1].", the proceeds of which were donated by ".$email[fname]." ".$email[lname]." to:<br><br>";
				$message .= $silo['org_name']."<br>";
				$message .= $silo['address']."<br>";
				$message .= $silo['phone_number']."<br><br>";
				$message .= "...for a silo entitled \"".$silo['title']."\", under the fund-raising administration of ".$admin[fname]." ".$admin[lname].", on this day.<br><br>";
				$message .= "Thank You,<br><br>";
				$message .= "<a href='".ACTIVE_URL."'>".SITE_NAME.".com</a>";
			}
			else {
				$subject = "Item sold on ".SHORT_URL."!";
				$message = "<h3>You have officially sold an item on ".SITE_NAME."!</h3>";
				$message .= "With your sale of the item <b>".$item['title']."</b>, you have donated $".$item['price']." to the silo <b>".$silo['org_name']."</b><br><br>";
				$message .= "Thanks for your help!";
			}
				$subjectBuyer = "Item purchased through ".SHORT_URL."!";
				$messageBuyer = "<h3>You have officially purchased an item through ".SITE_NAME."!</h3>";
				$messageBuyer .= "With your purchase of the item <b>".$item['title']."</b>, you have donated $".$item['price']." to the silo <b>".$silo['org_name']."</b><br><br>";
				$messageBuyer .= "Thanks for your help!";

				email_with_template($emailBuyer[0], $subjectBuyer, $messageBuyer);
		}
		elseif ($this->type == "Cancel Item") {
			$item = mysql_fetch_array(mysql_query("SELECT id, title, user_id FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$item[2]'"));

			$subject = "Item deleted on ".SHORT_URL;
			$message = "<h3>'".$item[1]."' has received more than 4 flags on ".SITE_NAME.".com</h3>";
			$message .= "Your item has received five or more flags and has been suspended.<br><br>";
			$message .= "If you feel like this is an error, please contact ".SITE_NAME.".com support immediately. <br><br>";
		}
		elseif ($this->type == "Warn Item") {
			$item = mysql_fetch_array(mysql_query("SELECT id, title, user_id FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$item[2]'"));

			$subject = "Item flag warning";
			$message = "<h3>'".$item[1]."' has received too many flags on ".SITE_NAME.".com</h3>";
			$message .= "Your item has received two or more flags in a single category.<br><br>";
			$message .= "This is just a warning! If your item gets a total of <b>5</b> flags, it will be shut down immediately!<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item[0]."'>here</a> to view the item that is being flagged.";
		}
		elseif ($this->type == "Cancel Silo") {
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name FROM silos WHERE silo_id = '$this->silo_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$silo[0]'"));

			$subject = "Silo deleted on ".SHORT_URL;
			$message = "<h3>'".$silo[1]."' has received more than 4 flags on ".SITE_NAME.".com</h3>";
			$message .= "Your silo has received five or more flags and has been suspended.<br><br>";
			$message .= "If you feel like this is an error, please contact ".SITE_NAME.".com support immediately. <br><br>";
		}
		elseif ($this->type == "Warn Silo") {
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name, id FROM silos WHERE silo_id = '$this->silo_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$silo[0]'"));

			$subject = "Silo flag warning";
			$message = "<h3>'".$silo[1]."' has received too many flags on ".SITE_NAME.".com</h3>";
			$message .= "Your silo has received two or more flags in a single category.<br><br>";
			$message .= "This is just a warning! If your silo gets a total of <b>5</b> flags, it will be shut down immediately!<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo[2]."'>here</a> to view the silo that is being flagged.";
		}
		elseif ($this->type == "Vouch Warn Silo") {
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name, id FROM silos WHERE silo_id = '$this->silo_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$silo[0]'"));

			$subject = "Silo low familiarity index";
			$message = "<h3>'".$silo[1]."' has too low of a familiarity index on ".SITE_NAME.".com</h3>";
			$message .= "Your silo has less than 80% of its members who have researched or have any personal knowledge about your silo's purpose.<br><br>";
			$message .= "You have <b>72</b> hours to raise your familiarity index score. If the familiarity index is still too low after <b>72</b> hours, it will be shut down immediately!<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo[2]."'>here</a> to view the silo that has too low of a familiarity index.";
		}
		elseif ($this->type == "Reactivate") {
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name, id FROM silos WHERE silo_id = '$this->silo_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$silo[0]'"));

			$subject = "Your silo has been reactivated";
			$message = "<h3>'".$silo[1]."' has been reactivated on ".SITE_NAME.".com</h3>";
			$message .= "Your silo has been reactivated after getting shut down from being flagged. This silo's flag count has been reset as well.<br><br>";
			$message .= "No further action needs to be taken.<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_silo&id=".$silo[2]."'>here</a> to view the silo that has been reactivated.";
		}
		elseif ($this->type == "Item Reactivate") {
			$item = mysql_fetch_array(mysql_query("SELECT user_id, title, id FROM items WHERE item_id = '$this->item_id'"));
			$email = mysql_fetch_row(mysql_query("SELECT email FROM users WHERE user_id = '$item[0]'"));

			$subject = "Your item has been reactivated";
			$message = "<h3>'".$item[1]."' has been reactivated on ".SITE_NAME.".com</h3>";
			$message .= "Your item has been reactivated after getting shut down from being flagged. This item's flag count has been reset as well.<br><br>";
			$message .= "No further action needs to be taken.<br><br>";
			$message .= "Click <a href='".ACTIVE_URL."index.php?task=view_item&id=".$item[2]."'>here</a> to view the silo that has been reactivated.";
		}

		email_with_template($email[0], $subject, $message);

		return true;		
	}

	public function DeleteEmail($item_id, $user_id, $silo_id, $type) {

		if ($type == "item") {
			$item = mysql_fetch_array(mysql_query("SELECT title FROM items WHERE item_id = '$item_id'"));	
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name FROM silos WHERE silo_id = '$silo_id'"));		
			$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$user_id'"));	
			$admin = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$silo[admin_id]'"));

			$subject = "Item removed from the silo ".$silo['name'];
			$message = "<h3>".$item['title']." has been removed from the silo ".$silo['name'].".</h3>";
			$message .= "The administrator of the silo has removed your item from the silo. A silo administrator has the ability to delete any item that has not sold or is not pending to be sold. Your item has been deleted, but you can repledge your item to a different silo at any time.<br><br>";
			$message .= "See our Terms of Use and FAQ for more information about removing items from a silo.  If you feel your item was wrongfully removed, you can contact the silo administrator at ".$admin['email'].".  If you feel your silo administrator was not abiding our tenets of legality, respect, goodwill and inclusiveness, you can report inappropriate action to us by using the 'Contact Us' link in the footer of the website.<br/><br/>";

			$fav = mysql_query("SELECT email FROM favorites INNER JOIN users USING (user_id) WHERE item_id = '$item_id'");
			$delFav = mysql_query("DELETE FROM favorites WHERE item_id = '$item_id'");
			while ($fUser = mysql_fetch_array($fav)) {
				$subjectF = "Favorited item deleted";
				$messageF = "<h3>".$item['title']." has been removed from ".SITE_NAME.".com</h3>";
				$messageF .= "You favorited ".$item['title'].", but it has been removed and deleted. This item has automatically been removed from your favorites.<br><br>"; 
				$messageF .= "No further action is required.";

				$Item = new Item(); $Item->user_id = $user_id; $Item->item_id = $item_id; $Item->RemoveFav();

				email_with_template($fUser['email'], $subjectF, $messageF);
			}

			$offer = mysql_query("SELECT user_id, email FROM offers INNER JOIN users USING (user_id) WHERE item_id = '$item_id' AND offers.status = 'pending' OR offers.status = 'accepted'");
			$delFav = mysql_query("DELETE FROM offers WHERE item_id = '$item_id'");
			while ($oUser = mysql_fetch_array($offer)) {
				$subjectO = "Offer canceled due to item deletion";
				$messageO = "<h3>".$item['title']." has been removed from ".SITE_NAME.".com</h3>";
				$messageO .= "You made an offer on ".$item['title'].", but it has been removed and deleted. Your offer is no longer valid and it has been canceled.<br><br>"; 
				$messageO .= "No further action is required.";

				$Item = new Item(); $Item->item_id = $item_id; $Item->buyer_id = $oUser['user_id']; $Item->RemoveOffer();

				email_with_template($oUser['email'], $subjectO, $messageO);
			}

		}
		elseif ($type == "user") {
			$silo = mysql_fetch_array(mysql_query("SELECT admin_id, name FROM silos WHERE silo_id = '$silo_id'"));		
			$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$user_id'"));	
			$admin = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$silo[admin_id]'"));

			$subject = "You have been removed from the silo ".$silo['name'];
			$message = "<h3>The administrator of ".$silo['name']." has removed you from their silo.</h3>";
			$message .= "The administrator of the silo has removed you from the silo. A silo administrator has the ability to delete anyone who is a member of their silo at any time. If you have pledged an item that has already been sold or is pending to be sold, those items will not be effected.<br><br>";
			$message .= "See our Terms of Use and FAQ for more information about removing members from a silo.  If you feel you have been wrongfully removed, you can contact the silo administrator at ".$admin['email'].".  If you feel your silo administrator was not abiding our tenets of legality, respect, goodwill and inclusiveness, you can report inappropriate action to us by using the 'Contact Us' link in the footer of the website.<br/><br/>";
			
			$fav = mysql_query("SELECT item_id, title, favorites.user_id AS favuser FROM favorites INNER JOIN items USING (item_id) WHERE items.user_id = '$user_id' AND silo_id = '$silo_id'");
			while ($favs = mysql_fetch_array($fav)) {
				$favUser = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$favs[favuser]'"));
				$delFav = mysql_query("DELETE FROM favorites WHERE item_id = '$favs[item_id]'");
				$subjectF = "Favorited item deleted";
				$messageF = "<h3>".$favs['title']." has been removed from ".SITE_NAME.".com</h3>";
				$messageF .= "You favorited ".$favs['title'].", but it has been removed and deleted. This item has automatically been removed from your favorites.<br><br>"; 
				$messageF .= "No further action is required.";

				$Item = new Item(); $Item->user_id = $user_id; $Item->item_id = $item_id; $Item->RemoveFav();

				email_with_template($favUser['email'], $subjectF, $messageF);
			}

		}

		email_with_template($user['email'], $subject, $message);

		return true;		
	}

	public function SiloEnded($silo_id) {
	
		$silo = mysql_fetch_array(mysql_query("SELECT name, admin_id, collected FROM silos WHERE silo_id = '$silo_id'"));
		$admin = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$silo[admin_id]'"));

			$subjectAdmin = "Your silo has ended";
			$messageAdmin = "<h3>The silo titled <b>".$silo['name']."</b> has either reached its deadline or passed its goal. Congratulations! </h3>";
			$messageAdmin .= "We are still waiting for some members of the silo to finish their transactions. Once all of the transactions in your silo have been completed, you (as the administrator) will be paid.<br><br>";
			$messageAdmin .= "Right now, your silo is inert. You do not need to do anything until you have been paid. We will send you an e-mail once you been paid successfully.<br><br>";
			$messageAdmin .= "Thank you for using ".SITE_NAME." and we will contact you very soon regarding the money that you have raised!";

		email_with_template($admin['email'], $subjectAdmin, $messageAdmin);

		$getUsers = mysql_query("SELECT user_id FROM silo_membership WHERE silo_id = '$silo_id' AND removed_date = 0");
		while ($getUser = mysql_fetch_array($getUsers)) {
			$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$getUser[0]'"));
				$subjectUser = "A silo has ended";
				$messageUser = "<h3>The silo titled <b>".$silo['name']."</b> has ended.</h3>";
				$messageUser .= "This silo is no longer active, which means that either the silo reached its goal, or the runtime has surpassed. Since this silo is inacitve, no more transactions will be allowed. The silo administator will be paid once all of the current pending transactions have finished. Once the silo administrator has been paid, we will send you another e-mail with more information about the silo and how much money it raised.<br><br>";
				$messageUser .= "Thank you for participating in this silo. All of the help is always greatly appreciated! You will be hearing back from us very soon.";

			email_with_template($user['email'], $subjectUser, $messageUser);
		}

		return true;
	}

	public function SiloPaid($silo_id) {
	
		$silo = mysql_fetch_array(mysql_query("SELECT name, admin_id, collected FROM silos WHERE silo_id = '$silo_id'"));
		$admin = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$silo[admin_id]'"));

			$subjectAdmin = "Your silo has been paid!";
			$messageAdmin = "<h3>You have been paid for the silo titled <b>".$silo['name']."</b>! </h3>";
			$messageAdmin .= "Now that you have been paid, it is time to thank the members who pledged items to your silo. We strongly encourage you to upload images or files that give your members proof regarding what the money helped pay for.<br><br>";
			$messageAdmin .= "To thank your members, you can click on the 'manage silo' tab under your account page or you can simply click <a href='".ACTIVE_URL."index.php?task=manage_silo_thank'>here</a>.<br><br>";
			$messageAdmin .= "Great job on the completion of your silo! We hope the money will help your organziation a great deal. We hope to see you back at ".SITE_NAME." soon!";

		email_with_template($admin['email'], $subjectAdmin, $messageAdmin);

		$siloReport = "<table width='100%'>";
		$siloReport .= "<tr><td><b>Name</b></td><td><b>Number of items pledged</b></td><td><b>Total amount pledged</b></td><td><b>Total amount sold and raised</b></td></tr>";

		$getStats = mysql_query("SELECT user_id FROM silo_membership WHERE silo_id = '$silo_id' AND removed_date = 0");
		while ($userStats = mysql_fetch_array($getStats)) {
			$user = mysql_fetch_array(mysql_query("SELECT fname, lname FROM users WHERE user_id = '$userStats[0]'"));
			$item_pledged = mysql_fetch_array(mysql_query("SELECT SUM(price) FROM items WHERE silo_id = '$silo_id' AND user_id = '$userStats[0]' AND (status = 'sold' OR status = 'inert')"));
			$item_sold = mysql_fetch_array(mysql_query("SELECT SUM(price) FROM items WHERE silo_id = '$silo_id' AND user_id = '$userStats[0]' AND status = 'sold'"));
			$item_count = mysql_num_rows(mysql_query("SELECT * FROM items WHERE silo_id = '$silo_id' AND user_id = '$userStats[0]' AND (status = 'sold' OR status = 'inert')"));
			$siloReport .= "<tr><td>".$user['fname']." ".$user['lname']."</td><td>".$item_count."</td><td>$".number_format($item_pledged[0], 2)."</td><td>$".number_format($item_sold[0], 2)."</td></tr>";
		}

		$siloReport .= "</table>";
		$siloReport .= "<br>Total funds raised for this silo: <b>$".number_format($silo['collected'], 2)."</b><br><br>Great work!";

		$getUsers = mysql_query("SELECT user_id FROM silo_membership WHERE silo_id = '$silo_id' AND removed_date = 0");
		while ($getUser = mysql_fetch_array($getUsers)) {
			$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$getUser[0]'"));
				$subjectUser = "A silo has been paid!";
				$messageUser = "<h3>The silo titled <b>".$silo['name']."</b> has been paid out!</h3>";
				$messageUser .= "Thanks for helping this silo reach their goal. Every item counts! You can look at how much money was raised and how much each member raised in the report below:<br><br>";
				$messageUser .= $siloReport;

			email_with_template($user['email'], $subjectUser, $messageUser);
		}

		return true;
	}

}
?>