<?php

			$siloReport = "<table width='100%'>";
			$siloReport .= "<tr><td><b>Name</b></td><td><b>Number of items pledged</b></td><td><b>Total pledged amount</b></td><td><b>Total raised</b></td></tr>";
			$siloReport .= "<tr><td>Robert Arone</td><td>5</td><td>$1200.00</td><td>$550.50</td></tr>";
			$siloReport .= "</table>";
			$siloReport .= "<br>Total funds raised for this silo: <b>$24,960</b><br><br>Great work!";
echo $siloReport;

//$notif = new Notification();
//echo $notif->SiloPaid(80);

?>