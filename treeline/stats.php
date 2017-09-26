<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
	
	// PAGE specific HTML settings
	
	$css = array(); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Statistics';
	$pageTitle = 'Statistics';
	
	$pageClass = 'statistics';
	
	$show = read($_GET,'show',false);
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
	<div id="primary_inner">
	<? if( !$show ){ ?>
        <h2>Website statistics</h2>
        <p>Your website's statistics are stored using Google Analytics.</p>
        <p><a href="http://www.google.com/analytics/">http://www.google.com/analytics/</a></p>
        <h3>What is Google Analytics?</h3>
        <p>Google Analytics is a free software package that tracks your website and can tell you lots of information about the how people are using your website, where your visitors are coming from and what problems they're facing.</p>
        <p>It's a pretty complex piece of software so if you're new to it it can be a little overwhelming but, don't worry, there's plenty of information out there about how to use Google Analytics:</p>
        <ul>
          <li><a href="http://www.google.com/analytics/media/report_tour/feature_tour.html">Official video tour</a></li>
          <li><a href="http://www.conversationmarketing.com/2007/02/google_analytics_video_tutoria.htm">Google Analytics Video Tutorial 2: Essential Stats</a></li>
          <li><a href="http://www.conversationmarketing.com/2007/02/google_analytics_tutorial_3_di.htm">Google Analytics Tutorial 3: Digging Deeper</a></li>
          </ul>
        <h3>Customising the data</h3>
        <p>Google Analytics works in such a way that with a little work practically anything can be tracked. By default it tracks pages but if you ask us nicely we can set it up so it tracks specific user tasks e.g. how many make it to your donation/sales page and where if they don't make it all the way where do they leave.</p>
        
        <h2>Poll Statistics</h2>
        <p>The poll panels used around your site have their results here for you to view at any point.  
        Once poll panels have been deleted, their results will no longer be available.</p>
        <p>Click here to <a href="?show=poll">view poll statistics</a>.</p>
    <? }else{ ?>
        <h2>Poll Statistics</h2>
        <p>The poll panels used around your site have their results here for you to view at any point.  
        Once poll panels have been deleted, their results will no longer be available.</p>
        <?
			include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/poll.class.php");
			$poll = new Poll();
			$polls = $poll->getSitePolls($siteID); // list of polls...
			//echo '<pre>'. print_r($polls,true) .'</pre>';

			foreach($polls as $row){
		?>
        	<div class="poll_results">
            	<h3><?= $row['title'] ?></h3>
                <p><strong>Question: </strong><?= $row['question'] ?></p>
                <p><strong>Total votes: </strong><?= $row['total_votes'] ?></p>
            <ul id="poll_results">
            <? foreach( $row['answers'] as $id => $answer ){ ?>
            	<li<?= ( $answer['default']==1 ? ' class="poll_correct_answer"' : '' ) ?>>
					<span class="poll_answer">Answer: <?= $answer['text'] ?></span>
                    <span class="poll_votes">Votes: <?= $answer['votes'] ?> (<?= $answer['percentage'] ?>%)</span>
                    <!--//
                    <div class="poll_result_bar">
    					<span class="colourBar" style="width: <?//=$answer['percentage']?>% !important"></span>
    					<span class="whiteBar" style="width: <?//=(100 - $answer['percentage'])?>% !important"></span>
    				</div>
                    /-->
                </li>
            <? } ?>
            </ul>
            </div>
        <? } ?>
            	
    <? } ?>
    </div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>