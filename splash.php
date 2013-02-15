<div class="row">
	<div id="logo_container">
		<a href="index.php" style="text-decoration:none"><img src="images/logo_main.png"/></a>			
	</div>
	<div id="logged_out_supernav">
		<span class="gray">Your location:</span> 
		<span>Oakland, CA</span>
		<span class="blue separator">|</span>
		<span class="blue">login/create account</span>
		<span class="blue separator">|</span>
		<span class="blue">start a silo</span>
	</div>
</div>

<div class="row">
	<h2 class="tagline">siloz is a marketplace for items donated to raise money for causes (silos) in your community</h2>
</div>

<div class="row">
	<div id="slider_frame">
		<div id="slider">
			<img src="images/splash/civic.jpg" alt="Civic" />
			<img src="images/splash/education.jpg" alt="Education" />
			<img src="images/splash/neighborhood.jpg" alt="Neighborhood" />
			<img src="images/splash/non_profits.jpg" alt="Non-profit Organizations" />
			<img src="images/splash/public_university.jpg" alt="Public University" />
			<img src="images/splash/religious.jpg" alt="Religious" />
			<img src="images/splash/youth_sports.jpg" alt="Local Youth Sports" />
		</div>
	</div>
</div>

<div id="action_call" class="row">
	<!-- shop button -->
	<div class="row">
		<div class="action shop" onClick="window.location = 'index.php?search=item'"></div>
		<p class="action_text">pay online and pick up items locally, with <b>PayKey</b></p>
	</div>

	<!-- donate button -->
	<div class="row">
		<div class="action donate" onClick="window.location = 'index.php?search=silo'"></div>
		<p class="action_text">many donated items are <b>tax deductible</b></p>
	</div>
	
	<!-- start silo button -->
	<div class="row">
		<div class="action startsilo" onClick="window.location = 'index.php?search=item'"></div>
		<div class="action donate_vehicle"></div>
	</div>
</div>

