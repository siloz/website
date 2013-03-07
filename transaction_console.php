<div class="spacer"></div>

<div class="userNav">
	<table width="940px" style="border-spacing: 0px;">
		<tr><form action="">
			<td>
				<span class="accountHeading">User Account</span>
			</td>
			<td align="center" width="400px"></td>
			<td align="center">
				<a href="index.php?task=transaction_console" class="blue" style="float: left"><input type="radio" CHECKED>Transaction Console</input></a>
			</td>				
			<td align="center">
				<a href="index.php?task=my_listings" class="blue" style="float: left"><input type="radio">My Listings</input></a>
			</td>
			<td align="center">
				<a href="index.php?task=my_account" class="blue" style="float: left"><input type="radio">Account Settings</input></a>
			</td>
		</form></tr>
	</table>
</div>

<div class="spacer"></div>

<table width="100%">
<tr><td>
	<span class="accountHeading">Selling</span>
</td>
<td width="20px"></td>
<td>
	<span class="accountHeading">Buying</span>
</td></tr>

<tr>
<td>

<div class="plateTConsoleSell">
<table border=1 width="100%" height="100%">
<tr valign=top>
	<td valign="top" rowspan="2">
			<?php
			$limit = "LIMIT $start_from, $itemsPerPage";
			$items = Item::getSoldItems(80, $order_by, $limit);
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
			?>
	</td>
	<td>
		Status: <br>
		Notifications: <br>
		Other Party Contact: <br>
	</td>
</tr>
<tr>
	<td valign="top">
		Actions: <br>
		Edit Item | Delete Item
	</td>
</tr>
</table>
</div>

</td>
<td width="20px"></td>
<td>

<div class="plateTConsoleBuy">
<table width="100%" height="100%">
<tr valign=top>
	<td valign=top colspan=2>
	test
	</td>
</tr>
</table>
</div>

</td>
</tr>
</table>

<br>