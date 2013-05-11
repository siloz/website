<?php include_once("include/GoogleAnalytics.php"); ?>

<span class="greyFont">

<div class="headingPad"></div>

<div class="userNav" align="center">
	<span class="accountHeading">Contact Us</span>
</div>

<div class="headingPad"></div><br>

<div class="rounded_box" style="width: 80%; margin: auto;">
	<?php
		if (param_post('submit') == 'Send') {
			$name = trim(param_post('name'));
			$email = trim(param_post('email'));
			$phone = trim(param_post('phone'));
			$department = trim(param_post('department'));
			$subject = trim(param_post('subject'));
			$content = trim(param_post('content'));
			$err = "";
			if ($name == '')
				$err .= "Your name must not be empty. <br/>";
			if ($email == '')
				$err .= "Your email must not be empty. <br/>";
			else if (!is_valid_email($email)) {
				$err .= 'Email address is invalid.<br/>';		
			}		
			if ($subject == '')
				$err .= "Subject must not be empty. <br/>";
			if ($content == '')
				$err .= "What's on your mind? <br/>";

			if ($err != '') {
				echo "<div style='color: red; font-weight: bold;'>ERROR: <br/>$err</div><br/>";
			}
			else {
				$to      = 'Siloz Support <support@siloz.com>';
				$subject = "Contact Us: $subject";
				$message = "Department: $department\nPhone: $phone\nInquiry: $content";
				$headers = "From: $email" . "\r\n" .
				    "X-Mailer: PHP/" . phpversion();
			    $sent = mail($to, $subject, $message, $headers);
				if (param_post('send_copy') == 'on') {
					mail($email, $subject, $message, $headers);
				}		    
				if (!$sent) {
					echo "<div style='color: red; font-weight: bold;'>ERROR: Failed to sent email!</div><br/>";
				} else {
					echo "<div style='color: red; font-weight: bold;'>Your information has been emailed to <?=SITE_NAME?> Administrator</div><br/>";
				}
			}
		}
	?>
	<b>Mailing address:</b> <?=SITE_NAME?> LLC., 4413 S. 1st Street, Austin, TX 78745
	<br/>
	<b>Phone number: </b> 510-842-6077
	<br/>
	<br/>
	Feel free to contact us with business or press-related matters by using this form or giving us a call. 
	<br/>
	Thanks very much for your interest in <?=SITE_NAME?>! 
	<br/>	
	<form name="contact_us" id="contact_us" method="POST">
		<table cellpadding="20px">
			<tr>
				<td width="50%" valign="top">
					Your Name <font color=red>*</font>: <br/>
					<input type="text" id="name" name="name" style="width: 300px;"/><br/>
					Your Email <font color=red>*</font>: <br/>
					<input type="text" id="email" name="email" style="width: 300px;"/><br/>					
					Your Phone (Optional): <br/>
					<input type="text" id="phone" name="phone" style="width: 300px;"/><br/>					
					Department: <br/>
					<select style="width: 300px" name="department">
						<option value="">Select the department</option>
						<option value="Customer support">Support</option>
						<option value="Feedback">Feedback or requests</option>
						<option value="Business and press inquiries">Business and press inquiries</option>
					</select>
				</td>
				<td width="50%" valign="top">
					Subject/Reason <font color=red>*</font>: <br/>
					<input type="text" id="subject" name="subject" style="width: 300px;"/><br/>					
					What's on your mind? <font color=red>*</font> <br/>
					<textarea id="content" name="content" style="width: 300px; height: 100px;"></textarea><br/>
					<input type="checkbox" name="send_copy" id="send_copy"/>Send a copy of this message to yourself<br/>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<button type="submit" name="submit" value="Send">Send</button>
				</td>
			</tr>
		</table>
	</form>
</div>

</span>			