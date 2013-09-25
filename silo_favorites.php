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
$qry = mysql_query("SELECT * FROM silo_membership AS m LEFT JOIN silos AS s ON (m.silo_id = s.silo_id) WHERE m.user_id = '$user_id' AND s.status = 'active' ORDER BY m.joined_date ASC");

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

	if (!mysql_num_rows($qry)) { echo "You have not pledged any items towards a silo yet. To pledge an item to a silo, you have two options: <br> <br>
					1) You can find a silo that interests you and click on the 'donate' button on that silo's page <br><br> or <br><br> 
					2) You can pledge an item to a silo that has not been created yet by clicking <a class='blue' href='index.php?task=pledge_first'>here</a>. You must know someone who would be willing to start and manage a silo."; }
?>

</td>

<td width="2%"></td>

<td width="49%" valign="top">

<?php
$qry = mysql_query("SELECT * FROM silo_private AS p LEFT JOIN silos AS s ON (p.silo_id = s.silo_id) WHERE p.user_id = '$user_id' AND s.status = 'active' ORDER BY p.date ASC");

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