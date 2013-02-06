<?php
$long = -117.1572551;
$lat = 32.7153292;

$sqlDist = " ( 3959 * acos( cos( radians($long) ) * cos( radians( silos.latitude ) ) * cos( radians( silos.longitude ) - radians($lat) ) + sin( radians($long) ) * sin( radians( silos.latitude ) ) ) ) ";

$sql = "SELECT *, $sqlDist AS distance 
FROM silos HAVING distance < 90 ORDER BY distance";

$tmp = mysql_query($sql);

$siloz_html = "<table cellpadding='5px' style='border-spacing: 7px'><tr>";
while ($s = mysql_fetch_array($tmp)) {	
	$silo = new Silo($s['id']);
	$siloz_html .= "<td>";				
	$siloz_html .= $silo->getPlate();
	$siloz_html .= "</td>";
}
$siloz_html .= "</tr></table>";
echo $siloz_html;
?>