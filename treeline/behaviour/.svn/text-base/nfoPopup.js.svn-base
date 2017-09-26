// JavaScript Document

var posx = 0;
var posy = 0;
var nfo=document.getElementById("nfoholder");
var curint=0;

function set_nfobox_coordinates(e)
{
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) {
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) {
		posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	}
	//You have the coordinates in the posx and posY variables
	//You can do whatever you want with them after this point
	if (nfo.style.display=="block") {
		nfo.style.top = (posy+3)+"px";
		nfo.style.left = (posx+3)+"px";
	}

}
document.body.onmousemove = set_nfobox_coordinates

function nfoPopup(text) {
	var txt = document.getElementById("nfo");
	var nhead = document.getElementById("nfotopline");
	var nbody = document.getElementById("nfotext");
	
	txt.innerHTML=text;
	nfo.style.display="block";
	var newWidth = txt.offsetWidth;
	//alert ("set width to "+newWidth);
	txt.style.width = (newWidth)+"px";
	//nhead.style.width = (newWidth)+"px";
	nhead.style.width = (newWidth-8)+"px";
	nbody.style.width = (newWidth+12)+"px";
	nfo.style.visibility="visible";
}
function nfoPopout(text) {
	nfo.style.display="none";
	nfo.style.visibility="hidden";
	//document.body.onmousemove = null
}


