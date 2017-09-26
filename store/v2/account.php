<?php

	// Header image
	$header_img = new HTMLPlaceholder();
	$header_img->load($siteID, 'header_img');
	if (!$header_img->draw()) {
			$header_img->load($siteData->primary_msv, 'header_img');
			if (!$header_img->draw()) {
					$header_img->load(1, 'header_img');
			}
	}
	$header_img->setMode("view");

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode',false);
	$message = false;
	$field_error = array();
	$accountExists=false;
	
	if( $basket->getGrandTotal()==0 ){
		$_POST = false;
		$mode = 'emptyBasket';
		$feedback = 'error';
		$message = 'You have nothing in your shopping basket';
	}
	
	if( isset($_POST) && $_POST ) {
		extract($_POST);
		
		//echo '<pre>'. print_r($_POST,true) .'</pre>';
		
		// existing member
		if( $cust_email && $cust_pass && $login ){
			if( $account->validate($cust_email,$cust_pass) ){
				//echo '<pre>'. print_r($account->properties,true) .'</pre>';
				setcookie('memberID',$account->properties->member_id,$expires,'/');
				redirect('/shop/checkout/?mode=elog'.($_REQUEST['subscribe']==1?"&subscribe=1":""));
			}else{
				$feedback = 'error';
				$message = 'Your email and/or password were not found in our system';
			}
		}
		
		// register a new member
		if( $mode=='register' && $register>'' ){
			//echo 'REGISTER!<br />';
			//echo 'message: '. $message .'<br />';
			$fields_required = array('cust_title', 'cust_fname','cust_lname','cust_email','cust_pass','cust_cpass');
			// first, check we have what we need...
			foreach( $fields_required as $field ){
				if( $field<='' ){
					$field_error[] = $field;
					$feedback = 'error';
					$message = 'Please check that all fields have been completed';
				} 
			}

			if( !$message && ($cust_pass!= $cust_cpass) ){
				$feedback = 'error';
				$message = 'Your passwords don\'t match.  Please make sure your password and the confirmation are the same';
			}
			
			if( !$message && ($account->memberExists($cust_email)) ){
				$feedback = 'error';
				$message = 'An account already exists with that email address';
				$accountExists=true;
			}
			
			
			
			if( !$message || $message<='' ){
				//echo 'processing registration...<br />';
				$properties->email =  $cust_email;
				$properties->firstname = $cust_fname;
				$properties->surname = $cust_lname;
				$properties->password = $cust_pass;
				$properties->cust_title = $cust_title;
				if( $account->create( $properties ) ){
					setcookie('memberID',$account->properties->member_id,$expires,'/');

					include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
					include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
					include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
					$emailOut = new Newsletter;
					$emailOut->sendText($account->properties->email, "STORE_REGISTER", array("NAME"=>$cust_fname));

					redirect('/shop/checkout/?mode=nlog'.($_REQUEST['subscribe']==1?"&subscribe=1":""));					
				}else{
					$feedback = 'error';
					$message = 'Your account details could not be saved';
				}
			}
			//echo 'message: '. $message .'<br />';
		}// end register
		
		
		if( $mode=='reminder' ){
			if( $account->memberExists($reminder_email,true) ){
				$to = $account->properties->firstname.' '.$account->properties->surname.'<'. $account->properties->email .'>';
				$storeEmail = 'fundraising@minesadvisorygroup.org';
				$from = $site->title.' Store <'. $storeEmail .'>';
				$subject = 'Your '.$site->title.' membership reminder';
				$headers = "From: ".$from."\n";
				$headers .= "Return-Path: ". $storeEmail ."\n";
				$headers .= "Reply-To: ". $storeEmail ."\n";
				$headers .= "X-Mailer: Treeline v3"; // Treeline branding everywhere!;
				$msg = "You have recently requested that we send you a password reminder for the new shop.\n\n";
				$msg .= "Your password is: ". $account->properties->password;
				$msg .= "\n\nVisit the new shop at: http://".$_SERVER['SERVER_NAME']."/shop\n\n\n";
				$msg .= "Kind Regards\n\nThe Fundraising Team";
				
				//echo "mail($to,$subject,$msg,$headers)<br />";
				
				if( mail($to,$subject,$msg,$headers) ){
					$feedback = 'success';
					$message = 'Your reminder has been sent';
					$mode=false;
				}else{
					$feedback = 'error';
					$message = 'Your email address could not be found in our database';
				}
			}else{
				
			}
		}
	}
	//print_r($account->properties);
	
	// Panels
	/*
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);
	*/
	$tags = new Tags();
	
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page','store','forms','basket','checkout'); // all attached stylesheets
	if($page->style != NULL){
		$css[] = $page->style;
	}
	
	$extraCSS = '';

	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours

	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
