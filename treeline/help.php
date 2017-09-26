<?php

//ini_set("display_errors", 1);
$st=time();
$msg.=(time()-$st)." Starting help <br>\n";

// If this page is loaded in popup mode we need to skip menus etc.....
$pp = $is_help_popup=$_REQUEST['pp']==1;
$noMenu=$is_help_popup;
$noUserInfo=$is_help_popup;
$noTitle=$is_help_popup;
	
//print "popup($is_popup)<br>";
$search_term=$_REQUEST['ssearch'];
$action=$_REQUEST['action'];
	
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

$help_id=$_GET['id'];
$help->loadByID($help_id);
	
$msg.=(time()-$st)." Created object<br>\n";
//print $msg; exit();

// variables needed for the page
$section = read($_REQUEST,'section','');
$orderBy = read($_REQUEST,'sort','title'); 
$currentpage = read($_REQUEST,'page',1); 	
//$perPage = read($_REQUEST,'show','');

// set up page title

// PAGE specific HTML settings
$css = array("help", "forms"); // all CSS needed by this page
if ($is_help_popup) {
	$css[]="helpp";
	$css[]="editor_notes";
	$extraCSS = '
	
	'; // extra on page CSS
}
	
$js = array(); // all external JavaScript needed by this page
$extraJS = '

