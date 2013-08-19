<div id="billboard-container">
	<div id="billboard">
		<?php
			include('header.php');
			include_once("include/GoogleAnalytics.php");

			if (param_get('ref') == "start") {
		?>

		<script>
		$(document).ready(function (){
			$("#learn-more").remove();
			$("#quick-start").toggle("slow");
     			$('html, body').animate({
           			scrollTop: $("#quick-start").offset().top
     			}, 1000);
		});
		</script>

		<?php } ?>

		<h2 class="tagline">Fund a local cause<span>by purchasing or donating a wanted item.</span></h2>
	</div>
</div>
<div id="billboard-boxes-container">
	<div id="billboard-boxes">
		<div id="shop-box" class="floatL mast-item" onclick="window.location='items'">
			<h1>Shop local items</h1>
			<span>buy items whose sale helps a silo</span>
		</div>
		<div id="donate-box" class="floatL mast-item" onclick="window.location='silos'">
				<h1>Donate an item</h1>
				<span>sell items to benefit a public silo</span>
		</div>
		<a class='fancybox' href='<?php if (!$user_id) { echo "#login"; } else { echo "index.php?task=create_silo"; } ?>'>
			<div id="create-box" class="floatL mast-item">
				<h1>Create a silo</h1>
				<span>public silos are often tax deductible</span>
			</div>
		</a>
		<a class='fancybox' href='<?php if (!$user_id) { echo "#login"; } else { echo "index.php?task=pledge_first"; } ?>'>
			<div id="pledge-box" class="floatL mast-item">
				<h1>Pledge first</h1>
				<span>we'll notify your cause to start a silo</span>
			</div>
		</a>
	</div>
</div>
</div>
</div>

<div id="mast-container" class="clear">
	<div class="row clear">
			<p class="silos_header">Popular Silos Near <span <?php if (!$_SESSION['is_logged_in']) echo 'class="s_change_location"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(change)</font>' ?></span>
			<a href="silos" class="bold_text">view more</a></p>
		
			<?php
				$sql = "SELECT *, $sqlDist AS distance FROM silos WHERE status = 'active' AND silo_type = 'public' ORDER BY distance LIMIT 5";
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
				
				//if ($num_siloz % 5 < 4) {
					//$siloz_html .= "</div>";
				//}
				
				$siloz_html .= "</div></div>";
				echo $siloz_html;
			?>
	</div>

	<div class="row">
		<p class="silos_header">Items for Sale Near <span <?php if (!$_SESSION['is_logged_in']) echo  'class = "s_change_location"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(change)</font>' ?></span> <a href="items" class="bold_text">view more</a></p>

		<?php
			$sql = "SELECT *, $sqlDist AS distance FROM items WHERE status = 'pledged' or status = 'offer' ORDER BY distance LIMIT 6";
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

			//if ($num_items % 6 < 5) {
			//	$items_html .= "</div>";
			//}
			
			$items_html .= "</div></div>";

			if ($num_items == 0) { $items_html = "<div class='greyFont' style='line-height: 155px; text-align: center'>There are currently no items being pledged in your area</div>"; }
			
			echo $items_html;
		?>
	</div>
</div>

<div id="getting-started-collapsible">
	<h1>Getting Started</h1>
</div>
<div id="getting-started">
	lorem ipsum
</div>

<div id="faq-collapsible">
	<h1>FAQ</h1>
</div>
<div id="faq">
	lorem ipsum
</div>

