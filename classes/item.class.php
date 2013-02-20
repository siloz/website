<?php
class Item {
	public $item_id;
	public $user_id;
	public $silo_id;
	public $id;
	public $title;
	public $avail;
	public $price;
	public $item_cat_id;
	public $description;
	public $longitude;
	public $latitude;
	public $status;
	public $link;
	public $added_date;
	public $sold_date;
	public $sent_date;
	public $received_date;
	public $deleted_date;
	public $photo_file_1;
	public $photo_file_2;
	public $photo_file_3;
	public $photo_file_4;			
	public $category;
	
	public $silo;
	public $owner;
	
	function __construct($id){
		return $this->Populate($id);
	}

	function Populate($id) {
		$id = (string)$id;
		if ($id[0] == '0')		
			$res = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE id = '$id'"));
		else
			$res = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE item_id = '$id'"));
		$this->item_id = $res['item_id'];
		$this->user_id = $res['user_id'];
		$this->silo_id = $res['silo_id'];
		$this->id = $res['id'];
		$this->title = $res['title'];
		$this->avail = $res['avail'];
		$this->price = $res['price'];
		$this->item_cat_id = $res['item_cat_id'];
		$this->description = $res['description'];
		$this->longitude = $res['longitude'];
		$this->latitude = $res['latitude'];
		$this->status = $res['status'];
		$this->link = $res['link'];
		
		
		$this->photo_file_1 = $res['photo_file_1'];
		$this->photo_file_2 = $res['photo_file_2'];
		$this->photo_file_3 = $res['photo_file_3'];
		$this->photo_file_4 = $res['photo_file_4'];
		$this->category = $res['category'];
		//dates
		$this->added_date = $res['added_date']; 
		$this->sold_date = $res['sold_date'];
		$this->sent_date = $res['sent_date'];
		$this->received_date = $res['received_date'];
		$this->deleted_date = $res['deleted_date'];
		$this->end_date = $res['end_date'];
		$this->flag_radar_id = $res['flag_radar_id'];
		
		//logic to update status automatically
		$empty_date = "0000-00-00 00:00:00";
		$status = "pledged";
		//see if the item has been sold
		if($this->sold_date != $empty_date){
			$status = "sold";
		}
		//check if the item has been paid for
		if($this->sent_date != $empty_date){
			$status = "paid";
		}
		//check if the item has been been picked up by the buyer
		if($this->received_date != $empty_date){
			$status = "complete";
		}
		//check to see if this item has been canceled by flags
		if($this->flag_radar_id){
			$FlagRadar = new FlagRadar($this->flag_radar_id);
			if($FlagRadar->status === "cancel"){
				$status = "flagged";
			}
		}
		//now check the status
		if($this->status != $status){
			$this->status = $status;
			self::Update();
		}
		
		$this->silo = new Silo($this->silo_id);
		$this->owner = new User($this->user_id);

		$resItem = mysql_fetch_array(mysql_query("SELECT * FROM items WHERE silo_id = '$this->silo_id'"));
		$this->itemLong = $resItem['longitude'];
		$this->itemLat = $resItem['latitude'];

	}
	
	function getOwner() {
		return $this->owner;
	}
	
	function getSilo() {
		return $this->silo;
	}
	
	function getShortTitle($len) {
		if (strlen($this->title) > $len) {
			return substr($this->title, 0, $len)."..";
		}
		else {
			return $this->title;
		}		
	}
	
	function getPlate() {
		$cell = "<div class='plateItem span2' id=item_".$this->id." onClick='window.location = index.php?task=view_item&id=".$this->id."'><a href='index.php?task=view_item&id=".$this->id."'";
		$cell .= "\<div style='margin-top: 5px; height: 15px'><img height=100px width=135px src=uploads/items/".$this->photo_file_1." style='margin-bottom: 3px'></div><div style='margin-bottom: 3px'><a href='index.php?task=view_item&id=".$this->id."'><b>".$this->getShortTitle(40)."</b></a><br><span style='color: #f60'><b>$".$this->price."</b></span></div></a></div>";
		return $cell;
	}
	
