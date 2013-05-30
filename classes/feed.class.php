<?php
class Feed {
	public $silo_id;
	public $user_id;
	public $item_id;

	public function Save(){
		$silo = mysql_fetch_row(mysql_query("SELECT silo_id FROM items WHERE item_id = '$this->item_id'"));
		$this->silo_id = $silo[0];

		if($this->status == "Joined") {
			return $this->InsertJoined();
		}
		elseif($this->status == "Pledged") {
			return $this->InsertPledged();
		}
		elseif($this->status == "Sold") {
			return $this->InsertSold();
		}
	}

public function InsertJoined(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type"
			.")VALUES("
				."'".$this->silo_id."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Joined'"
			.")"
		);
		mysql_query($query);

		return true;		
	}

public function InsertPledged(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type"
			.")VALUES("
				."'".$this->silo_id."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Pledged'"
			.")"
		);
		mysql_query($query);

		return true;		
	}

public function InsertSold(){
		$silo = new Silo($this->silo_id);
		$this->goal = $silo->goal;

		$checkBefore = mysql_fetch_row(mysql_query("SELECT goal_reached FROM feed WHERE silo_id = '$this->silo_id' ORDER BY goal_reached DESC"));
		$goalBefore = $checkBefore[0];
		$silo_type = mysql_fetch_row(mysql_query("SELECT silo_type FROM silos WHERE silo_id = '$this->silo_id'"));

		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type"
			.")VALUES("
				."'".$this->silo_id."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Sold'"
			.")"
		);
		mysql_query($query);

		$checkAfter = mysql_fetch_row(mysql_query("SELECT SUM(price) FROM items WHERE silo_id = '$this->silo_id' AND status = 'Sold'"));
		if ($this->silo_type == "public") {
			$pctOf = ".9";
		} else { 
			$pctOf = ".95"; 
		}
		$finalCollected = $checkAfter[0] * $pctOf;

		$totalAfter = ($finalCollected/$this->goal)*10;
		$this->goal_reached = floor($totalAfter)*10;

		if ($this->goal_reached - $goalBefore) {
			return $this->InsertGoal();
		}

		return true;		
	}

public function InsertGoal(){
		$query = (
			"INSERT INTO `feed` "
			."("
				."silo_id, user_id, item_id, type, goal_reached"
			.")VALUES("
				."'".$this->silo_id."',"
				."'".mysql_real_escape_string($this->user_id)."',"
				."'".mysql_real_escape_string($this->item_id)."',"
				."'Goal',"
				."'".$this->goal_reached."'"
			.")"
		);
		mysql_query($query);

		return true;		
	}

}
?>