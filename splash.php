<div id="header">
	<?php
		include('header.php');
		include_once("include/GoogleAnalytics.php");

		if (param_get('ref') == "start") { ?>
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

</div>

<div class="row top_row">
	<h2 class="tagline">A marketplace for items donated for causes in your community
	<span class="blue" style="font-size: 9pt;"><a class="fancybox" href="images/how-it-works.png" title="The chart, above, demonstrates how <?=SITE_NAME?> works"><b>how <?=SITE_NAME?> works</b></a></span>
</h2> 
</div>

<div class="row">
	<div id="slider_frame">
		<div id="slider">
			<img src="images/splash/field-trip.jpg" alt="<span class='sliderText'>Public silo:</span> Raise money for students' arts, sports, and extracurricular programs." />
			<img src="images/splash/wedding.jpg" alt="<span class='sliderText'>Private silo:</span> Any family can create a private silo to fund a wedding, honeymoon, baby shower, anniversary, or family reunion." />
			<img src="images/splash/firemens-fund.jpg" alt="<span class='sliderText'>Public silo:</span> Fund a civic cause, like a Fireman's or Policeman's fund, or a library." />
			<img src="images/splash/college.jpg" alt="<span class='sliderText'>Private silo:</span> Cover a graduation gift, pay college tuition, or fund a year abroad, with items donated by a student's personal supporters." />
			<img src="images/splash/neighborhood-cleanup.jpg" alt="<span class='sliderText'>Public silo:</span> Create a silo to keep your community clean, safe, and beautiful." />
			<img src="images/splash/kid-medical.jpg" alt="<span class='sliderText'>Private silo:</span> A silo is a great way for a private group to cover emergency medical or legal expenses." />
			<img src="images/splash/playground.jpg" alt="<span class='sliderText'>Public silo:</span> Create a silo to fund construction and cleanup projects in your immediate area." />
			<img src="images/splash/vet-fees.jpg" alt="<span class='sliderText'>Private silo:</span> A silo can be used to cover the cost of an unexpected bill, such as for veterinary services." />
			<img src="images/splash/religious.jpg" alt="<span class='sliderText'>Public silo:</span> Donate an item to a church, temple, mosque or synagogue, whose mission is to help others in your area." />
			<img src="images/splash/artistic-project.jpg" alt="<span class='sliderText'>Private silo:</span> An artist, filmmaker, playwright or musician could leverage supporters to cover production costs, with a silo." />
			<img src="images/splash/youth-sports.jpg" alt="<span class='sliderText'>Public silo:</span> Help a youth sports team cover officiating, field, tournament, awards ceremony, and uniform expenses." />
			<img src="images/splash/big-purchase.jpg" alt="<span class='sliderText'>Private silo:</span> A family can rally to make a dream of enrichment come true for a young member. " />
		</div>
	</div>
</div>

<div id="slider-options">
	<span class="slider-select">now viewing: public and private silo types</span> <a class="fancybox" href="images/silo-types.png" title="Explanation of the difference between public and private silos">what's the difference?</a>
</div>


<div id="action_call" class="row">
	<!-- shop button -->
	<div class="row">
		<table class="splash-shop" onClick="window.location = 'items'">
		<tr>
			<td width="86px"></td>
			<td><img src="images/btn-cart.png" width="45" height="34"></img></td>
			<td class="splashText" style="color: #FFF;">shop</td>
			<td width="81px"></td>
		</tr>
		</table>
		<div class="splashText" style="text-align: left;">buy items whose sale helps a silo</div>
		<div class="action_text">pay online and pick up items locally, with a Voucher &nbsp; 
		<span class="blue"><a class="fancybox" href="images/items-sold.png" title="The graphic, above, shows you how the buying, selling, and payment process works"><b>how Voucher/Key works</b></a></span>
		</div>
	</div>

	<!-- donate button -->
	<div class="row">
		<table class="splash-donate" onClick="window.location = 'silos'">
		<tr>
			<td width="40px"></td>
			<td><img src="images/btn-heart.png" width="44" height="33"></img></td>
			<td class="splashText" style="color: #FFF;">donate items</td>
			<td width="26px"></td>
		</tr>
		</table>
		<div class="splashText" style="text-align: left;">sell items to benefit a public silo</div>
		<div class="action_text">many donated items are <b>tax deductible</b> &nbsp; 
		<span class="blue"><a class="fancybox" href="images/how-it-works.png" title="The chart, above, demonstrates how <?=SITE_NAME?> works"><b>how <?=SITE_NAME?> works</b></a></span>
		</div>
	</div>
	
	<!-- start silo button -->
	<div class="row">
		<a class='fancybox login-redirect' id='index.php?task=create_silo' href='<?php if (!$user_id) { echo "#login"; } else { echo "index.php?task=create_silo"; } ?>'>
			<div class="action splash-create">
				<div class="splash-create_text">create a private or a public silo</div>
				<div>private silos keep 95%, public silos are often tax-deductible.</div>
			</div>
		</a>

		<a class='fancybox login-redirect' id='index.php?task=pledge_first' href='<?php if (!$user_id) { echo "#login"; } else { echo "index.php?task=pledge_first"; } ?>'>
			<div class="splash-pledge">
				<div class="splash-pledge_text">pledge first</div>
				<div>we will notify your cause<br>to start a silo</div>
			</div>
		</a>
	</div>
		<div class="blue" style="padding-top: 10px; text-align: center; font-size: 9pt;">
			<a class="fancybox" href="images/how-it-works.png" title="The chart, above, demonstrates how <?=SITE_NAME?> works"><b>how <?=SITE_NAME?> works</b></a>
		</div>
</div>

<div class="row">
		<p class="silos_header">Popular Silos Near <span <?php if (!$_SESSION['is_logged_in']) echo 'class="s_change_location"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(change)</font>' ?></span> <a href="silos" class="bold_text">view more</a></p>
	
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


<div id="footer_container" class="row">
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
				<p><?=SITE_NAME?> has created a class of disaster relief silo, which we run. These are always authorized by the benefiting organization. Contact us for more information.  </p>
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

		<div id="bottom_menu">
			<a href="index.php?task=contact_us">contact <?=SITE_NAME?></a> | <a href="index.php?task=about_us">about</a> | <a href="index.php?task=tos">terms of use</a> | <a href="index.php?ref=start">get started!</a> | <a href="<?=ACTIVE_URL?>faq" target="_blank">faq</a> | <a href="index.php?task=stories"><?=SITE_NAME?> stories</a>
		</div>
	</div>
</div>

<script>
$("#learn-more").click(function() {
	$("#learn-more").remove();
	$("#quick-start").toggle("slow");
});
$(document).ready(function (){
    $("#learn-more").click(function (){
         $('html, body').animate({
               scrollTop: $("#quick-start").offset().top
         }, 1000);
    });
});
$('.login-redirect').click(function (){
	var location = $(this).attr('id');
	$('#login_form').get(0).setAttribute('action', location);
});
</script>