<?php
	$silosPerRow = "2";
?>

<div class="spacer"></div>

<div class="userNav">
	<table width="100%" style="border-spacing: 0px;">
		<tr>
			<td>
				<span class="accountHeading">Favorite Silos</span>
			</td>
		</tr>
	</table>
</div>

<div class="spacer"></div>

<span class="greyFont">

<table width="100%">
<tr><td style="padding: 5px;">
	<span class="accountHeading orange">Silos pledged to</span>
</td>
<td width="2%"></td>
<td style="padding: 5px;">
	<span class="accountHeading orange">Private silos invited to</span>
</td></tr>

<tr>
<td width="49%" valign="top">

<?php
$qry = mysql_query("SELECT * FROM silo_membership WHERE user_id = '$user_id' ORDER BY joined_date ASC");

	$siloz_html = "<div class='row_search'><div class='span12'><table>";
	$i=0;

	while ($s = mysql_fetch_array($qry)) {	
		$i++;
		$silo = new Silo($s['silo_id']);
		if ($i % $silosPerRow == 1 && $i > 1) {
			$siloz_html .= "</tr><tr>";
		}
		else if ($i % $silosPerRow == 1) {
			$siloz_html .= "<tr>";
		}
		$siloz_html .= "<td>".$silo->getSiloPlate()."</td>";
	}
	$siloz_html .= "</tr></table></div></div>";

	echo $siloz_html;

	if (!mysql_num_rows($qry)) { echo "You have not pledged any items towards a silo yet. Start donating now!"; }
?>

</td>

<td width="2%"></td>

<td width="49%" valign="top">

<?php
$qry = mysql_query("SELECT * FROM silo_private WHERE user_id = '$user_id' ORDER BY date ASC");

	$siloz_html = "<div class='row_search'><div class='span12'><table>";
	$i=0;

	while ($s = mysql_fetch_array($qry)) {	
		$i++;
		$silo = new Silo($s['silo_id']);
		if ($i % $silosPerRow == 1 && $i > 1) {
			$siloz_html .= "</tr><tr>";
		}
		else if ($i % $silosPerRow == 1) {
			$siloz_html .= "<tr>";
		}
		$siloz_html .= "<td>".$silo->getSiloPlate()."</td>";
	}
	$siloz_html .= "</tr></table></div></div>";

	echo $siloz_html;

	if (!mysql_num_rows($qry)) { echo "You haven't been invited to any private silos yet."; }
?>

</td>

</tr>
</table>
</div>

</td>
</tr>
</table>

</span>

<br>

<script>
      $(document).ready( function() {
        $('#notification').delay(1000).fadeOut();
      });
</script>