<div>
	<div style="margin-left: 10px;">
		<script>
		    // Can be CSSED
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
		
	<p class="silos_header">Popular silos near <span id="enterLocation_silo" style="display: none;"></span> <span id="userLocation_silo" <?php if (!$_SESSION['is_logged_in']) echo  'onclick="changeLocation_silo()"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(click to change)</font>' ?></span> <a href="index.php?search=silo" class="bold_text">view more</a></p>
	
	<?php
	$sql = "SELECT *, $sqlDist AS distance FROM silos ORDER BY distance LIMIT 5";
	$tmp = mysql_query($sql);

	$siloz_html = "<div class='row'><div class='span12'>";
	
	$num_siloz = 0;
	
	while ($s = mysql_fetch_array($tmp)) {
		$silo = new Silo($s['id']);		
		
		if ($num_siloz % 5 == 0) {
			$siloz_html .= "<div class='row item_row'>";
		}
		
		$siloz_html .= $silo->getSiloPlate($num_siloz % 5 == 0);
		
		if ($num_siloz % 5 == 4) {
			$siloz_html .= "</div>";
		}
		
		$num_siloz++;
	}
	
	if ($num_siloz % 5 < 4) {
		$siloz_html .= "</div>";
	}
	
	$siloz_html .= "</div></div>";
	echo $siloz_html;
	?>

	<p class="silos_header">Items for Sale near <span id="enterLocation_item" style="display: none;"></span> <span id="userLocation_item" <?php if (!$_SESSION['is_logged_in']) echo  'onclick="changeLocation_item()"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(click to change)</font>' ?></span> <a href="index.php?search=item" class="bold_text">view more</a></p>
	
	<?php
	$sql = "SELECT *, $sqlDist AS distance FROM items WHERE deleted_date = 0 ORDER BY distance LIMIT 6";
	$tmp = mysql_query($sql);
	$items_html = "<div class='row'><div class='span12'>";
	
	$num_items = 0;
	
	while ($item = mysql_fetch_array($tmp)) {
		$it = new Item($item['id']);
		
		if ($num_items % 6 == 0) {
			$items_html .= "<div class='row item_row'>";
		}
		
		$items[] = $it;	
		$items_html .= $it->getItemPlate($num_items % 6 == 0);		

		if ($num_items % 6 == 5) {
			$items_html .= "</div>";
		}		
		
		$num_items++;
	}
	
	if ($num_items % 6 < 5) {
		$items_html .= "</div";
	}
	
	$items_html .= "</div></div>";
	
	echo $items_html;
	?>
	</div>
	</div>

	<div id="quick_start_bg">

	<table width="100%" class="quick_start">
		<tr>
		<td valign="top" width="47%">
			<h2>Who can start a silo?</h2>
			<p>siloz is for community - and never private - fundraisers.  To administrate a silo, you must officially represent any of the following: a school (faculty or staff, or a student funding a school cause), a religious organization, a youth sports team or league, a civic or neighborhood organization, or a non-profit that both a) has a physical office where the silo is run, and b) that has some outreach in that same area.  You cannot spend any money raised on personal expenses, and must disclose a public address and telephone number.  Additionally, members will be asked to certify (vouch for) your standing.  We have additional basic security measures.</p>
			<h2>How Does it Work?</h2>
			<p>Administrators are equipped with on-line (Facebook Connect, email address book), and off-line (ability to print sign-up sheets and business cards, which can then be printed on perforated, card-stock paper, and torn, to make business cards) to get your fundraiser started.  It will last for 1 to 3 weeks.  Your supporters, and the general public, can donate items that sell on the site.  They may also shop for them.  At the end of the silo, we pay you either through PayPal, or through an electronic check.  After some time (up to 60 days), we ask silo administrators to upload photos showing how raised money was spent, at some point (up to 60 days).</p>
		</td>
		<td width="6%"></td>
		<td valign="top" width="47%">
			<h2>How are items bought and sold?</h2>
			<p>A silo's supporters donate items.  The public shops for those items, and makes payment online.  Nothing is shipped on the site.  Items are picked up locally.  You are not permitted to shop for items in a region too distant from your home address.  At the end of the silo, the silo administrator is paid (see above).</p>
			<h2>Are Donated Items Tax-Deductible?</h2>
			<p>We can verify the 501(c)3 status of any silo.  Donations to non-profits, churches and schools are always tax-deductible.  Neighborhood and civic organizations and youth sports programs may be able to offer tax-deductions.  Tax-deductible silos are labeled.</p>
			<h2>Is siloz Safe?</h2>
			<p>We provide 'vouching' score information, Facebook Connect information, contact information, and complete transparency of a silo, in the interest of keeping reducing fraud.  When making purchases, try to meet in public with your item.  Never agree to ship an item.  siloz is not liable for crimes incidental to use, but will cooperate with law enforcement, where possible, when laws have been broken.  For more information, see our Terms of Use and FAQ.</p>
		</td>
		</tr>
	</table>

	<div id="bottom_menu">
		<a href="index.php?task=contact_us" class="normal_text">Contact Us</a> | <a href="index.php?task=about_us" class="normal_text">About Us</a> | <a href="index.php?task=tos" class="normal_text">Terms of Use</a> | <a href="index.php?task=faq" class="normal_text">FAQ</a> | <a href="index.php?task=getting_started" class="normal_text">Getting Started</a>
	</div>
	</div>
	<br>

	<script>
	function changeLocation_silo()
	{
	  var str = '<form action="" method="POST"><input onclick=this.value=""; type="text" value="Enter Zip Code" name="zip"> <button type="submit" name="location" value="Update">Update</button></form>';
	  $('#enterLocation_silo').append( str );
	  userLocation_silo.style.display = 'none';
	  enterLocation_silo.style.display = 'inline-block';

	}

	function changeLocation_item()
	{
	  var str = '<form action="" method="POST"><input onclick=this.value=""; type="text" value="Enter Zip Code" name="zip"> <button type="submit" name="location" value="Update">Update</button></form>';
	  $('#enterLocation_item').append( str );
	  userLocation_item.style.display = 'none';
	  enterLocation_item.style.display = 'inline-block';

	}
	</script>
</div>