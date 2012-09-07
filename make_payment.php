<?php
	require_once('utils.php');
	require_once('config.php');
	$silo_id = param_get('id');
	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
	mysql_select_db(DB_NAME, $conn);
	$silo = mysql_fetch_array(mysql_query("SELECT * FROM silos WHERE silo_id = $silo_id"));
	$admin_id = $silo['admin_id'];
	$admin = mysql_fetch_array(mysql_query("SELECT * FROM users WHERE user_id = $admin_id"))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"	xml:lang="en">
	<head>
		<link rel="stylesheet" type="text/css" href="css/siloz.css" />	
		<link rel="stylesheet" type="text/css" href="css/siloz_header.css" />	
		<link rel="stylesheet" type="text/css" href="css/siloz_footer.css" />			
	</head>
	<body style="background: #fff; margin: 20px 200px;">
		<h2>Making Payment for a Silo Item</h2>

		While donations can only be accepted through PayPal, there are multiple ways to make payment to a silo administrator:

		<ol>
			<li> <b>Check, or Money Order.</b>  Mail any of these to:
				<div style="text-align: center; font-weight: bold;">
			<?php echo $admin['fullname']?><br/>
			<?php echo $admin['email']?><br/>
			<?php echo $admin['phone']?><br/>
			<?php echo $admin['address']?><br/>								
				</div>
				<br/>
			...Please be sure to include the name of the silo, and your username, so your administrator can give you credit!  Remember: if you send a <b>money order</b>, keep your money order stub!  That way, if it gets lost in the mail, you can recover your funds.
			</li>
			<li> <b>Cash.</b>  If you see your silo administrator, you can remit payment to him or her in cash.  <b>We advise you to request a receipt for your donation!</b>
			</li>
			<li><b>PayPal.</b>  Your silo administrator's email address is: <a href="mailto:<?php echo $admin['email']?>" style="color:#2F8DCB"> <?php echo $admin['email']?></a>
				<br/><br/>
			a) Go to <a href="http://www.paypal.com" style="color: #2F8DCB">http://www.paypal.com</a>.  Log in or create an account.  Note: you will be charged a nominal (c. 3%) fee if you use a credit or debit card.  To avoid a fee, associate your bank account with PayPal, and follow the instructions below.  
				<br/><br/>
			b)  Select <b>'Send Money'</b>.  
				<br/><br/>
			c) Enter the <b>silo administrator's email address</b>, and the amount you wish to remit, along with the <b>silo name</b> and the <b>title/item number</b> you are sending payment for.  
				<br/><br/>
			d) Select 'Personal' tab, and 'Other' radial button, and select 'Continue' at the bottom of the screen.  If you do not have a credit/debit card on file with PayPal, you will be prompted for one.  
				<br/><br/>
			<b>To avoid PayPal fees</b>.  Before finalizing payment, select 'eCheck' option, and enter your bank account information.  Payment will then be drawn from your bank account, with no fees incurred.
			</li>
		</ol>
	</body>
</html>