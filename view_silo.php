<?php
	$feedPerPage = "3";
	$usersPerPage = "12";
	$itemsPerPage = "4";

	$id = param_get('id');
	
	$view = param_get('view');
	if ($view == '')
		$view = 'home';
	if ($id == '') {
		echo "<script>window.location = 'index.php';</script>";	
	}	
	else {
		$silo = new Silo($id);
		$silo_id = $silo->silo_id;
		$today = date('Y-m-d')."";
		$silo_ended = $silo->end_date < $today;
		$admin = $silo->admin;

		$checkUser = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo_id' AND user_id = '$user_id' AND removed_date > 0"));
?>

<div class="login" id="sold" style="width: 300px;">
	<div id="sold_drag" style="float:right">
		<img id="sold_exit" src="images/close.png"/>
	</div>
	<div>
		This Item Has Been Sold!
	</div>
</div>

<div class="login" id="pending" style="width: 300px;">
	<div id="pending_drag" style="float:right">
		<img id="pending_exit" src="images/close.png"/>
	</div>
	<div>
		This Item Is Pending To Be Sold!
	</div>
</div>

<div class="contact_seller" id="contact_admin">
	<div id="contact_admin_drag" style="float: right">
		<img id="contact_admin_exit" src="images/close.png"/>
	</div>
	<div>
		<form name="contact_admin_form" id="contact_admin_form" method="POST">
			<h2>Contact Admin</h2>
			<p>Silo <b><?php echo $silo->name; ?></b></p>
			<div id="contact_admin_status"></div>			
			<table>
				<tr>
					<td valign="top">
						<b>Email</b>
					</td>
					<td>
						<input type="text" name="contact_email" id="contact_email" onfocus="select();" style="width:300px;" 
						value=<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?> >
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Subject</b>
					</td>
					<td>
						<input type="text" name="contact_subject" id="contact_subject" onfocus="select();" style="width:300px;"/>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Body</b>
					</td>
					<td>
						<textarea style='width: 300px; height: 200px' name="inquiry" id="inquiry"></textarea>
					</td>
				</tr>
			</table>
			<br/>			
			<button type="button" id="contact_admin_button">Send</button>
			<button type="button" onclick="document.getElementById('overlay').style.display='none';document.getElementById('contact_admin').style.display='none';">Cancel</button>
		</form>
		<script>
			$("#contact_admin_button").click(function(event) {	
				document.getElementById('overlay').style.display='none';
				document.getElementById('contact_admin').style.display='none';
				$.post(<?php echo "'".API_URL."'"; ?>, 
					{	
						request: 'email_silo_admin',
						silo_id: <?php echo $silo->silo_id; ?>,
						email: document.getElementById('contact_email').value,
						subject: document.getElementById('contact_subject').value,
						content: document.getElementById('inquiry').value
					}, 
					function (xml) {
						$(xml).find('response').each(function (){
							if ($(this).text() == 'successful') 
								alert("Your inquiry has been sent!");
							else
								alert("Failed to send your inquiry!");
							document.getElementById('contact_email').value = "<?php echo $_SESSION['is_logged_in'] != 1 ? "" : $current_user['email'];?>";
							document.getElementById('contact_subject').value = "";
							document.getElementById('inquiry').value = "";							
						});
					}
				);
			});
		</script>		
	</div>
</div>

<div class="span12 span_static">
	<div class="row">
		<div class="silo-header">
			<span><?php echo $silo->getTitle(); ?></span>
			<span></span>
		</div>
	</div>
	<div class="row">
		<div class="span4">
			<div class="row">
				<?php
					$collected = $silo->getCollectedAmount();
					$pct = round($collected*100.0/floatval($silo->goal),1);
					$end_date = $silo->end_date;
					$end = strtotime("$end_date");
					$now = time();
					$timeleft = $end-$now;
					$daysleft = ceil($timeleft/86400);
					
					if ($daysleft > 1) { $dayplural = "Days"; } else { $dayplural = "Day"; }

					$c_user_id = $current_user['user_id'];
					$show = mysql_num_rows(mysql_query("SELECT * FROM silo_membership WHERE silo_id = '$silo->silo_id' AND user_id = '$c_user_id' AND removed_date = 0"));
					if ($show) { $admin_name = $admin->fname; $admin_name .= "&nbsp;".$admin->lname; } else { $admin_name = $admin->fname; };
				?>
				<div class='siloInfo'>
					<img src=<?php echo 'uploads/silos/'.$silo->photo_file;?> width='250px'/>
					<div class='bio'>
		
					<div class='floatL'><b>Goal:</b> <?php echo money_format('%(#10n', floatval($silo->goal));?> (<?=$pct?>%)</div>
					<div class='floatR'><b><?=$daysleft?> <?=$dayplural?> Left</b></div>
					<div class='floatL'><div class='padding'><b>Progress:</b> &nbsp;&nbsp;&nbsp;<div style='float: right; width: 160px; height: 12px; border: 1px solid #2F8ECB;'><div style='float: left; width: <?=$pct;?>%; height:12px; background: #2F8ECB;'></div></div></div></div>
					<div class='padding'>&nbsp;</div>
					<p><a href='index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalMembers();?> Members</a>, <a href='index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>'><?php echo $silo->getTotalItems();?> Items Pledged</a></p>
					<div class='floatL'><b>Organization:</b> <?php echo $silo->name; ?></div>
					<div class='floatL'><b>Purpose:</b> <?php echo $silo->getPurpose();?></div>
					<div class='floatL'><b>Category:</b> <?php echo $silo->type;?></div>
					<div class='padding2'>&nbsp;</div>
					<table class='floatL'>
					<tr>
					<td>
						<img src=<?php echo 'uploads/members/'.$admin->photo_file;?> width='90px'/><br>
						<button type='button' class='buttonEmail'>Email Admin.</button>
					</td>
					<td width='6%'></td>
					<td>
						<b>Silo Admin:</b> <br> <?php echo $admin_name?><br>
						<b>Title:</b> <?php echo $silo->title; ?><br>
						<b>Official Address:</b> <br> <?php echo $silo->address; ?><br>
						<b>Telephone:</b> <?php echo $silo->phone_number; ?>
					</td>
					</tr>
					</table>
					<div class='floatL'><div class='voucher'>Research a silo and administrator</div></div>
					<div class='floatL'><?php include('include/UI/flag_box.php'); ?></div>
		
				</div>
				</div>
				</td>
				
				<div id='fb-root'></div>
				<script src='http://connect.facebook.net/en_US/all.js'></script>
				<?php
					$url = ACTIVE_URL."/index.php?task=view_silo&id=$silo->id";
					$photo_url = ACTIVE_URL.'/uploads/silos/'.$silo->photo_file;
					$name = $silo->name;
				?>
		
				<script> 
				 	FB.init({
				            appId      : <?php echo "'".FACEBOOK_ID."'"; ?>,
				            status     : true, 
				            cookie     : true,
				            xfbml      : true
				          });
				  	function postToFeed() {
				    	FB.ui({
				      		method: 'feed',
				      		link: "<?php echo $url; ?>",
				      		picture: "<?php echo $photo_url; ?>",
				      		name: "<?php echo $silo->type.' Silo: '.$name; ?>",
							caption: "Siloz.com - Commerce thats count",
				      		description: "<?php echo $description; ?>"
				    	});
				  	}
				</script>
			</div>	
		</div>
		
		<div class="span8">
			<div class="row">
			<button type="button" class="buttonSilo" style="font-size: 12px; <?php echo ($view == 'home' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&id=<?php echo $silo->id;?>'">Feed</button>
			<button type="button" class="buttonSilo" style="font-size: 12px; <?php echo ($view == 'members' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>'">Members</button>
			<button type="button" class="buttonSilo" style="font-size: 12px; <?php echo ($view == 'items' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>'">Items</button>
			<button type="button" class="buttonSilo" style="font-size: 12px; <?php echo ($view == 'map' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=map&id=<?php echo $silo->id;?>'">Map</button>
			
			<div class="icons">
				<img class="space" src="images/fb.png" onclick='postToFeed();'/>
				<a href="mailto:?Subject=www.siloz.com/index.php?task=view_silo%26<?php echo $silo->id;?>&Body=Check out this silo on siloz!"><img class="space" src="images/mail-icon.png"></a>
		
				<?php
					if ($checkUser) {
				?>
				<button type="submit" class="buttonDonations" onclick="alert('You cannot pledge anymore items to this silo because the administrator has removed you. Please find a different silo!')" id="sell_on_siloz">Donate<br>Items</button>
	
				<?php
					}
					elseif ($_SESSION['is_logged_in']) {			
				?>
					<button type="submit" class="buttonDonations" onclick="window.open('index.php?task=sell_on_siloz<?php echo "&id=".$silo->id;?>', '_parent');" id="sell_on_siloz">Donate<br>Items</button>						
		
					<?php
						$ref = "S".$silo->id."-U".$current_user['id']."-".date('m/d/Y H:i:s');	
					?>
		
				<?php
					}
					else {
				?>
				<button type="submit" class="buttonDonations" onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);" id="sell_on_siloz">Donate<br>Items</button>						
				<?php
					}
				?>
		
				<?php
					$new_sort_order = '';
					$sort_order = param_get('sort_order');
					$img1_path = 'images/none.png';
					$img2_path = 'images/none.png';			
					$order_by = param_get('sort_by');	
					if ($sort_order == 'asc') {
						$new_sort_order = '&sort_order=desc';
						if ($order_by == 'date')
							$img2_path = 'images/up.png';
						else if ($order_by != '')
							$img1_path = 'images/up.png';
					}
					else {
						$new_sort_order = '&sort_order=asc';										
						if ($order_by == 'date')
							$img2_path = 'images/down.png';
						else if ($order_by != '')
							$img1_path = 'images/down.png';
					}
					if ($view == 'item') {
						echo "<b>sort by <a href=index.php?task=view_silo&view=items&sort_by=price$new_sort_order&id=".$silo->id." class=simplebluelink>price <img src=$img1_path></a> or <a href=index.php?task=view_silo&view=items&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>list date <img src=$img2_path></a></b>";
					}
					else if ($view == 'member') {
						echo "<b>sort by <a href=index.php?task=view_silo&view=members&sort_by=name$new_sort_order&id=".$silo->id." class=simplebluelink>username <img src=$img1_path></a> or <a href=index.php?task=view_silo&view=members&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>join date <img src=$img2_path></a></b>";
					}
				?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="info-container">
