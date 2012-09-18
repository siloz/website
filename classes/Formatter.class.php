<?php

class Formatter{

	function Currency($amount){
		$amount = money_format('%i', $amount);
		return $amount;
	}
	/**
	 *Needs work but usable
	 *
	 */
	function DisplayDate($date =  false,$character = false,$no_create = false){
		$c = $character;
		switch($c){
			case "/": $c = "/"; break;
			case ".": $c = "."; break;
			case ":": $c = ":"; break;
			case "space":
			case " "    :
			case "&nbsp;": $c = " "; break;
			default : $c = "-"; break;
		}
		if(
			(!$date)
				||
			($date === "0000-00-00 00:00:00")
				||
			($date === "0000-00-00")
		){
			if($no_create){return false;}
			return date("m".$c."d".$c."Y");
		}
		else{
			$a = explode(" ",$date);
			$b = explode("-",$a[0]);
			return $b[1].$c.$b[2].$c.$b[0];
		}
	}
	function DayDiff($start,$end){
		    if(empty($end)) {
		   return "No date provided";
	    }
//	   date
	    $periods         = array("second", "minute", "hour", "day");
	    $lengths         = array("60","60","24","7");
	   
	    $start        = strtotime($start);
	    $end         = strtotime($end);
	   
		  // check validity of date
	    if(empty($end)) {   
		   return "Bad date";
	    }

	    // is it future date or past date
	    if($start > $end) {   
		   $difference     = $start - $end;
		   $tense         = "ago";
		  
	    } else {
		   $difference     = $end - $start;
		   $tense         = "from now";
	    }
	   
	    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		   $difference /= $lengths[$j];
	    }
	   
	    $difference = round($difference);
	   
	    if($difference != 1) {
		   $periods[$j].= "s";
	    }
	   
	    return $difference;
	}
	/**
	 *  CleanArray($array)
	 *
	 * @return $array; no duplicates or flase values
	 */
	function CleanArray($array){
		foreach($array as $key=>$value){
			if($value){
				$x[$value] = $value;
			}
		}
		if(!$x){return false;}
		return $x;
	
	}
	function PhoneNumber($num){
		$num = preg_replace('/[^0-9]/', '', $num);
		$len = strlen($num);
		if($len == 7){
			$num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
		}elseif($len == 10){
			$num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num); 
		}
		return $num;
	}
}

?>
