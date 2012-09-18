<?php

class Radar{

/**
 * 
 *
 * @param $item_id; //optional but the item related to these flags 
 *
 * @return $id; // the record id or false on error
 * @author james kenny
 * 
 */
function Silo($silo_id,$flag_id,$cause){
	$FlagSilo = new FlagSilo();
	$FlagSilo->GetIds($silo_id,"silo_same_flag",$flag_id);
	if($FlagSilo->id_count >= 3 ){
		$status = "warn";
		$FlagRadar = new FlagRadar();
		$exist = $FlagRadar->PopulateBySiloStatus($silo_id,$flag_id,$status);
		if($FlagSilo->id_count >= 5){
			//check if the silo has already been warned
			
			$warned_id = $FlagRadar->CheckSiloWarnedBeforeCancel($silo_id,$flag_id);
			if($warned_id){
					$FlagRadar->hidden = "1";
					$FlagRadar->Save();
					$FlagRadar->UnsetId(); //to insert a new record	
					$status = "cancel";
					$exist = $FlagRadar->PopulateBySiloStatus($silo_id,$flag_id,$status);
			
			}//end if warned			
			if($FlagRadar->status != $status){
				$FlagRadar->status = $status;
				if(!$exist){
					$FlagRadar->silo_id = $silo_id;
					$FlagRadar->who = "silo";
					$FlagRadar->cause = "flagging";
					$FlagRadar->user_id = self::GetUserIdBySiloId($silo_id);
					$FlagRadar->flag_id = $flag_id;
					$FlagRadar->item_id = $item_id;
				}
				$id = $FlagRadar->Save();
				if($status === "cancel" && $id){
					self::CancelSilo($silo_id,$id);
				}
			}// end status check
		}// end cancel
	}//end warn
}



/**
 *
 *
 *
 */
function Item($item_id,$flag_id,$cause){
	//$flag_count = self::CheckItemFlagsSameTypeCount($item_id,$flag_id);
	
	$FlagItem = new FlagItem();
	$FlagItem->GetIds($item_id,"item_same_flag",$flag_id); //only to get count
	if($FlagItem->count >= 3){
		$status = "warn";
		$FlagRadar = new FlagRadar();
		$exist = $FlagRadar->PopulateByItemStatus($item_id,$flag_id,$status);
		if($FlagItem->count >= 5){
			$warned_id = $FlagRadar->CheckItemWarnedBeforeCancel($item_id,$flag_id);
			if($warned_id){
					$FlagRadar->hidden = "1";
					$FlagRadar->Save();
					$FlagRadar->UnsetId(); //to insert a new record		
					$status = "cancel";
					$exist = $FlagRadar->PopulateByItemStatus($item_id,$flag_id,$status);
			}
		}
		if($FlagRadar->status != $status){
			$FlagRadar->status = $status;
			if(!$exist){
				$silo_id = self::GetSiloIdByItemId($item_id);
				$FlagRadar->silo_id = $silo_id;
				$FlagRadar->who = "item";
				$FlagRadar->cause = "flagging";
				$FlagRadar->user_id = self::GetUserIdBySiloId($silo_id);
				$FlagRadar->flag_id = $flag_id;
				$FlagRadar->item_id = $item_id;
			}
			$id = $FlagRadar->Save();
			if($status === "cancel" && !$exist){
				self::CancelItem($item_id,$id);
			}
		}// end status check
	}
}

/**
 *
 *
 *
 */
private function GetUserIdBySiloId($silo_id){
	$Silo = new Silo($silo_id);
	return $Silo->admin_id;
}

/**
 *
 *
 *
 */
private function GetSiloIdByItemId($item_id){
	$I = new Item($item_id);
	return $I->silo_id;
}
/**
 *
 *
 *
 *
 */
private function CheckIfThisItemStatusExists($item_id,$flag_id,$status){
	$query = 
		"select count(id) as count "
		."from flag_radar "
		."where "
		."flag_id = '".mysql_real_escape_string($flag_id)."' "
		."and status = '".mysql_real_escape_string($status)."' "
		."and who = 'item' "
		."and created > '".mysql_real_escape_string(self::DaysAgo(90))."' "
		."and active = '1' "
	;
	$count = self::ExtractCount($query);
	if($count >= 1){
		return true;
	}else{
		return false;
	}

}


private function CheckIfThisSiloStatusExists($silo_id,$flag_id,$status){
	$query = 
		"select count(id) as count "
		."from flag_radar "
		."where "
		."silo_id = '".mysql_real_escape_string($silo_id)."' "
		."and flag_id = '".mysql_real_escape_string($flag_id)."' "
		."and status = '".mysql_real_escape_string($status)."' "
		."and who = 'silo' "
		."and created > '".mysql_real_escape_string(self::DaysAgo(90))."' "
		."and active = '1' "
	;
	$count = self::ExtractCount($query);
	if($count >= 1){
		return true;
	}else{
		return false;
	}

}



/**
 *
 *
 */
private function CheckIfSiloWarnedBeforeCancel($silo_id,$flag_id){
	$query = 
		"select count(id) as count "
		."from flag_radar "
		."where "
		."silo_id = '".mysql_real_escape_string($silo_id)."' "
		."and flag_id = '".mysql_real_escape_string($flag_id)."' "
		."and created < '".mysql_real_escape_string(self::DaysAgo(3))."' "
		."and active = '1' "
	;
	if(self::ExtractCount($query) >= 1){
		return true;
	}else{return false;}
} //CheckIfSiloWarnedBeforeCancel

function CancelItem($item_id,$radar_id){
	$I = new Item($item_id);
	$I->end_date = date("Y-m-d H:i:s");
	$I->status = "flagged";
	$I->flag_radar_id = $radar_id;
	return $I->Save();
}

function RestoreItem($item_id){
	$I = new Item($item_id);
	$I->end_date = "0000-00-00 00:00:00";
	$I->status = "pledged";
	return $I->Save();
}

function CancelSilo($silo_id,$radar_id){
	$ids = Item::GetIds($silo_id,"silo_id");
	if($ids){
		foreach($ids as $x){
			self::CancelItem($x,$radar_id);
		}
	}
	$Silo = new Silo($silo_id);
	$Silo->end_date = date("Y-m-d H:i:s");
	$Silo->flag_radar_id = $radar_id;
	$Silo->Save();
}

function RestoreSilo($silo_id,$radar_id){
		$ids = Item::GetIds($silo_id,"restore",$radar_id);
		if($ids){
			foreach($ids as $x){
				$this->RestoreItem($x);
			}
		}
		$Silo = new Silo($silo_id);
		$Silo->end_date = "0000-00-00 00:00:00";
		$Silo->flag_radar_id = $radar_id;
		$Silo->Save();
}


} //end class

?>