<?php
		if ($view == 'home') {
			$count = "SELECT COUNT(*) as num FROM feed WHERE silo_id = '$silo_id'";
			$countRow = mysql_fetch_array(mysql_query($count));
			$total_records = $countRow[num];
			$total_pages = ceil($total_records / $feedPerPage);

			if (param_get('page')) {
				$page  = param_get('page');
			} 
			else { 
				$page = 1;
			};

			$start_from = ($page-1) * $feedPerPage; 

			$feed = mysql_query("SELECT * FROM feed WHERE silo_id = '$silo_id' ORDER BY date DESC LIMIT $start_from, $feedPerPage");
			$num = mysql_num_rows($feed);

			if ($total_pages < 2) {}
			else	{
					if ($page != "1") {
						$prev = $page - 1;
						echo '<a href="index.php?task=view_silo&id='.$silo->id.'&page='.$prev.'" style="text-decoration: none" class="blue"><< previous page</a> &nbsp; &nbsp;';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_silo&id='.$silo->id.'&page='.$i.'" style="text-decoration: none" class="blue">' . $i . '</a> &nbsp;';
					} 
					else {
						echo '<font class="orange">'.$i.'</font> &nbsp;';
					}
				};
					if ($page != $total_pages) {
						$next = 2;
						echo '<a href="index.php?task=view_silo&id='.$silo->id.'&page='.$next.'" style="text-decoration: none" class="blue">&nbsp; &nbsp; next page >></a>';
					}
			}

			echo '<div style="padding-bottom: 5px;"></div>';

			if (!$num) {
    				echo "This silo feed is empty. Any activity for this silo will be posted here to keep everyone involved up to date.";
  			}

			while ($result = mysql_fetch_array($feed)) {

				//Get and set info for feed
				$user = new User($result['user_id']);
				$userCell = $user->getMemberCell($silo_id, $c_user_id);
				$item = new Item($result['item_id']);
				$itemCell = $item->getItemCell($silo_id, $c_user_id);
				$siloCell = $silo->getPlate($silo_id);

				$date = date('M d, Y', strtotime($result['date']));
				$time = date('h:i A T', strtotime($result['date']));
				$type = $result['type'];
				$goal_reached = $result['goal_reached'];
				$total_raised = floatval($collected);

				if ($type == "Pledged") {
					$cellInfo = "<div class=nicebox><table width=675px><tr><td width=30% valign=middle><table height=150px width=95%><tr><td valign=top><font class='blue' size='4'><b>$user->fname has pledged $item->title.</b></font></td></tr>
							<tr><td valign=bottom><a href='index.php?task=view_item&id=$item->id' target='_blank'><font class=orange>Check it out</font></a> <font class=blue>and spread the word if you know any buyers!</blue></td></tr></table></td>";
					$cellInfo .= "$userCell";
					$cellInfo .= "$itemCell";
					$cellInfo .= "<td width=20% valign=top align=center>$date<br>$time</td></tr></table></div><br/>";
				}
				if ($type == "Sold") {
					$cellInfo = "<div class=nicebox><table width=675px><tr><td width=30% valign=middle><table height=150px width=95%><tr><td valign=top><font class='blue' size='4'><b>$user->fname has sold $item->title.</b></font></td></tr>
							<tr><td valign=bottom><font class=blue>The total amount raised for this silo is now $$total_raised!</blue></td></tr></table></td>";
					$cellInfo .= "$userCell";
					$cellInfo .= "$itemCell";
					$cellInfo .= "<td width=20% valign=top align=center>$date<br>$time</td></tr></table></div><br/>";
				}
				if ($type == "Goal") {
					$cellInfo = "<div class=nicebox><table width=675px><tr><td width=30% valign=top><font class='blue' size='4'><b>Silo $silo->name reached $goal_reached% of its goal!</b></font></td>";
					$cellInfo .= "<td>$siloCell</td>";
					$cellInfo .= "<td width=20% valign=top align=center>$date<br>$time</td></tr></table></div><br/>";
				}
			echo $cellInfo;
			}
		}

		//VIEW MEMBERS
		if ($view == 'members' && $_SESSION['is_logged_in']) {
			$count = "SELECT * FROM users WHERE user_id IN (SELECT user_id FROM silo_membership WHERE silo_id = $silo_id AND removed_date = 0)";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $usersPerPage);

			if (param_get('page')) {
				$page  = param_get('page');
			} 
			else { 
				$page = 1;
			};

			$start_from = ($page-1) * $usersPerPage;

			if ($total_pages < 2) {}
			else	{
					if ($page != "1") {
						$prev = $page - 1;
						echo '<a href="index.php?task=view_silo&view=members&id='.$silo->id.'&page='.$prev.'" style="text-decoration: none" class="blue"><< previous page</a> &nbsp; &nbsp;';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_silo&view=members&id='.$silo->id.'&page='.$i.'" style="text-decoration: none" class="blue">' . $i . '</a> &nbsp;';
					} 
					else {
						echo '<font class="orange">'.$i.'</font> &nbsp;';
					}
				};
					if ($page != $total_pages) {
						$next = $page + 1;
						echo '<a href="index.php?task=view_silo&view=members&id='.$silo->id.'&page='.$next.'" style="text-decoration: none" class="blue">&nbsp; &nbsp; next page >></a>';
					}
			}

			echo '<div style="padding-bottom: 5px;"></div>';

			$limit = "LIMIT $start_from, $usersPerPage";
			$users = User::getMembers($silo_id, $order_by, $limit);
			echo "<table cellpadding='10px'>";
			$n = 0;
			foreach ($users as $user) {
				if ($n == 0)
					echo "<tr>";
				echo $user->getMemberCell($silo_id, $c_user_id);					
				$n++;
				if ($n == 4) {
					echo "</tr>";
					$n = 0;
				}					
			}
			echo "</table>";
			if ($total_records == "0") { echo "There are currently no members in this silo. Only users who pledge an item to this silo are considered members."; }
		}
		elseif ($view == 'members') {
			echo "Please create an account or login to an existing account to view silo members.";
		}
		
		//VIEW ITEMS
		if ($view == 'items') {
			$sold_items = param_get('items');

		if ($sold_items == "") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'pledged' AND silo_id = $silo_id";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);

			if (param_get('page')) {
				$page  = param_get('page');
			} 
			else { 
				$page = 1;
			};

			$start_from = ($page-1) * $itemsPerPage;

			if ($total_pages < 2) {}
			else	{
					if ($page != "1") {
						$prev = $page - 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$prev.'" style="text-decoration: none" class="blue"><< previous page</a> &nbsp; &nbsp;';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$i.'" style="text-decoration: none" class="blue">' . $i . '</a> &nbsp;';
					} 
					else {
						echo '<font class="orange">'.$i.'</font> &nbsp;';
					}
				};
					if ($page != $total_pages) {
						$next = $page + 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$next.'" style="text-decoration: none" class="blue">&nbsp; &nbsp; next page >></a>';
					}
			}

			echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <font class='orange'><u>Pledged</u></font> &nbsp; | &nbsp; <a href='index.php?task=view_silo&view=items&items=sold&id=".$silo->id."' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <a href='index.php?task=view_silo&view=items&items=pending&id=".$silo->id."' style='text-decoration: none' class='blue'>Pending Sales</a></div>";

			echo '<div style="padding-bottom: 5px;"></div>';

			$limit = "LIMIT $start_from, $itemsPerPage";
			$items = Item::getItems($silo_id, $order_by, $limit);
			$n = 0;
			echo "<table cellpadding='10px'>";			
			foreach ($items as $item) {
				if ($n == 0)
					echo "<tr>";							
				echo $item->getItemCell($silo_id, $c_user_id);					
				$n++;
				if ($n == 4) {
					echo "</tr>";
					$n = 0;
				}					
			}
			echo "</table>";

			if ($total_records == "0") { echo "There are currently no items being pledged in this silo. Once an item is pledged to this silo, it will be added to this list."; }
	
		}

		if ($sold_items == "sold") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'sold' AND silo_id = $silo_id";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);

			if (param_get('page')) {
				$page  = param_get('page');
			} 
			else { 
				$page = 1;
			};

			$start_from = ($page-1) * $itemsPerPage;

			if ($total_pages < 2) {}
			else	{
					if ($page != "1") {
						$prev = $page - 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$prev.'" style="text-decoration: none" class="blue"><< previous page</a> &nbsp; &nbsp;';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$i.'" style="text-decoration: none" class="blue">' . $i . '</a> &nbsp;';
					} 
					else {
						echo '<font class="orange">'.$i.'</font> &nbsp;';
					}
				};
					if ($page != $total_pages) {
						$next = $page + 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$next.'" style="text-decoration: none" class="blue">&nbsp; &nbsp; next page >></a>';
					}
			}

			echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='index.php?task=view_silo&view=items&id=".$silo->id."' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <font class='orange'><u>Sold</u></font> &nbsp; | &nbsp; <a href='index.php?task=view_silo&view=items&items=pending&id=".$silo->id."' style='text-decoration: none' class='blue'>Pending Sales</a> </div>";

			echo '<div style="padding-bottom: 5px;"></div>';

			$limit = "LIMIT $start_from, $itemsPerPage";
			$items = Item::getSoldItems($silo_id, $order_by, $limit);
			$n = 0;
			echo "<table cellpadding='10px'>";			
			foreach ($items as $item) {
				if ($n == 0)
					echo "<tr>";							
				echo $item->getSoldItemCell($silo_id, $c_user_id);					
				$n++;
				if ($n == 4) {
					echo "</tr>";
					$n = 0;
				}	
			}
			echo "</table>";

			if ($total_records == "0") { echo "There are currently no items that have sold in this silo. Once an item has sold for this silo, it will be added to this list."; }
	
		}

		if ($sold_items == "pending") {
			$count = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE items.status = 'pending' AND silo_id = $silo_id";
			$countRow = mysql_num_rows(mysql_query($count));
			$total_records = $countRow;
			$total_pages = ceil($total_records / $itemsPerPage);

			if (param_get('page')) {
				$page  = param_get('page');
			} 
			else { 
				$page = 1;
			};

			$start_from = ($page-1) * $itemsPerPage;

			if ($total_pages < 2) {}
			else	{
					if ($page != "1") {
						$prev = $page - 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$prev.'" style="text-decoration: none" class="blue"><< previous page</a> &nbsp; &nbsp;';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$i.'" style="text-decoration: none" class="blue">' . $i . '</a> &nbsp;';
					} 
					else {
						echo '<font class="orange">'.$i.'</font> &nbsp;';
					}
				};
					if ($page != $total_pages) {
						$next = $page + 1;
						echo '<a href="index.php?task=view_silo&view=items&id='.$silo->id.'&page='.$next.'" style="text-decoration: none" class="blue">&nbsp; &nbsp; next page >></a>';
					}
			}

			echo "<div class='blue' style='float: right'><i>View:</i> &nbsp; <a href='index.php?task=view_silo&view=items&id=".$silo->id."' style='text-decoration: none' class='blue'>Pledged</a> &nbsp; | &nbsp; <a href='index.php?task=view_silo&view=items&items=sold&id=".$silo->id."' style='text-decoration: none' class='blue'>Sold</a> &nbsp; | &nbsp; <font class='orange'><u>Pending Sales</u></font> </div>";

			echo '<div style="padding-bottom: 5px;"></div>';

			$limit = "LIMIT $start_from, $itemsPerPage";
			$items = Item::getPendingItems($silo_id, $order_by, $limit);
			$n = 0;
			echo "<table cellpadding='10px'>";			
			foreach ($items as $item) {
				if ($n == 0)
					echo "<tr>";							
				echo $item->getPendingItemCell($silo_id, $c_user_id);					
				$n++;
				if ($n == 4) {
					echo "</tr>";
					$n = 0;
				}	
			}
			echo "</table>";

			if ($total_records == "0") { echo "There are currently no items that have sold in this silo. Once an item has sold for this silo, it will be added to this list."; }
	
		}
	}

		//VIEW Map
		if ($view == 'map') {
			echo "<div id='map_canvas' style='width: 675px; height: 590px;'></div>";
		}
	?>
