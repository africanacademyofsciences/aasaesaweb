<?php 
if ($page->getMode() == 'edit'){ 

	?>
    <script type="text/javascript">
		function toggleAltcontent() {
			var block = document.getElementById("alt-content");
			var currentstate = block.style.display;
			//alert(currentstate);
			if (currentstate == "none") block.style.display = "block";
			else block.style.display = "none"; 
		}
	</script>
    <?php

	$jsBottom[]="simple_ajax";
	
	echo '</fieldset>
	</form>
	<script type="text/javascript" src="/treeline/behaviour/helpPopup.js"></script>
	'.$page->drawTinyMCE($mceFiles).'
	<script type="text/javascript" src="/treeline/behaviour/treeline.js"></script>
	<script type="text/javascript" src="/treeline/behaviour/thickbox/thickbox.js"></script>
	';

	$extraJSbottom.='
	function editorNotes(guid) {
		var settings="scrollbars=yes,top=100,left=200,height=500,width=346,directories=no,location=no,resizeable=no";
		var newwindow = window.open("/treeline/ednotes/?guid="+guid, "editwin", settings);
		if (window.focus) { newwindow.focus(); }	
	}
	function panelManager(guid) {
		var settings="scrollbars=yes,top=100,left=200,height=500,width=600,directories=no,location=no,resizable=yes";
		var panelwindow = window.open("/treeline/panelManager/?guid="+guid, "panelwin", settings);
		if (window.focus) { panelwindow.focus(); }	
	}
	';
	
	$extraJSbottom.='
	var panels = new Array();
	var panels_being_edited = 0;
	
	function toggle_edit(panel) {
		//var panel_parent = document.getElementById("panel-wrap-"+panel);

		var panel_id = "panel-"+panel;
		var panel_edit_id = "panel-editor-"+panel;
		if (!panels[panel]) {
			//alert("show edit("+panel_edit_id+") hide content("+panel_id+")");
			panels[panel]=1;
			//panel_parent.insertBefore(document.getElementById(panel_edit_id), document.getElementById(panel_id));
			GetContent(panel, '.rand(1,1000).');
			document.getElementById(panel_id).style.display="none";
			document.getElementById(panel_edit_id).style.display="block";
			panels_being_edited++;
			hide_move_controls(panel);
		}
		else {
			//alert("show content("+panel_id+") hide editor("+panel_edit_id+")");
			panels[panel]=0;
			document.getElementById(panel_edit_id).style.display="none";
			document.getElementById(panel_id).style.display="block";
			//panels_being_edited=panels_being_edited-1;
			document.getElementById("panel-content-"+panel).innerHTML = "";
			panels_being_edited--;
			show_move_controls();
		}
	}
	function delete_panel(panel, custom) {
		var f = document.getElementById("treeline_edit");
		var msg = "'.html_entity_decode($page->getLabel("tl_pedit_serr_rempanel")).'";
		if (custom) msg = msg + " " + "'.html_entity_decode($page->getLabel("tl_pedit_serr_remcustom")).'";
		if (confirm(msg)) {
			f.delete_panel.value=panel;
			setTarget(0);
			setAction("Delete")			
		}
	}
	';
	
	$extraJSbottom.='
	function hide_move_controls(panel) {
		var holder = document.getElementById("secondarycontent");
		var blocks = holder.getElementsByTagName("li");
		for(var i = 0; i < blocks.length; i++){	
			//alert ("found block("+blocks[i].id+")");
			if (blocks[i].className=="edit") {
				blocks[i].style.display="none";
				if (blocks[i].id) {
					thispanel = blocks[i].id.substr(0,13);
					//alert("got this panel("+thispanel+":"+panel+")");
					if (thispanel==panel) {
						document.getElementById(thispanel+"-unedit").style.display="block";
					}
					else {
						document.getElementById(thispanel+"-rejedit").style.display="block";
					}
				}
			}
			//if (blocks[i].className=="moveup") blocks[i].className="noedit";
			//if (blocks[i].className=="movedown") blocks[i].className="noedit";
			if (blocks[i].className=="moveup") blocks[i].style.display="none";
			if (blocks[i].className=="moveup-hidden") blocks[i].style.display="block";
			if (blocks[i].className=="movedown") blocks[i].style.display="none";
			if (blocks[i].className=="movedown-hidden") blocks[i].style.display="block";
		}
	}
	function show_move_controls() {
		var holder = document.getElementById("secondarycontent");
		var blocks = holder.getElementsByTagName("li");
		for(var i = 0; i < blocks.length; i++){	
			//alert ("found block("+blocks[i].id+")");
			thispanel = blocks[i].id.substr(0,13);
			if (blocks[i].className=="unedit") {
				blocks[i].style.display="none";
				document.getElementById(thispanel+"-edit").style.display="block";
			}
			if (blocks[i].className=="rejedit") {
				blocks[i].style.display="none";
				document.getElementById(thispanel+"-edit").style.display="block";
			}
			//if (blocks[i].className=="moveup") blocks[i].className="noedit";
			//if (blocks[i].className=="movedown") blocks[i].className="noedit";
			if (blocks[i].className=="moveup") blocks[i].style.display="block";
			if (blocks[i].className=="moveup-hidden") blocks[i].style.display="none";
			if (blocks[i].className=="movedown") blocks[i].style.display="block";
			if (blocks[i].className=="movedown-hidden") blocks[i].style.display="none";
		}
	}
	';
	
	$extraJSbottom.='
	function swapNodes(first, dir) {
	
		if (panels_being_edited>0) {
			alert("edit mode used");
			return;
		}
		var holder = document.getElementById("secondarycontent");
		var blocks = holder.getElementsByTagName("div");
		var lastBlock=currentBlock=nextBlock="";
		
		var second="";
		var setSecond = 0;
		for(var i = 0; i < blocks.length; i++){	
			//alert ("found block("+blocks[i].id+")");
			if (blocks[i].className!="panel-wrapper") continue;
			//alert("got a div id("+blocks[i].id+") class("+blocks[i].className+")");
			lastBlock=currentBlock;
			currentBlock = blocks[i].id;
			if (currentBlock == first) {
				if (dir==1) second = lastBlock;
				setSecond = 1;
			}
			else if (setSecond && !second) second = currentBlock;
			//alert ("found last("+lastBlock+") cur("+currentBlock+") next("+nextBlock+")");
		}
		if (first && second) {
			realSwap(holder, first, second, dir);
		}
		//else alert("Nothing to do ("+first+":"+second+")");
		
		// Get the current list of IDs
		blocks = holder.getElementsByTagName("div");
		var panels=guid="";
		var ignore;
		var guids = new Array();
		for(i = 0; i < blocks.length; i++){	
			ignore = 0;
			if (blocks[i].className=="panel-wrapper") {
				guid = blocks[i].id.substr(11);
				for(j=0; j<guids.length; j++) {	
					if (guids[j]==guid) ignore=1;
				}
				if (!ignore) {
					panels+=guid+",";
					guids.push(guid);
				}
			}
		}
		var newpanels = panels.substr(0,panels.length-1);	
		cp = document.getElementById("treeline_panels");
		//alert(cp.value+"\n"+newpanels);
		cp.value=newpanels;
	}
	function realSwap(p, a, b, dir) {
		//alert ("realSwap("+p+", "+a+", "+b+")");
		da = document.getElementById(a);
		db = document.getElementById(b)
		//alert ("realSwap("+da+", "+db+")");
		if (dir) p.insertBefore(da, db);
		else p.insertBefore(db, da);
	}
	';

}
?>