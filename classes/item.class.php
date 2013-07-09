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
	public $last_update;
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

		$offerUser = mysql_fetch_array(mysql_query("SELECT status, amount FROM offers WHERE buyer_id = '$_SESSION[user_id]' AND item_id = '$this->item_id'"));
		$offerStatus = $offerUser['status'];
		$offerAmount = $offerUser['amount'];
		if ($offerStatus == 'accepted') { $this->priceOffer = $offerAmount; } else { $this->priceOffer = $res['price']; }

		$this->price = $res['price'];
		$this->item_cat_id = $res['item_cat_id'];
		$this->description = $res['description'];
		$this->longitude = $res['longitude'];
		$this->latitude = $res['latitude'];
		$this->status = $res['status'];
		$this->link = $res['link'];
		$this->last_update = strtotime($res['last_update']);
		
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
		//if($this->status != $status){
		//	$this->status = $status;
		//	self::Update();
		//}
		
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
		$cell .= "\<div style='margin-top: 5px; height: 15px'><img height=100px width=135px src='uploads/items/".$this->photo_file_1."?".$this->last_update."' style='margin-bottom: 3px'></div><div style='margin-bottom: 3px'><a href='index.php?task=view_item&id=".$this->id."'><b>".$this->getShortTitle(40)."</b></a><br><span style='color: #f60'><b>$".$this->priceOffer."</b></span></div></a></div>";
		return $cell;
	}
	
	function getItemPlate($first) {
		if (strlen($this->title) < 22) { 
			$extraBreak = "<br>";
		}

		$cell = "<div class='plateItem span2";
		
		if ($first) {
			$cell .= " first_element";
		}
		
		$cell .= "' id=item_".$this->id." onclick='window.location = \"index.php?task=view_item&id=".$this->id."\"'>";
		$cell .= "<img class='photoItem' height=110px width=149px src='uploads/items/".$this->photo_file_1."?".$this->last_update."'><div style='display: table; margin-left: -1px; height: 40px; #position: relative; overflow: hidden; width: 100%;'><div style='#position: absolute; #top: 50%;display: table-cell; vertical-align: middle; text-align: center;'>
			<a href='index.php?task=view_item&id=".$this->id."'><span style='color: #0F5684;'><b>".$this->getShortTitle(29)."</b></span></a><br>".$extraBreak."<span style='color: #f60'><b>$".$this->priceOffer."</b></span></div></div></a></div>";
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

	public function getFormattedEndDate() {
		$end = strtotime($this->silo->end_date);
		$end_date = date("M jS, Y", $end);
		return $end_date;
	}

	public function getUrl() {
		$url = ACTIVE_URL."index.php?task=view_item&id=".$this->id;
		return $url;
	}

	public function getPhotoUrl() {
		$photo_url = ACTIVE_URL.'uploads/items/'.$this->photo_file_1.'?'.$this->last_update;
		return $photo_url;
	}
	
	public static function getItems($silo_id, $order_by, $limit) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE silo_id = $silo_id AND (items.status = 'pledged' OR items.status = 'offer') $order_by_clause $limit";
		$items = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new Item($res['item_id']);
			$items[] = $u;
		}
		return $items;
	}

	public static function getSoldItems($silo_id, $order_by, $limit) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'sold' AND silo_id = $silo_id $order_by_clause $limit";
		$items = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new Item($res['item_id']);
			$items[] = $u;
		}
		return $items;
	}

	public static function getPendingItems($silo_id, $order_by, $limit) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'pending' AND silo_id = $silo_id $order_by_clause $limit";
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
		$title = (strlen($this->title) > 20) ? substr($this->title, 0, 20) . '...' : $this->title;

		$cell .= "<td><div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_item&id=".$this->id."'>";
		$cell .= "<img src='uploads/items/".$this->photo_file_1."?".$this->last_update."'>";
		$cell .= "<div style='padding-bottom: 0px;'>".$title."</div>  <span class='blue'>$".$this->priceOffer."</span>";
		$cell .= "</a></div></td>";
		return $cell;
	}

	public function getItemCellAdmin($silo_id, $c_user_id) {
		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->owner->fname; $admin_name .= "&nbsp;".$this->owner->lname; } else { $admin_name = $this->owner->fname; };
		$title = (strlen($this->title) > 20) ? substr($this->title, 0, 20) . '...' : $this->title;

		$cell = "<td><form name='f".$this->item_id."' id='f".$this->item_id."' method='post' action=''><input type='hidden' name='item_id' value='".$this->item_id."'><input type='hidden' name='delete_item' value='delete_".$this->item_id."'><a href='javascript:document.f".$this->item_id.".submit()' class='confirmation'><img src=images/delete.png  style='position: absolute; padding-left: 145px;'></a></form>";
		$cell .= "<div class='plate'><a style='color: grey; text-decoration: none;' href='index.php?task=view_item&id=".$this->id."'>";
		$cell .= "<img src='uploads/items/".$this->photo_file_1."?".$this->last_update."'>";
		$cell .= "<div style='padding-bottom: 0px;'>".$title."</div>  <span class='blue'>$".$this->priceOffer."</span>";
		$cell .= "</a></div></td>";
		return $cell;
	}


	public function getSoldItemCell($silo_id, $c_user_id) {
		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->owner->fname; $admin_name .= "&nbsp;".$this->owner->lname; } else { $admin_name = $this->owner->fname; };
		$title = (strlen($this->title) > 20) ? substr($this->title, 0, 20) . '...' : $this->title;

		$cell = "<td><div class='plate'><a class='fancybox' href='#sold' style='color: grey; text-decoration: none;'";
		$cell .= "<img src='uploads/items/".$this->photo_file_1."?".$this->last_update."'>";
		$cell .= "<div style='padding-bottom: 0px;'>".$title."</div>  <span class='blue'>$".$this->price."</span>";
		$cell .= "</a></div></td>";
		return $cell;
	}

	public function getPendingItemCell($silo_id, $c_user_id) {
		$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
		if ($show) { $admin_name = $this->owner->fname; $admin_name .= "&nbsp;".$this->owner->lname; } else { $admin_name = $this->owner->fname; };
		$title = (strlen($this->title) > 20) ? substr($this->title, 0, 20) . '...' : $this->title;

		$cell = "<td><div class='plate'><a class='fancybox' href='#pending' style='color: grey; text-decoration: none;'>";
		$cell .= "<img src='uploads/items/".$this->photo_file_1."?".$this->last_update."'>";
		$cell .= "<div style='padding-bottom: 0px;'>".$title."</div>  <span class='blue'>$".$this->price."</span>";
		$cell .= "</a></div></td>";
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
				."'".mysql_real_escape_string(date('Y-m-d H:i:s'))."',"
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

	public function SaveBuyer(){
		$check = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'"));
		if ($check) {
			$updBuyer = mysql_query("UPDATE buyer SET purchase = '1', cleared = '0' WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'");
		} else {
			$updBuyer = (
				"INSERT INTO `buyer` "
				."("
					."user_id,item_id,purchase"
				.")VALUES("
					."'".mysql_real_escape_string($this->user_id)."',"
					."'".mysql_real_escape_string($this->item_id)."',"
					."'1'"
				.")"
			);
			mysql_query($updBuyer);
		}
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

		$check = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'"));
		if ($check) {
			$updBuyer = mysql_query("UPDATE buyer SET favorite = '1', cleared = '0' WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'");
		} else {
			$updBuyer = (
				"INSERT INTO `buyer` "
				."("
					."user_id,item_id,favorite"
				.")VALUES("
					."'".mysql_real_escape_string($this->user_id)."',"
					."'".mysql_real_escape_string($this->item_id)."',"
					."'1'"
				.")"
			);
			mysql_query($updBuyer);
		}
	}

	public function RemoveFav(){
		$query = "DELETE FROM favorites WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'";
		mysql_query($query);

		$check = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$this->user_id' AND item_id = '$this->item_id' AND (offer = '1' OR purchase = '1')"));
		if ($check) {
			$qry = mysql_query("UPDATE buyer SET favorite = '0' WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'");
		} else {
			$qry = mysql_query("DELETE FROM buyer WHERE user_id = '$this->user_id' AND item_id = '$this->item_id'");
		}
	}

	public function NewOffer(){
		$exp = date('Y-m-d H:i:s', strtotime('+1 day'));

		if (!$this->seller_id) { 
			$seller = mysql_fetch_row(mysql_query("SELECT user_id FROM items WHERE item_id = '$this->item_id'"));
			$this->seller_id = $seller[0];
		}

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

		$updItem = mysql_query("UPDATE items SET status = 'offer' WHERE item_id = '$this->item_id'");

		$checkBuyer = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$this->buyer_id' AND item_id = '$this->item_id'"));
		if ($checkBuyer) {
			$updBuyer = mysql_query("UPDATE buyer SET offer = '1', cleared = '0' WHERE user_id = '$this->buyer_id' AND item_id = '$this->item_id'");
		} else {
			$updBuyer = (
				"INSERT INTO `buyer` "
				."("
					."user_id,item_id,offer"
				.")VALUES("
					."'".mysql_real_escape_string($this->buyer_id)."',"
					."'".mysql_real_escape_string($this->item_id)."',"
					."'1'"
				.")"
			);
			mysql_query($updBuyer);
		}

		$Notification = new Notification();
		$Notification->user_id = $this->seller_id;
		$Notification->item_id = $this->item_id;
		$Notification->type = "New Offer";
		$Notification->Send();
	}

	public function RemoveOffer(){
		if (!$this->seller_id) { 
			$seller = mysql_fetch_row(mysql_query("SELECT user_id FROM items WHERE item_id = '$this->item_id'"));
			$this->seller_id = $seller[0];
		}

		$query = "UPDATE offers SET status = 'canceled', expired_date = '0' WHERE item_id = '$this->item_id' AND buyer_id = '$this->buyer_id' AND seller_id = '$this->seller_id'";
		mysql_query($query);

		$updItem = mysql_query("UPDATE items SET status = 'pledged' WHERE item_id = '$this->item_id'");

		$check = mysql_num_rows(mysql_query("SELECT * FROM buyer WHERE user_id = '$this->buyer_id' AND item_id = '$this->item_id' AND (favorite = '1' OR purchase = '1')"));
		if ($check) {
			$query = mysql_query("UPDATE buyer SET offer = '0' WHERE user_id = '$this->buyer_id' AND item_id = '$this->item_id'");
		} else {
			$query = mysql_query("DELETE FROM buyer WHERE user_id = '$this->buyer_id' AND item_id = '$this->item_id'");
		}

		$Notification = new Notification();
		$Notification->user_id = $this->seller_id;
		$Notification->item_id = $this->item_id;
		$Notification->type = "Cancel Offer";
		$Notification->Email();
	}

	public function GetPayCode($buyer_id, $seller_id){
		$id = rand(1,100);

		$buyer = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$buyer_id' AND paycode_id = '$id'"));
		$seller = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$seller_id' AND paycode_id = '$id'"));

		while ($buyer || $seller) {
			$id = rand(1,100);

			$buyer = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$buyer_id' AND paycode_id = '$id'"));
			$seller = mysql_num_rows(mysql_query("SELECT * FROM user_paycodes WHERE user_id = '$seller_id' AND paycode_id = '$id'"));
		}

		$codes = mysql_fetch_array(mysql_query("SELECT paylock, paykey FROM paycodes WHERE id = '$id'"));

		$addBuyer = mysql_query("INSERT INTO user_paycodes (user_id, paycode_id) VALUES ('$buyer_id', '$id')");
		$addSeller = mysql_query("INSERT INTO user_paycodes (user_id, paycode_id) VALUES ('$seller_id', '$id')");

		return array($codes[0], $codes[1]);
	}
}
?>
