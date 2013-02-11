<div class="heading" style="padding-bottom:5px;">
	<table width="940px" style="border-spacing: 0px;">
		<tr>
			<td width="700px">
				<b>Your Account</b><?php echo " (".$_SESSION['username'].")"?>
			</td>
			<td align="center">
				<a href="index.php?task=my_transactions" style="font-size: 12px; font-weight: bold; background: transparent; border: 0px; color: #fff">My Transactions</a>
			</td>	
			<td align="center">
				<span style="color: #fff">|</span>
			</td>					
			<td align="center">
				<a href="index.php?task=my_listings" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">My Listings</a>
			</td>
			<td align="center">
				<span style="color: #fff">|</span>
			</td>
			<td align="center">
				<a href="index.php?task=my_account" style="font-size: 12px; text-decoration: none; font-weight: bold; background: transparent; border: 0px; color: #fff">Home</a>
		</tr>
	</table>
</div>
<br/>
	
	<hr/>
	<font size="4"><b>View Account Transactions</b></font>
	<hr/>

<br>

<table id='alternate_table'>
<tr>
	<th width="10%">Transaction</th>
	<th width="10%">Price</th>
	<th width="10%">Date</th>
</tr>
<tr>
	<td>Sold Item - Signed Football</td>
	<td>$1200</td>
	<td>2/14/13</td>
</tr>
<tr>
	<td>Pledged Item - Galaxy Note II</td>
	<td>$300</td>
	<td>2/10/13</td>
</tr>
<tr>
	<td>Bought Item - Super Nintendo</td>
	<td>$250</td>
	<td>2/05/13</td>
</tr>
</table>

<br>