?>	

    <div id="primarycontent">
        <?=drawFeedback($feedback, $message)?>
		<div id="basket">
			<h1 class="pagetitle"><?= $page->getTitle() ?></h1>
			
			<? if( $mode=='reminder' ){ ?>
			<form action="/shop/account/" method="post" id="existing" class="checkout">
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<input type="hidden" name="mode" value="reminder" />
				<fieldset>
					<legend>Password reminder</legend>
					<p class="instructions">Please enter your registered email address and we'll send your password to your inbox.</p>
					<label for="reminder_email">Email address</label>
					<input type="text" maxlength="150" name="reminder_email" id="reminder_email" />
					<button type="submit" name="login" value="1">Send reminder</button>				
				</fieldset>
			</form>				
			<? } ?>
			
			<? if( ($cust_email && $cust_pass) || !$mode ){ ?>

			<form action="/shop/account/" method="post" id="existing" class="checkout">
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<fieldset>
					<legend>Existing customers</legend>
					<p class="instructions">Have you used any of MAGs online services before?</p>
					<label for="cust_email">Email address</label>
					<input type="text" maxlength="150" name="cust_email" id="cust_email" />
					<label for="cust_pass">Password</label>
					<input type="password" maxlength="20" name="cust_pass" id="cust_pass" />
					<a href="/shop/account/?mode=reminder" id="forgottenPass">Forgotten your password?</a>
					<button type="submit" name="login" value="1">Log-in</button>				
				</fieldset>
			</form>

			<? } ?>
			
			<? if( $mode=='register' && $register ){ ?>
			<form action="/shop/account/" method="post" id="register" class="checkout">
				<input type="hidden" name="mode" value="register"/>
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<fieldset>
					<legend>Create a new account</legend>
					<p class="instructions">Please fill in all of these fields.</p>
					<label for="cust_title">Title</label>
                    <select name="cust_title" id="cust_title">
                    	<option>Mr</option>
                    	<option>Mrs</option>
                    	<option>Miss</option>
                    	<option>Dr</option>
                    	<option>Rev</option>
                    	<option>Prof</option>
                    </select>
					<label for="cust_fname">First name</label>
					<input type="text" name="cust_fname" id="cust_fname" maxlength="50" value="<?= $cust_fname ?>" class="<?= (in_array('cust_fname',$field_error) ? 'error' : '') ?>" />
					<label for="cust_lname">Surname</label>
					<input type="text" name="cust_lname" id="cust_lname" maxlength="50" value="<?= $cust_lname ?>" class="<?= (in_array('cust_lname',$field_error) ? 'error' : '') ?>" />
					<label for="cust_email">Email address</label>
					<input type="text" maxlength="150" name="cust_email" id="cust_email" value="<?= $cust_email ?>" class="<?= (in_array('cust_email',$field_error) ? 'error' : '') ?>" />
					<label for="cust_pass">Password</label>
					<input type="password" maxlength="20" name="cust_pass" id="cust_pass" class="<?= (in_array('cust_pass',$field_error) ? 'error' : '') ?>" />
					<label for="cust_cpass">Confirm Password</label>
					<input type="password" maxlength="20" name="cust_cpass" id="cust_cpass" class="<?= (in_array('cust_pass',$field_error) ? 'error' : '') ?>" />
					<button type="submit" name="register" value="1">Create Account</button>	
				</fieldset>
			</form>
			<? } ?>


		</div>
    </div>
    <div id="secondarycontent">
    </div>
    
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>
