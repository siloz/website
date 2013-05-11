<?php
	header('content-type:text/xml');
	echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
	include_once('config.php');
	include_once('utils.php');
	include_once('classes/item.class.php');
	include_once('classes/silo.class.php');
	include_once('classes/user.class.php');

	$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
	mysql_select_db(DB_NAME, $conn);
	
	function verify_ein() {
		 $ein = param_post('ein');
		 $xml_data = 
		 	"<npreq:apirequest  xmlns:npreq='http://www.libertydata.net/api/apirequest'> 
		 	 <npreq:requestercredentials> 
		 	  <npreq:login>zackery.n.west@gmail.com</npreq:login>
		 	  <npreq:password>P@ssword3343</npreq:password> 
		 	 </npreq:requestercredentials> 
		 	 <npreq:version>1</npreq:version> 
		 	 <npreq:SearchByEIN> 
		 	  <npreq:ein>$ein</npreq:ein> 
		 	 </npreq:SearchByEIN> 
		 	</npreq:apirequest>";
		 $ch = curl_init();
		 $url = "https://www.libertydata.net/api/nonprofits/api.asp";
		 $headers = array("Content-Type: text/xml", "Content-Length: ".strlen($xml_data), "X-NONPROFITS-API-CALL-NAME:SearchByEIN");
		 
		 curl_setopt($ch, CURLOPT_URL, $url);
		 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);         
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		         curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data); 
		 
		$rxml=curl_exec($ch);
		$rxml = str_replace(array("<![CDATA[","]]>"), "", $rxml);
		echo $rxml;

	}
	
	function login() {
		$email = param_post('email');
		$password = param_post('password');	
		$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_array($res);
			return "<authenticated>1</authenticated><fullname>".$row['fullname']."</fullname>";
		} 
		return "<authenticated>0</authenticated><sql>$sql</sql>";
	}
	
	function update_item_status() {
		$item_id = param_post('item_id');
		$status = param_post('status');
		$event = 'sold_date';
		if ($status == 'Funds Sent')
			$event = 'sent_date';
		else if ($status == 'Funds Received') 
			$event = 'received_date';
		$sql = "UPDATE items SET status = '$status', $event = CURRENT_TIMESTAMP WHERE item_id = $item_id";
		mysql_query($sql);		
	}
		
	function get_item_info() {
		$item_id = param_post('item_id');
		$s = mysql_query("SELECT * FROM items WHERE id = '$item_id'");
		$item = mysql_fetch_array($s);
		$res = "";
		foreach (array_keys($item) as $key) {
			if (!is_numeric($key))
				$res .= "<".$key.">".$item[$key]."</".$key.">";
		}
		return "<item>".$res."</item>";
	}
	
	function email_seller() {
		$email = param_post('email');
		$id = param_post('item_id');
		$item = new Item($id);
		$to = $item->owner->email;
		$to = 'vinhanh@gmail.com';
		$subject = "Email from $email regarding item '".$item->title."'";
		$message = "<h3>Guess What?</h3>";
		$message .= "Someone is interested in your item selling for silo - ".$item->silo->name.".  If you reply to this inquiry, you will be disclosing your email address.  If you do not wish for the recipient to have your email address, ignore this message, or click here [blocks buyer from contacting seller] to block this buyer from contacting you again for the duration of this silo.<br/><br/>";
		$message .= "<i>Their Message:</i><br/>";
		$message .= "<b>Subject: </b>".param_post('subject')."<br/><b>Content:</b> ".param_post('content');
	    return email_with_template($to, $subject, $message);
	}
	
	function email_silo_admin() {
		$email = param_post('email');
		$silo_id = param_post('silo_id');
		$s1 = mysql_query("SELECT * FROM silos WHERE silo_id = ".$silo_id);
		$silo = mysql_fetch_array($s3);
		$s2 = mysql_query("SELECT * FROM users WHERE user_id = ".$silo['user_id']);
		$user = mysql_fetch_array($s2);
		$to = $user['email'];
		$subject = "Email from [$email] regarding Silo [".$silo['name']."]";
		$message .= "<h2>SOME INTRO</h2>";
		$message .= "<i>Their Message:</i><br/>";
		$message .= "<b>Subject: </b>".param_post('subject')."<br/><b>Content:</b> ".param_post('content');
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: noreply@siloz.com" . "\r\n" ."X-Mailer: PHP/" . phpversion();
	    $sent = mail($to, $subject, $message, $headers);
		return $sent ? "successful" : "failed";
	}
	
	
	function record_donation() {
		$ref = param_post('reference');
		$silo_id = param_post('silo_id');
		$user_id = param_post('user_id');
		$amount = param_post('amount');
		$sql = "INSERT INTO donations (user_id, silo_id, amount, status, ref) VALUES ($user_id, $silo_id, $amount, 'Donation Initiated', '$ref')";
		return mysql_query($sql) ? "successful" : "failed";
	}
		
	$request = param_post('request');	
	
	if ($request == 'login') {
		echo '<response>'.login().'</response>';
	}
	else if ($request == 'get_item_info') {
		echo '<response>'.get_item_info().'</response>';
	}
	else if ($request == 'email_seller') {
		echo '<response>'.email_seller().'</response>';
	}
	else if ($request == 'email_silo_admin') {
		echo '<response>'.email_silo_admin().'</response>';
	}
	else if ($request == 'update_item_status') {
		update_item_status();
	}
	else if ($request == 'delete_item') {
		delete_item();
	}
	else if ($request == 'delete_user_items') {
		delete_user_items();
	}
	else if ($request == 'record_donation') {
		echo "<response>".record_donation()."</response>";
	}
	else if ($request == 'verify_ein') {
		verify_ein();
	}
	
?>