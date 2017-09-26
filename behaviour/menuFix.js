/* menuFix */
/* AMREF's non-web friendly menu needs reigning in asome */

$(document).ready(
	function() { 
		/* ADD CLASS TO ALL LIST ITEMS AFTER ITEM 4 */
		$("#menu li:nth-child(5)").addClass("bottomRow");
		$("#menu li:nth-child(6)").addClass("bottomRow");
		$("#menu li:nth-child(7)").addClass("bottomRow");
		$("#menu li:nth-child(8)").addClass("bottomRow");
		
		/* PAIR UP LIST ITEMS SO THEY CAN BE THE SAME WIDTH */
		$("#menu li:nth-child(1)").addClass("pair1");
		$("#menu li:nth-child(5)").addClass("pair1");
		
		$("#menu li:nth-child(2)").addClass("pair2");
		$("#menu li:nth-child(6)").addClass("pair2");
		
		$("#menu li:nth-child(3)").addClass("pair3");
		$("#menu li:nth-child(7)").addClass("pair3");
		
		$("#menu li:nth-child(4)").addClass("pair4");
		$("#menu li:nth-child(8)").addClass("pair4");
	}
);