var junkdrawer = ToolMan.junkdrawer()

function mySubmit(t) {
	if (t.value != '') {
		document.getElementById('section_choice').submit();
	}
}

// NEW STUFF:

// NOTE : known bug : we need to update ALL arrows once we've moved an item -- or at least;
// the item moved, the item above it, the item underneath it and the new first item in the list [if we've moved the first item]

var moving = false;

function updateHiddenField() {
	var list = document.getElementById('mm');
	document.getElementById('mm_content').value = junkdrawer.serializeList(list,Array('guid','myclass'));
	// readd/remove even classes
	$("ul#mm li:even").addClass("even");
	$("ul#mm li:odd").removeClass("even");
}

function selectLI(guid) {
	if (!moving) {
		moving = guid;
		document.getElementById('mm').className = 'moving';
		var li = document.getElementById('mm_' + guid);
		var myClass = li.className;
		li.className = myClass + ' selected';
		document.getElementById('move_'+guid).innerHTML = 'Moving';
	}
}

function over(t) {
//	window.status = 'OVER';
	var myClass = t.className;
	if (moving && myClass.indexOf(' selected') == -1 ) {
		// If we're moving the item, but it isn't selected
		t.className = myClass + ' over';
//		window.status += ' MOVING [' + myClass + ' over]';
	}
	else {
//		window.status += ' NOT MOVING';
	}
}

function out(t) {
	var myClass = t.className;
	newClass = myClass.replace(/ over/, '');
	t.className = newClass;
}

function position(t) {
	if (moving) {
		// Move the LI within the list
		var mm = document.getElementById('mm');
		var moved = document.getElementById('mm_' + moving);
		var clicked = document.getElementById(t.id);	
		insertAfter(mm,moved,clicked);
		document.getElementById('mm').className = '';
	
		// Turn off the 'selected' highlight on the LI we've just moved
		var myClass = moved.className;
		newClass = myClass.replace(/ selected/, '');
		//moved.myClass = newClass;
		moved.className = newClass;
		moved.setAttribute('myclass', newClass);
//		document.getElementById('move_'+moving).innerHTML = 'Move';		
		document.getElementById('move_'+moving).innerHTML = '<a href="javascript: selectLI(\''+moving+'\')">Move</a>';				
		
	

		// Indicate that we've finished moving:
		moving = false;
		
		// Update the hidden field that the PHP uses:
		updateHiddenField();
	
	}
}

function getLevel(t) {
	if (t) {
		var myClass = t.className;
		var x = myClass.match(/level_(\d)/);
		return parseInt(RegExp.$1);
	}
	else {
		return 0;
	}
}
function getGuid(t) {
	var id = t.id;
	var x = id.match(/mm_(\w*)/);
	return RegExp.$1;
}

function indent(guid) {

	var t = document.getElementById('mm_' + guid);
	var myClass = t.className;
	var x = getLevel(t);
	var y = getLevel(t.previousSibling);

	if (x < 9 && (x-y < 1)) {
		var reg = new RegExp("level_"+x);
		var newClass = myClass.replace(reg,"level_" + (x+1));
		t.className = newClass;
		t.setAttribute('myclass', newClass);
	}
	
	updateArrows(t,true);

}
function outdent(guid) {
	var t = document.getElementById('mm_' + guid);
	var myClass = t.className
	var x = getLevel(t);
	var y = getLevel(t.previousSibling);
	if (x > 1) {
		var reg = new RegExp("level_"+x);
		var newClass = myClass.replace(reg,"level_" + (x-1));
		t.className = newClass;
		t.setAttribute('myclass', newClass);
	}
	updateArrows(t,true);
}

function updateArrows(li,next) {
	// This enables/disables indenting/outdenting for a certain LI

	var guid = getGuid(li);

	var level = getLevel(li);
	var above = getLevel(li.previousSibling);

	if (level == 1) {
		document.getElementById('outdent_' + guid).src = '/treeline/img/menumanager/outdent.gif';
	}	
	else {
		document.getElementById('outdent_' + guid).src = '/treeline/img/menumanager/outdent_hi.gif';
	}
	if (level == 9 || (level-above >= 1)) {
		document.getElementById('indent_' + guid).src = '/treeline/img/menumanager/indent.gif';
	}	
	else {
		document.getElementById('indent_' + guid).src = '/treeline/img/menumanager/indent_hi.gif';
	}
	if (next && li.nextSibling) {
		// Indicate whether or not we should update the next item in the list as well
		// [This is needed because outdenting an item can implicitly enable indenting on the one beneath it]
		updateArrows(li.nextSibling,false);
	}
	// Finally, just flag that we've updated the menu
	updateHiddenField();
	
}

function insertAfter(parent, node, referenceNode) {
	// From http://javascript.internet.com/snippets/insertafter.html
  parent.insertBefore(node, referenceNode.nextSibling);
}

$(document).ready(function(){
	$("ul#mm li:even").addClass("even");
 });