<?php
/* EMAIL MEMBER PASSWORD */

$forgot_email = read($_POST,'forgot_email',NULL);

// If an email address was passed look it up and email password to them.
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	// check data
	if($forgot_email){ // email entered

		$memberId = $member->checkEmail($forgot_email);
		if($memberId){ // is email registered?
			
			//send password
			// This should be added to the newsletter->sendText function?
			if($sent = $member->emailMember($memberId, 'forgotpassword')){
				$feedback = 'success';
				$message[] = 'Your password has been emailed to you';
				$message[] = 'Please use the form on the right of the page to log into your account';
			}
			else{
				$feedback = 'error';
				$message = 'A technical error has occurred please try again in a few moments';
			}
		}
		else{ // emial not registered
			$feedback = 'error';
			$message = 'Your email address is not registered with us.';
		}
	}
	else{ // No email entered
		$feedback = 'error';
		$message = "You didn't enter an email address";
	}
	
}
echo drawFeedback($feedback, $message);
?>

<h3>Forgotten password</h3>
<p>Please enter your email address below to recover your password</p>



<form class="form" role="form" action="<?=$site->link?>member-login/" method="post" style="width: 50%;" >
	<input type="hidden" name="action" value="forgotten-password" />
	<input type="hidden" name="fwd" value="<?=$fwd?>" />
    <div class="form-group form-group-sm">
        <label class="sr-only" for="email">Email address</label>
        <input class="form-control" type="email" id="email" placeholder="Enter email" name="forgot_email" value="<?=$forgot_email?>" />
    </div>
    <button type="submit" class="btn btn-default btn-block">Send password</button>
</form>   

