
// JavaScript Document
function openhelp(lnk) {
	var settings="menubar=no,top=100,left=300,width=400,height=600,scrollbars=yes,status=no,resizable=no";
	var helpwin = window.open(lnk, "helpwin", settings)
	if (window.focus) helpwin.focus();
}
