<?php
class VouchType {
/* VouchType()
 * the types of vouches available
 * @todo create a function that updates the table with a new type
 *		vouch_type->type should be enum but that would make the save function 
 *		beyond the scope of this task
 */

var $id; //the id number
var $text; // the text blob to be displayed
var $created; //the day this was created
var $active; //if this vouch type is active or not
var $type; // the types of vouches available

/*
 *mysql> desc vouch_type;
 *+---------+--------------+------+-----+-------------------+----------------+
 *| Field   | Type         | Null | Key | Default           | Extra          |
 *+---------+--------------+------+-----+-------------------+----------------+
 *| id      | int(11)      | NO   | PRI | NULL              | auto_increment |
 *| text    | text         | NO   |     | NULL              |                |
 *| created | timestamp    | NO   |     | CURRENT_TIMESTAMP |                |
 *| active  | int(5)       | NO   |     | NULL              |                |
 *| type    | varchar(255) | YES  |     | NULL              |                |
 *+---------+--------------+------+-----+-------------------+----------------+
 *5 rows in set (0.03 sec)
 *
 */		
function _construct($id=false){
	if($id){return $this->Populate($id);}
}

/* Populate($id)
 * Populate this object by its id
 *
 * @param $id // vouch->id
 * @return object($this)
 * 
 */	
function Populate($id){
	$query = 
		"SELECT type,text,created,active "
	 	."FROM vouch_type "
	 	."WHERE id = '".mysql_real_escape_string($id)."' ;"
	 ;
	 $result = mysql_query($query);
	 if(mysql_affected_rows() >= 1){
	 	while($row = mysql_fetch_object($result)){
	 		foreach($row as $key=>$value){$this->$key = $value;}
	 		return $this;
	 	}
	 }else{return false;}
}
/* GetIds()
 *
 * typically this function would support key value params 
 * but since the table is so small we are going to just return all
 * values until further filtering is required
 *
 * @return array($ids)
 */
function GetIds(){
	$query = "SELECT id FROM vouch_type ;";
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
	 	while($row = mysql_fetch_object($result)){
	 		$x[] = $row->id;	
	 	}
	 } else {return false;}
	 return $x;
}

}
?>
