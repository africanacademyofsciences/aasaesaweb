<?

	// This should sit on every editable page --
	// it checks if the user's logged in,
	// establishes what they're trying to do [view, preview, create, edit or publish]
	// and checks they're allowed to do it

//	session_start();

	$userID = read($_SESSION,'userid',0);
	$user = new User();
	$user->loadById($userID);

	if ($user->getStatus() != 'logged in') {
//		echo "NOT LOGGED IN: " . $user->getStatus() . $user->getID() .";";
		$MODE = 'view';
	}	
	else {
//		echo "LOGGED IN";
		$action = read($_REQUEST,'action','view');
		$level = $page->getPermissions($user->getGroup());

		define('READ', 1);
		define('WRITE', 2);
		define('DELETE', 4);
		define('PUBLISH', 8);
	
		$canread = ($level & READ) == READ ? true : false;
		$canwrite = ($level & WRITE) == WRITE ? true : false;
		$canpublish = ($level & PUBLISH) == PUBLISH ? true : false;		
	
		if ($action == 'view' || $action == 'preview') {
			if ($canread) {
				$MODE = $action;
			}
			else {
				die ("Permission denied: no read access on page {$page->getGUID()} for user {$user->getID()}");
			}
		}
		else if ($action == 'create' || $action == 'edit') {
			if ($canwrite) {
				$MODE = $action;
			}
			else {
				die ("Permission denied: no write access on page {$page->getGUID()} for user {$user->getID()}");
			}
		}
		else if ($action == 'save') {
			if ($canwrite) {
				$MODE = 'save';
				// Write to the database here
				// no -- hang on -- surely we write to the database within page.php itself?
				// How would this class know what to do with the content being posted?
			}
			else {
				die ("Permission denied: no save [write] access on page {$page->getGUID()} for user {$user->getID()}");
			}
		}
		else if ($action == 'publish') {
			// Do we publish from the page directly? Or not?
			// See above -- I think we do, but I think we have to do so from within page.php and not this class
			if ($canpublish) {
				$MODE = 'publish';
				// update the database here
			}
			else {
				die ("Permission denied: no publish access on page {$page->getGUID()} for user {$user->getID()}");
			}
		}		
		else {
			$MODE = 'view';
		}
		
	}

?>