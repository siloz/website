<?php
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
		
?>

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
	
<div class="heading">
	<table width="100%">
		<tr>
			<td>
				<?php echo $silo->getTitle(); ?>
			</td>
			<td align="right">
				<span style="font-size: 11px">Pledges and donations to this silo are tax-deductible! <a href="#" style="color: #fff;"><i>more...</i></a></span>
			</td>
	</table>
</div>

<table width="100%">
	<tr>
		<td>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'home' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=home&id=<?php echo $silo->id;?>'">Home</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'feed' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=feed&id=<?php echo $silo->id;?>'">Feed</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'members' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>'">Members</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'items' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>'">View Items</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'donations' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=donations&id=<?php echo $silo->id;?>'">Donations</button>
			<button type="button" style="font-size: 12px; <?php echo ($view == 'map' ? 'background-color: #f60' : ''); ?>" onclick="window.location='index.php?task=view_silo&view=map&id=<?php echo $silo->id;?>'">Map</button>
		</td>
		<td valign="center" align="right">
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
				if ($view == 'items') {
					echo "<b>sort by <a href=index.php?task=view_silo&view=items&sort_by=price$new_sort_order&id=".$silo->id." class=simplebluelink>price <img src=$img1_path></a> or <a href=index.php?task=view_silo&view=items&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>list date <img src=$img2_path></a></b>";
				}
				else if ($view == 'members') {
					echo "<b>sort by <a href=index.php?task=view_silo&view=members&sort_by=name$new_sort_order&id=".$silo->id." class=simplebluelink>username <img src=$img1_path></a> or <a href=index.php?task=view_silo&view=members&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>join date <img src=$img2_path></a></b>";
				}
				else if ($view == 'donations') {
					echo "<b>sort by <a href=index.php?task=view_silo&view=donations&sort_by=amount$new_sort_order&id=".$silo->id." class=simplebluelink>amount <img src=$img1_path></a> or <a href=index.php?task=view_silo&view=donations&sort_by=date$new_sort_order&id=$silo_id class=simplebluelink>date <img src=$img2_path></a></b>";
				}
			?>
		</td>
	</tr>
</table>


