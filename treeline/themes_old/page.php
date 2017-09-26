<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="robots" content="noindex,nofollow"><title>Treeline | Home</title>
<link rel="stylesheet" media="all" type="text/css" href="styles/reset.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/fonts-min.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/global.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/forms.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/page.css">
<link rel="Shortcut Icon" href="/treeline/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="../behaviour/jquery.js"></script>
</head>

<body id="treelineCMS" class="login">
<form action="" method="post" enctype="multipart/form-data">
	<ul id="shortcuts" class="hide">
	  <li><a href="#primarycontent" accesskey="2">Skip to content</a></li>
	  <li><a href="#menu">Skip to navigation menu</a></li>
	   <li><a href="#footer">Skip to Treeline system menu</a></li>
	  <li><a href="/treeline/accessibility/" accesskey="0">Accessibility Statement</a></li>
	</ul>
	
	<div id="holder">
		<div id="header">
			<h1 id="logo"><a href="/treeline/" title="return to your Treeline home-page">Treeline</a></h1>    
			<h2 id="pagetitle">Home</h2>   
			<ul id="loginDetails">
				<li>Your are signed in as <strong>Russell Jones</strong></li>
				<li>Your access level is <strong>Superuser</strong></li>
				<li>Your are editing the <a href="#eng">English</a> site at <a href="#site">clientdomain.co.uk</a></li>
			</ul>
		</div>
		
		<div id="sidebar">
			<ul id="mainMenu">
				<li class="selected">Home</li>
				<li><a href="">My tasks</a> (7)</li>
				<li><a href="">Create content</a></li>
				<li><a href="">Manage existing content</a></li>
				<li><a href="">Manage asset libraries</a></li>
				<li><a href="">Manage site structure</a></li>
			</ul>
			<ul>
				<li><a href="">Email newsletters</a></li>
			</ul>
			<ul>
				<li><a href="">Manage microsites</a></li>
				<li><a href="">Manage languages</a></li>
				<li><a href="">Manager events</a></li>
			</ul>
			<ul>
				<li><a href="">Settings</a></li>
				<li><a href="">Access rights</a></li>
				<li><a href="">Statistics</a></li>
			</ul>
			<ul>
				<li><a href="">Help and support</a></li>
			</ul>
			<ul>
				<li><a href="">Sign out</a></li>
			</ul>
		</div>
		
		<div id="primarycontent">
			<div id="stepHeader">
				<h2>Step 1 of 3: Set-up the page attributes</h2>
			</div>

			<div class="contentBox box_blue">
				<div class="box_blue_top">
					<h3>Choose what type of page you want to create</h3>
					<a class="getHelp" href="help/#sign-in">Get help with this</a>
				</div>
				<p>Check the box next to the type of page you want to create.  You <strong>cannot</strong> change this later.</p>
				<ul id="pageTypes">
					<li id="ptContent">
						<label for="ptContent">Content page</label>
						<input type="radio" name="pageType" id="ptContent" />
					</li>
					<li id="ptLanding">
						<label for="ptLanding">Landing page</label>
						<input type="radio" name="pageType" id="ptLanding" />
					</li>
					<li id="ptNews">
						<label for="ptNews">News page</label>
						<input type="radio" name="pageType" id="ptNews" />
					</li>
					<li id="ptMedia">
						<label for="ptMedia">Media gallery</label>
						<input type="radio" name="pageType" id="ptMedia" />
					</li>
				</ul>
				<div class="box_blue_footer">
				</div>
			</div>
			

			<div class="contentBox box_blue">
				<div class="box_blue_top">
					<h3>Choose names, labels and tags for your page</h3>
					<a class="getHelp" href="help/#sign-in">Get help with this</a>
				</div>
				
				<p>These details will make your page easier for search engines to find.  You <strong>can</strong> change these later.</p>
				
				<fieldset id="pageDetails">
					<div class="formField">
						<label for="pageTitle">Page title<a href=""><img src="img/icons/popup.gif" alt="help?" width="9" height="8" /></a></label>
						<input type="text" name="pageTitle" id="pageTitle" value="" maxlength="60" />
					</div>
					<div class="formField">
						<label for="pageTag">Add tags<a href=""><img src="img/icons/popup.gif" alt="help?" width="9" height="8" /></a></label>
						<input type="file" name="pageTag" id="pageTag" />
						<div id="tagList">
							<a href="">tag1 (20)</a> <a href="">tag3 (20)</a> <a href="">tag4 (2)</a> <a href="">tag5 (19)</a>
							<a href="">tag2 (12)</a> <a href="">tag1 (20)</a> <a href="">tag1 (20)</a> <a href="">tag1 (20)</a>
						</div>
					</div>
					<div class="formField">
						<label for="pageDesc">Add a description<a href=""><img src="img/icons/popup.gif" alt="help?" width="9" height="8" /></a></label>
						<textarea name="pageDesc" id="pageDesc"></textarea>
					</div>
					<div class="formField buttons">
						<input type="button" name="cancel" class="cancel" value="Cancel" />
						<input type="submit" name="" class="submit" value="Next step" />
					</div>
				</fieldset>
							
				<div class="box_blue_footer">
				</div>
			</div>
		
		
			<div class="contentBox box_grey lastBox">
				<div class="box_grey_top">
					<h3>Little extras</h3>
					<a class="getHelp" href="help/#sign-in">Get help with this</a>
				</div>
				<fieldset id="littleExtras">
					<p>Some extra options you can use for this page.</p>
					<div class="formField">
						<label for="extraShortcut">Page shortcut<a href=""><img src="img/icons/popup.gif" alt="help?" width="9" height="8" /></a></label>
						<input type="text" name="extraShortcut" id="extraShortcut" value="" maxlength="60" />
					</div>
					<div class="formField">
						<label for="extraPubDate">Change the publication date<a href=""><img src="img/icons/popup.gif" alt="help?" width="9" height="8" /></a></label>
						<?
						$thisDay = date('j');
						$thisMonth = date('n');
						$thisYear = date('Y');
						?>
						<select name="datePubDay">
						<? for($i=1; $i<=31;$i++){ ?>
							<option<?= ($i==$thisDay ? ' selected="selected"' : '') ?>><?= $i ?></option>
						<? } ?>							
						</select>
						<select name="datePubMonth">
						<? for($i=1; $i<=12; $i++){ ?>
							<option value="<?= $i ?>"<?= ($i==$thisMonth ? ' selected="selected"' : '') ?>><?= date('F',mktime(0,0,0,$i,'1',date('Y'))) ?></option>
						<? } ?>					
						</select>
						<select name="pubDateYear">
						<? for($i=date('Y'); $i<=(date('Y')+5);$i++){ ?>
							<option<?= ($i==$thisYear ? ' selected="selected"' : '') ?>><?= $i ?></option>
						<? } ?>
						</select>
					</div>	
				</fieldset>
					
				<div class="box_grey_footer">
				</div>
			</div>
			
			
			<br style="clear:both" />
		</div>

	
		<div id="footer">
			<ul id="links">
				<li><a href="terms">Terms &amp; Conditions</a></li>
				<li><a href="about">About Treeline</a></li>
				<li><a href="feedback">Feedback</a></li>
				<li><a href="requests">Function requests</a></li>
				<li class="last"><a href="contact">Contact Us</a></li>
			</ul>
			<ul id="credits">
				<li class="last"><a href="http://ichameleon.com/"><abbr title="Copyright">&copy;</abbr>2008 Ichameleon <abbr title="Limited">Ltd</abbr></a></li>
			</ul>
		</div>
	</div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$("div#sidebar").height( $("div#primarycontent").height() );
});
</script>
</body>
</html>