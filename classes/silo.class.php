<?php
class Silo {
	//table silos
	public $silo_id;
	public $admin_id;
	public $id;
	public $name;
	public $shortname;
	public $paypal_account;
	public $financial_account;
	public $bank_name;
	public $bank_account_number;
	public $bank_routing_number;
	public $org_name;
	public $ein;
	public $issue_receipts;
	public $title;
	public $phone_number;
	public $address;
	public $zip_code;
	public $longitude;
	public $latitude;
	public $start_date;
	public $end_date;
	public $created_date;
	public $last_update;
	public $goal;
	public $purpose;
	public $photo_file;
	public $silo_cat_id;
	public $type;
	public $paid;
	public $thanked;
	
	public $admin;
	
	function __construct($id= false){
		if($id){return $this->Populate($id);}
	}
	
	function Populate($id) {
		$id = (string)$id;
		if ($id[0] == '0')		
			$res = mysql_fetch_array(mysql_query("SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE id  = '$id'"));
		else
			$res = mysql_fetch_array(mysql_query("SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE silo_id  = $id"));

		$this->silo_id = $res['silo_id'];
		$this->admin_id = $res['admin_id'];
		$this->id = $res['id'];
		$this->name = $res['name'];
		$this->shortname = $res['shortname'];
		$this->paypal_account = $res['paypal_account'];
		$this->financial_account = $res['financial_account'];
		$this->bank_name = $res['bank_name'];
		$this->bank_account_number	 = $res['bank_account_number'];
		$this->bank_routing_number = $res['bank_routing_number'];
		$this->org_name = $res['org_name'];
		$this->ein = $res['ein'];
		$this->issue_receipts = $res['issue_receipts'];
		$this->title = $res['title'];
		$this->phone_number = $res['phone_number'];
		$this->address = $res['address'];
		$this->zip_code = $res['zip_code'];
		$this->longitude = $res['longitude'];
		$this->latitude = $res['latitude'];
		$this->start_date = $res['start_date'];
		$this->end_date = $res['end_date'];
		$this->created_date = $res['created_date'];
		$this->last_update = strtotime($res['last_update']);
		$this->goal = $res['goal'];
		$this->purpose = $res['purpose'];
		$this->photo_file = $res['photo_file'];
		$this->flag_radar_id = $res['flag_radar_id'];
		$this->schedule_end_date = $res['schedule_end_date'];

		$this->silo_cat_id = $res['silo_cat_id'];
		$this->silo_type = $res['silo_type'];
		$this->status = $res['status'];
		$this->paid = $res['paid'];
		$this->thanked = $res['thanked'];

		$this->admin = new User($this->admin_id);
	}
	
	static function itemsFromQuery($query) {
		$tmp = mysql_query($query);
		$items = array();
		while ($item = mysql_fetch_array($tmp)) {
			$items[] = $item;
		}
		return $items;
	}
	
	public function getTitle() {
		return "<b>".$this->name."</b>";
	}
	
	public function getAdmin() {
		return $this->admin;
	}

	public function getAdminFCount() {
		$friend_count = mysql_fetch_row(mysql_query("SELECT friend_count FROM users WHERE user_id = '$this->admin_id'"));
		return $friend_count[0];
	}
	