<hr/>				
	<?php
		if ($view == 'home') {
?>
<table cellpadding="5px">
	<tr>
		<td valign="top" width="250px">
			<img src=<?php echo 'uploads/silos/300px/'.$silo->photo_file;?> width="250px"/>
			<br/><br/>
			<table>
				<tr>
					<td valign="top"><b>ID: </b></td><td valign="top"><b><?php echo $silo->id; ?></b></td>
				</tr>				
				<tr>
					<td><b>Date Range: </b></td><td><?php echo $silo->start_date;?> <b>to</b> <?php echo $silo->end_date;?></td>
				</tr>
				<tr>
					<td><b>Goal: </b></td><td><?php echo money_format('%(#10n', floatval($silo->goal));?></td>
				</tr>
				<tr>
					<td><b>Pledged: </b></td>
					<td>
						<?php
							echo money_format('%(#10n', $silo->getPledgedAmount());
						?>
					</td>
				</tr>
				<tr>
					<td valign="top"><b>Collected: </b></td>
					<td>
						<?php
							$collected = $silo->getCollectedAmount();
							$pct = round($collected*100.0/floatval($silo->goal),1);															
							echo money_format('%(#10n', $collected)." <br/>(reached $pct %)";
						?>								
					</td>
				</tr>
				<tr>
					<td><b>Progress: </b></td>
					<td>
						<?php
							echo "<div style='width: 160px; height: 12px; border: 1px solid #2F8ECB;'><div style='width: $pct%; height:12px; background: #2F8ECB;'></div></div>"
						?>
					</td>
				</tr>
				<tr>
					<td><b>Deadline: </b></td>
					<td>
						<?php
							$pct_day = 100*min(1,max(0,(time() - strtotime($silo->start_date))/(strtotime($silo->end_date) - strtotime($silo->start_date))));
						
							echo "<div style='width: 160px; height: 12px; border: 1px solid #2F8ECB;'><div style='width: $pct_day%; height:12px; background: #2F8ECB;'></div></div>"
						?>
					</td>
				</tr>				
			</table>
		</td>
		
		<td valign="top" width="160px" align="center">			
			<img src=<?php echo 'uploads/members/300px/'.$admin->photo_file;?> width="160px"/><br/><br/>
			<b>Silo Admin: </b><?php echo $admin->fullname; ?><br/><br/>
			<b>Title: </b><?php echo $silo->title; ?><br/><br/>
			<b>Organization Name: </b><br/><?php echo $silo->name; ?><br/><br/>		
			<button type="button" onclick="popup_show('contact_admin', 'contact_admin_drag', 'contact_admin_exit', 'screen-center', 0, 0);" style="font-size: 12px;">Contact Admin</button>						
					
		</td>
		
		
		<td valign="top" width="320px" align="justify">
			<b>Silo Type: </b> <?php echo $silo->subtype." (".$silo->subsubtype.")"; ?>
			<br/><br/>
			Has <a href="index.php?task=view_silo&view=members&id=<?php echo $silo->id;?>"><?php echo $silo->getTotalMembers();?> Members</a>, <a href="index.php?task=view_silo&view=items&id=<?php echo $silo->id;?>"><?php echo $silo->getTotalItems();?> Items Pledged</a><br/><br/>
			<b>Purpose: </b> <?php echo $silo->getPurpose();?>
			<br/>
			<br/>
			<b>Organization Description</b>: 
				<?php 
					echo $silo->getDescription();
				?>
			<br/><br/>
			<b>Official Address:</b> <?php echo $silo->address; ?>
			<br/><br/>
			<b>Telephone Number:</b> <?php echo $silo->phone_number; ?>
			<br/>
		</td>
		
		<td valign="top" width="200px" align="justify">
		<?php
			if ($_SESSION['is_logged_in']) {			
		?>
			<button type="submit" style="font-size: 12px;" onclick="window.open('index.php?task=sell_on_siloz<?php echo "&id=".$silo->id;?>', '_parent');" id="sell_on_siloz">Pledge/Sell for this Silo</button>						
		<?php
			}
			else {
		?>
		<button type="submit" style="font-size: 11px; width:200px;" onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);" id="sell_on_siloz">Pledge/Sell for this Silo</button>						
		<br/>
		<br/>
		<button type="submit" style="font-size: 11px; width:200px;" onclick="popup_show('login', 'login_drag', 'login_exit', 'screen-center', 0, 0);" id="sell_on_siloz">Donate to this Silo</button>								
		<?php
			}
		?>
		<div id='fb-root'></div>
		<script src='http://connect.facebook.net/en_US/all.js'></script>
		<p><img src="images/facebook.jpg" onclick='postToFeed();'/></p>
		<?php
			$url = ACTIVE_URL."/index.php?task=view_silo&id=$silo->id";
			$photo_url = ACTIVE_URL.'/uploads/silos/300px/'.$silo->photo_file;
			$name = $silo->name;
			$description = substr($silo->description, 0, 200)."...";
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
		<div style="word-wrap: break-word; width: 200px;"><?php echo preg_replace("/\n/","<br>",html_entity_decode($silo->admin_notice))." ";?></div>
		
		</td>
	</tr>
</table>

