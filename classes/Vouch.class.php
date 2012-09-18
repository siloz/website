<?php
/* Vouch
 * // when a user vouches how they know a silo
 *mysql> desc vouch;
 *+---------------+-----------+------+-----+-------------------+----------------+
 *| Field         | Type      | Null | Key | Default           | Extra          |
 *+---------------+-----------+------+-----+-------------------+----------------+
 *| id            | int(11)   | NO   | PRI | NULL              | auto_increment |
 *| silo_id       | int(11)   | NO   |     | NULL              |                |
 *| user_id       | int(11)   | NO   |     | NULL              |                |
 *| vouch_type_id | int(11)   | NO   |     | NULL              |                |
 *| created       | timestamp | NO   |     | CURRENT_TIMESTAMP |                |
 *+---------------+-----------+------+-----+-------------------+----------------+
 *5 rows in set (0.00 sec)
 *
 * @author James Kenny (Sep 8th, 2012)
 */	

class Vouch {
var $id; //the id number
var $siloId; // silos->silo_id
var $user_id; // user->user_id
var $vouchTypeId; // vouch_type->id
var $created; // timestamp (Y-m-d H:i:s)
	
function _construct($id=false){
	if($id){return self::Populate($id);}
}

/* Populate($id)
 * Populate this object by its id
 *
 * @param $id // vouch->id
 * @return object($this)
 * 
 */	
function Populate($id){
	self::Deconstruct();
	$query = 
		"SELECT "
		."silo_id as siloId,user_id as userId,vouch_type_id as vouchTypeId,created  "
	 	."FROM vouch "
	 	."WHERE id = '".mysql_real_escape_string($id)."' ;"
	 ;
	 $result = mysql_query($query);
	 if(mysql_affected_rows() >= 1){
	 	$this->id = $id;
	 	while($row = mysql_fetch_object($result)){
	 		foreach($row as $key=>$value){$this->$key = $value;}
	 		return $this;
	 	}
	 }else{return false;}
}

/* GetIds($value,$key = false)
 *
 *
 * typically this function would support key value params 
 * but since the table is so small we are going to just return all
 * values until further filtering is required
 *
 * @return array($ids)
 */
function GetIds($value, $key=false ){
	switch(strtolower($key)){
		case "silo":
		case "siloid":
		case "silo_id": $key = "silo_id"; break;
		case "vouchtype":
		case "vouchtypeid":
		case "vouch_type_id": $key = "vouch_type_id"; $key = "vouch_type_id"; break; 
		default: $key = "user_id"; break;
	}	
	$query = "SELECT id FROM vouch WHERE `".$key."` = '".mysql_real_escape_string($value)."';";
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
	 	while($row = mysql_fetch_object($result)){
	 		$x[] = $row->id;	
	 	}
	 }else{return false;}
	 return $x;
}

/* Save($silo_id,$user_id,$vouch_type_id)
 * // save a users vouch
 * 
 * @param $silo_id // silos->silo_id
 * @param $user_id // users->user_id
 * @param $vouch_type_id // vouch_type->id
 * 
 * @return $id // vouch->id (or false on error)
 */
function Save($silo_id,$user_id,$vouch_type_id){
	$query = 
		"INSERT INTO vouch "
		."(silo_id,user_id,vouch_type_id,created) "
		."VALUES "
		."("
			."'".mysql_real_escape_string($silo_id)."',"
			."'".mysql_real_escape_string($user_id)."',"
			."'".mysql_real_escape_string($vouch_type_id)."',"
			."'".mysql_real_escape_string(date("Y-m-d H:i:s"))."'"
			
		.");"
	;
	mysql_query($query);
	$id = mysql_insert_id();
	if($id >= 1){return $id;}
	else{return false;}
}

/* GetUsersLastVouchId($user_id,$silo_id)
 * This function returns the last vouch_type_id for a silo
 *
 * @param $user_id
 * @param $silo_id
 *
 * @return $id
 */
function GetUsersLastVouchId($user_id,$silo_id){
	$query = 
		"SELECT vouch_type_id FROM vouch WHERE user_id = '".mysql_real_escape_string($user_id)."' "
		."and silo_id = '".mysql_real_escape_string($silo_id)."' ORDER BY created DESC limit 1;"
	;
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
	 	while($row = mysql_fetch_object($result)){
	 		return $row->vouch_type_id;	
	 	}
	 }else{return false;}
}

/* GetHasResearchedCount($silo_id)
 *
 * @param int($silo_id)	
 * @return int($count)
 * @author james kenny //sep 8th,2012
 */
function GetHasResearchedCount($silo_id){
	$ids = self::GetSiloUserIds($silo_id);
	$i = 0;
	foreach($ids as $id){
		$last_vouch_id = self::GetUsersLastVouchId($id,$silo_id);
		$VouchType = new VouchType();
		$VouchType->Populate($last_vouch_id);
		//if($last_vouch_id == 78){die("I have one");}
		if($VouchType->type === "Has Researched"){$i++;}
	}
	return $i;
}
/* GetHasPersonallyKnownCount($silo_id)
 * // get the count of users that personally know a silo
 * @param int($silo_id)	
 * @return int($count)
 * @author james kenny //sep 8th,2012
 */
function GetHasResearchedAndKnownCount($silo_id){
	$ids = self::GetSiloUserIds($silo_id);
	$i = 0;
	foreach($ids as $id){
		$last_vouch_id = self::GetUsersLastVouchId($id,$silo_id);
		$VouchType = new VouchType();
		$VouchType->Populate($last_vouch_id);
		//if($last_vouch_id == 78){die("I have one");}
		if($VouchType->type === "Has Researched Has Personal Knowledge"){$i++;}
	}
	return $i;
}

/* GetHasReserachedAndKnownCount()
 * // get the count of users that have personal knowledge of a silo
 * @param int($silo_id)	
 * @return int($count)
 * @author james kenny //sep 8th,2012
 */
function GetHasPersonallyKnownCount($silo_id){
	$ids = self::GetSiloUserIds($silo_id);
	$i = 0;
	foreach($ids as $id){
		$last_vouch_id = self::GetUsersLastVouchId($id,$silo_id);
		$VouchType = new VouchType();
		$VouchType->Populate($last_vouch_id);
		if($VouchType->type === "Has Personal Knowledge"){$i++;}
	}
	return $i;
}

/* GetUknownCount($silo_id)
 * // Count the users that do not know anything about the silo
 * @param int($user_id)	
 * @return int($count)
 * @author james kenny //sep 8th,2012
 */
function GetUknownCount($silo_id){
	$ids = self::GetSiloUserIds($silo_id);
	$i = 0;
	foreach($ids as $id){
		$last_vouch_id = self::GetUsersLastVouchId($id,$silo_id);
		$VouchType = new VouchType();
		$VouchType->Populate($last_vouch_id);
		if($VouchType->type === "Not Researched No Personal Knowledge"){$i++;}
	}
	return $i;
}

/* GetSiloUserIds($silo_id)
 * // get the user_ids from the vouch table that belong to a silo
 * @param $silo_id
 * @return array($user_ids)
 * @author james kenny //sep 8th,2012
 */
function GetSiloUserIds($silo_id){
	$query = "select distinct user_id from vouch where silo_id = '".mysql_real_escape_string($silo_id)."';";
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){$x[] = $row->user_id;}
		return $x;
	}else{return false;}
}
/* Deconstruct()
 * // unset $this variable
 */
private function Deconstruct(){
	foreach($this as $key => $value){
		$this->$key = false;
	}
}
}
?>
