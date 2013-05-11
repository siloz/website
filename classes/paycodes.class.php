<?php
class PayCodes {
	public $buyer_id;
	public $seller_id;

	public function GetPayCode($buyer_id, $seller_id){
		$id = rand(1,100);

		$buyer = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$buyer_id' AND paycode_id = '$id'"));
		$seller = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$seller_id' AND paycode_id = '$id'"));

		while ($buyer || $seller) {
			$id = rand(1,100);

			$buyer = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$buyer_id' AND paycode_id = '$id'"));
			$seller = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$seller_id' AND paycode_id = '$id'"));
		}

		$codes = mysql_fetch_array(mysql_query("SELECT paykey, paylock FROM paycodes WHERE id = '$id'"));

		return array($codes[0], $codes[1]);
	}
}
?>