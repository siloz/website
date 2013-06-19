<div id="header">
	<?php
		include('header.php');
		include_once("include/GoogleAnalytics.php");
	?>
</div>

<div class="row top_row">
	<h2 class="tagline">A marketplace for items donated for causes in your community
	<span class="blue" style="font-size: 9pt;"><a onclick="popup_show('works', 'works_drag', 'works_exit', 'screen-center', 0, 0);"><b>how <?=SITE_NAME?> works</b></a></span>
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
	<span class="slider-select">now viewing: public and private silo types</span> <a onclick="popup_show('silo_types', 'silo_types_drag', 'silo_types_exit', 'screen-center', 0, 0);">what's the difference?</a>
</div>


<div id="action_call" class="row">
	<!-- shop button -->
	<div class="row">
		<table class="splash-shop" onClick="window.location = 'items'">
		<tr>
			<td width="81px"></td>
			<td><img src="images/btn-cart.png" width="45" height="34"></img></td>
			<td class="splashText" style="color: #FFF;">shop</td>
			<td width="81px"></td>
		</tr>
		</table>
		<div class="splashText" style="text-align: left;">buy items whose sale helps a silo</div>
		<p class="action_text">pay online and pick up items locally, with a Voucher &nbsp; 
		<span class="blue"><a onclick="popup_show('items_sold', 'items_sold_drag', 'items_sold_exit', 'screen-center', 0, 0);"><b>how Voucher/Key works</b></a></span>
		</p>
	</div>

	<!-- donate button -->
	<div class="row">
		<table class="splash-donate" onClick="window.location = 'silos'">
		<tr>
			<td width="26px"></td>
			<td><img src="images/btn-heart.png" width="44" height="33"></img></td>
			<td class="splashText" style="color: #FFF;">donate items</td>
			<td width="26px"></td>
		</tr>
		</table>
		<div class="splashText" style="text-align: left;">sell items to benefit a public silo</div>
		<p class="action_text">many donated items are <b>tax deductible</b> &nbsp; 
		<span class="blue"><a onclick="popup_show('works', 'works_drag', 'works_exit', 'screen-center', 0, 0);"><b>how <?=SITE_NAME?> works</b></a></span>
		</p>
	</div>
	
	<!-- start silo button -->
	<div class="row">
		<table class="action splash-create" onClick="window.location = '<?php if (!$user_id) { echo "javascript:create_silo_need_login();"; } else { echo "index.php?task=create_silo"; } ?>'">
		<tr>
			<td class="splashText">create a private or a public silo</td>
		</tr>
		<tr>
			<td style="padding-bottom: 5px;">private silos keep 95%, public silos are often tax-deductible.</td>
		</tr>
		</table>

		<div class="splash-pledge" onClick="window.location = 'index.php?task=pledge_first'">
			<span class="splashText" style="color: #FFF;">pledge <br> first</span>
		</div>
	</div>
		<div class="blue" style="padding-top: 10px; text-align: center; font-size: 9pt;">
			<a onclick="popup_show('works', 'works_drag', 'works_exit', 'screen-center', 0, 0);"><b>how <?=SITE_NAME?> works</b></a>
		</div>
</div>

<div class="row">
		<p class="silos_header">Popular Silos Near <span <?php if (!$_SESSION['is_logged_in']) echo 'class="s_change_location"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(click to change)</font>' ?></span> <a href="silos" class="bold_text">view more</a></p>
	
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
	<p class="silos_header">Items for Sale Near <span <?php if (!$_SESSION['is_logged_in']) echo  'class = "s_change_location"' ?> style="color: #f60;"><?=$userLocation?> <?php if (!$_SESSION['is_logged_in']) echo  '<font size="1">(click to change)</font>' ?></span> <a href="items" class="bold_text">view more</a></p>

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

<div id="quick-start"></div>

