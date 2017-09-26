<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="robots" content="noindex,nofollow"><title>Treeline | Home</title>
<link rel="stylesheet" media="all" type="text/css" href="styles/reset.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/fonts-min.css">
<link rel="stylesheet" media="all" type="text/css" href="styles/global.css">
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
			<h2 id="pagetitle">My Tasks</h2>   
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
				<h2>Step 1 of 2: Choose tasks allocated to you</h2>
			</div>

			<table id="myTasks">
				<thead>
					<tr>
						<th scope="col">Task</th>
						<th scope="col">Date</th>
						<th scope="col">Allocated To</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="task">
							<a href="">Note to you from Stuart Johnson</a>
						</td>
						<td class="taskDate">
							19th June 08
						</td>
						<td class="taskAllocation">
							You
						</td>
					</tr>
					<tr>
						<td class="task">
							<a href="">Awaiting publication</a>
						</td>
						<td class="taskDate">
							14th June 08
						</td>
						<td class="taskAllocation">
							Any superuser
						</td>
					</tr>
					<tr>
						<td class="task">
							<a href="">Missing image</a>
						</td>
						<td class="taskDate">
							8th June 08
						</td>
						<td class="taskAllocation">
							Any superuser
						</td>
					</tr>
				</tbody>
			</table>
			
			
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