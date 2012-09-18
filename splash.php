<div id="logo">
	<a href="index.php" style="text-decoration:none"><img src="images/logo.png"/></a>			
</div>
<div align="right" style="margin-top: -60px; margin-right: 10px; font-size: 12px; line-height: 25px;">
	<a href="index.php?search=item" class="bold_text" style="text-decoration:none">Proceed to Main Site...</a>
	<br/>
	<a href="http://www.youtube.com/watch?v=4_M7hFK2ScE" target="_blank" class="bold_text" style="text-decoration:none">Watch a video on siloz</a>
</div>
<p style="margin-left: 10px; color: #2F8ECB; font-size: 18px; font-weight: bold;">siloz allow you to organize an 'online rummage sale' to raise money for anything, public or private.</p>
<table>
	<tr>
		<td width="10px"></td>
		<td valign="top">
			<img src="images/dreamtime.jpg"/>
		</td>
		<td width="20px"></td>
		<td valign="top">
			<p style="margin:0px; color: #2F8ECB; font-size: 18px; font-weight: bold;">Popular Community silo Types</p>
			<p style="margin:5px 0px; color: #2F8ECB; font-size: 16px;">church, school, youth sports team, playground/park, more...</p>
			<img src="images/row1.jpg"/>
			<p style="margin:12px 0px 0px 0px; color: #2F8ECB; font-size: 18px; font-weight: bold;">Popular Personal silo Types</p>
			<p style="margin:5px 0px; color: #2F8ECB; font-size: 16px;">wedding, funeral, relocation funds, baby shower, more...</p>
			<img src="images/row2.jpg"/>
		</td>
	</tr>
</table>
<br/>
<table width="100%">
	<tr>
		<td width="33%" align=center>
			<a href="index.php?search=item"><img src="images/shop_items.png"/></a>
		</td>
		<td width="33%" align=center>
			<a href="index.php?search=silo"><img src="images/donate_silos.png"/></a>	
		</td>
		<td width="33%" align=center>
			<a href="index.php?search=item"><img src="images/start_silo.png"/></a>	
		</td>	
	</tr>
</table>
<br/>
<div style="margin-left: 10px;">
	<script>
	function highlight_silo(id) {			
		document.getElementById("silo_"+id).style.background = "#fff";			
	}
	function unhighlight_silo(id) {			
		document.getElementById("silo_"+id).style.background = "#E0EFF9";			
	}
	function highlight_item(id) {			
		document.getElementById("item_"+id).style.background = "#fff";			
	}
	function unhighlight_item(id) {			
		document.getElementById("item_"+id).style.background = "#E0EFF9";			
	}
	</script>
	
<p style="color: #2F8ECB; font-size: 18px; font-weight: bold;">Popular silos near <span style="color: #f60;">Oakland, California</span> <a href="index.php?search=silo" class="bold_text">view more</a></p>
<?php
$sql = "SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE 1 > 0 ORDER BY silo_id DESC LIMIT 6";
$tmp = mysql_query($sql);		

$siloz_html = "<table cellpadding='5px' style='border-spacing: 0px'><tr>";
while ($s = mysql_fetch_array($tmp)) {	
	$silo = new Silo($s['id']);
	$siloz_html .= "<td>";				
	$siloz_html .= $silo->getPlate();
	$siloz_html .= "</td>";
}
$siloz_html .= "</tr></table>";
echo $siloz_html;
?>

<p style="color: #2F8ECB; font-size: 18px; font-weight: bold;">Items for Sale near <span style="color: #f60;">Oakland, California</span> <a href="index.php?search=item" class="bold_text">view more</a></p>
<?php
$sql = "SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE deleted_date = 0 ORDER BY id DESC LIMIT 6";
$tmp = mysql_query($sql);
$items_html = "<table cellpadding='5px' style='border-spacing: 0px'><tr>";
while ($item = mysql_fetch_array($tmp)) {
	$it = new Item($item['id']);
	$items[] = $it;	
	$items_html .= "<td>".$it->getPlate()."</td>";				
}	
$items_html .= "</tr></table>";
echo $items_html;
?>
</div>