<div id="footer_container" class="row">
	<div id="quick_start_bg">
		<table width="100%" class="quick_start">
			<tr>
			<td valign="top" width="47%">
				<h2>What is <?=SITE_NAME?>?</h2>
				<p>You could think of a silo as a rummage sale, online, to benefit a local cause, whether private or public.  We think <?=SITE_NAME?> is transparent, safe, and fun.  Everybody wins: Buyers get merchandise while helping local causes.  Those donating items often receive a tax-deduction for a sold item and silo administrators raise money without asking for cash!</p>
				<h2>How Does <?=SITE_NAME?> Work?</h2>
				<div style="margin-top: -15px; text-align: center">(click to enlarge)</div> <a onclick="popup_show('works', 'works_drag', 'works_exit', 'screen-center', 0, 0);"><img src="images/how-it-works.png" width="452" height="321"></img></a>
				<h2>How Do I Join A silo?</h2>
				<p>You join a silo by pledging (donating) an item to a cause in your area.  That item then appears on the site, for sale to the local public.</p>
				<h2>What Are The silo Types?</h2>
				<p>There are two top-level silo types: <b>private</b> and <b>public</b>. Within each of these categories, there are many sub-types. <br><br>
					<b><i>Private</i></b><br>
					Only those invited (and not the general public) can see information about a private silo.<br><br>
					<b><i>Public</i></b><br>
					A public silo is visible to anyone on the site, and anybody can donate an item for one.
					<div style="text-align: center">(click to enlarge)</div> 
					<a onclick="popup_show('silo_types', 'silo_types_drag', 'silo_types_exit', 'screen-center', 0, 0);"><img src="images/silo-types.png" width="452" height="277"></img></a> <br><br>
					Public silos must fall into one of the following categories: <br><br>
					Religious Organization <br>
					Public K-12 School (PTA, student group) <br>
					Public University (student group, sorority/fraternity) <br>
					Registered Non-Profit With a Presence and Outreach in a Given Region <br>
					Civic Organization <br>
					Neighborhood Organization (including an HOA) <br>
					Youth Sports Organization (adult organizations are considered private) <br><br>
					And must benefit the community and a public silo administrator must enjoy some leadership role with that organization.  
				</p>
				<h2>Are Donated Items Tax-Deductible?</h2>
				<p>Only public silos may potentially issue tax-deductible receipts, if they can verify the 501(c)3 when they launch, or they help a public school.</p>
			</td>
			<td width="6%"></td>
			<td valign="top" width="47%">
				<h2>How Are Items Sold?</h2>
				<p>Nothing is shipped on the site; you are only permitted to shop for items within a 75 mile radius of your address. <br><br>
					1) Buyer pays online, with a credit/debit card, initiating a purchase. <br><br>
					2) Buyer and seller are given each other's contact information (telephone and email address), and one week in which to transact a sale. <br><br>
					Additionally, buyers are issued a one-time Voucher, which acts as cash, and sellers are issued a Voucher Key, which proves a Voucher is authentic. <br><br> 
					3) If the buyer wishes to purchase the item, he/she provides the seller the Voucher.  The sale is considered 'closed' when the seller enters the buyer's Voucher into the site. <br><br>
					At the end of a silo (1, 2, or 3 weeks), all the money from 'closed' sales are paid-out to a silo administrator through either PayPal (private silos) or an e-check (public silos).  This usually takes no more than a week.
					<div style="text-align: center">(click to enlarge)</div> 
					<a onclick="popup_show('items_sold', 'items_sold_drag', 'items_sold_exit', 'screen-center', 0, 0);"><img src="images/items-sold.png" width="452" height="321"></img></a> <br><br>
					Note: If a seller enters the Voucher <?=SITE_NAME?> issued to the buyer into the site within the designated time, <?=SITE_NAME?> WILL NOT refund payment to the buyer, whether the item has actually been collected or not.  If the seller has not done so, <?=SITE_NAME?> WILL refund payment to the buyer, whether the item was actually collected or not. <br><br>
					So, buyers should not provide a seller with a Voucher via email or over the telephone, or if they do not wish to collect an item.  Sellers should never accept a Voucher that does not match to their Voucher Key.  Contact us if you suspect any trickery!
				</p>
				<h2>What Are Some Good Private silo Applications?</h2>
				<p>Private silos leverage existing, strong, social networks, and ask your supporters to donate items to a cause, which is kept private and not visible to the general public, who may only see items donated to your cause. <br><br>
					Some very common needs for a private silo include: <br>
					<table style="text-align: center" width="100%">
						<tr><td>Family Reunion</td> <td>Medical or Legal Emergency</td></tr>
						<tr><td>Meetup</td> <td>College Loans or Tuition</td></tr>					
						<tr><td>Wedding or Honeymoon</td> <td>Graduation or Prom</td></tr>
						<tr><td>Mother's Day or Father's Day</td> <td>A Birthday or Holiday Gift</td></tr>
						<tr><td>An Anniversary</td> <td>Music or Film Project</td></tr>
						<tr><td>Adult Sports League or Team</td> <td>Unforeseen Household Bill</td></tr>
						<tr><td>Youth Education or Enrichment</td> <td>Down-Payment on a Car or Home</td></tr>
					</table> <br>
					Remember: money you collect from a private silo is income, and should be reported to the IRS.  
				</p>
				<h2>What Are Some Good Public silo Applications?</h2>
				<p>Here are some great silo ideas: <br>
					<table style="text-align: center" width="100%">
						<tr><td>Neighborhood: Festival or Party</td> <td>K-12 Public Education: Class Trip</td></tr>
						<tr><td>Local Youth Sports: Uniforms or Attire</td> <td>Civic: Commissioned Art</td></tr>					
						<tr><td>K-12 Public Education: Graduation</td> <td>Neighborhood: Park or Playground</td></tr>
						<tr><td>Religious: Charity or Outreach</td> <td>Civic: Library</td></tr>
						<tr><td>Civic: Fireman's or Police Fund</td> <td>K-12 Public Education: Arts Programs</td></tr>
						<tr><td>Public University: Fraternity or Sorority</td> <td>Public University: Scholarship</td></tr>
						<tr><td>Neighborhood: Cleanup</td> <td>Local Youth Sports: Tournament Fees</td></tr>
					</table>
				</p>
			</td>
			</tr>
		</table>

		<div id="bottom_menu">
			<a href="index.php?task=contact_us">contact <?=SITE_NAME?></a> | <a href="index.php?task=about_us">about</a> | <a href="index.php?task=tos">terms of use</a> | <a href="index.php#quick-start">get started!</a> | <a href="<?=ACTIVE_URL?>faq" target="_blank">faq</a> | <a href="index.php?task=stories"><?=SITE_NAME?> stories</a>
		</div>
	</div>
</div>

<div class="login" id="silo_types">
	<div id="silo_types_drag" style="float:right">
		<img id="silo_types_exit" src="images/close.png"/>
	</div>
	<div>
		<img src="images/silo-types.png"></img>
	</div>
</div>

<div class="login" id="works">
	<div id="works_drag" style="float:right">
		<img id="works_exit" src="images/close.png"/>
	</div>
	<div>
		<img src="images/how-it-works.png"></img>
	</div>
</div>

<div class="login" id="items_sold">
	<div id="items_sold_drag" style="float:right">
		<img id="items_sold_exit" src="images/close.png"/>
	</div>
	<div>
		<img src="images/items-sold.png"></img>
	</div>
</div>