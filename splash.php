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
<div id="getting-started" class="greyFont">
	<h3>What is s&igrave;loz?</h3>
	<p>s&igrave;loz is simple: Buyers purchase items donated by local sellers.  The proceeds go to a fundraiser, or 'silo'.</p>
	<p>Traditional (off-line) rummage sale fundraisers are a headache to organize, and attract orphaned, unwanted items.</p>
	<p>A silo can be started in minutes, by one person, and shared with others who will donate to, and promote (this process of spreading labor or cost is called crowd-sourcing) also, in a few minutes.  A silo costs nothing, up-front, and 10% (public) or 5% (private) of the money raised, afterward, making it at least 90% efficient.</p>

	<h3>How do I donate?</h3>
	<p>You join a silo by donating an item to a silo you support.  Buyers see the item on the site, and make payment to receive a Voucher (code word), which is provided to you when the buyer collects the item.</p>

	<h3>How do I make a purchase?</h3>
	<p>Everything on s&igrave;loz sells locally; nothing is shipped.  A buyer pays online and receives a Voucher (code word), that he or she provides to the seller, after inspecting and collecting an item.  Buyers are not permitted to buy items more than 75 miles away.</p>

	<h3>Got it!  How do I get started?</h3>
	<p>If you're a shopper, just shop!  You will only be permitted to make a purchase within a 75 mile radius of your address (which is kept hidden from others), because you have to pick up the item, and use your Voucher (that you receive when you make payment) to collect your item.</p>
	<p>To donate, find a silo you like, and click the 'donate' button on its page.  We're getting started here, so remember to use our 'post to Craigslist' tool, so your item gets more chances to sell!</p>
	<p>To start a silo, click the 'Create a Silo' button on our landing page, or anywhere at the top of any page.</p>
</div>

<div id="faq-collapsible">
	<h1>FAQ</h1>
</div>
<div id="faq" class="greyFont">
	<h3>Buyers</h3>
	<h4>Do I have to be near a seller to purchase an item? Can items be shipped?</h4>
	<p>We believe a buyer should have the option to actually see and inspect an item before collecting it. Shipping would not permit that. We have designed the site so that you may only make payment for an item within a 75 mile radius of you.</p>
	<h4>Can i decline an item after paying and inspecting it?</h4>
	<p>You can decline an item you have made payment for, and receive 95% of your money back &ndash; provided you have not shared your Voucher with the seller. You may also decline an item by selecting 'decline' in your transaction console.</p>

	<h3>Sellers (Donors)</h3>
	<h4>What is 'Pledge First'?</h4>
	<p>List your item, give us a name and an email address, and we'll contact that potential silo administrator, with a link to start a silo. If they use the link, your item will be the first item pledged to that silo.</p>
	<h4>Do I have to be located near a given silo to donate items to it?</h4>
	<p>You can donate to a silo that is not in your area. Think of a silo as a 'hub', and the people who donated items as 'spokes'. Only buyers in your area (near your spoke end) will be permitted to purchase your items.</p>
	<h4>Do I have to physically move items I want to donate to some location?</h4>
	<p>You do not have to move your item to donate it to a silo, you list it where it sits. Only local buyers will be able buy it.</p>
	<h4>How can I ensure my item sells?</h4>
	<p>Price your item low; also, promote it by using our 'post to Craigslist' feature, visible in your transaction console, or on the item's page.</p>

	<h3>silo Administrators</h3>
	<h4>What kinds of silos are there?</h4>
	<p>A silo can be either public or private. Items for both public and private silos are visible on the site, so they can sell, but public silos &ndash; themselves &ndash; are visible on the site, which allows them to accept donated items from the general public. Private silos are not listed and are only visible by invitation only.</p>
	<h4>Who can create a silo?</h4>
	<p>Anybody over age 14 may create a private silo for a legal purpose. When the silo ends, we pay out 95% of the money raised via PayPal. To create a public silo, you must represent an organization with a presence and impact in a given community, which also falls into one of the following categories: 1) youth sports, 2) religious, 3) public education, 4) civic, 5) neighborhood, or 6) regional non-profit.</p>
	<h4>May I create a public silo?</h4>
	<p>Anybody over age 14 can create a private silo for any legal purpose. Public silos must 1) have an impact and presence in a community, and 2) fall neatly into one of the designated categories of public silo (below). 3) the person creating the silo must be a leader in said organization.</p>
	<h4>How do I promote my silo?</h4>
	<p>Promote your silo on- and off-line, using our digital tools (send promotional emails, post to Facebook), and by printing &frac14; page flyers.</p>
	<h4>Are all public silos tax-deductible?</h4>
	<p>Not all public silos are tax-deductible, but no private silos are. To qualify for the site to automatically issue tax-deductible receipts, you must be registered as a 501(c)3 with the IRS, be in good standing, and furnish a valid EIN.</p>
	<h4>How are tax-deductions handled?</h4>
	<p>If a silo qualifies, is a registered 501(c)3 who has furnished a valid EIN number), when a donated item's sale is complete, we email a tax-deductible receipt to the donor.</p>
</div>

<div id="bottom_menu">
	<a href="index.php?task=contact_us">Contact <?=SITE_NAME?></a> | <a href="index.php?task=about_us">About</a> | <a href="index.php?task=tos">Terms of Use</a> | <a href="index.php?ref=start">Get Started</a> | <a href="<?=ACTIVE_URL?>faq" target="_blank">FAQ</a> | <a href="index.php?task=stories"><?=SITE_NAME?> Stories</a>
	<div id="logo-footer">&nbsp;</div>
</div>

<script>
$("#getting-started-collapsible").click(function() {
	$("#getting-started").slideToggle("fast");
	$("#getting-started-collapsible h1").toggleClass("collapsible-icon");
});
$("#faq-collapsible").click(function() {
	$("#faq").slideToggle("fast");
	$("#faq-collapsible h1").toggleClass("collapsible-icon");
});
</script>