<?php
		}
		if ($view == 'feed') {
			//NEW MEMBER
			$r = mysql_fetch_array(mysql_query("SELECT user_id, joined_date FROM silo_membership WHERE silo_id = $silo_id ORDER BY joined_date DESC LIMIT 1"));			
			$s = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$r['user_id']));			
			$t = mysql_fetch_array(mysql_query("SELECT COUNT(DISTINCT user_id) FROM silo_membership WHERE silo_id = $silo_id"));			
			if ($r != null) {
				$new_mem_photo = "<img src='uploads/members/100px/".$s['photo_file']."' width='100px'>";
				$new_mem_info = $s['fullname']." has joined this Silo. Thanks ".$s['fullname']." for joining! This Silo now has ".$t[0]." members.";
				$date = date('M d, Y', strtotime($r['joined_date']));
				$time = date('h:i A T', strtotime($r['joined_date']));
			}
			else {
				$new_mem_photo = "";
				$new_mem_info = "<font size=2>No member has joined yet</font>";
				$date = date('M d, Y');
				$time = date('h:i A T');
			}
			$new_mem_date = "$date<br/>$time<br/>";
			echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>New Member</b></font></td><td width=120px valign=top>$new_mem_photo</td><td width=120px valign=top></td><td width=460px valign=top>$new_mem_info</td><td width=120px valign=top align=right>$new_mem_date</td></tr></table></div><br/>";
			
			
			//FUNDS COLLECTED
			$admin_photo = "<img src='uploads/members/100px/".$admin->photo_file."' width='100px'/>";
			$tmp = mysql_query("SELECT user_id, SUM(price) as total_received FROM items WHERE silo_id = $silo_id AND status = 'Funds Received' GROUP BY user_id ORDER BY added_date DESC LIMIT 3");
			$n = 0;
			$funds_info = "";
			while ($r = mysql_fetch_array($tmp)) {
				$s = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = ".$r['user_id']));
				$funds_info .= $s['fullname']." has sent ".money_format('%(#10n', floatval($r['total_received'])).", ";				
			}
			if ($funds_info == "")
				$funds_info .= "No fund has been collected yet.";
			else
				$funds_info .= "... bringing the <b>Silo total to ".money_format('%(#10n', floatval($s2[0]))." collected</b>. Thanks everyone!";
			$date = date('M d, Y');
			$time = date('h:i A T');
			$funds_date = "$date<br/>$time<br/>";			
			echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>Funds Collected</b></font></td><td width=120px valign=top>$admin_photo</td><td width=120px valign=top></td><td width=460px valign=top>$funds_info</td><td width=120px valign=top align=right>$funds_date</td></tr></table></div><br/>";

			$milestone_info = "No milestone has been reached!";
			$pct_round = "";
			if ($pct >= 10) {
				$pct_round = (round($pct/10)*10)."%";
				$milestone_info = "<b>Silo has reached $pct_round of its Goal!</b><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum";
			}
			echo "<div class=nicebox><table width=940px><tr><td width=120px valign=top><font size=3><b>Milestone Reached</b></font></td><td width=120px valign=top>$admin_photo</td><td width=120px valign=center align=center><font size=6><b>$pct_round</b></font></td><td width=460px valign=top>$milestone_info</td><td width=120px valign=top align=right>$funds_date</td></tr></table></div>";
			
		}
		
		
		//VIEW MEMBERS
		if ($view == 'members') {
			$users = User::getMembers($silo_id, $order_by);
			echo "<table cellpadding='3px'>";
			$n = 0;
			foreach ($users as $user) {
				if ($n == 0)
					echo "<tr>";
				echo $user->getMemberCell($silo_id);					
				$n++;
				if ($n == 6) {
					echo "</tr>";
					$n = 0;
				}					
			}
			echo "</table>";
		}
		
		//VIEW ITEMS
		if ($view == 'items') {
			$items = Item::getItems($silo_id, $order_by);
			$n = 0;
			echo "<table cellpadding='3px'>";			
			foreach ($items as $item) {
				if ($n == 0)
					echo "<tr>";							
				echo $item->getItemCell($silo_id);					
				$n++;
				if ($n == 6) {
					echo "</tr>";
					$n = 0;
				}					
			}
			echo "</table>";		
		}
		
		//VIEW DONATIONS
		if ($view == 'donations') {
			$order_by_clause = "";
			if ($order_by == 'amount')
				$order_by_clause = " ORDER BY amount $sort_order ";
			if ($order_by == 'date')
				$order_by_clause = " ORDER BY sent_date $sort_order ";						
			$sql = "SELECT * FROM donations INNER JOIN users USING (user_id) WHERE deleted_date = 0 AND silo_id = $silo_id $order_by_clause";
			$donations = mysql_query($sql);
			$n = 0;
			echo "<table cellpadding='3px'>";			
			while ($don = mysql_fetch_array($donations)) {
				if ($n == 0)
					echo "<tr>";
				$donation_id = $don['donation_id'];
				$user = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id=".$don['user_id']));
				$cell = "<td><div class=plate id='item_".$don['donation_id']."' style='color: #000;'>";
				$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><b>Donation Plate Title</b></div><img height=100px width=135px src=uploads/members/100px/".$user['photo_file']." style='margin-bottom: 3px'><div style='color: #000;line-height: 120%;'><b>Status: </b>".$don['status']."<br/><b>Member: </b><a href='index.php?task=view_user&id=".$user['user_id']."'>".$user['username']."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$don['amount']."</b></span></td><td align=right><i><b>more...</b></i></td></tr></table></div></td>";							
				echo $cell;					
				$n++;
				if ($n == 6) {
					echo "</tr>";
					$n = 0;
				}					
			}
			echo "</table>";		
		}
	?>
<?php
	}
?>
