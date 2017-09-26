<?
	/*
	=====================
	Contact Class
	---------------------
	Allows email to be send from a web form to a nominated person
	=====================
	
	written by: Phil Thompson phil.thompson@ichameleon.com (modified Treelin's own code)
	when: February/March 2007
	
	
	
	
	*/

class Contact {

	public $errmsg = array();
	private $defmsg;
		
	public function __construct() {
		// This is loaded when the class is created	
		$this->defmsg = 'Type your comment here';
	}
		
	// WATCH FOR SPAM	
	function checkMessage($str) {
		// prevents email injection attacks
		$safe = (preg_replace(array("/%0a/", "/%0d/", "/Content-Type:/i", "/bcc:/i","/to:/i","/cc:/i" ), "", $str ) );
		if ($safe != $str) {
			mail($tech_email,'Mail injection attempt','On ' . $_SERVER['HTTP_HOST'] . ' in ' . $_SERVER['PHP_SELF'] . ' at line '. __LINE__.' from ' . $_SERVER['REMOTE_ADDR'] .'. String [when cleaned] was '. $safe);
		}
		return $safe;
	}
	
	// VALIDATE EMAIL ADDRESS	
	function checkValidEmail($email){

		//copyright - http://www.ilovejackdaniels.com/php/email-address-validation
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!$%&'*+/=?^_`{|}~-][A-Za-z0-9!$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
		}
		return true;
	}
	
	// HTML FOR FORM	
	function drawContactForm($type=''){
		global $page, $site, $captcha;
		
		$name = $_POST['name'];
		$email = $_POST['email'];
		$number = $_POST['number'];
		$message = $_POST['message'];
	
		$form = '
      	<h3>Contact form</h3>
		
		<form method="post">
			<input type="hidden" name="action" value="send" />

			<div class="form-group">
				<label for="commentName" class="sr-only">Your name</label>
				<input type="text" name="name" value="'.$name.'" class="form-control" id="commentName" placeholder="Your name" />
			</div>

			<div class="form-group">
				<label for="commentEmail" class="sr-only">Email address</label>
				<input name="email" type="email" class="form-control" id="commentEmail" placeholder="Your email address" value="'.$email.'" />
			</div>
			
			<div class="form-group">
				<label for="commentText" class="sr-only">Your comment</label>
				<textarea name="message" rows="5" class="form-control commentbox" id="commentText">'.($message?$message:$this->defmsg).'</textarea>
			</div>

			'.($site->getConfig("setup_use_captcha")?$captcha->drawForm():'').'
			
			<label>
				<input type="checkbox" name="terms" value="1" '.($_POST['terms']==1?'checked="checked"':'').' /> I agree to the <a title="" data-original-title="" href="#">terms and conditions</a>
			</label>
			
			<button type="submit" class="btn btn-info btn-sm pull-right"><i class="ion-checkmark"></i> Submit</button>

		</form>
		';
			
		$oldform = '
<p class="instructions">'.$labels['CONTACTMSG']['txt'].'</p>
<!-- <p>Fields marked with a <img src="/img/layout/star.gif" alt="star/asterisk" /> are mandatory.</p> -->
<form id="contactForm" class="std-form" action="" method="post">
<fieldset class="border">
	<input type="hidden" name="action" value="send" />
	<fieldset class="field">
		<label for="name" class="required">'.$page->drawLabel('contact-name', "Full name").':</label>
		<input type="text" name="name" id="name" class="text required" value="'.$name.'" />
	</fieldset>
	<fieldset class="field">
		<label for="number">'.$page->drawLabel('contact-phone', "Phone number").':</label>
		<input type="text" name="number" class="text" id="number" value="'.$number.'" />
	</fieldset>
	<fieldset class="field">
		<label for="email" class="required">'.$page->drawLabel("contact-email", "email").':</label>
		<input type="text" name="email" id="email" class="text required" value="'.$email.'" />
	</fieldset>
	<fieldset class="field">
		<label for="f_about" class="required">'.$page->drawLabel("contact-about", "Contact about").':</label>
		<select name="about" id="f_about" class="text required">
			<option>Site feedback</option>
			<option>Event feedback</option>
			<option'.($type=="suggest"?' selected=" selected"':"").'>Suggest an event</option>
			<option'.($type=="member"?' selected=" selected"':"").'>'.$page->drawLabel("contact-type-member", "Become a member").'</option>
			<option>Information request</option>
			<option>Other</option>
		</select>
	</fieldset>
	<fieldset class="field">
		<label for="message" class="required">'.$page->drawLabel("contact-message", 'message').':</label>
		<textarea name="message" class="text" id="message" rows="5" cols="30">'.$message.'</textarea><br />
	</fieldset>
	'.($site->getConfig("setup_use_captcha")?$captcha->drawForm():'').'
	<fieldset class="field">
		<label for="contact_submit" style="visibility:hidden;">Submit</label>
		<input type="submit" id="contact_submit" class="submit" name="submit" value="'.$page->drawLabel("contact-button-send", "Send").'" />
	</fieldset>
</fieldset>
</form>
';
		return $form;
	}
	
	// SEND EMAIL
	public function sendEmail(){
		global $site, $captcha;

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$name = $_POST['name'];
			$number = $_POST['number'];
			$email = $_POST['email'];
			$message = $_POST['message'];
			$about =$_POST['about'];
			$terms = $_POST['terms'];
			
			
			$contact_email = $site->contact['email'];
			$contact_name = $site->contact['name'];
			$contact_number = $site->contact['phone'];

			if(!$name) $this->errmsg[]="You have not entered your name.";
			if(!$email) $this->errmsg[]="You haven't entered your email address.";
			else if (!$this->checkValidEmail($email)) $this->errmsg[]="Your email address is not valid.";
			if(!$message) $this->errmsg[]="You have not entered a message";
			else if ($message == $this->defmsg) $this->errmsg[] = "You have not entered a message";
			if (is_object($captcha) && !$captcha->valid) array_splice($this->errmsg, count($this->errmsg), 0, $captcha->errmsg);
			if (!$terms) $this->errmsg[] = "You must agree to the terms and conditions";
		
			//proceed if all required elements are present
			//if($name && email && $this->checkValidEmail($email) && $message){
			if(!count($this->errmsg)){

				$subject = "Contact from ".$site->title; 	// contact details of sender (required)
				$message = 'Message: '. $this->checkMessage($message) ."\n\n";
				if ($about) $message.="Contact about: ".$about."\n";
				$message .= "From: ". $name ."\n";
				$message .= "Email: ". $email ."\n";
				$message .= "Telephone: ". $number ."\n";
				$message .= "Form used at ". date("H:i, jS F Y");
				
				if($email) $headers = "From: $name<$email>\r\n";
				//$headers .= 'Bcc: iggyfred@yahoo.com'."\r\n";
				$headers .= 'X-Mailer: Treeline v3 using PHP/'.phpversion();
				
				$to = $site->contact['email'];
				if ($to) {
					//print "would mail($to, $subject, $message, $headers)<br>\n";
					if(@mail($to, $subject, $message, $headers)){
						// send automated reply
						$this->sendAutomatedReply($name, $email, $contact_email, $contact_name);
						return true;
					}
					else $this->errmsg[]="This website encountered a technical error and your email was not sent. Please try again.";
				}
				else {
					$this->errmsg[]="There is nobody configured for this site to send your message to!";
					// Should really bang an email to the global site admin to let them know.
				}
			}
			// otherwise there must be missing required fields so show errors
			else {
			}
			return false;
		}
	}

	function sendAutomatedReply($name, $email, $contact_email, $contact_name) {
		global $site;
		$reply = "Hello $name,
        
Thank you for emailing ".$site->name.". We will deal with your enquiry as soon we can.
 
Best wishes.
";

		$headers = "From: $contact_name<$contact_email>\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		$subject  = "Hello from ".$site->title;
		//print "would mail($email, $subject, $reply, $headers)<br>";
		@mail($email, $subject, $reply, $headers);
	}
}

?>