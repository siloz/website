<?php
class Feed {
	public $silo_id;
	public $user_id;
	public $item_id;

	public function Save(){
		if($this->status == "Pledged") {
			return $this->InsertPledged();
		}
		elseif($this->status == "Sold") {
			return $this->InsertSold();
		}
		else {
			return $this->InsertGoal();
		}
	}

public function InsertPledged(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type"
			.")VALUES("
				."'".mysql_real_escape_string($this->silo_id)."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Pledged'"
			.")"
		);
		mysql_query($query);

		return true;		
	}

public function InsertSold(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type"
			.")VALUES("
				."'".mysql_real_escape_string($this->silo_id)."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Sold'"
			.")"
		);
		mysql_query($query);

		return true;		
	}

public function InsertGoal(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type, goal_reached"
			.")VALUES("
				."'".mysql_real_escape_string($this->silo_id)."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Goal'"
				."'".mysql_real_escape_string($this->goal_reached)."',"
			.")"
		);
		mysql_query($query);

		return true;		
	}

}
?>