	public function getPledgedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE silo_id = ".$this->silo_id." AND (status = 'pledged' OR status = 'offer')";
		$res = mysql_fetch_row(mysql_query($sql));								
		return floatval($res[0]);
	}
	
	public function getCollectedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE silo_id = ".$this->silo_id." AND status = 'sold'";
		$res = mysql_fetch_row(mysql_query($sql));
		if ($this->silo_type == "public") { $pctOf = ".9"; } else { $pctOf = ".95"; }		
		return floatval($res[0] * $pctOf);
	}
	
	public function getTotalMembers() {
		$sql = "SELECT COUNT(DISTINCT user_id) FROM silo_membership WHERE silo_id = ".$this->silo_id." AND removed_date = 0";
		$res = mysql_fetch_row(mysql_query($sql));
		$total = intval($res[0]);
		if ($total == 1) { $total .= " member"; } else { $total .= " members"; }
		return $total;
	}

	public function getTotalMembersAC() {
		$sql = "SELECT COUNT(DISTINCT user_id) FROM silo_membership WHERE silo_id = ".$this->silo_id." AND removed_date = 0";
		$res = mysql_fetch_row(mysql_query($sql));
		$total = intval($res[0]);
		return $total;
	}

	public function getDaysLeft() {
		$end_date = $this->end_date;
		$end = strtotime("$end_date");
		$now = time();
		$timeleft = $end-$now;
		$days = ceil($timeleft/86400);		
		if ($days > 1) { $days .= " Days Left"; } elseif ($days == 1) { $days .= " Day Left"; } else { $days = "Ended on ".date("m/d/y", $end); }
		return $days;
	}

	public function getDeadline() {
		$end_date = $this->end_date;
		$end = strtotime("$end_date");
		$deadline = date("M jS, Y", $end);
		return $deadline;
	}

	public function getAvgItemPrice() {
		$p = "SELECT SUM(price) FROM items WHERE silo_id = ".$this->silo_id." AND status != 'flagged' AND status != 'deleted'";
		$pres = mysql_fetch_row(mysql_query($p));								
		$ptotal = $pres[0];
		$i = "SELECT COUNT(DISTINCT item_id) FROM items where silo_id = ".$this->silo_id;
		$ires = mysql_fetch_row(mysql_query($i));		
		$itotal = $ires[0];
		return floatval($ptotal/$itotal);
	}

	public function getAvgListings() {
		$i = "SELECT COUNT(DISTINCT item_id) FROM items WHERE silo_id = ".$this->silo_id." AND status != 'flagged' AND status != 'deleted'";
		$ires = mysql_fetch_row(mysql_query($i));		
		$itotal = $ires[0];
		$m = "SELECT COUNT(DISTINCT user_id) FROM silo_membership where silo_id = ".$this->silo_id." AND removed_date = 0";
		$mres = mysql_fetch_row(mysql_query($m));
		$mtotal = $mres[0];
		return intval($itotal/$mtotal);
	}
	
	public function getTotalItems() {
		$sql = "SELECT COUNT(DISTINCT item_id) FROM items WHERE silo_id = ".$this->silo_id." AND status != 'flagged' AND status != 'deleted'";
		$res = mysql_fetch_row(mysql_query($sql));		
		$total = intval($res[0]);
		if ($total == 1) { $total .= " item"; } else { $total .= " items"; }
		return $total;
	}

	public function getTotalItemsAC() {
		$sql = "SELECT COUNT(DISTINCT item_id) FROM items WHERE silo_id = ".$this->silo_id." AND status != 'flagged' AND status != 'deleted'";
		$res = mysql_fetch_row(mysql_query($sql));		
		$total = intval($res[0]);
		return $total;
	}

	public function getRunTime() {
		$sql = "SELECT DATEDIFF(end_date, created_date) AS days FROM silos WHERE silo_id = ".$this->silo_id."";
		$res = mysql_fetch_array(mysql_query($sql));
		$days = $res['days'];
		if ($days < 8) { $runtime = 1; } elseif ($days > 7 && $days < 21) { $runtime = 2; } else { $runtime = 3; }
		return $runtime;
	}

	public function getPurpose() {
		$order   = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, html_entity_decode($this->purpose));
	}
	
	public function getDescription() {
		$order   = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, html_entity_decode($this->description));
	}
	
	public function getShortName($len) {
		if (strlen($this->name) > $len) {
			return substr($this->name, 0, $len)."..";
		}
		else {
			return $this->name;
		}		
	}
	
	public function getLink() {
		return "index.php?task=view_silo&id=".$this->id;
	}
	
	public function getPreview() {
		$admin = $this->getAdmin();
		$html = "<a href='index.php?task=view_silo&id=".$this->id."' onmouseover=highlight_silo('".$this->id."') onmouseout=unhighlight_silo('".$this->id."')>";
		$html .= "<div style='height: 325px; overflow: hidden;'>";
		$html .= "<table>";
		$html .= "<tr><td colspan=2 align=center><div style='color: #2f8dcb; font-weight: bold; font-size:12px; margin-bottom: 5px;'>".$this->name."</div></td></tr>";
		$html .= "<tr><td height=20% colspan=2 align=center>Category: ".$this->subtype.", Members: ".$this->getTotalMembers().", Items: ".$this->getTotalItems()."</td></tr>";
		$collected = $this->getCollectedAmount();
		$pct = round($collected*100.0/floatval($this->goal),1);															
		$html .= "<tr><td>Goal: ".money_format('%(#10n', $this->goal)."<br/>Collected: ".money_format('%(#10n', $collected)." ($pct %)<br/>";
		$html .= "<b>Progress:</b><div style='width: 120px; height: 10px; border: 1px solid #2F8ECB;'><div style='width: $pct%; height:10px; background: #2F8ECB;'></div></div><br/>";
		$html .= "Ends: ".$this->end_date."<br/>";
		$pct_day = 100*min(1,(time() - strtotime($this->start_date))/(strtotime($this->end_date) - strtotime($this->start_date)));
		$html .= "<b>Deadline:</b><div style='width: 120px; height: 10px; border: 1px solid #2F8ECB;'><div style='width: $pct_day%; height:10px; background: #2F8ECB;'></div></div>";
		$html .= "</td><td><img height=100px width=135px src='uploads/silos/".$this->photo_file."?".$this->last_update."'></td></tr>";
		$html .= "<tr><td colspan=2><b>Purpose:</b> ".$this->getPurpose()."<br/><br/><b>Administrator:</b> ".$admin_name."<br/><br/>";
		$html .= "<b>Official address:</b>".$this->address."</td></tr>";
		$html .= "</table>";
		$html .= "</div>";
		return $html;
	}
	
	public function getCat($len) {
		$s = $this->subtype." (".$this->subsubtype.")";
		if (strlen($s) >= $len) {
			return substr($s, 0, $len-2)."..";
		}
		else {
			return $s;
		}
	}
	public function getPlate() {
		$collected = $this->getCollectedAmount();
		$pct = round($collected*100.0/floatval($this->goal),1);

		$end_date = $this->end_date;
		$end = strtotime("$end_date");
		$now = time();
		$timeleft = $end-$now;
		$daysleft = ceil($timeleft/86400);
		
		if ($daysleft > 1){ $dayplural = "Days"; } else { $dayplural = "Day"; }
														
		$cell = "<div class='plateSilo span2' id=silo_".$this->id."><a href='index.php?task=view_silo&id=".$this->id."' onmouseover=highlight_silo('".$this->id."') onmouseout=unhighlight_silo('".$this->id."')>";				
		$cell .= "<div style='text-align: center; height: 40px'><a href='index.php?task=view_silo&id=".$this->id."'><b>".substr($this->name, 0, 40)."</b></a></div><center><img height=126px width=168px src='uploads/silos/".$this->photo_file."?".$this->last_update."' style='margin-left: -4px; margin-bottom: 3px'></center><div style='text-align: center; color: #000;'><div style='color: #f60'><b>Goal: </b>$".round($this->goal)."</div><span>$daysleft $dayplural Left</span></a></div></div>";							
		return $cell;
	}
	
	public function getSiloPlate($first) {
		$collected = $this->getCollectedAmount();
		$pct = round($collected*100.0/floatval($this->goal),1);

		$end_date = $this->end_date;
		$end = strtotime("$end_date");
		$now = time();
		$timeleft = $end-$now;
		$daysleft = ceil($timeleft/86400);
		
		if ($daysleft > 1){ $dayplural = "Days"; } else { $dayplural = "Day"; }
														
		$cell = "<div class='plateSilo span2";
		
		if ($first) {
			$cell .= " first_element";
		}
		
		$cell .= "' id=silo_".$this->id." onclick='window.location = \"index.php?task=view_silo&id=".$this->id."\"'>";				
		$cell .= "<div style='display: table; margin-left: -1px; height: 40px; #position: relative; overflow: hidden; width: 100%;'><div style='#position: absolute; #top: 50%;display: table-cell; vertical-align: top; text-align: center;'>
				<a href='index.php?task=view_silo&id=".$this->id."'><b>".substr($this->name, 0, 40)."</b></a></div></div><center><img height=136px width=182px src='uploads/silos/".$this->photo_file."?".$this->last_update."' style='margin-left: -4px; margin-bottom: 3px'></center><div style='text-align: center; class='blue'><b>Goal:</b> <span class='orange'>$".round($this->goal)."</span> &nbsp; &nbsp; &nbsp; $daysleft $dayplural Left</span></a></div></div>";							
		return $cell;
	}

	public function isEnded() {
		$today = date('Y-m-d')."";
		//return $this->end_date < $today;
		return false;
	}
	
	/*getMemberIds()
	 *
	 *
	 * @return array($member_ids)
	 * @author james kenny //sep 8th,2012
	 */
	function getMemberIds($silo_id = false){
		if($silo_id){$silo_id = $this->silo_id;}
		$query = 
			"select user_id from silo_membership where silo_id = '"
			.mysql_real_escape_string($silo_id)."';"
		;
		$result - mysql_query($result);
		if(mysql_affected_rows >= 1){
			while($row = mysql_fetch_object($result)){
				$x[] = $row->user_id;
			}
			return $x;
		}else{return false;}
	}
	
	function Save(){
		if($this->id){
			return $this->Update();
		}else{
			return $this->Insert();
		}
	}
	
	private function Update(){
		$query = (
			"UPDATE `silos` "
			."SET "
			."`name` = '".mysql_real_escape_string($this->name)."', "
			."`shortname` = '".mysql_real_escape_string($this->shortname)."', "
			."`silo_cat_id` = '".mysql_real_escape_string($this->silo_cat_id)."', "
			."`silo_type` = '".mysql_real_escape_string($this->silo_type)."', "
			."`paypal_account` = '".mysql_real_escape_string($this->paypal_account)."', "
			."`financial_account` = '".mysql_real_escape_string($this->financial_account)."', "
			."`bank_name` = '".mysql_real_escape_string($this->bank_account)."', "
			."`bank_account_number` = '".mysql_real_escape_string($this->bank_account_number)."', "
			."`bank_routing_number` = '".mysql_real_escape_string($this->bank_routing_number)."', "
			."`org_name` = '".mysql_real_escape_string($this->org_name)."', "
			."`ein` = '".mysql_real_escape_string($this->ein)."', "
			."`issue_receipts` = '".mysql_real_escape_string($this->issue_receipts)."', "
			."`title` = '".mysql_real_escape_string($this->title)."', "
			."`phone_number` = '".mysql_real_escape_string($this->phone_number)."', "
			."`address` = '".mysql_real_escape_string($this->address)."', "
			."`longitude` = '".mysql_real_escape_string($this->longitude)."', "
			."`latitude` = '".mysql_real_escape_string($this->latitude)."', "
			."`start_date` = '".mysql_real_escape_string($this->start_date)."', "
			."`goal` = '".mysql_real_escape_string($this->goal)."', "
			."`purpose` = '".mysql_real_escape_string($this->purpose)."', "
			."`status` = '".mysql_real_escape_string($this->status)."', "
			."`photo_file` = '".mysql_real_escape_string($this->photo_file)."', "
			."`flag_radar_id` = '".mysql_real_escape_string($this->flag_radar_id)."', "
			."`end_date` = '".mysql_real_escape_string($this->end_date)."' "
			."WHERE "
			."`id` = '".mysql_real_escape_string($this->id)."' "
		);
		mysql_query($query);
		
		if(mysql_affected_rows() >= 1){return $this->id;}
		else{return false;}
	
	}

	private function InsertNEW(){
		$sql = mysql_query("INSERT INTO silos (admin_id, name, shortname, silo_cat_id, silo_type, org_name, ein, issue_receipts, 
					title, phone_number, address, longitude, latitude, start_date, goal, purpose, photo_file, flag_radar_id, schedule_end_date, end_date) 
			VALUES (
				'".mysql_real_escape_string($this->admin_id)."',
				'".mysql_real_escape_string($this->name)."',
				'".mysql_real_escape_string($this->shortname)."',
				'".mysql_real_escape_string($this->silo_cat_id)."',
				'".mysql_real_escape_string($this->silo_type)."',
				'".mysql_real_escape_string($this->org_name)."',
				'".mysql_real_escape_string($this->ein)."',
				'".mysql_real_escape_string($this->issue_receipts)."',
				'".mysql_real_escape_string($this->title)."',
				'".mysql_real_escape_string($this->phone_number)."',
				'".mysql_real_escape_string($this->address)."',
				'".mysql_real_escape_string($this->longitude)."',
				'".mysql_real_escape_string($this->latitude)."',
				'".mysql_real_escape_string($this->start_date)."',
				'".mysql_real_escape_string($this->goal)."',
				'".mysql_real_escape_string($this->purpose)."',
				'".mysql_real_escape_string($this->photo_file)."',
				'".mysql_real_escape_string($this->flag_radar_id)."',
				'".mysql_real_escape_string($this->schedule_end_date)."',
				'".mysql_real_escape_string($this->end_date)."'
			)");
		return true;
			
	}
	
	private function Insert(){
		error_log($this->purpose);
		error_log($this->financial_account);
		$query = (
			"INSERT INTO "
			."`silos` "
			."(`admin_id`,`name`,`shortname`,`silo_cat_id`,`silo_type`,`paypal_account`,`financial_account`,"
			."`bank_name`,`bank_account_number`,`bank_routing_number`,`org_name`,`ein`,"
			."`issue_receipts`,`title`,`phone_number`,`address`,`longitude`,`latitude`,"
			."`start_date`,`goal`,`purpose`,`photo_file`,"
			."`flag_radar_id`,`end_date`,`created_date`,`status`) "
			."VALUES "
			."("
				."'".mysql_real_escape_string($this->admin_id)."',"
				."'".mysql_real_escape_string($this->name)."',"
				."'".mysql_real_escape_string($this->shortname)."',"
				."'".mysql_real_escape_string($this->silo_cat_id)."',"
				."'".mysql_real_escape_string($this->silo_type)."',"
				."'".mysql_real_escape_string($this->paypal_account)."',"
				."'".mysql_real_escape_string($this->financial_account)."',"
				."'".mysql_real_escape_string($this->bank_name)."',"
				."'".mysql_real_escape_string($this->bank_account_number)."',"
				."'".mysql_real_escape_string($this->bank_routing_number)."',"
				."'".mysql_real_escape_string($this->org_name)."',"
				."'".mysql_real_escape_string($this->ein)."',"
				."'".mysql_real_escape_string($this->issue_receipts)."',"
				."'".mysql_real_escape_string($this->title)."',"
				."'".mysql_real_escape_string($this->phone_number)."',"
				."'".mysql_real_escape_string($this->address)."',"
				."'".mysql_real_escape_string($this->longitude)."',"
				."'".mysql_real_escape_string($this->latitude)."',"
				."'".mysql_real_escape_string($this->start_date)."',"
				."'".mysql_real_escape_string($this->goal)."',"
				."'".mysql_real_escape_string($this->purpose)."',"
				."'".mysql_real_escape_string($this->photo_file)."',"
				."'".mysql_real_escape_string($this->flag_radar_id)."',"
				."'".mysql_real_escape_string($this->end_date)."',"
				."'".mysql_real_escape_string(date('Y-m-d H:i:s'))."',"
				."'".mysql_real_escape_string($this->status)."'"						
			.")"
		);
		mysql_query($query);
		$actual_id = mysql_insert_id();
		$id = "01".time()."0".$actual_id;
		$this->id = $id;
		$query = 
			"UPDATE `silos` SET `id` = '"
			.mysql_real_escape_string($id)
			."' WHERE `silo_id` = '"
			.mysql_real_escape_string($actual_id)."' "
		;
		mysql_query($query);
		return $actual_id;
			
	}
	
	function GetUserSiloId($user_id){
		$query = (
			"select silo_id from silos "
			."where admin_id = '".mysql_real_escape_string($user_id)."' " 
			//."and status = 'active' "
			."order by id desc limit 1;"
		);
		error_log($query);
		$result = mysql_query($query);
		if(mysql_affected_rows() >=1 ){
			$row = mysql_fetch_object($result);
			error_log("returning ".$row->silo_id);
			return $row->silo_id;
		}
		else{
			return false;
		}
	}
	
	//probably not going to need this
	private function AddDays($int){
		$query = "select (NOW() + INTERVAL ".mysql_real_escape_string($int)." DAY) as time;";
		$result = mysql_query($query);
		$x = mysql_fetch_object($result);
		return $x->time;
	}
}
?>