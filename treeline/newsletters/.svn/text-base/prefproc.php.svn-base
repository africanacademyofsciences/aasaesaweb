<?

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/preference.class.php");

	session_start();

	$nextPage = "prefbrowse/";
	$err=0;
	
	if($_POST["save"] == "Save"){

		if(isset($_POST["preference_id"]) && (($_POST["preference_id"] + 0) > 0)){
		// Update an existing preference
				
				$preference = new preference(addslashes($_POST["preference_id"]), $_POST["siteID"]);
				
				$preference->set('preference_title', $_POST["preference_title"]);
				$preference->set('preference_description', $_POST["preference_description"]);
				$preference->set('siteID', $_POST["siteID"]);
				
				if ($preference->isValid()) {
					if ($preference->updated) {
						$err = $preference->update() ? 1 : 2;
					}
				}
				else $err = 4;

		}else{
			// Create a new Preference
				$preference = new preference();
				
				$preference->set('preference_title', $_POST["preference_title"]);
				$preference->set('preference_description', $_POST["preference_description"]);
				$preference->set('siteID', $_POST["siteID"]);

				$err = $preference->createNew() ? 1 : 2;
				
				//echo $err;
		}

	} else if ($_GET['action'] == "delete") {
	
		// Delete id
		$preference = new preference(addslashes($_GET["preference_id"]));
		$err = ($preference->delete() ? 1 : 2);
		
	}else if ($_GET['action'] == "re_enable") {
	
		// Delete id
		$preference = new preference(addslashes($_GET["preference_id"]));
		$err = ($preference->re_enable() ? 1 : 2);
		
	}
	// Cancel pressed by really nothing to do
	else {

		;

	}

	$redirecturl = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])
								 . (dirname($_SERVER['PHP_SELF']) == "/" ? "" : "/") . $nextPage. ($err>0?"?err=$err":"");
	header("Location: ".$redirecturl);


?>