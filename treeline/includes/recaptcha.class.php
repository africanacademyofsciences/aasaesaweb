<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/recaptchalib.php');

class reCaptcha {

	var $publickey = "6LfDgtcSAAAAACT3bOn1ZVpwJOD5q60HXZLapso2";
	var $privatekey = "6LfDgtcSAAAAAOV8IWU9_3BaQzZXk4WFvpmtL-mW";
	var $valid = false;
	public $errmsg = array();
	public $formID;
	
	public function reCaptcha($id='') {
		//echo '<!-- initialising recaptcha -->'."\n";
		$this->formID = $id;
	}
	public function draw() {
		//echo '<!-- rC(r_g_h('.$this->publickey.')) -->'."\n";
		if (!$_SESSION[$this->formID]) {
	        $html = '
<fieldset id="recaptcha-holder">
	<input type="hidden" name="usarca" value="1" />
	'.recaptcha_get_html($this->publickey).'
</fieldset>
';
		}
		return $html;
    }


	public function validate() {	
		if (!$_SESSION[$this->formID]) {
			$resp = recaptcha_check_answer ($this->privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
				);
			if (!$resp->is_valid) {
				// What happens when the CAPTCHA was entered incorrectly
				$this->errmsg[] = "The reCAPTCHA wasn't entered correctly. Go back and try it again";
				//$this->errmsg[] = "reCAPTCHA said: ".$resp->error;
				$this->valid = false;
			} 
			else {
				$this->valid = true;
				$_SESSION[$this->formID]=1;
				// Your code here to handle a successful verification
			}
		}
		else $this->valid=true;
		
		return $this->valid;
	}	

}


?>