<?php
	}
?>

<?php
//Get items in silo for map
$qry = mysql_query("SELECT * FROM items WHERE silo_id = $silo_id");
$num = mysql_num_rows($qry);

    echo "<script> var locations = [";

        while ($map = mysql_fetch_array($qry)){

        echo "['" . $map['title'] . "', " . $map['latitude'] . ", " . $map['longitude'] . "],";

        }

    echo " ];</script>";

?>
</div>

<script  type="text/javascript">

function initialize() {
var styles = [
	{
		featureType: 'water',
		elementType: 'all',
		stylers: [
			{ hue: '#84BFE5' },
			{ saturation: 37 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'landscape.man_made',
		elementType: 'all',
		stylers: [
			{ hue: '#FFFFFF' },
			{ saturation: -100 },
			{ lightness: 100 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.highway',
		elementType: 'all',
		stylers: [
			{ hue: '#FFC92F' },
			{ saturation: 100 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.arterial',
		elementType: 'all',
		stylers: [
			{ hue: '#FFE18C' },
			{ saturation: 100 },
			{ lightness: 2 },
			{ visibility: 'on' }
		]
	}
];

siloLat = <?=$silo->latitude?>;
siloLong = <?=$silo->longitude?>;

var siloLocation = new google.maps.LatLng(siloLat, siloLong);
var options = {
	mapTypeControlOptions: {
		mapTypeIds: [ 'Styled']
	},
	center: siloLocation,
	zoom: 8,
	maxZoom: 13,
	mapTypeId: 'Styled'
};

var div = document.getElementById('map_canvas');
var map = new google.maps.Map(div, options);
var styledMapType = new google.maps.StyledMapType(styles, { name: 'Silo Map' });
map.mapTypes.set('Styled', styledMapType);

    var marker, i;

    for (i = 0; i < locations.length; i++) {  
            	marker = new google.maps.Marker({
            	position: new google.maps.LatLng(locations[i][1], locations[i][2]),
            	map: map,
		animation: google.maps.Animation.DROP
            });
}

infoWindow = new google.maps.InfoWindow();
    infoWindow.setOptions({
        content: "<div align='center'><img src='uploads/silos/<?=$silo->photo_file?>' width=100px id='current_item_photo'/></div>",
        position: siloLocation,
    });

infoWindow.open(map);
}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<div style="padding-bottom: 60px;"></div>