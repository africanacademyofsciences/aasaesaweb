<?php
	
class Help{
		
	public $id, $title, $text, $searchable, $domain;
	
	public function __construct($id=0){
		//
		//print "load help ($id)<br>\n";
		if ($id>0) $this->loadByID($id);
	}
		
	public function loadByID($id) {
		global $db_admin;
		if (!$id) return ;
		
		$this->id = $id;
		// First get the help data to avoid having to pull it over several times.
		$query ="select * from help_texts where id=".$id;
		//print "Q($query) <br>\n";
		if ($row=$db_admin->get_row($query)) {
			$this->title = $row->title;
			$this->text = $row->text;
			$this->domain = $row->domain;
			$this->searchable = $row->searchable;
		}
		//print "loaded (".$this->title.") help text<br>\n";
	}
		
	// Old help system used to open help pages in a thickbox
	public function helpLink($s) {
		return "/treeline/help/?pp=1&ssearch=".urlencode($s)."&action=search&KeepThis=true&TB_iframe=true&height=500&width=600";
	}

	public function _helpLinkByID($id) {
		if ($id>0) return "/treeline/help/?pp=1&amp;id=".$id."&amp;ssearch=&amp;action=search&amp;KeepThis=true&amp;TB_iframe=true&amp;height=500&amp;width=600";
		else return $this->helpLink($id);
	}
	// 10th Jan 2009 - Phil Redclift
	// Open help in a real popup window so people can keep it open if the need to.
	public function helpLinkByID($id) {
		if ($id>0) return "/treeline/help/?pp=1&amp;id=".$id."&amp;ssearch=&amp;action=search&amp;";
		else return $this->helpLink($id);
	}
	
	public function helpLinkByTitle($s) {
		global $db_admin;
		$query = "select id from help_texts where title='$s'";
		$pageid = $db_admin->get_var($query);
		//return "/treeline/help/?pp=1&amp;id=".$pageid."&amp;ssearch=$s&amp;action=search&amp;KeepThis=true&amp;TB_iframe=true&amp;height=500&amp;width=600";
		return "/treeline/help/?pp=1&amp;id=".$pageid."&amp;ssearch=$s&amp;action=search&amp;";
	}
	
	public function drawHrefByID($id, $title) {
		return '<a href="'.$this->helpLinkByID($id).'" class="thickbox">'.$title.'</a>';
	}
	

	public function drawInfoPopup($info, $style="text") {	
		global $site;
		if (!$info || !$site->getConfig("tl_show_tooltip")) return '';
		
		if ($style=="text") $info= str_replace(" ", "&nbsp;", $info)."&nbsp;&nbsp;";
		return 'onmouseover="javascript:nfoPopup(\''.$info.'\')" onmouseout="javascript:nfoPopout()"';
	}

	public function drawSmallPopupByID($id) {
		return '<a class="help-small" title="Get help with this" href="javascript:openhelp(\''.$this->helpLinkByID($id).'\')"><img border="0" src="/treeline/img/icons/help_small.gif" alt="get help with this" /></a>';
	}
	
}
	
?>