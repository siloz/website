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
	public $goal;
	public $purpose;
	public $description;
	public $admin_notice;
	public $photo_file;
	
	//table silo_categories
	public $silo_cat_id;
	public $type;
	public $subtype;
	public $subsubtype;	
	
	public $admin;
	
	function __construct($id) {
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
		$this->goal = $res['goal'];
		$this->purpose = $res['purpose'];
		$this->description = $res['description'];
		$this->admin_notice = $res['admin_notice'];
		$this->photo_file = $res['photo_file'];
		$this->flag_radar_id = $res['flag_radar_id'];
		$this->schedule_end_date = $res['schedule_end_date'];

		$this->silo_cat_id = $res['silo_cat_id'];
		$this->status = $res['status'];
		$this->type = $res['type'];
		$this->subtype = $res['subtype'];
		$this->subsubtype = $res['subsubtype'];

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
		return "<b>".$this->name."</b> (".$this->type." Silo)";
	}
	
	public function getAdmin() {
		return $this->admin;
	}
	
	public function getPledgedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = ".$this->silo_id." AND status = 'Pledged'";
		$res = mysql_fetch_row(mysql_query($sql));								
		return floatval($res[0]);
	}
	
	public function getCollectedAmount() {
		$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = ".$this->silo_id." AND status = 'Funds Received'";
		$res = mysql_fetch_row(mysql_query($sql));		
		return floatval($res[0]);
	}
	
	public function getTotalMembers() {
		$sql = "SELECT COUNT(DISTINCT user_id) FROM silo_membership where silo_id = ".$this->silo_id;
		$res = mysql_fetch_row(mysql_query($sql));		
		return intval($res[0]);
	}
	
	public function getTotalItems() {
		$sql = "SELECT COUNT(DISTINCT item_id) FROM items where silo_id = ".$this->silo_id;
		$res = mysql_fetch_row(mysql_query($sql));		
		return intval($res[0]);
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
		$html = "<div style='height: 325px; overflow: hidden;'>";
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
		$html .= "</td><td><img height=100px width=135px src=uploads/silos/300px/".$this->photo_file."></td></tr>";
		$html .= "<tr><td colspan=2><b>Purpose:</b> ".$this->getPurpose()."<br/><br/><b>Administrator:</b> ".$admin->username."<br/><br/>";
		$html .= "<b>Official address:</b>".$this->address."</td></tr>";
		$html .= "</table>";
		$html .= "</div>";
		$html .= "<div style='height: 20px;line-height: 20px;text-align:right; padding-right: 10px;'><a href='".$this->getLink()."'><i><b>more...</b></i></a></div>";
		$html .= "<div style='height: 20px;line-height: 20px;'><table width='100%'><tr><td align=left><button>Pledge Items For This Silo</button></td><td align=right><a href='#'>Add silo to favorites</a></td></tr></table></div>";		
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
		$cell = "<div class=plate id=silo_".$this->id."><a href='index.php?task=view_silo&id=".$this->id."' onmouseover=highlight_silo('".$this->id."') onmouseout=unhighlight_silo('".$this->id."')>";				
		$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><a href='index.php?task=view_silo&id=".$this->id."'><b>".substr($this->name, 0, 40)."</b></a></div><img height=100px width=135px src=uploads/silos/300px/".$this->photo_file." style='margin-bottom: 3px'><div style='color: #000;'>".$this->getCat(25)."<br/><b>View: </b><a href='index.php?task=view_silo&view=items&id=".$this->id."'>Items</a> | <a href='index.php?task=view_silo&view=members&id=".$this->id."'>Members</a></div></td></tr><tr valign=bottom><td align=left align=left><div style='color: #000;'><b>Goal:</b> <span style='color: #f60'>$".round($this->goal)."</span></td><td align=right><a href='index.php?task=view_silo&id=".$this->id."'><i><b>more...</b></i></a></div></td></tr></table></a></div>";							
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
			."`description` = '".mysql_real_escape_string($this->description)."', "
			."`admin_notice` = '".mysql_real_escape_string($this->admin_notice)."', "
			."`status` = '".mysql_real_escape_string($this->status)."', "
			."`photo_file` = '".mysql_real_escape_string($this->photo_file)."', "
			."`flag_radar_id` = '".mysql_real_escape_string($this->flag_radar_id)."', "
			."`schedule_end_date` = '".mysql_real_escape_string($this->schedule_end_date)."', "
			."`end_date` = '".mysql_real_escape_string($this->end_date)."' "
			."WHERE "
			."`id` = '".mysql_real_escape_string($this->id)."' "
		);
		mysql_query($query);
		if(mysql_affected_rows() >= 1){return $this->id;}
		else{return false;}
	
	}
	
	private function Insert(){
		$query = (
			"INSERT INTO "
			."`silos` "
			."(`admin_id`,`name`,`shortname`,`silo_cat_id`,`paypal_account`,`financial_account`,"
			."`bank_name`,`bank_account_number`,`bank_routing_number`,`org_name`,`ein`,"
			."`issue_receipts`,`title`,`phone_number`,`address`,`longitude`,`latitude`,"
			."`start_date`,`goal`,`purpose`,`description`,`admin_notice`,`photo_file`,"
			."`flag_radar_id`,`schedule_end_date`,`end_date`,`created_date`) "
			."VALUES "
			."("
				."'".mysql_real_escape_string($this->admin_id)."',"
				."'".mysql_real_escape_string($this->name)."',"
				."'".mysql_real_escape_string($this->shortname)."',"
				."'".mysql_real_escape_string($this->silo_cat_id)."',"
				."'".mysql_real_escape_string($this->paypal_account)."',"
				."'".mysql_real_escape_string($this->finacial_account)."',"
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
				."'".mysql_real_escape_string($this->description)."',"
				."'".mysql_real_escape_string($this->admin_notice)."',"
				."'".mysql_real_escape_string($this->photo_file)."',"
				."'".mysql_real_escape_string($this->flag_radar_id)."',"
				."'".mysql_real_escape_string($this->schedule_end_date)."',"
				."'".mysql_real_escape_string($this->end_date)."',"
				."'".mysql_real_escape_string(date('Y-m-d H:i:s'))."' "						
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
	

	//probably not going to need this
	private function AddDays($int){
		$query = "select (NOW() + INTERVAL ".mysql_real_escape_string($int)." DAY) as time;";
		$result = mysql_query($query);
		$x = mysql_fetch_object($result);
		return $x->time;
	}
}
?>
