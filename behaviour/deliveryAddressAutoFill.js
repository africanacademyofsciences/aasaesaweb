$(document).ready(
		function() {

	$("#sameaddress").click(function(){
		if ($(this).attr("checked"))
		{
			$("#del_addr_1").val($("#donor_addr_1").val());
			$("#del_addr_2").val($("#donor_addr_2").val());
			$("#del_addr_3").val($("#donor_addr_3").val());
			$("#del_addr_4").val($("#donor_addr_4").val());
			$("#del_addr_country").val($("#donor_addr_country").val());
			$("#del_addr_postcode").val($("#donor_addr_postcode").val());
		}
		else
		{
			$("#del_addr_1").val("");
			$("#del_addr_2").val("");
			$("#del_addr_3").val("");
			$("#del_addr_4").val("");
			$("#del_addr_country").val("");
			$("#del_addr_postcode").val("");
		}
		
});

});