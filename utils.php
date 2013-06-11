<?php
	require_once('config.php');
	
	require_once('include/phpmailer/class.phpmailer.php');
	
	function email_with_template($to, $subject, $content) {
		$mail = new PHPMailer(true);
		$mail->SetFrom("noreply@".SHORT_URL, SHORT_URL);
		$mail->AddAddress($to);
		$mail->AddEmbeddedImage("images/logo.png", "logo", "logo.png");
		$mail->Subject = $subject;
		$mail->MsgHTML(
		"<html>
			<body style='background-color: #84BFE5'>
				<div style='width: 600px; margin: auto; padding: 20px; background-color: #fff;'>
					<img src='cid:logo' width='289' height='62' /><br/>					
					$content
				</div>
				<div style='width: 600px; margin: auto; padding: 20px; text-align:center; background-color: #f60;'>
					This is an event-triggered email notification.<br/>
					To stop certain emails, visit your account settings.					
				</div>
			</body>
		</html>");
		if ($mail->Send()) {
			return "successful";
		}
		else {
			return "failed";
		}
	}

	function email_with_attachment($to, $subject, $content, $id, $filename) {
		$mail = new PHPMailer(true);
		$mail->SetFrom("noreply@".SHORT_URL, SHORT_URL);
		$mail->AddAddress($to);
		$mail->AddEmbeddedImage("images/logo.png", "logo", "logo.png");
		$mail->Subject = $subject;
		$mail->MsgHTML(
		"<html>
			<body style='background-color: #84BFE5'>
				<div style='width: 600px; margin: auto; padding: 20px; background-color: #fff;'>
					<img src='cid:logo' width='289' height='62' /><br/>					
					$content
				</div>
				<div style='width: 600px; margin: auto; padding: 20px; text-align:center; background-color: #f60;'>
					This is an event-triggered email notification.<br/>
					To stop certain emails, visit your account settings.					
				</div>
			</body>
		</html>");

		foreach ($filename as $file) {
			$path = "uploads/thank-you/$id/$file";
			$mail->AddAttachment($path);
		}

		if ($mail->Send()) {
			return "successful";
		}
		else {
			return "failed";
		}
	}

	function genRandomString($length) {
    		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvwxyz";
    		$string = "";

    		for ($p = 0; $p < $length; $p++) {
        		$string .= $characters[mt_rand(0, strlen($characters))];
    		}

    		return $string;
	}
		
	function param_get($key) {
		if (array_key_exists($key, $_GET)) {
			return $_GET[$key];
		}
		else {
			return '';
		}
	}
	
	function param_post($key) {
		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}
		else {
			return '';
		}
	}
	
	//gets the data from a URL  
	function get_tiny_url($url)  {  
		$ch = curl_init();  
		$timeout = 5;  
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
		$data = curl_exec($ch);  
		curl_close($ch);  
		return $data;  
	}
		
	function is_valid_email($email) {
	  // First, we check that there's one @ symbol, 
	  // and that the lengths are right.
	  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
	    // Email invalid because wrong number of characters 
	    // in one section or wrong number of @ symbols.
	    return false;
	  }
	  // Split it into sections to make life easier
	  $email_array = explode("@", $email);
	  $local_array = explode(".", $email_array[0]);
	  for ($i = 0; $i < sizeof($local_array); $i++) {
	    if
	(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
	↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
	$local_array[$i])) {
	      return false;
	    }
	  }
	  // Check if domain is IP. If not, 
	  // it should be valid domain name
	  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
	    $domain_array = explode(".", $email_array[1]);
	    if (sizeof($domain_array) < 2) {
	        return false; // Not enough parts to domain
	    }
	    for ($i = 0; $i < sizeof($domain_array); $i++) {
	      if
	(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
	↪([A-Za-z0-9]+))$",
	$domain_array[$i])) {
	        return false;
	      }
	    }
	  }
	  return true;
	}		
	
	function validate_cc_number($cc_number) {
	   /* Validate; return value is card type if valid. */
	   $false = false;
	   $card_type = "";
	   $card_regexes = array(
	      "/^4\d{12}(\d\d\d){0,1}$/" => "visa",
	      "/^5[12345]\d{14}$/"       => "mastercard",
	      "/^3[47]\d{13}$/"          => "amex",
	      "/^6011\d{12}$/"           => "discover",
	      "/^30[012345]\d{11}$/"     => "diners",
	      "/^3[68]\d{12}$/"          => "diners",
	   );

	   foreach ($card_regexes as $regex => $type) {
	       if (preg_match($regex, $cc_number)) {
	           $card_type = $type;
	           break;
	       }
	   }

	   if (!$card_type) {
	       return $false;
	   }

	   /*  mod 10 checksum algorithm  */
	   $revcode = strrev($cc_number);
	   $checksum = 0; 

	   for ($i = 0; $i < strlen($revcode); $i++) {
	       $current_num = intval($revcode[$i]);  
	       if($i & 1) {  /* Odd  position */
	          $current_num *= 2;
	       }
	       /* Split digits and add. */
	           $checksum += $current_num % 10; if
	       ($current_num >  9) {
	           $checksum += 1;
	       }
	   }

	   if ($checksum % 10 == 0) {
	       return $card_type;
	   } else {
	       return $false;
	   }
	}
?>
