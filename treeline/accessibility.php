<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
	
	// PAGE specific HTML settings
	
	$css = array(); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Accessibility information';
	$pageTitle = 'Accessibility information';
	
	$pageClass = 'accessibility';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
      <div id="primarycontent">
        <div id="primary_inner">
          <p>Ichameleon <abbr title="Limited">Ltd</abbr> are committed to providing Treeline to everyone, regardless of physical ability.</p>
          <h3>Text size</h3>
          <p>The  size of the text on Treeline can be easily increased or decreased  without disrupting the design. If you are using Internet Explorer or  Firefox and have a mouse with a scroll wheel simply hold down the <em>Ctrl</em> key on your keyboard while scrolling up or down to increase/decrease the text size. Alternatively, select<em> View &gt; Text Size</em> from your menu and select the size option you prefer from <em>Smallest</em> to <em>Largest</em>.</p>
          <h3>Navigation without a mouse</h3>
          <p>This site has been equipped with some basic access keys hopefully making navigation via a keyboard/mobile phone a little easier.</p>
          <h4>Access keys&rsquo; assignments</h4>
          <ul>
            <li>1 - <a href="/treeline/">Treeline home page</a></li>
            <li>2 - <a href="#content">skip to content<br />
            </a></li>
            <li>9 - <a href="/treeline/feedback/">feedback</a></li>
            <li>0 - accessibility (this page)</li>
          </ul>
          <p>Access Keys can be activated by pressing <kbd>Alt</kbd> + the access key and pressing Enter or <kbd>Alt</kbd> + <kbd>Shift </kbd>+ the access key if you're using Firefox. (Mac users press <kbd>Ctrl</kbd> and not <kbd>Alt</kbd>).</p>
          <h4>Skip links</h4>
          <p>If  you are using a mobile phone, tabbing through all the links with your  keyboard or listening to the site through a screen reader you may find  the Skip to Content link really useful. They are the first links on  every page. Simply click them and you will avoid all the repetitive  text/images (existing on every page) and you&rsquo;ll be taken straight to  the content you want.</p>
        </div>
      </div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>