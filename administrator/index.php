<?php
	ini_set("include_path", "/var/www/vhosts/siloz.com/httpdocs/"); 
	require_once("include/autoload.class.php");
	require_once('utils.php');
	require_once('config.php');
	setlocale(LC_MONETARY, 'en_US');

	if(isset($_POST['submit'])) {

        	if (!isset($_SESSION)){ session_start(); }

		$email = mysql_escape_string(trim($_POST['email']));
		$password = mysql_escape_string(trim($_POST['password']));
		$enc_pw = md5($password);

        	$res = mysql_query("SELECT user_id FROM users WHERE email = '$email' AND password='$enc_pw' AND admin = 'yes'");

       	if (empty($_POST['email']))
        	{
			$error = "Please enter an e-mail.<br>";
        	}
       	elseif (empty($_POST['password']))
        	{
			$error = "Please enter your password.<br>";
        	}

       	if (!$res || mysql_num_rows($res) <= 0)
        	{
			$error = "Login does not match and/or you are not an authorized administrator."; 
        	}
		else {
        		$_SESSION['admin_access']  = true;
        		$_SESSION['email']  = $email;
		}
	}

	if(isset($_POST['logout'])) {
		unset($_SESSION['admin_access']);
	}

	if (param_post('task') == 'unflag') {
		$silo_id = param_post('silo_id');
		$status = mysql_query("UPDATE silos SET status = 'active' WHERE silo_id = '$silo_id'");
		$flags = mysql_query("DELETE FROM flag_silo WHERE silo_id = '$silo_id'");
		$flagRadar = mysql_query("DELETE FROM flag_radar WHERE silo_id = '$silo_id' AND type='silo'");
			$notification = new Notification();
			$notification->silo_id = $silo_id;
			$notification->type = "Reactivate";
			$notification->Email();
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
	elseif (param_post('task') == 'item unflag') {
		$item_id = param_post('item_id');
		$status = mysql_query("UPDATE items SET status = 'pledged' WHERE item_id = '$item_id'");
		$flags = mysql_query("DELETE FROM flag_item WHERE item_id = '$item_id'");
		$flagRadar = mysql_query("DELETE FROM flag_radar WHERE item_id = '$item_id'");
			$notification = new Notification();
			$notification->item_id = $item_id;
			$notification->type = "Item Reactivate";
			$notification->Email();
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"	xml:lang="en">
	<head>
		<title><?=SITE_NAME?> - Site Admin</title>
		<link rel="stylesheet" type="text/css" href="../css/admin.css" />	
		<link rel="stylesheet" tyle="text/css" href="../css/jquery-ui-1.8.16.css"/>
	    <script type="text/javascript" src="../js/jquery-1.6.4.min.js"></script>
		<script type="text/javascript" src="../js/jquery-ui-1.8.16.min.js"></script> 				
		<script type="text/javascript" src="../js/popup-window.js"></script>	  
	    <script type="text/javascript" src="../js/jquery.placeholder.js"></script>		
	    <script type="text/javascript" src="../js/jquery.jconfirmation.js"></script>				
		<script type="text/javascript" src="../js/jquery.truncator.js"></script>
	  	<script type="text/javascript">
			$(document).ready(function() {
		      	$('.long_text').truncate({max_length: 1000});			    
				$('.confirmation').jConfirmAction({question : "Are you sure to delete?", yesAnswer : "Yes", cancelAnswer : "No"});		
			});
	  	</script>
		<?php
			//SPECIAL REDIRECT CASES
			$view = param_get('view');						
		?>
		
	</head>

<?php
if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'on') { 
	echo "<script>window.location = 'https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "';</script>";
    	exit();
} elseif (!isset($_SESSION['admin_access'])) {
?>
<div align="center" style="margin-top: 100px;" class="login" id="login">
	<div>
		<form name="login_form" id="login_form" method="POST">
			<input type="hidden" name="purpose" value=""/>
			<h2>Admin login</h2>
			<table>
				<tr>
					<td>
						<input type="text" name="email" id="email" onfocus="select();" placeholder="E-mail" value="<?=$email?>"/>
					</td>
					<td>
						<input type="password" name="password" id="password" onfocus="select();"  placeholder="Password"/>
					</td>
				</tr>
			</table>
			<br/>			
			<button type="submit" name="submit">Login</button>
			<br><br>
			<font color="red"><b><?=$error?></b></font>
		</form>
<?php
} else {
?>

	<body style="background: #fff">

	<?php
	$checkRadar = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE notify = 1"));
		if ($checkRadar) { $bColor = "red"; } else { $bColor = "#2f8dcb"; }
	$silopay = mysql_query("SELECT * FROM silos WHERE status = 'ended' and paid = 'no'");
		if (mysql_num_rows($silopay) > 0) {
	?>
	<div align="center" style="margin-top: 25px">
		<a href="index.php?view=paysilo" style="text-decoration: none; font-size: 16px; color: red;">**ALERT: A silo needs to be payed out**</a>
	</div>
	<?php } ?>

		<div style="margin: 50px;">
			<div id="header">
				<button type="button" style="<?php echo ($view == 'stats' ? 'border-color: #f60' : ''); ?>" onclick="window.location='index.php?view=stats'">Site Statistics</button>
				<button type="button" style="<?php echo ($view == 'members' ? 'border-color: #f60' : ''); ?>" onclick="window.location='index.php?view=members'">Members</button>
				<button type="button" style="<?php echo ($view == 'items' ? 'border-color: #f60' : ''); ?>" onclick="window.location='index.php?view=items'">Items</button>
				<button type="button" style="<?php echo ($view == 'silos' ? 'border-color: #f60' : ''); ?>" onclick="window.location='index.php?view=silos'">Silos</button>
				<button type="button" style="border-color: <?=$bColor?> <?php echo ($view == 'radar' ? '; border-color: #f60' : ''); ?>" onclick="window.location='index.php?view=radar'">Radar Notifications</button>
			<div style="float: right; padding-right: 50px;"><form method="post"><input type="submit" name="logout" value="Logout"></form></div>
			<div style="float: right; padding-right: 100px;">Logged in as <b><?=$_SESSION['email']?></b> <br><br>
			<?php	if ($checkRadar) {
					echo "<a href='index.php?view=radar' style='text-decoration: none'><font color='red'>New Flag Activity!</font></a></div>";
				}
			?>
			</div>
			<div id="main">
			<?php
			
				//STATS
				if ($view == 'stats') {
					echo "<br/>";
					$today = date("Y-m-d")."";					
					$html = "<table>";
					$active_community_silos_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE end_date >= '$today' AND type = 'Community'"));
					$active_personal_silos_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE end_date >= '$today' AND type = 'Personal'"));

					$community_silos_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE type = 'Community'"));
					$personal_silos_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE AND type = 'Personal'"));
					$personal_silos_count = $personal_silos_count[0] == null ? 0 : $personal_silos_count[0];
					$html .= "<tr><td>Total number of <b>Community</b> silos:</td><td align=right><b> $active_community_silos_count[0] (active) / $community_silos_count[0] (total) </b></td></tr>";
					$html .= "<tr><td>Total number of <b>Personal</b> silos:</td><td align=right><b> $active_personal_silos_count[0] (active) / $personal_silos_count (total)</b></td></tr>";
					
					$users_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM users"));
					$html .= "<tr><td>Total number of members:</td><td align=right><b>$users_count[0]</b></td></tr>";
					
					$active_listings_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0"));
					$listing_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items"));
					$html .= "<tr><td>Total number of listings:</td><td align=right><b> $active_listings_count[0] (active) / $listing_count[0] (total)</b></td></tr>";
					
					$listing_average_value = mysql_fetch_array(mysql_query("SELECT AVG(price) FROM items"));
					$html .= "<tr><td>Average value of listings:</td><td align=right><b>".money_format('%(#10n', round($listing_average_value[0],2))."</b></td></tr>";
					
					$donations_count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM donations"));
					$donations_average_value = mysql_fetch_array(mysql_query("SELECT AVG(amount) FROM donations"));
					$html .= "<tr><td>Total number of donations:</td><td align=right><b> $donations_count[0]</b></td></tr>";
					$html .= "<tr><td>Average value of donations:</td><td align=right><b>".money_format('%(#10n', round($donations_average_value[0],2))."</b></td></tr>";

					$community_funds_received = mysql_fetch_array(mysql_query("SELECT SUM(price) FROM items WHERE status = 'Funds Received' AND silo_id IN (SELECT silo_id FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE type = 'Community')"));
					$personal_funds_received = mysql_fetch_array(mysql_query("SELECT SUM(price) FROM items WHERE status = 'Funds Received' AND silo_id IN (SELECT silo_id FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE type = 'Personal')"));
					$html .= "<tr><td>Total funds received - <b>Community</b> silos:</td><td align=right><b>".money_format('%(#10n', round($community_funds_received[0],2))."</b></td></tr>";
					$html .= "<tr><td>Total funds received - <b>Personal</b> silos:</td><td align=right><b>".money_format('%(#10n', round($personal_funds_received[0],2))."</b></td></tr>";

					$html .= "</table>";
					echo $html;
				}
				
				//ITEMS
				else if ($view == 'items') {
					$checkItems = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE type = 'item'"));
					if ($checkItems) {
					echo "<h2>Flagged items</h2>";
					$flagged_items = mysql_query("SELECT * FROM flag_radar WHERE type = 'item' ORDER BY item_id");				
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='30%'>Title</th><th width='16%'>Status</th><th width='8%'>Seller</th><th width='7%' style='text-align:center'>Override?</th></tr>";
					while ($getItem = mysql_fetch_array($flagged_items)) {
						$item = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE item_id = '$getItem[item_id]' ORDER BY item_id"));
						$item_id = $item['item_id'];
						$mem = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));						
						$html .= "<tr><td><input type='checkbox' name='items[]' value=$item_id></td><td>#$item_id</td><td><a class='bluelink'  href='index.php?task=view_item&id=$item_id'>".$item['title']."</a></td><td>".$getItem['status']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$mem['user_id']."'>".$mem['username']."</a></td><td align=center>";
						$html .= "<form action='' method='POST' name='unflag_".$item_id."'>
								<input type='hidden' name='task' value='item unflag'>
								<input type='hidden' name='item_id' value='$item_id'>
								<input type='submit' value='Reactivate'></input>
							</form></td></tr>";
					}
					$html .= "</table>";
					echo $html;
					}

					echo "<h2>Active items</h2>";
					$active_items = mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE status = 'pledged' ORDER BY item_id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='7%'>Item #</th><th width='40%'>Title</th><th width='15%'>Category</th><th width='15%'>Seller</th><th width='10%'>Status</th><th width='10%' style='text-align:center'>Price</th></tr>";
					while ($item = mysql_fetch_array($active_items)) {
						$item_id = $item['item_id'];
						$mem = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));						
						$html .= "<tr><td><input type='checkbox' name='items[]' value=$item_id></td><td>#$item_id</td><td><a class='bluelink'  href='index.php?task=view_item&id=$item_id'>".$item['title']."</a></td><td>".$item['category']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$mem['user_id']."'>".$mem['username']."</a></td><td>".$item['status']."</td><td align=right>".money_format('%(#10n', floatval($item['price']))."</td></tr>";
					}
					$html .= "</table>";
					echo $html;					

					echo "<h2>Deleted items</h2>";					
					$deleted_items = mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE status = 'deleted' ORDER BY item_id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='7%'>Item #</th><th width='40%'>Title</th><th width='15%'>Category</th><th width='15%'>Seller</th><th width='10%'>Status</th><th width='10%' style='text-align:center'>Price</th></tr>";
					while ($item = mysql_fetch_array($deleted_items)) {
						$item_id = $item['item_id'];
						$mem = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));						
						$html .= "<tr><td><input type='checkbox' name='items[]' value=$item_id></td><td>#$item_id</td><td><a class='bluelink'  href='index.php?task=view_item&id=$item_id'>".$item['title']."</a></td><td>".$item['category']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$mem['user_id']."'>".$mem['username']."</a></td><td>".$item['status']."</td><td align=right>".money_format('%(#10n', floatval($item['price']))."</td></tr>";
					}
					$html .= "</table>";
					echo $html;				
				}
				
				//MEMBERS
				else if ($view == 'members') {
					if (param_post('task') == 'delete') {
						$users_to_delete = param_post('users');
						$in_clause = implode(',', $users_to_delete);
						$sql1 = "DELETE FROM users WHERE user_id IN ($in_clause)";
						mysql_query($sql1);
						$sql2 = "DELETE FROM silos WHERE admin_id IN ($in_clause)";
						mysql_query($sql2);
						$sql3 = "DELETE FROM items WHERE user_id IN ($in_clause)";
						mysql_query($sql3);
						$sql4 = "DELETE FROM silo_membership WHERE user_id IN ($in_clause)";
						mysql_query($sql4);
					}
					echo "<br/><form name='members' id='members' action='index.php?view=members' method=post>";
					echo "<input type='hidden' name='task' value='delete'>";
					$users = mysql_query("SELECT * FROM users ORDER BY user_id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='7%'>Account #</th><th width='10%'>Username</th><th width='30%'>Address</th><th width='8%'>Since</th><th width='10%' style='text-align:right'>Sold</th><th width='10%' style='text-align:right'>Fees</th><th width='7%' style='text-align:center'>Listings</th><th width='5%' style='text-align:right'>Success</th><th width='5%' style='text-align:center'>Silos</th><th width='5%'>Flags</th></tr>";
					while ($user = mysql_fetch_array($users)) {
						$user_id = $user['user_id'];
						$listings = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 AND user_id=$user_id"));
						$siloz = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM silos WHERE admin_id=$user_id"));
						
						$html .= "<tr><td><input type='checkbox' name='users[]' value=$user_id></td><td>$user_id</td><td><a class='bluelink'  href='index.php?task=view_user&id=$user_id'>".$user['username']."</a></td><td>".$user['address']."</td><td>".substr($user['joined_date'],0,10)."</td><td align=right>".money_format('%(#10n', floatval("5000"))."</td><td align=right>".money_format('%(#10n', floatval("200"))."</td><td align=center>".$listings[0]."</td><td align=center>77%</td><td align=center>".$siloz[0]."</td><td align=center>2</td></tr>";
					}
					$html .= "</table><br/>";
					echo $html;		
					echo "<button type='submit' value='Delete' class='round'>Delete</button>";
					echo "</form>";
				}
				
				//SILOS
				else if ($view == 'silos') {
					if (param_post('task') == 'delete') {
						$silos_to_delete = param_post('silos');
						$in_clause = implode(',', $silos_to_delete);
						$sql2 = "DELETE FROM silos WHERE silo_id IN ($in_clause)";
						mysql_query($sql2);
						$sql3 = "DELETE FROM items WHERE silo_id IN ($in_clause)";
						mysql_query($sql3);
						$sql4 = "DELETE FROM silo_membership WHERE silo_id IN ($in_clause)";
						mysql_query($sql4);
					}
					if (param_post('task') == 'markPaid') {
						$paid_status = param_post('paid');
						$silo_id = param_post('silo_id');
						$paid = mysql_query("UPDATE silos SET status = 'completed', paid = '$paid_status' WHERE silo_id = '$silo_id'");
						if ($paid_status == "yes") { $notif = new Notification(); $notif->SiloPaid($silo_id); }
						header('Location: '.$_SERVER['REQUEST_URI']);
						exit;
					}
					echo "<br><a href='".ACTIVE_URL."index.php?task=create_silo_admin'>Create a Disaster Relief Silo</a>";
					$checkSilos = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE type = 'silo'"));
					if ($checkSilos) {
					echo "<br/><form name='silos' id='silos' action='index.php?view=silos' method=post>";
					echo "<input type='hidden' name='task' value='delete'>";					
					echo "<h2>Flagged silos</h2>";
					$today = date("Y-m-d")."";
					$silos = mysql_query("SELECT * FROM flag_radar WHERE type = 'silo' ORDER BY silo_id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='30%'>Silo Name</th><th width='16%'>Status</th><th width='8%'>Admin Name</th><th width='8%'>Phone</th><th width='8%'>E-mail</th><th width='7%' style='text-align:center'>Override?</th></tr>";
					while ($getSilo = mysql_fetch_array($silos)) {
						$silo_id = $getSilo['silo_id'];
						$silo = mysql_fetch_array(mysql_query("SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE silo_id = '$silo_id' ORDER BY silo_id"));						
						$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$silo['admin_id']));
						$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = $silo_id AND status = 'Sold'";
						$s2 = mysql_fetch_row(mysql_query($sql));
						$pct = round(floatval($s2[0])*100.0/floatval($silo['goal']),1);
						$listings = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 AND silo_id=$silo_id"));
						$html .= "<tr><td><input type='checkbox' name='silos[]' value=$silo_id></td><td>$silo_id</td><td><a class='bluelink'  href='index.php?task=view_silo&id=$silo_id'>".$silo['name']."</a></td><td>".$getSilo['status']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$admin['user_id']."'>".$admin['fname']." ".$admin['lname']."</a></td><td>".$admin['phone']."</td><td>".$admin['email']."</td><td align=center>";
						$html .= "<form action='' method='POST' name='unflag_".$silo_id."'>
								<input type='hidden' name='task' value='unflag'>
								<input type='hidden' name='silo_id' value='$silo_id'>
								<input type='submit' value='Reactivate'></input>
							</form></td></tr>";
					}
					$html .= "</table>";
					echo $html;
					}

					echo "<br/><form name='silos' id='silos' action='index.php?view=silos' method=post>";
					echo "<input type='hidden' name='task' value='delete'>";					
					echo "<h2>Active silos</h2>";
					$today = date("Y-m-d")."";
					$silos = mysql_query("SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE status = 'active' ORDER BY silo_id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='30%'>Silo Name</th><th width='16%'>Category</th><th width='8%'>Admin Name</th><th width='8%'>Phone</th><th width='8%'>E-mail</th><th width='10%' style='text-align:right'>Goal</th><th width='5%' style='text-align:center'>%</th><th width='5%'>Listings</th><th width='7%' style='text-align:center'>Ends</th></tr>";
					while ($silo = mysql_fetch_array($silos)) {
						$silo_id = $silo['silo_id'];						
						$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$silo['admin_id']));
						$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = $silo_id AND status = 'Sold'";
						$s2 = mysql_fetch_row(mysql_query($sql));
						$pct = round(floatval($s2[0])*100.0/floatval($silo['goal']),1);
						$listings = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 AND silo_id=$silo_id"));
						$ends_in = floor((strtotime($silo['end_date']) - strtotime($today))/(60*60*24));
						$html .= "<tr><td><input type='checkbox' name='silos[]' value=$silo_id></td><td>$silo_id</td><td><a class='bluelink'  href='index.php?task=view_silo&id=$silo_id'>".$silo['name']."</a></td><td>".$silo['type']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$admin['user_id']."'>".$admin['fname']." ".$admin['lname']."</a></td><td>".$admin['phone']."</td><td>".$admin['email']."</td><td align=right>".money_format('%(#10n', floatval($silo['goal']))."</td><td align=center>".$pct."</td><td align=center>".$listings[0]."</td><td align=center>$ends_in days</td></tr>";
					}
					$html .= "</table>";
					echo $html;										
					
					
					echo "<h2>Ended silos</h2>";
					$today = date("Y-m-d")."";
					$silos = mysql_query("SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE status = 'latent' OR status = 'completed' ORDER BY paid, id");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='25%'>Silo Name</th><th width='16%'>Category</th><th width='8%'>Admin Name</th><th width='8%'>Phone</th><th width='8%'>E-mail</th><th width='10%' style='text-align:right'>Goal</th><th width='5%' style='text-align:center'>%</th><th width='5%'>Listings</th><th width='10%' style='text-align:center'>Paid?</th></tr>";
					while ($silo = mysql_fetch_array($silos)) {
						$silo_id = $silo['silo_id'];						
						$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$silo['admin_id']));
						$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = $silo_id AND status = 'Sold'";
						$s2 = mysql_fetch_row(mysql_query($sql));
						$pct = round(floatval($s2[0])*100.0/floatval($silo['goal']),1);
						$listings = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 AND silo_id=$silo_id"));
						$ends_in = floor((strtotime($silo['end_date']) - strtotime($today))/(60*60*24));
						$paid = $silo['paid'];
						$opt = ""; $other_opt = "";
						$opt .= '<option value="' . $paid . '">' . $paid . '</option>';
						if ($paid == "no") { $other_opt .= '<option value="yes">yes</option>'; } else { $other_opt .= '<option value="no">no</option>'; }
						$html .= "<tr><td><input type='checkbox' name='silos[]' value=$silo_id></td><td>$silo_id</td><td><a class='bluelink'  href='index.php?task=view_silo&id=$silo_id'>".$silo['name']."</a></td><td>".$silo['type']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$admin['user_id']."'>".$admin['fname']." ".$admin['lname']."</a></td><td>".$admin['phone']."</td><td>".$admin['email']."</td><td align=right>".money_format('%(#10n', floatval($silo['goal']))."</td><td align=center>".$pct."</td><td align=center>".$listings[0]."</td><td align=center>";
							$pur = mysql_fetch_array(mysql_query("SELECT expired_date  FROM item_purchase WHERE silo_id = '$silo_id' ORDER BY expired_date DESC LIMIT 1"));
							$expired = strtotime($pur[0]);
							$now = strtotime("now");
							$exp_date = date("D, M jS (g:i a)", $expired);
						if ($expired < $now) {
							$html .= "<form action='' method='POST'>
								<input type='hidden' name='task' value='markPaid'>
								<input type='hidden' name='silo_id' value='$silo_id'>
    								<select name='paid' onchange='this.form.submit()'>
									".$opt."
									".$other_opt."
    								</select>
							</form></td></tr>";
						} else { $html .= "Pay after: ".$exp_date; }
					}
					$html .= "</table>";					
					echo $html;									
					
					echo "<br/><button type='submit' value='Delete' class='round'>Delete</button>";
					echo "</form>";	
				}elseif($view === "radar"){
				$checkSilos = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE type = 'silo'  AND notify = 1"));
				$checkItems = mysql_num_rows(mysql_query("SELECT * FROM flag_radar WHERE type = 'item' AND notify = 1"));
				$updNotify = mysql_query("UPDATE flag_radar SET notify = 0");
				if ($checkSilos) {
					echo "<br/><form name='silos' id='silos' action='index.php?view=silos' method=post>";
					echo "<input type='hidden' name='task' value='delete'>";					
					echo "<h2>Flagged silos</h2>";
					$today = date("Y-m-d")."";
					$silos = mysql_query("SELECT *, flag_radar.status AS flagStatus FROM flag_radar INNER JOIN silos USING (silo_id) WHERE type = 'silo'");
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='30%'>Silo Name</th><th width='16%'>Status</th><th width='8%'>Admin</th><th width='7%' style='text-align:center'>Override?</th></tr>";
					while ($silo = mysql_fetch_array($silos)) {
						$silo_id = $silo['silo_id'];						
						$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$silo['admin_id']));
						$sql = "SELECT SUM(price) FROM items WHERE deleted_date = 0 AND silo_id = $silo_id AND status = 'Sold'";
						$s2 = mysql_fetch_row(mysql_query($sql));
						$pct = round(floatval($s2[0])*100.0/floatval($silo['goal']),1);
						$listings = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 AND silo_id=$silo_id"));
						$html .= "<tr><td><input type='checkbox' name='silos[]' value=$silo_id></td><td>$silo_id</td><td><a class='bluelink'  href='index.php?task=view_silo&id=$silo_id'>".$silo['name']."</a></td><td>".$silo['flagStatus']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$admin['user_id']."'>".$admin['username']."</a></td><td align=center>";
						$html .= "<form action='' method='POST' name='unflag_".$silo_id."'>
								<input type='hidden' name='task' value='unflag'>
								<input type='hidden' name='silo_id' value='$silo_id'>
								<input type='submit' value='Reactivate'></input>
							</form></td></tr>";
					}
					$html .= "</table>";
					echo $html;
				}
				if ($checkItems) {
					$items = mysql_query("SELECT *, flag_radar.status AS flagStatus FROM flag_radar INNER JOIN items USING (item_id)");
					echo "<h2>Flagged items</h2>";
					$flagged_items = mysql_query("SELECT * FROM flag_radar WHERE type = 'item' ORDER BY item_id");				
					$html = "<table id='alternate_table'><tr><th width='3%'></th><th width='5%'>ID #</th><th width='30%'>Title</th><th width='16%'>Status</th><th width='8%'>Seller</th><th width='7%' style='text-align:center'>Override?</th></tr>";
					while ($getItem = mysql_fetch_array($flagged_items)) {
						$item = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE item_id = '$getItem[item_id]' ORDER BY item_id"));
						$item_id = $item['item_id'];
						$mem = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$item['user_id']));						
						$html .= "<tr><td><input type='checkbox' name='items[]' value=$item_id></td><td>#$item_id</td><td><a class='bluelink'  href='index.php?task=view_item&id=$item_id'>".$item['title']."</a></td><td>".$getItem['status']."</td><td><a class='bluelink'  href='index.php?task=view_user&id=".$mem['user_id']."'>".$mem['username']."</a></td><td align=center>";
						$html .= "<form action='' method='POST' name='unflag_".$item_id."'>
								<input type='hidden' name='task' value='item unflag'>
								<input type='hidden' name='item_id' value='$item_id'>
								<input type='submit' value='Reactivate'></input>
							</form></td></tr>";
					}
					$html .= "</table>";
					echo $html;
				}
				if (!$checkSilos && !$checkItems) { echo "<br><br><center><b>There are currently no items or silos on the flag radar. Yahoo!</b></center>"; }
				}
			?>
			</div>
		</div>
	</body>
<?php
}
?>
</html>
