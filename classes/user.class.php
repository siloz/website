<?php
class User {
	public $user_id;
	public $id;
	public $username;
	public $password;
	public $fullname;
	public $phone;
	public $email;
	public $address;
	public $zip_code;
	public $user_type;
	public $joined_date;
	public $photo_file;
	function __construct($id) {
		if (is_array($id)) {
			$res = $id;
		}
		else {
			$id = (string)$id;
			if ($id[0] == '0')		
				$res = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE id='$id'"));
			else
				$res = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=$id"));
		}
		$this->user_id = $res['user_id'];
		$this->id = $res['id'];
		$this->username = $res['username'];
		$this->password = $res['password'];
		$this->fullname = $res['fullname'];
		$this->phone = $res['phone'];
		$this->email = $res['email'];
		$this->address = $res['address'];
		$this->zip_code = $res['zip_code'];
		$this->user_type = $res['user_type'];
		$this->joined_date = $res['joined_date'];
		$this->photo_file = $res['photo_file'];
	}
	
	public function getCurrentSilo() {
		$sql = "SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE admin_id = ".$this->user_id." AND end_date >= '".date('Y-m-d')."'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) == 0) 
			return null;
		else {
			$row = mysql_fetch_assoc($res);
			return new Silo($row['id']);
		}
	}
		
	public function getPledgedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND status = 'Pledged' AND user_id = ".$this->user_id;
		$res = mysql_fetch_row(mysql_query($sql));		
		return floatval($res[0]);
	}
	
	public function getCollectedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND status <> 'Pledged' AND user_id = ".$this->user_id;
		$res = mysql_fetch_row(mysql_query($sql));		
		return floatval($res[0]);
	}
	
	public static function getMembers($silo_id, $order_by) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM silo_membership WHERE silo_id = $silo_id) $order_by_clause";
		$members = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new User($res);
			$members[] = $u;
		}
		return $members;
	}
	
	public function getMemberCell($silo_id) {
		$date = substr($this->joined_date,5,2).'/'.substr($this->joined_date,8,2).'/'.substr($this->joined_date,2,2);		
		$cell = "<td><div class=plate id='user_".$this->id."' style='color: #000;'><table width=100% height=100%><tr valign=top><td>";
		$cell .= "<div style='height: 15px'><a href='index.php?task=view_user&id=".$this->id."'><b>".$this->username."</b></a></div><b>Member Since:</b>".$date."<br/><img height=100px width=135px src=uploads/members/300px/".$this->photo_file." style='margin-bottom: 5px; margin-top: 5px;'><br/><b>Pledged: </b><span style='color: #f60'>$".$this->getPledgedAmount()."</span><br/><b>Sold/Donated: </b><span style='color: #f60'>$".$this->getCollectedAmount()."</span><br/>View Items: <a href='index.php?task=view_user&id=".$this->id."&silo_id=$silo_id'>This</a> | <a href=#><a href='index.php?task=view_user&id=".$this->id."'>All Silos</a></td></table></div></td>";
		return $cell;		
	}
}
?>