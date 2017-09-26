$(document).ready(
		function() {

	$("#samedonor").click(function(){
		if ($(this).attr("checked"))
		{
			$("#del_salutation").val($("#donor_salutation").val());
			$("#del_first_name").val($("#donor_first_name").val());
			$("#del_middle_initial").val($("#donor_middle_initial").val());
			$("#del_last_name").val($("#donor_last_name").val());
			$("#del_company").val($("#donor_company").val());
		}
		else
		{
			$("#del_salutation").val("");
			$("#del_first_name").val("");
			$("#del_middle_initial").val("");
			$("#del_last_name").val("");
			$("#del_company").val("");
		}
		
});

});