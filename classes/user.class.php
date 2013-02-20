<?php
class User {
	public $user_id;
	public $id;
	public $username;
	public $password;
	public $fname;
	public $lname;
	public $phone;
	public $email;
	public $address;
	public $zip_code;
	public $longitude;
	public $latitude;
	public $user_type;
	public $joined_date;
	public $photo_file;
	
	function __construct($id){
		if($id){return $this->Populate($id);}
	}
	
	function Populate($id) {
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
		$this->fname = $res['fname'];
		$this->lname = $res['lname'];
		$this->phone = $res['phone'];
		$this->email = $res['email'];
		$this->address = $res['address'];
		$this->zip_code = $res['zip_code'];
		$this->longitude = $res['longitude'];
		$this->latitude = $res['latitude'];
		$this->user_type = $res['user_type'];
		$this->joined_date = $res['joined_date'];
		$this->photo_file = $res['photo_file'];
		$this->status = $res['status'];
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
	
	public static function getMembers($silo_id, $order_by, $limit) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM silo_membership WHERE silo_id = $silo_id AND removed_date = 0) $order_by_clause $limit";
		$members = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new User($res);
			$members[] = $u;
		}
		return $members;
	}
	
	public function getMemberCell($silo_id, $c_user_id) {
		$date = substr($this->joined_date,5,2).'/'.substr($this->joined_date,8,2).'/'.substr($this->joined_date,2,2);

		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->fname; $admin_name .= "&nbsp;".$this->lname; } else { $admin_name = $this->fname; };

		$cell = "<td><div class=plate id='user_".$this->id."' style='color: #000;'><table width=100% height=100%><tr valign=top><td>";
		$cell .= "<div style='height: 15px'><a href='index.php?task=view_user&id=".$this->id."'><b>".$admin_name."</b></a></div><b>Member Since: </b>".$date."<br/><img height=100px width=135px src=uploads/members/".$this->photo_file." style='margin-bottom: 5px; margin-top: 5px;'><br/><b>Pledged: </b><span style='color: #f60'>$".$this->getPledgedAmount()."</span><br/><b>Sold: </b><span style='color: #f60'>$".$this->getCollectedAmount()."</span><br/>View Items: <a href='index.php?task=view_user&id=".$this->id."&silo_id=$silo_id'>This</a> | <a href=#><a href='index.php?task=view_user&id=".$this->id."'>All Silos</a></td></table></div></td>";
		return $cell;		
	}
	
	public function Save(){
		if($this->id){return $this->Update();}
		else{return $this->Insert();}
	}
	
	private function Insert(){
		$query = (
			"INSERT INTO `users` "
			."(`username`,`password`,`fname`,`lname`,`phone`,`email`,`address`,`zip_code`,`longitude`,`latitude`,"
			."`user_type`,`joined_date`,`validation_code`,`status`) "
			."VALUES "
			."("
				."'".mysql_real_escape_string($this->username)."',"
				."'".mysql_real_escape_string($this->password)."',"
				."'".mysql_real_escape_string($this->fname)."',"
				."'".mysql_real_escape_string($this->lname)."',"
				."'".mysql_real_escape_string($this->phone)."',"
				."'".mysql_real_escape_string($this->email)."',"
				."'".mysql_real_escape_string($this->address)."',"
				."'".mysql_real_escape_string($this->zip_code)."',"
				."'".mysql_real_escape_string($this->longitude)."',"
				."'".mysql_real_escape_string($this->latitude)."',"
				."'".mysql_real_escape_string($this->user_type)."',"
				."'".mysql_real_escape_string(date("Y-m-d H:i:s"))."',"
				."'".mysql_real_escape_string($this->validation_code)."',"
				."'pending'"
				
			.")"
		);
		mysql_query($query);
		$actual_id = mysql_insert_id();
		if($actual_id){
			$this->id = "00".time()."0".$actual_id;
			$query = "UPDATE `users` SET `id` = '".$this->id."' WHERE `user_id` = '".$actual_id."'";
			mysql_query($query);
			
			return $actual_id;
		}else{return false;}
		
	}
	
	private function Update(){
		$query = (
			"UPDATE `users` "
			."SET "
			."`username` = '".mysql_real_escape_string($this->username)."',"
			."`fname` = '".mysql_real_escape_string($this->fname)."',"
			."`lname` = '".mysql_real_escape_string($this->lname)."',"
			."`phone` = '".mysql_real_escape_string($this->phone)."',"
			."`email` = '".mysql_real_escape_string($this->email)."',"
			."`address` = '".mysql_real_escape_string($this->address)."',"
			."`zip_code` = '".mysql_real_escape_string($this->zip_code)."',"
			."`longitude` = '".mysql_real_escape_string($this->longitude)."',"
			."`latitude` = '".mysql_real_escape_string($this->latitude)."',"
			."`user_type` = '".mysql_real_escape_string($this->user_type)."',"
			."`status` = '".mysql_real_escape_string($this->status)."',"
			."`validation_code` = '".mysql_real_escape_string($this->validation_code)."'"
			."WHERE `user_id` = '".$actual_id."' "
		);
		mysql_query($query);
		if(mysql_affected_rows() >= 1){return $this->user_id;}
		else{return false;}
	}
	
	public function ValidateRegistration($id,$code){
		$query = (
			"UPDATE `users` SET `validation_code` = '-1', "
			."`status` = 'active' "
			."WHERE `id` = '".mysql_real_escape_string($id)."' "
			."AND `validation_code` = '".mysql_real_escape_string($code)."' "
		);
		error_log($query);
		mysql_query($query);
		if(mysql_affected_rows() >= 1){return "success";}
		else{
			$query = (
				"SELECT `activation_code` FROM `users` "
				."WHERE `id` = '".mysql_real_escape_string($id)."' "
				."AND `validation_code` = '-1'"
			);
			mysql_query($query);
			if(mysql_affected_rows() >= 1){return "active";}
			else{return false;}
		}
	}

	public function DeleteUser(){
		$qry = "DELETE FROM users WHERE user_id = '$this->user_id'";
		mysql_query($qry);

		$src = 'uploads/members/'.$this->id.'.jpg';
		unlink($src);

	}
	
}
?>
