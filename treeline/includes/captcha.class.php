<?php

class Captcha {

	public $errmsg = array();
	public $valid = true;		// Default to always valid, only changes if we submit a captcha form
		
	
	public function Captcha($test) {
		global $site;
		
		$this->md5code = md5(uniqid(time()));

		if ($test) {
			$this->valid = false;
			//print "C(".$_POST['captcha'].") sess(".$_SESSION['securimage_code_value'].")<br>\n";	
			// Check if a capture has been initiated for this session
			if ($_SESSION['securimage_code_value']) {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/treeline/includes/securimage.class.php');
				$secur = new securimage();
				if (!$secur->check($_POST['captcha'])) {
					$this->errmsg[]="You have not entered the correct comparison code, please try again";
					//print "captcha comp failed<br>\n";
				}
				else $this->valid = true;
			}
		}		
	}
	
	
	
	public function drawForm() {
	
        $captcha_style = '';
        if(!$this->valid) {
            $captcha_style = 'border:2px solid red';
        }

		$html = '

	<style type="text/css">
		div.d-captcha {
		}
			div.d-captcha img,
			div.d-captcha span,
			div.d-captcha input {
			}
	</style>

	<div class="form-group">
		<label for="commentEmail" class="sr-only">Confirm text below</label>
		<div class="d-captcha">
			<input name="captcha" type="text" id="captcha" class="form-control" placeholder="Enter text below" maxlength="5" style="'.$captcha_style.'" />
			<div>
			<img src="/behaviour/ajax/securimage_show.php?sid='.$this->md5code.'" id="securimage" align="absmiddle" />
			</div><div>
			<p><span id="f_repeat">Cannot read text? <a href="#" onclick="document.getElementById(\'securimage\').src = \'/behaviour/ajax/securimage_show.php?sid=\'+Math.random(); return false">Show another</a></span></p>
			</div>
		</div>
	</div>
		
';

		return $html;
		
	}



}

?>