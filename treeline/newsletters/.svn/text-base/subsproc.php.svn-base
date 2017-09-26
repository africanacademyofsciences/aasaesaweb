<?

	//ini_set("display_errors", 1);
	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");

	session_start();

	$nextPage = "subsbrowse/";
	$err=0;
//	print_r($_SESSION);
	$siteID = $_SESSION['treeline_user_site_id'];
	
	if($_POST["save"] == "Save"){

		if(isset($_POST["id"]) && (($_POST["id"] + 0) > 0)){
		// Update an existing subscriber

				$subscriber = new subscriber(addslashes($_POST["id"]));
				
				$subscriber->set('email', $_POST["email"]);
				$subscriber->set('firstname', $_POST["firstname"]);
				$subscriber->set('surname', $_POST["surname"]);
//				$subscriber->set('fullname', $_POST["fullname"]);
				if ($subscriber->isValid()) {
					if ($subscriber->updated) {
						$err = $subscriber->update() ? 1 : 2;
						//print "updated ($err) 1=success<br>".$db->last_error;
					}
				}
				else $err = 4;

		}

	} 
	// Cancel pressed by really nothing to do
	else {

		;

	}

	$redirecturl = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])
								 . (dirname($_SERVER['PHP_SELF']) == "/" ? "" : "/") . $nextPage. ($err>0?"?err=$err":"");
	header("Location: ".$redirecturl);


?>