	function getItemPlate($first) {
		$cell = "<div class='plateItem span2";
		
		if ($first) {
			$cell .= " first_element";
		}
		
		$cell .= "' id=item_".$this->id." onclick='window.location = \"index.php?task=view_item&id=".$this->id."\"'>";
		$cell .= "<div style='margin-top: 5px;'><img height=100px width=135px src=uploads/items/".$this->photo_file_1." style='margin-bottom: 3px;'></div><div style='margin-bottom: 3px'><a href='index.php?task=view_item&id=".$this->id."'><b>".$this->getShortTitle(40)."</b></a><br><span style='color: #f60'><b>$".$this->price."</b></span></div></a></div>";
		return $cell;
	}
	
	public function getSiloPreview() {
		return $this->silo->getPreview();
	}
	
	public function getPhoto($id) {
		if ($id == 1)
			return $this->photo_file_1;
		else if ($id == 2)
			return $this->photo_file_2;
		else if ($id == 3)
			return $this->photo_file_3;
		else if ($id == 4)
			return $this->photo_file_4;
	}
	
	public static function getItems($silo_id, $order_by, $limit) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE deleted_date = 0 AND silo_id = $silo_id $order_by_clause $limit";
		$items = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new Item($res['item_id']);
			$items[] = $u;
		}
		return $items;
	}
	
	public function getItemCell($silo_id, $c_user_id) {
		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->owner->fname; $admin_name .= "&nbsp;".$this->owner->lname; } else { $admin_name = $this->owner->fname; };

		$cell = "<td><div class=plate id='item_".$this->id."' style='color: #000;'>";
		$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><a href='index.php?task=view_item&id=".$this->id."'><b>".substr($this->title, 0, 40)."</b></a></div><img height=100px width=135px src=uploads/items/".$this->photo_file_1." style='margin-bottom: 3px'><b>Member: </b><a href='index.php?task=view_user&id=".$this->owner->id."'>".$admin_name."</a></div></td></tr><tr valign=bottom><td align=center align=center><span style='color: #f60'><b>$".$this->price."</b></span></td></tr></table></div></td>";							
		return $cell;
	}
	
	public function GetIds($value,$key = false,$overide = false){
		switch(strtolower($key)){
			case "silo":
			case "silo_id": $key = "silo_id"; break;
			case "flag_radar_id": $key = "flag_radar_id"; break;
			case "user":
			case "user_id": $key = "user_id"; break;
			case "cat_id":
			case "restore": $key = "silo_id"; $append = "flag_radar_id";break;
			case "item_cat_id": $key = "item_cat_id"; break;
			default: return false;
		}
		
		$query = (
			"SELECT `item_id` FROM "
			."`items` "
			."WHERE "
			."`".$key."` = '".mysql_real_escape_string($value)."' "
		);
		if(!$overide){
			$query .= ("AND `status`= 'pledged' ");
		
		}
		if($overide && $append){
			$query .= "AND `".$append."` = '".mysql_real_escape_string($overide)."' ";
		}
		error_log("ITEM->GETIDS() ".$query);
		$result = mysql_query($query);
		if(mysql_affected_rows() >= 1){
			while($row = mysql_fetch_object($result)){$x[] = $row->item_id;}
			return $x;
		}else{return false;}
		
	}

    function getDistance($lat1, $lng1, $lat2, $lng2, $miles = true)
    {
    $pi80 = M_PI / 180;
    $lat1 *= $pi80;
    $lng1 *= $pi80;
    $lat2 *= $pi80;
    $lng2 *= $pi80;
     
    $r = 6372.797; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;
     
    $miles = ($miles ? ($km * 0.621371192) : $km);

	if ($miles < 75) { return true; } else { return false; } 
    }
	
	public function Save(){
		if($this->id){
			return $this->Update();
		}else{
			return $this->Insert();
		}
	}
	
	public function Update(){
		$query = (
			"UPDATE `items` "
			."SET "
			."`title` = '".mysql_real_escape_string($this->title)."', "
			."`avail` = '".mysql_real_escape_string($this->avail)."', "
			."`price` = '".mysql_real_escape_string($this->price)."', "
			."`item_cat_id` = '".mysql_real_escape_string($this->item_cat_id)."', "
			."`description` = '".mysql_real_escape_string($this->description)."', "
			."`status` = '".mysql_real_escape_string($this->status)."', "
			."`link` = '".mysql_real_escape_string($this->link)."', "
			."`photo_file_1` = '".mysql_real_escape_string(str_replace("'","",$this->photo_file_1))."', "
			."`photo_file_2` = '".mysql_real_escape_string(str_replace("'","",$this->photo_file_2))."', "
			."`photo_file_3` = '".mysql_real_escape_string(str_replace("'","",$this->photo_file_3))."', "
			."`photo_file_4` = '".mysql_real_escape_string(str_replace("'","",$this->photo_file_4))."', "
			."`added_date` = '".mysql_real_escape_string($this->added_date)."', "
			."`sold_date` = '".mysql_real_escape_string($this->sold_date)."', "
			."`sent_date` = '".mysql_real_escape_string($this->sent_date)."', "
			."`received_date` = '".mysql_real_escape_string($this->received_date)."', "
			."`flag_radar_id` = '".mysql_real_escape_string($this->flag_radar_id)."', "
			."`deleted_date` = '".mysql_real_escape_string($this->deleted_date)."', "
			."`end_date` = '".mysql_real_escape_string($this->end_date)."' "
			."WHERE "
			."`item_id` = '".mysql_real_escape_string($this->item_id)."' "
		);
		return Common::MysqlQuery($query);
	}
	
	public function Insert(){
		$query = (
			"INSERT INTO `items` "
			."("
				."user_id,silo_id,title,avail,price,item_cat_id,description,status,"
				."link,photo_file_1,photo_file_2,photo_file_3,photo_file_4,added_date,"
				."sold_date,sent_date,received_date,deleted_date"
			.")VALUES("
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->silo_id)."',"
				."'".mysql_real_escape_string($this->title)."',"
				."'".mysql_real_escape_string($this->avail)."',"
				."'".mysql_real_escape_string($this->price)."',"
				."'".mysql_real_escape_string($this->item_cat_id)."',"
				."'".mysql_real_escape_string($this->description)."',"
				."'active',"
				."'".mysql_real_escape_string($this->link)."',"
				."'".mysql_real_escape_string($this->photo_file_1)."',"
				."'".mysql_real_escape_string($this->photo_file_2)."',"
				."'".mysql_real_escape_string($this->photo_file_3)."',"
				."'".mysql_real_escape_string($this->photo_file_4)."',"
				."'".mysql_real_escape_string($this->added_date)."',"
				."'".mysql_real_escape_string($this->sold_date)."',"
				."'".mysql_real_escape_string($this->sent_date)."',"
				."'".mysql_real_escape_string($this->received_date)."',"
				."'".mysql_real_escape_string($this->deleted_date)."'"
			.")"
		);
		mysql_query($query);
		$this->item_id = mysql_insert_id();
		$id = "01".time()."0".$this->item_id;
		$this->id = $id;
		$query = 
			"UPDATE `items` SET `id` = '"
			.mysql_real_escape_string($id)
			."' WHERE `item_id` = '"
			.mysql_real_escape_string($this->item_id)."' "
		;
		mysql_query($query);
		return $this->item_id;
		
	}

	public function AddFav(){
		$query = (
			"INSERT INTO `favorites` "
			."("
				."user_id,item_id"
			.")VALUES("
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."'"
			.")"
		);
		mysql_query($query);
	}

	public function RemoveFav(){
		$query = "DELETE FROM favorites WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'";
		mysql_query($query);
	}

	public function NewOffer(){
		$exp = date('Y-m-d H:i:s', strtotime('+1 day'));

		$query = (
			"INSERT INTO `offers` "
			."("
				."item_id,buyer_id,seller_id,amount,expired_date"
			.")VALUES("
				."'".mysql_real_escape_string($this->item_id)."',"
				."'".mysql_real_escape_string($this->buyer_id)."',"
				."'".mysql_real_escape_string($this->seller_id)."',"
				."'".mysql_real_escape_string($this->amount)."',"
				."'".mysql_real_escape_string($exp)."'"
			.")"
		);
		mysql_query($query);
	}

	public function RemoveOffer(){
		$query = "UPDATE offers SET avail = 'no' WHERE item_id = '$this->item_id' AND buyer_id = '$this->buyer_id' AND seller_id = '$this->seller_id'";
		mysql_query($query);
	}
}
?>
