<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$referer.=(strpos($referer, "?")?"&":"?");
	$action = read($_POST,'treeline','');
	if ($_POST['post_action']) $action = $_POST['post_action'];
	$redirect = true;
	//print "got(".print_r($_POST, true).") action($action)<br>\n";
	
	if ($action == 'Save changes') {

		$content = new HTMLPlaceholder();
		$content->setMode($page->getMode());
		$content->load($panelGUID, 'panelcontent');
		$content->save();
		
		$page->save(true);	

		// Content is saved so redirect the user
		//$feedback .= '&feedback=success&message='.$page->getLabel("tl_paedit_err_saved", true);
		//$publish_redirect = '/treeline/panels/?action=saved&guid='.$page->getGUID();
		include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
		if($user->drawGroup() != 'Author') $redirectURL='/treeline/panels/?action=saved&guid='.$panelGUID;
		else $redirectURL='/treeline/panels/?action=saved';
		if ($redirect) redirect($redirectURL);
		//else print "Would go to($redirectURL)<br>\n";
	} 
	
	if ($action == 'Discard changes') {

		$page->releaseLock($_SESSION['treeline_user_id']);			
		$redirectURL = '/treeline/panels/?action=discarded';
		//print "ref($redirectURL)<br>\n";
		if ($redirect) redirect ($redirectURL);
	}
}
?>