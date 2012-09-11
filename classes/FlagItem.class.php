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
		."from flag_item where id = '".mysql_real_ecape_string($id)."';"
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
function GetIds($value,$key = false){
	switch(strtolower($key)){
		case "user":
		case "user_id":
		case "userid": $key = "user_id"; break;
		case "flag":
		case "flag_id":
		case "flagid": $key = "flag_id"; break;
		case "ip": $key = "ip"; break;
		default: $key = "item_id"; break;
	}
	$query = 
		"select id from flag where ".$key." = ".mysql_real_escape_string($value)."';"
	;
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
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
	if($Flag->CheckIfUserFlaggedItem($user_id,$item_id)){ return "flagged"; }
	$query = 
		"insert into flag_item "
		."(item_id,user_id,flag_id,ip) "
		."values "
		."('"
		.mysql_real_escape_string($item_id)."','"
		.mysql_real_escape_string($user_id)."','"
		.mysql_real_escape_string($flag_id)."','"
		.mysql_real_escape_string($ip)."');"
	;
	$result = mysql_query($query);
	$id = mysql_insert_id();
	if($id >= 1){return $id;}
	else{return false;} 
}

}
?>
