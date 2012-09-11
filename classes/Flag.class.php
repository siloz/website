<?php
/*
 *mysql> desc flag;
 *+---------+--------------+------+-----+-------------------+----------------+
 *| Field   | Type         | Null | Key | Default           | Extra          |
 *+---------+--------------+------+-----+-------------------+----------------+
 *| id      | int(11)      | NO   | PRI | NULL              | auto_increment |
 *| type    | varchar(100) | NO   |     | NULL              |                |
 *| created | timestamp    | NO   |     | CURRENT_TIMESTAMP |                |
 *| active  | int(3)       | NO   |     | NULL              |                |
 *+---------+--------------+------+-----+-------------------+----------------+
 *4 rows in set (0.04 sec)
 * @author james kenny September 10th, 2012
 */

class Flag{
 
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
		"select id,type,created,active from flag where id = '".mysql_real_escape_string($id)."';"
	;
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){
			foreach($row as $key => $value){$this->$key = $value;}
		}
		return $this;
	}else{return false;}
}

/* GetIds()
 * //get ids from the flag table
 *
 * @return array($ids)
 */
function GetIds(){
	$query = 
		"select id from flag order by display_order asc;"
	;
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){
			$x[] = $row->id;
		}
		return $x;
	}else{return false;}
}
/* GetSiloFlaggedCount($silo_id)
 * // get the amount of times a silo has been flagged
 * @param $silo_id;
 * @return int($count)
 *
 */
function GetSiloFlaggedCount($silo_id){
	$query = 
		"select count(id) as count from flag_silo where silo_id = '".mysql_real_escape_string($silo_id)."';"
	;
	$result = mysql_query($query);
	$x = mysql_fetch_object($result);
	return $x->count;
}

/* GetItemFlaggedCount($silo_id)
 * // get the amount of times a item has been flagged
 * @param $item_id;
 * @return int($count)
 *
 */
function GetItemFlaggedCount($item_id){
	$query = 
		"select count(id) as count from flag_item where item_id = '".mysql_real_escape_string($item_id)."';"
	;
	$result = mysql_query($query);
	$x = mysql_fetch_object($result);
	return $x->count;
}

/* CheckIfUserFlaggedSilo($user_id,$silo_id)
 * // check if a user has already flagged a silo
 * @param $user_id;
 * @param $silo_id;
 * @return true/false
 *
 */
function CheckIfUserFlaggedSilo($user_id,$silo_id){
	$query = 
		"select count(id) as count "
		."from flag_silo where "
		."silo_id = '".mysql_real_escape_string($silo_id)."' "
		."and user_id = '".mysql_real_escape_string($user_id)."';"
	;
	$result = mysql_query($query);
	$x = mysql_fetch_object($result);
	if($x->count >= 1){return true;}
	else{return false;}
}

/* CheckIfUserFlaggedSilo($user_id,$item_id)
 * // check if a user has already flagged a silo
 * @param $user_id;
 * @param $item_id;
 * @return true/false
 *
 */
function CheckIfUserFlaggedItem($user_id,$item_id){
	$query = 
		"select count(id) as count "
		."from flag_item where "
		."item_id = '".mysql_real_escape_string($item_id)."' "
		."and user_id = '".mysql_real_escape_string($user_id)."';"
	;
	$result = mysql_query($query);
	$x = mysql_fetch_object($result);
	if($x->count >= 1){return true;}
	else{return false;}
}

}
?>
