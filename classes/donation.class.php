<?php
class Donation {
	public $donation_id;
	public $user_id;
	public $silo_id;
	public $amount;
	public $status;
	public $ref;
	public $sent_date;
	public $received_date;
	public $deleted_date;
	
	public __construct($id) {
		$res = mysql_fetch_array(mysql_query("SELECT * FROM donations WHERE donation_id  = $id"));		
		
		$this->donation_id = $res['donation_id'];
		$this->user_id = $res['user_id'];
		$this->silo_id = $res['silo_id'];
		$this->amount = $res['amount'];
		$this->status = $res['status'];
		$this->ref = $res['ref'];
		$this->sent_date = $res['sent_date'];
		$this->received_date = $res['received_date'];
		$this->deleted_date = $res['deleted_date'];		
	}
}
?>