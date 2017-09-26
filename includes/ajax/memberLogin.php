<?php 
	$email = ($_POST['email']) ? $_POST['email'] : ''; 
	
	// Check fwd URL is valid
	if ($_SERVER['REQUEST_METHOD']=="GET") {
		//$fwd = $_SERVER['REQUEST_URI'];
		if (substr($fwd, -6, 6)=="logout") $fwd = '';
	}
	else $fwd = $_POST['fwd'];
	
	
	$formclass="form";
	$buttonclass="btn-default btn-block";
	
	if ($loginforminline) {
		$formclass = "form-inline";
		$buttonclass = "btn-primary btn-sm";
	}
	
	//$fellowText = '';
	
	if ($page->getGUID() == 1)
	{
		$fellowText = 'Fellow';
	}
?>


<form class="login-form <?=$formclass?>" role="form" action="<?=$site->link?>member-login/" method="post">
	<input type="hidden" name="fwd" value="<?=$fwd?>" />
    <div class="form-group form-group-sm">
        <label class="sr-only" for="email">Email address</label>
        <input type="email" class="form-control" id="email" placeholder="Enter email" name="email" value="<?=$email?>" />  
    </div>
    <div class="form-group form-group-sm">
        <label class="sr-only" for="password">Email address</label>
    	<input type="password" name="password" class="form-control" id="Password" placeholder="Enter password">
    </div>
    <input type="hidden" name="action" value="login" />
    <input type="hidden" name="treeline" value="login" />
    <button type="button" class="btn btn-link btn-sm login-forgot" onclick="document.location='<?=$site->link?>member-login/?action=forgotten-password';">Password reminder</button>
    <button type="submit" class="btn <?=$buttonclass?>"><?=$fellowText?> Sign in</button>
</form>   

<p class="login-forgot">
	<a href="<?=$site->link?>member-login/?action=forgotten-password">Password reminder</a>
    <!-- <a href="<?=$site->link?>enewsletters/">Register</a> -->
    <?php
	if ($page->private && $page->private!=2) ;
	else {
		?>
         | 
	    <a href="/recognising-excellence/the-aas-fellowships/recognising-excellence-/">Become a fellow</a>
    	<?php
	}
	?>
    
</p>

<!--
<form id="loginForm" class="form-inline" method="post" action="<?=$site->link?>member-login/">

	<input type="hidden" name="fwd" value="<?=$fwd?>" />
    <div class="form-group form-group-sm">
	    <label class="sr-only" for="loginEmail1">Email address</label>
        <input class="form-control" type="email" value="<?php echo $email; ?>" id="loginEmail" name="email" placeholder="Your email" />
    </div>

    <div class="form-group form-group-sm">
	    <label class="sr-only" for="loginPassword1">Password</label>
    	<input type="password" name="password" class="form-control" id="loginPassword1" placeholder="Your password">
    </div>

    <input type="hidden" name="action" value="login" />
    <input type="hidden" name="treeline" value="login" />
    <button type="submit" class="btn btn-link btn-sm" onclick="document.location='<?=$site->link?>member-login/?action=forgotten-password';">Password reminder</button>
    <button type="submit" id="f_submit" class="btn btn-primary btn-sm"><i class="ion-log-in"></i> Sign in</button>

</form>

    <p id="loginForm-options" style="padding-left:0px;">
	    <a class="register" href="<?=$site->link?>contact-details/?type=member"><?=$page->drawLabel("pan_log_register", "Register")?></a>
    </p>
-->
