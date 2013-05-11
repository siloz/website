<?php
class FlagRadar {
	public $item_id;
	public $silo_id;

function KillItem($item_id) {
	$itemInfo = mysql_fetch_row(mysql_query("SELECT silo_id, user_id FROM items WHERE item_id = '$item_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE item_id = '$item_id'"));
	if ($check) {
		$upd = mysql_query("UPDATE flag_radar SET status = 'cancel', expired_date = NOW() WHERE item_id = '$item_id'");
	} else {
		$insert = mysql_query("INSERT INTO flag_radar (type, status, item_id, user_id, silo_id, expired_date) VALUES ('item', 'cancel', '$item_id', '$itemInfo[1]', '$itemInfo[0]', NOW())");
	}

	$killItem = mysql_query("UPDATE items SET status = 'flagged' WHERE item_id = '$item_id'");

   }

function WarnItem($item_id) {
	$itemInfo = mysql_fetch_row(mysql_query("SELECT silo_id, user_id FROM items WHERE item_id = '$item_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE item_id = '$item_id'"));
	if ($check) {
		$upd = mysql_query("UPDATE flag_radar SET status = 'warn' WHERE item_id = '$item_id'");
	} else {
		$insert = mysql_query("INSERT INTO flag_radar (type, status, item_id, user_id, silo_id, created) VALUES ('item', 'warn', '$item_id', '$itemInfo[1]', '$itemInfo[0]', NOW())");
	}

   }

function KillSilo($silo_id) {
	$silo = mysql_fetch_row(mysql_query("SELECT admin_id FROM silos WHERE silo_id = '$silo_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE silo_id = '$silo_id' AND type = 'silo'"));
	if ($check) {
		$upd = mysql_query("UPDATE flag_radar SET status = 'cancel', expired_date = NOW() WHERE silo_id = '$silo_id' AND type = 'silo'");
	} else {
		$insert = mysql_query("INSERT INTO flag_radar (type, status, user_id, silo_id, expired_date) VALUES ('silo', 'cancel', '$silo[0]', '$silo_id', NOW())");
	}

	$killSilo = mysql_query("UPDATE silos SET status = 'flagged' WHERE silo_id = '$silo_id'");

   }

function WarnSilo($silo_id) {
	$silo = mysql_fetch_row(mysql_query("SELECT admin_id FROM silos WHERE silo_id = '$silo_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE silo_id = '$silo_id' AND type = 'silo'"));
	if ($check) {
		$upd = mysql_query("UPDATE flag_radar SET status = 'warn' WHERE silo_id = '$silo_id' AND type = 'silo'");
	} else {
		$insert = mysql_query("INSERT INTO flag_radar (type, status, user_id, silo_id, created) VALUES ('silo', 'warn', '$silo[0]', '$silo_id', NOW())");
	}

   }

function VouchWarnSilo($silo_id) {
	$silo = mysql_fetch_row(mysql_query("SELECT admin_id FROM silos WHERE silo_id = '$silo_id'"));

	$check = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE silo_id = '$silo_id' AND status = 'vouch'"));
	if ($check) {
		$upd = mysql_query("UPDATE flag_radar SET status = 'vouch' WHERE silo_id = '$silo_id' AND status = 'vouch'");
	} else {
		$insert = mysql_query("INSERT INTO flag_radar (type, status, user_id, silo_id, created, expired_date) VALUES ('silo', 'vouch', '$silo[0]', '$silo_id', NOW(), NOW() + INTERVAL 3 DAY)");
	}

   }
}
?>