function bookmark(url, description)
{
	netscape="Netscape User\'s hit CTRL+D to add a bookmark to this site."
	if (navigator.appName==\'Microsoft Internet Explorer\')
	{
		window.external.AddFavorite(url, description);
	}
	else if (navigator.appName==\'Netscape\')
	{
		alert(netscape);
	}
}

function printpage() {
	window.print();  
}
//-->


'; // extra on page JavaScript

	// Page title	
	$pageTitleH2 = ($section) ? 'Help and support : '.ucwords($section) : 'Help and support';
	$pageTitle = ($section) ? 'Help and support : '.ucwords($section) : 'Help and support';
	
	if ($is_help_popup) {
		$pageTitleH2 = '<a href="?id='.($db_admin->get_var("select id from help_texts where title='Quick start guide'")).'&pp=1&ssearch=Quick start guide" style="margin-left:200px;font-size:55%;">Quick start guide</a>';
		$functionlink = '?id='.($db_admin->get_var("select id from help_texts where title='Function request'")).'&pp=1&ssearch=Function request';
		$buglink = '?id='.($db_admin->get_var("select id from help_texts where title='Bug report'")).'&pp=1&ssearch=Bug report';
	}
	
	$pageClass = 'help';
	

if (!$pp) include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=($_SESSION['treeline_user_encoding']?$_SESSION['treeline_user_encoding']:"iso-8859-1")?>" />
<meta name="robots" content="noindex,nofollow" />
<title>&nbsp;</title>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonCSS.inc.php"); ?>
<script type="text/javascript">
</script>
</head>

<body onload="javascript:window.focus();">

<h1 class="notes-header">
	<span>Treeline help</span>
	<a href="<?=$help->helpLinkByID(90)?>" class="tl-help-link">Quick start guide</a>
</h1>
<?php

}
		
?>
<div id="primarycontent" class="column">
	<div id="primary_inner">
    
    
<script type="text/javascript" src="http://assets.freshdesk.com/widget/freshwidget.js"></script>
<style type="text/css" media="screen, projection">
                @import url(http://assets.freshdesk.com/widget/freshwidget.css);
</style>
<iframe class="freshwidget-embedded-form" id="freshwidget-embedded-form" src="http://treeline.freshdesk.com/widgets/feedback_widget/new?&widgetType=embedded&submitThanks=Thank+you+for+your+message.+We'll+review+it+and+get+back+to+you.&screenshot=no" scrolling="no" height="600px" width="100%" frameborder="0" >
</iframe>

<?php
/*    
    	<div id="tabber" class="tabber">
          
		<?php 
		$msg.=(time()-$st)." action($action) id($help_id)<br>\n";

		if (!$action && !$help_id && !$pp) { 
			?>
        	<div id="search_top"></div>

            <form id="search_form" method="get" action="">
            <fieldset>
                <input type="hidden" name="pp" value="<?=$is_help_popup?>" />
                <input type="hidden" name="action" value="search" />
                <h2 style="padding-top:0px;padding-bottom:10px;">Search for help</h2>
                <div class="field">
                    <input type="text" class="text" name="ssearch" autocomplete="off" id="ssearch" style="width:240px;height:18px;" />
                    <button type="submit" class="submit" style="width:120px;margin:0.5em 0pt 0.5em 8px;">Search</button>
                </div>
                <div style="width: 30px; height: 30px; float: left;">
                    <div id="eggTimer" style="display: none; 23px; height: 23px; padding: 3px;"><img src="/treeline/ajax/hourglass.png" alt="" width="16" height="16" /></div>
                </div>
                <div class="auto_complete" id="ssearch_auto_complete" style="display:none;"></div>
            </fieldset>
            </form>
			<script type="text/javascript" src="/behaviour/ajax/script.aculo.us/prototype.js" ></script>
            <script type="text/javascript" src="/behaviour/ajax/script.aculo.us/scriptaculous.js" ></script>
			<script type="text/javascript">
            new Ajax.Autocompleter("ssearch", "ssearch_auto_complete", "/behaviour/ajax/help_tags.php", {indicator: "eggTimer"});
            </script>

            <div id="search_bot"></div>
            
            <div class="tabbertab" style="width:405px;">
            <p><b>About Treeline Help</b></p>
            <p>To get help, search for a keyword. </p>
            <p>For instance, if you need help creating a new page, try searching for the term <b><i>create</i></b> or <b><i>new page</i></b>.</p>
            <p>If you can’t find the help you need, click the link to <a href="/treeline/help/?action=request&amp;pp=<?=$is_help_popup?>">request a help article</a> at the bottom of each page.</p>
			<p>If you’re new to Treeline, try reading the Treeline <a href="/treeline/help/?id=90" title="Quick start guide">Quick Start Guide</a>.</p>
            </div>
                
        <?php 
		} 
		else if ($action=="request") { 
			?>
            <h2>More help?</h2>
            <?php 
            if ($_POST['newtopic']) { 
                $query="select client_id from clients where client_url='http://".$_SERVER['SERVER_NAME']."/'";
                $clientid=$db_admin->get_var($query);
                $query="insert into items (item_type, client_id, title, description, date_added, product_id)
                        values (2, ".($clientid+0).", 'Help text request', '".mysql_real_escape_string($_POST['newtopic'])."', now(), 0)";
				// print "$query<br>";
                $db_admin->query($query);
                echo drawFeedback('success', '<p class="feedback">Your request for a new topic has been logged</p>');
            }
            ?>
            
            <p>Is there an aspect of Treeline that still flumoxes you? Please give as much information below regarding the problem you are having and what kind of assistance would best help you understand this aspect of the system</p>
            <div id="search_top"></div>
            <form id="search_form" method="post" action="">
            <fieldset>
                <input type="hidden" name="pp" value="<?=$is_help_popup?>" />
                <input type="hidden" name="action" value="request" />
                <h2 style="padding-top:0px;padding-bottom:10px;">Request new help topic</h2>
                <textarea name="newtopic" style="width:350px;height:300px;"></textarea>
                <button type="submit" class="submit" style="width:120px;margin:0.5em 0pt 0.5em 8px;">Post request</button>
            </fieldset>
            </form>
            <div id="search_bot"></div>
                
		<?php 
		} 
		else { 
			
			// Look up a search terms if one is passed.
			$msg.=(time()-$st)." Do we need to search ($search_term)<br>\n";
			if ($search_term && !$id) {

				$resultids=array();
				$query="select distinct h.id, h.title, 
						 (match(tag) against('$search_term')*4) + 
						 (match(title) against('$search_term')*2) +
						 (match(text) against('$search_term')) as score 
						from help_texts h 
						left join tag_relationships tr on h.id=tr.guid
						left join tags t on tr.tag_id=t.id 
						where (h.domain = '' or h.domain like '%,".$_SERVER['SERVER_NAME']."%')
						and h.searchable=1
						having score > 0
						order by score desc";
				$msg.=(time()-$st)." Run ($query)<br>";
				if ($search_result=$db_admin->get_results($query)) {
					foreach($search_result as $result) {
						if (!$max) $max=$result->score;
						$pcscore=ceil(($result->score/$max)*100);
						if (!in_array($result->id, $resultids)) {
							$resultids[]=$result->id;
							$result_html.='<div class="search_result"><a class="left" href="?id='.$result->id.'&amp;pp='.$is_help_popup.'&amp;ssearch='.$search_term.'">'.$result->title.'</a><span class="right">'.$pcscore.'%</span></div>';
							if ($result->id!=$help_id && $similar++<2) {
								$similar_html.='<p class="similar"><a class="left" href="?id='.$result->id.'&amp;pp='.$is_help_popup.'&amp;ssearch='.$search_term.'">'.$result->title.'</a></p>';
							}
						}
					}
				}
				$msg.=(time()-$st)." Ran ok<br>\n";
				if ($similar_html) $similar_html='<p class="similar"><strong>Similar help files</strong></p>'.$similar_html;
			}
			if (!$result_html) $result_html='<div class="search_result"><p>No results found</p></div>';
		

			$msg.=(time()-$st)." Got id(".$help->id.")<br>\n"; 
			if ($help->id) { 
                
				// This query appears to be totally slowing things down
				// but I dont have a blues clues why??
				$query="select t.tag from help_texts h 
						left join tag_relationships tr on h.id=tr.guid
						left join tags t on tr.tag_id=t.id
						where h.id=".$help->id;
				//print "$query<br>";
				$msg.="q($query)<br>\n";
				if ($results = $db_admin->get_results($query)) {
					$msg.=(time()-$st)." Ran ID query<br>\n";
					$tags='';
					foreach($results as $result) {
						if ($result->tag) {
							$tags.='<a href="?pp='.$is_help_popup.'&action=search&ssearch='.$result->tag.'">'.$result->tag.'</a>, ';
						}
					}
					if ($tags) $tags='<p style="padding-bottom:15px;"><strong>Tags</strong> : '.substr($tags, 0, -2).'</p>';
				}
				?>
                
                <h2><?=$help->title?></h2>
                
                <?php if ($help->searchable && !$pp) { ?>
	                <p><a href="javascript:printpage();">Print this page</a> | <a href="javascript:bookmark('http://<?=$_SERVER['SERVER_NAME']?><?=$_SERVER['REQUEST_URI']?>', 'Treeline help: <?=$result->title?>');">Bookmark this page</a></p>
                <?php } ?>

                <div id="helptext"><?=$help->text?></div>
                <?php 
                if ($tags || $similar_html) { 
                    ?>
                    <div id="tags_top"></div>
                    <div id="tags_mid"<?php //echo ' style="padding-right:162px;width:318px;background:#E4EBEB url(\'/treeline/img/layout/happy_stu.gif\') right bottom no-repeat;min-height:144px;"'; ?>>
                    <?=($tags.$similar_html)?>
                    </div>
                    <div id="tags_bot"></div>
                    <?php 
				}
				?>
                
                <?php 
            } 
			else if ($action=="search") { 
				$msg.=(time()-$st)." Need to search<br>\n";
				//print $msg; 
				$msg.="resuts .... <br>".$result_html;
				//print $msg; 
				?>
            
                <h2>Search results for [<?=$search_term?>]</h2>
                <div class="search_result" style="background:0;">
                    <span style="float:left;"><b>Help file</b></span>
                    <span class="right"><b>Percentage match</b></span>
                </div>
                <?php 
				
				echo $result_html;
            } 
            
        } 
		?>
      	</div>

*/
?>
	</div>
</div>


<?php 
$msg.="ending ".(time()-$st)."<br>\n";

if (!$pp) include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php");  

?>
	 