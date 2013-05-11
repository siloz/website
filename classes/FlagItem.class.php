<?php
/*
 *mysql> desc flag_item;
 *+---------+-------------+------+-----+-------------------+----------------+
 *| Field   | Type        | Null | Key | Default           | Extra          |
 *+---------+-------------+------+-----+-------------------+----------------+
 *| id      | int(11)     | NO   | PRI | NULL              | auto_increment |
 *| item_id | int(11)     | NO   |     | NULL              |                |
 *| user_id | int(11)     | NO   |     | NULL              |                |
 *| flag_id | int(11)     | NO   |     | NULL              |                |
 *| created | timestamp   | NO   |     | CURRENT_TIMESTAMP |                |
 *| ip      | varchar(55) | NO   |     | NULL              |                |
 *+---------+-------------+------+-----+-------------------+----------------+
 *6 rows in set (0.00 sec)
 *
 * @author james kenny September 10th, 2012
 */

class FlagItem{

function _construct($id=false){
	if($id){return self::Populate($id);}
}
/* Populate($id)
 * //populate the class by id
 *
 * @param $id // flag->id
 * @return obj($this)
 * @author james kenny
 */
function Populate($id){
	$query = 
		"select id,item_id as itemId,user_id as userId,flag_id as flagId,created,ip "
		."from flag_item where id = '".mysql_real_escape_string($id)."';"
	;
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){
			foreach($row as $key => $value){$this->$key = $value;}
		}
		return $this;
	}else{return false;}
}

/* GetIds($value,$key = false)
 * //get ids from the flag table
 *
 * @param $value // the value you want to search for
 * @param $key // the key you would like to search for
 *		$key defaults to item_id
 *
 * @return array($ids)
 * 
 * @todo get by date for radar
 */
function GetIds($value,$key = false,$other_value = false){
	$this->count = 0;
	switch(strtolower($key)){
		case "user":
		case "user_id":
		case "userid": $key = "user_id"; break;
		case "flag":
		case "flag_id":
		case "flagid": $key = "flag_id"; break;
		case "ip": $key = "ip"; break;
		case "item_same_flag": $key = "item_id"; $append = "flag_id"; break;
		default: $key = "item_id"; break;
	}
	
	$query = 
		"SELECT `id` FROM `flag_item` WHERE `".$key."` = '".mysql_real_escape_string($value)."' "
	;
	if($append && $other_value){
		$query .= (
			"AND `".$append."` = '".mysql_real_escape_string($other_value)."' "
		);
	}
	$result = mysql_query($query);
	$this->count = mysql_affected_rows();
	if($this->count >= 1){
		while($row = mysql_fetch_object($result)){
			$x[] = $row->id;
		}
		return $x;
	}else{return false;}
}

/*Insert($user_id,$silo_id,$flag_id,$ip)
 * //save a silo flag
 * @param $user_id //the user that is flagging
 * @param $item_id //the item that is being flagged
 * @param $flag_id // the id of the flag to refrence
 * @param $ip // the user that is flaggings ip address
 *
 * @return $id //(the id of the record that was saved, false on error, flagged if alreday flagged)
 * @author james kenny September 10th, 2012
 *
 */
function Insert($item_id,$user_id,$flag_id,$ip){
	$Flag = new Flag();
	//if($Flag->CheckIfUserFlaggedItem($user_id,$item_id)){ return "flagged"; }
	$query = 
		"insert into flag_item "
		."(item_id,user_id,flag_id,ip,active) "
		."values "
		."('"
			.mysql_real_escape_string($item_id)."','"
			.mysql_real_escape_string($user_id)."','"
			.mysql_real_escape_string($flag_id)."','"
			.mysql_real_escape_string($ip)."',"
			."'1'"
		.");"
	;
	$result = mysql_query($query);
	$id = mysql_insert_id();

	$checkTotal = mysql_num_rows(mysql_query("SELECT * FROM flag_item WHERE item_id = '$item_id'"));
	if ($checkTotal > 4) {
		$notification = new Notification();
		$notification->item_id = $item_id;
		$notification->type = "Cancel Item";
		$notification->Email();

		$radar = new FlagRadar();
		$radar->KillItem($item_id);
	}
	
	$i=56;
	$warn = "";
	while ($i < 62) {
		$checkCount = mysql_num_rows(mysql_query("SELECT * FROM flag_item WHERE item_id = '$item_id' AND flag_id = '$i'"));
		if ($checkCount > 1) { $warn = "true"; $i=100; }
		$i++;
	}

	$checkRadar = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE item_id = '$item_id' AND type = 'item'"));

	if ($warn && $checkTotal < 5  && !$checkRadar) {
		$notification = new Notification();
		$notification->item_id = $item_id;
		$notification->type = "Warn Item";
		$notification->Email();

		$radar = new FlagRadar();
		$radar->WarnItem($item_id);
	}

	if($id >= 1){return $id;}
	else{return false;}

}

}
?>