<!-- <div id="footer_container" class="row">
	<div id="quick_start_bg" class="quick_start">
		<h1 align="center" class="click_me" id="learn-more">Click Here to Learn More</h1>
	<div style="display: none" id="quick-start">
		<h2>What is <?=SITE_NAME?>?</h2>
			<p><?=SITE_NAME?> is a marketplace for items donated to help local causes.  A 'silo' is a virtual rummage sale that benefits a local cause, which can be private or public.</p>
		<h2>What Are The Benefits of an Online?</h2>
			<p>Rummage sales are challenging to organize. They take time and energy to move items. Often there is not sufficient traffic to sell items, or donated items are not exactly 'hot'; they're unwanted items. <?=SITE_NAME?> allows for a transparent, fun, safe fundraiser, for members of groups and organizations. Everybody can see what everybody else donated, which translates to less junk.</p>
			<p>We've provided promotional tools to everybody in your organization, to ensure your silo is a success. Best of all - you don't have to discard what doesn't sell, since people list items from their home!</p>
		<h2>How Does It Work?</h2>
			<table width="100%"><tr><td width="400px">
				<p>1) A silo administrator starts a silo, and uses Facebook, email, and off-line tools (1/4 page flyers) to promote it.</p>
				<p>2) Others join by pledging and selling items through the site. They can also promote a silo, getting more donations and buyers!</p>
				<p>3) After a silo ends (1, 2, or 3 weeks), we send the money raised (90% for public silos, 95% for private silos, paid via PayPal) to the silo administrator.</p>
				<p>After the silo administrator is paid, we invite the silo administrator send a 'Thank You' note.</p>
			</td><td align="center">
				<div style="margin-top: -15px; text-align: center">(click to enlarge)</div>
				<a class="fancybox" href="images/how-it-works.png" title="The chart, above, demonstrates how <?=SITE_NAME?> works"> <img src="images/how-it-works.png" width="421" height="300"></img></a>
			</td></tr></table>
		<h2>What Types of silos Are There?</h2>
			<p>There are two top-level silo types: <span class="orange"><b>private</b></span> and <span class="orange"><b>public</b></span>. While items donated to benefit any silo are always visible on the site, private silos themselves are unlisted. This means you must have been invited (provided a link) to ever see one. Public silos must belong to the following classes:</p>
			<table width="100%"><tr><td width="400px">
				<p>1) Religious Organization (church, synagogue, etc.), 2) Public University (student group, sorority/fraternity), 3) Registered Non-Profit (with a presence and outreach in a given region), 4) Civic Organization, 5) Neighborhood Organization (including an HOA), 6) Youth Sports Organization (adult organizations are considered private), 7) Public K-12 School (PTA, student group)</p>
				<p>A silo can only be begun by a person belonging to these groups, and enjoying a leadership position. We verify this.</p>
				<p><?=SITE_NAME?> has created a class of official silos, which we run. These are always authorized by the benefiting organization. Contact us for more information.  </p>
				<p>After the silo administrator is paid, we invite the silo administrator send a 'Thank You' note.</p>
			</td><td align="center">
				<div style="margin-top: -5px;">(click to enlarge)</div>
				<a class="fancybox" href="images/silo-types.png" title="Explanation of the difference between public and private silos"><img src="images/silo-types.png" width="490" height="300"></img></a>
			</td></tr></table>
			<p>Anybody over age 14 can create a silo, if it doesn't conflict with our mission statement of change through goodwill, inclusiveness, respect and legality.</p>
			<table width="100%"><tr>
				<td colspan="3"><p><i>Private silo Examples</i></p></td>
			</tr><tr>
				<td align="center"><p>Family Reunion<br>Unforeseen Household Bill<br>Wedding or Honeymoon<br>Mother's Day or Father's Day</p></td>
				<td align="center"><p>Music or Film Project<br>Youth Education or Training<br>An Anniversary<br>Down-Payment on a Car or Home</p></td>
				<td align="center"><p>Medical or Legal Emergency<br>College Loans or Tuition<br>Graduation or Prom<br>A Birthday or Holiday Gift</p></td>
			</tr></table>
			<p>Remember: money you collect from a private silo is income, and should be reported to the IRS.</p>
			<table width="100%"><tr>
				<td colspan="3"><p><i>Public silo Examples</i></p></td>
			</tr><tr>
				<td align="center"><p>Neighborhood: Festival or Party<br>Local Youth Sports: Uniforms or Attire<br>K-12 Public Education: Graduation<br>Religious: Charity or Outreach<br>Civic: Fireman's or Police Fund</p></td>
				<td align="center"><p>Neighborhood: Cleanup<br>Public University: Fraternity or Sorority<br>K-12 Public Education: Arts Programs<br>Public University: Scholarship<br>Local Youth Sports: Tournament Fees</p></td>
				<td align="center"><p>K-12 Public Education: Class Trip<br>Civic: Commissioned Art<br>Neighborhood: Park or Playground<br>Civic: Library<br>Religious: Guest Speaker</p></td>
			</tr></table>
		<h2>Are Donated Items Tax-Deductible?</h2>
			<p>Only public silos may potentially issue tax-deductible receipts, if they can verify the 501(c)3 when they launch, or they help a public school. Qualifying public silos are marked, on their page.</p>
		<h2>How Are Items Sold?</h2>
			<table width="100%"><tr><td width="400px">
				<p>We have a new way to buy/sell things online. It's safe and easy. We call it "Voucher/Key"</p>
				<p>Local items are paid for through the site, but picked up (or declined) in-person, using a Voucher, which acts like cash. Sellers have a Voucher Key that proves a buyer's Voucher is authentic, and the two parties have a week to transact a sale.</p>
				<p>The sale is considered 'closed' when the seller enters the buyer's Voucher into the site. That's how we know you got your item! If a seller doesn't enter a buyer's Voucher within a week, or if a buyer 'declines' an item from his Transaction Console, we refund 95% of the money he/she paid.</p>
				<p>After the silo administrator is paid, we invite the silo administrator send a 'Thank You' note.</p>
			</td><td align="center">
				<div style="margin-top: -15px; text-align: center">(click to enlarge)</div>
				<a class="fancybox" href="images/items-sold.png" title="The graphic, above, shows you how the buying, selling, and payment process works"> <img src="images/items-sold.png" width="421" height="300"></img></a>
			</td></tr></table>
		<h2>What are the Benefits of Voucher/Voucher Key?</h2>
			<table width="100%"><tr>
				<td valign="top"><p>If you see it on our site - the item is there to purchase. No 'dead' ads.<br><br>Sellers know buyers are serious and have money<br><br>You can decline an item if it is not what you want</p></td>
				<td valign="top"><p>Buyers can control whether they get their item or not.<br><br>No disputes as to whether an item was collected (no scammers)<br><br>Sellers are unlikely to misrepresent items, because they can be so easily declined</p></td>
				<td valign="top"><p>You don't have to bring money to pick up your item<br><br>You receive 95% of your payment back, if you decline an item<br><br>We avoid settling disputes, keeping our overhead low, which we pass on to you!</p></td>
			</tr></table>

		<?php if (!isset($_SESSION['is_logged_in'])) { ?>
			<a class="fancybox" href="index.php?task=create_account"><h1 align="center" class="click_me">Ready to go? Let's create an account now!</h1></a>
		<?php } else { ?>
			<a class="fancybox" href="items"><h1 align="center" class="click_me">Think you got it? Start looking for items to buy!</h1></a>
		<?php } ?>

		</div>

	
	</div>
</div> -->
<div id="bottom_menu">
	<a href="index.php?task=contact_us">Contact <?=SITE_NAME?></a> | <a href="index.php?task=about_us">About</a> | <a href="index.php?task=tos">Terms of Use</a> | <a href="index.php?ref=start">Get Started</a> | <a href="<?=ACTIVE_URL?>faq" target="_blank">FAQ</a> | <a href="index.php?task=stories"><?=SITE_NAME?> Stories</a>
	<div id="logo-footer">&nbsp;</div>
</div>

<script>
$("#getting-started-collapsible").click(function() {
	$("#getting-started").toggle("fast");
});
$("#faq-collapsible").click(function() {
	$("#faq").toggle("fast");
});
</script>