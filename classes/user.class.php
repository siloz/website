<?php
class User {
	public $user_id;
	public $id;
	public $password;
	public $fname;
	public $lname;
	public $phone;
	public $email;
	public $address;
	public $city;
	public $state;
	public $zip_code;
	public $longitude;
	public $latitude;
	public $user_type;
	public $joined_date;
	public $last_update;
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
		$this->password = md5($res['password']);
		$this->fname = $res['fname'];
		$this->lname = $res['lname'];
		$this->phone = $res['phone'];
		$this->email = $res['email'];
		$this->address = $res['address'];
		$this->city = $res['city'];
		$this->state = $res['state'];
		$this->zip_code = $res['zip_code'];
		$this->longitude = $res['longitude'];
		$this->latitude = $res['latitude'];
		$this->user_type = $res['user_type'];
		$this->joined_date = $res['joined_date'];
		$this->last_update = strtotime($res['last_update']);
		$this->photo_file = $res['photo_file'];
		$this->status = $res['status'];
	}

	public function getCurrentSilo() {
		$sql = "SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE admin_id = ".$this->user_id;
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
			$order_by_clause = " ORDER BY fname $sort_order ";
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
		$joined = date_create($this->joined_date);
		$date = date_format($joined, 'm/d/y');

		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->fname; $admin_name .= "&nbsp;".$this->lname; } else { $admin_name = $this->fname; };
		$admin_name = (strlen($admin_name) > 25) ? substr($this->fname, 0, 1) . '. ' . $this->lname : $admin_name;

		$cell = "<td><div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_user&id=".$this->id."'>";
		$cell .= "<img src=uploads/members/".$this->photo_file.">";
		$cell .= "<div style='padding-bottom: 0px;'>".$admin_name."</div> member since: ".$date;
		$cell .= "</a></div></td>";
		return $cell;		
	}

	public function getMemberCellAdmin($silo_id, $c_user_id) {
		$joined = date_create($this->joined_date);
		$date = date_format($joined, 'm/d/y');

		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->fname; $admin_name .= "&nbsp;".$this->lname; } else { $admin_name = $this->fname; };
		$admin_name = (strlen($admin_name) > 25) ? substr($this->fname, 0, 1) . '. ' . $this->lname : $admin_name;

		$cell = "<td><form name='delMem".$this->user_id."' id='delMem".$this->user_id."' method='post' action=''><input type='hidden' name='user_id' value='".$this->user_id."'><input type='hidden' name='silo_id' value='".$silo_id."'><input type='hidden' name='delete_user' value='delete_".$this->user_id."'><a href='javascript:document.delMem".$this->user_id.".submit()' class='confirmation'><img src=images/delete.png  style='position: absolute; padding-left: 145px;'></a></form>";
		$cell .= "<div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_user&id=".$this->id."'>";
		$cell .= "<img src=uploads/members/".$this->photo_file.">";
		$cell .= "<div style='padding-bottom: 0px;'>".$admin_name."</div> member since: ".$date;
		$cell .= "</a></div></td>";
		return $cell;		
	}

	public function getMemberCellOLD($silo_id, $c_user_id) {
		$date = substr($this->joined_date,5,2).'/'.substr($this->joined_date,8,2).'/'.substr($this->joined_date,2,2);

		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->fname; $admin_name .= "&nbsp;".$this->lname; } else { $admin_name = $this->fname; };

		$cell = "<td><div class=plate id='user_".$this->id."' style='color: #000;'><table width=100% height=100%><tr valign=top><td>";
		$cell .= "<div style='height: 15px'><a href='index.php?task=view_user&id=".$this->id."'><b>".$admin_name."</b></a></div><b>Member Since: </b>".$date."<br/><img height=100px width=135px src=uploads/members/".$this->photo_file." style='margin-bottom: 5px; margin-top: 5px;'><br/><b>Pledged: </b><span style='color: #f60'>$".$this->getPledgedAmount()."</span><br/><b>Sold: </b><span style='color: #f60'>$".$this->getCollectedAmount()."</span><br/>View Items: <a href='index.php?task=view_user&id=".$this->id."&silo_id=$silo_id'>This</a> | <a href=#><a href='index.php?task=view_user&id=".$this->id."'>All Silos</a></td></table></div></td>";
		return $cell;
	}

	public function getFullName() {
		$name = $this->fname." ".$this->lname;
		return $name;
	}
	
	public function Save(){
		if($this->id){return $this->Update();}
		else{return $this->Insert();}
	}
	
	private function Insert(){
		$query = (
			"INSERT INTO `users` "
			."(`fname`,`lname`,`password`,`phone`,`email`,`address`,`city`,`state`,`zip_code`,`longitude`,`latitude`,"
			."`user_type`,`joined_date`,`validation_code`,`status`) "
			."VALUES "
			."("
				."'".mysql_real_escape_string($this->fname)."',"
				."'".mysql_real_escape_string($this->lname)."',"
				."'".mysql_real_escape_string($this->password)."',"
				."'".mysql_real_escape_string($this->phone)."',"
				."'".mysql_real_escape_string($this->email)."',"
				."'".mysql_real_escape_string($this->address)."',"
				."'".mysql_real_escape_string($this->city)."',"
				."'".mysql_real_escape_string($this->state)."',"
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
			."`fname` = '".mysql_real_escape_string($this->fname)."',"
			."`lname` = '".mysql_real_escape_string($this->lname)."',"
			."`phone` = '".mysql_real_escape_string($this->phone)."',"
			."`email` = '".mysql_real_escape_string($this->email)."',"
			."`address` = '".mysql_real_escape_string($this->address)."',"
			."`city` = '".mysql_real_escape_string($this->city)."',"
			."`state` = '".mysql_real_escape_string($this->state)."',"
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
		if (mysql_affected_rows() >= 1) {
			$subject = "Make a difference in your community, as a shopper, item donor, or silo administrator!";
			$message = "<h2>Welcome to ".SITE_NAME."!</h2>";
			$message .= "We want to thank you for creating an account with ".SITE_NAME."! We wanted to briefly tell you what you can expect as a user. ".SITE_NAME." allows local organizations to raise money by accepting donated items from local supporters.  Those items then appear for sale to the general public. <br><br>";
			$message .= "We believe ".SITE_NAME." is, quite simply, the best way for a community – private or public – to raise money for a cause.  Here are some reasons why: <br><br>";
			$message .= "<ul>
					<li>It's not shaking a collection jar; it's asking for items.</li>
					<li>Whether private or public, causes are local, and assist people you know, involve features you drive by every day, and organizations that make a real-world difference in the life of your community.</li>
					<li>Everybody wins – the silo administrator, the donor (who often receives a tax-deduction), and the buyer, who not only gets an item, but the knowledge that he or she is helping a local cause of their choosing.</li>
					<li>It's designed for viral promotion. There is no limit to a fundraising goal, and no limit to how many members can be part of a given silo.</li>
					<li>It's safe, it's transparent, and it's 90% efficient for public silos, and 95% efficient for private silos.</li>
				      </ul> <br>";
			$message .= "We invite you to communicate your questions and concerns with us.<br><br>";
			$message .= "Thank You, and Happy Fundraising, <br><br><br>";
			$message .= "Zackery West <br><br> CEO, ".SITE_NAME." LLC";
			email_with_template($this->email, $subject, $message);
			mysql_query("UPDATE users SET info_emails = 1 WHERE user_id = '$this->user_id'");

			return "success";
		}
		else {
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

	public function DeleteUser() {
		$user = mysql_fetch_array(mysql_query("SELECT email FROM users WHERE user_id = '$this->user_id'"));
		$subject = "Account deleted on ".SHORT_URL;
		$message = "<h3>You have deleted your account on ".SITE_NAME.".com</h3>";
		$message .= "You have chosen to delete your account. Please remember, this action is permanent. Everything assosciated with your account (other than sold items benefitting silos) has been deleted!<br><br>"; 
		$message .= "You will not receive any future e-mails from ".SITE_NAME.".com. Thank you for using ".SITE_NAME."!";

		email_with_template($user['email'], $subject, $message);

		$delBuyer = mysql_query("DELETE FROM buyer WHERE user_id = '$this->user_id'");
		$delFav = mysql_query("DELETE FROM favorites WHERE user_id = '$this->user_id'");
		$delFeed = mysql_query("DELETE FROM feed WHERE user_id = '$this->user_id' AND (type = 'Joined' OR type = 'Pledged')");
		$delItem = mysql_query("DELETE FROM items WHERE user_id = '$this->user_id' AND status != 'sold'");
		$delNotif = mysql_query("DELETE FROM notifications WHERE user_id = '$this->user_id'");
		$delOffer = mysql_query("DELETE FROM offers WHERE seller_id = '$this->user_id'");
		$delSilo = mysql_query("DELETE FROM silos WHERE admin_id = '$this->user_id'");
		$delMemship = mysql_query("DELETE FROM silo_membership WHERE user_id = '$this->user_id'");
		$delUsers = mysql_query("DELETE FROM users WHERE user_id = '$this->user_id'");
		$delUserPC = mysql_query("DELETE FROM user_paycodes WHERE user_id = '$this->user_id'");
		$delUserSess = mysql_query("DELETE FROM user_sessions WHERE user_id = '$this->user_id'");

		$src = 'uploads/members/'.$this->id.'.jpg';
		unlink($src);

	}

	public function randString($length) {
    		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    		$randomString = '';
    		for ($i = 0; $i < $length; $i++) {
       		$randomString .= $characters[rand(0, strlen($characters) - 1)];
    		}
    		return $randomString;
}
	
}
?>
