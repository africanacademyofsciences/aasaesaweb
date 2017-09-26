<?php
// A poll panel has been submitted
// Log it to the database 
include_once($_SERVER['DOCUMENT_ROOT'] .'/treeline/includes/poll.class.php');
	
$poll = new Poll($page->getGUID());
$polldata = $poll->structure;
	
if ($_SERVER['REQUEST_METHOD'] == "POST") {

	if (read($_POST,'poll_vote', 0) == $page->getGUID()){
	
		foreach( $_POST as $key => $value ){
			if( substr_count($key,'poll')>0 ){
				${$key} = $value;
			}
		}

		$totalVotes = (${'poll_totalvotes_'.$poll_answer}+1);
		
		if( $poll->addVote($poll_guid, $poll_answer, $totalVotes) ){
			
			//echo 'Thanks for voting!';
			$poll->loadByGUID($page->getGUID());
			$polldata = $poll->structure;
			$_SESSION['voted_poll_'.$page->getGUID()] = 1;
		}
	}
}

?>