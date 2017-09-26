<?php


function event_form_replace ($content, $mode) {

	if ($mode=="edit") return $content;
	
	return str_replace("[[EVENTFORM]]", '

<h3 align="center">Application form</h3>

<form name="fEvent" id="fEvent" method="POST" onsubmit="javascript:return checkForm();" style="background:none;border:0;margin:0;padding:0;">
<fieldset>
<p>I would like to support AMREF in the </p>
<fieldset style="border:0;">
	<table border="0" style="margin:0;" cellpadding="0" cellspacing="0">
	<tr>
		<td><input type="checkbox" name="race_london" id="ef_race_london" style="margin-left:0px;" class="left"'.(($_POST['race_london']=='Y')?"checked":"").' value="Y" /> </td>
		<td><label for="ef_race_london" class="cright light">Flora London Marathon </label></td>
	</tr><tr>
		<td><input type="checkbox" name="race_newyork" id="ef_race_newyork" style="margin-left:0px;" class="left"'.(($_POST['race_newyork']=='Y')?"checked":"").' value="Y" /> </td>
		<td><label for="ef_race_newyork" class="cright light">New York Marathon</label></td>
	</tr><tr>
		<td colspan="2" class="spacebelow"><label for="ef_race_other" class="wide light">Another race, please specify</label> <input type="text" name="race_other" id="ef_race_other" value="'.$_POST['race_other'].'" /></td>
	</tr><tr>
		<td><input type="radio" name="goldenbond" id="ef_golden_place" style="margin-left:0px;" class="left" value="bondplace" '.(($_POST['goldenbond']=='bondplace')?"checked":"").' /> </td>
		<td><label for="ef_golden_place" class="cright light" style="width:500px;">I would like to apply for an AMREF golden bond guaranteed place, I intend to apply, or have applied already, for my own place via the marathon ballot. </label></td>
	</tr><tr>
		<td><input type="radio" name="goldenbond" id="ef_golden" style="margin-left:0px;" class="left" value="bond" '.(($_POST['goldenbond']=='bond')?"checked":"").' /> </td>
		<td><label for="ef_golden" class="cright light" style="width:500px;">I would like to apply for an AMREF golden bond guaranteed place, I do not intend to apply for my own place via the marathon ballot.</label></td>
	</tr><tr>
		<td><input type="radio" name="goldenbond" id="ef_place" style="margin-left:0px;" class="left" value="place" '.(($_POST['goldenbond']=='place')?"checked":"").' /> </td>
		<td><label for="ef_place" class="cright light" style="width:500px;"> I have been allocated my own place through the ballot and do not wish to apply for a golden bond place.</label></td>
	</tr>
	</table>
<p style="float:left;clear:left;padding-top:10px;">Please note AMREF only holds Golden Bond places for the London and New York Marathons.</p>
</fieldset>

<fieldset>
<legend>Personal details:</legend>
<label for="ef_title" class="inline">Title</label> <input id="ef_title" type="text" name="title" value="'.$_POST['title'].'" />
<label for="ef_firstname" class="inline">Firstname</label> <input id="ef_firstname" type="text" name="firstname" value="'.$_POST['firstname'].'" />
<label for="ef_surname" class="inline">Surname</label> <input id="ef_surname" type="text" name="surname" value="'.$_POST['surname'].'" />
<label for="ef_add1">Address line 1</label><input id="ef_add1" type="text" name="add1" value="'.$_POST['add1'].'" />
<label for="ef_add2">Address line 2</label><input id="ef_add2" type="text" name="add2" value="'.$_POST['add2'].'" />
<label for="ef_town">Town</label><input id="ef_town" type="text" name="town" value="'.$_POST['town'].'" />
<label for="ef_county">County</label><input id="ef_county" type="text" name="county" value="'.$_POST['county'].'" />
<label for="ef_postcode">Postcode</label><input id="ef_postcode" type="text" name="postcode" value="'.$_POST['postcode'].'" />
<label for="ef_country">Country</label><input id="ef_country" type="text" name="country" value="'.$_POST['country'].'" />
<label for="ef_tel_day">Daytime phone #</label><input id="ef_tel_day" type="text" name="day_tel" value="'.$_POST['day_tel'].'" />
<label for="ef_tel_eve">Evening phone #</label><input id="ef_tel_eve" type="text" name="eve_tel" value="'.$_POST['eve_tel'].'" />
<label for="ef_mobile">Mobile</label><input id="ef_mobile" type="text" name="mobile" value="'.$_POST['mobile'].'" />
<label for="ef_email">Email</label><input id="ef_email" type="text" name="email" value="'.$_POST['email'].'" />
<label for="ef_dummy_01">Sex</label><label for="ef_sex_m" style="clear:none;width:100px;">Male</label><input id="ef_sex_m" type="radio" name="sex" value="M" style="width:40px;" '.(($_POST['sex']=='M')?"checked":"").'  />
<label for="ef_dummy_02" style="visibility:hidden;"></label><label for="ef_sex_f" style="clear:none;width:100px;">Female</label><input id="ef_sex_f" type="radio" name="sex" value="F" style="width:40px;" '.(($_POST['sex']=='F')?"checked":"").'/>

</fieldset>

<fieldset>
<legend>Fundraising Pledge</legend>
<p><b>Please make sure you have read our terms and conditions before completing this section.</b></p>
	<table border="0" style="margin:0;" cellpadding="0" cellspacing="0">
	<tr>
		<td class="spacebelow"><label for="ef_pledge">I pledge to raise £</label></td>
		<td class="spacebelow"><input type="text" name="pledge" id="ef_pledge" value="'.$_POST['pledge'].'"></td>
	</tr><tr>
		<td colspan="2"><label for="ef_how" style="width:100%;">How did you hear about AMREF?</label></td>
	</tr><tr>
		<td><input type="checkbox" name="how_race_web" id="ef_how_1" class="left"'.(($_POST['how_race_web']=='Y')?"checked":"").' value="Y"></td>
		<td><label for="ef_how_1" class="light cright">Race website - please specify below</label></td>
	</tr><tr>
		<td><input type="checkbox" name="how_race_mag" id="ef_how_2" class="left"'.(($_POST['how_race_mag']=='Y')?"checked":"").' value="Y"></td>
		<td><label for="ef_how_2" class="light cright">Race magazine advert - please specify below</label></td>
	</tr><tr>
		<td><input type="checkbox" name="how_amref" id="ef_how_3" class="left"'.(($_POST['how_amref']=='Y')?"checked":"").' value="Y"></td>
		<td><label for="ef_how_3" class="light cright">AMREF website</label></td>
	</tr><tr>
		<td><input type="checkbox" name="how_donor" id="ef_how_3" class="left"'.(($_POST['how_donor']=='Y')?"checked":"").' value="Y"></td>
		<td><label for="ef_how_4" class="light cright">I am an existing donor to AMREF</label></td>
	</tr><tr>
		<td class="spacebelow"><label for="ef_how_text" class="right">Other/specify</label></td>
		<td><input type="text" name="how_text" id="ef_how_text" value="'.$_POST['how_text'].'"></td>
	</tr>

	<tr>
		<td valign="top" class="spacebelow"><label for="ef_why">Why do you want to run for AMREF?</label></td>
		<td class="spacebelow"><textarea name="why" rows="4" cols="60" id="ef_why">'.$_POST['why'].'</textarea></td>
	</tr>
	
	<tr>
		<td colspan="2"><label class="wide">Have you run a marathon, or half-marathon, before?</label></td>
	</tr><tr>
		<td><label class="light right" for="ef_runbefore_yes">Yes</label></td>
		<td><input type="radio" name="runbefore" value="Y" style="width:50px;" id="ef_runbefore_yes" '.(($_POST['runbefore']=='Y')?"checked":"").' /></td>
	</tr><tr>
		<td class="spacebelow"><label class="light right" for="ef_runbefore_no">No</label></td>
		<td class="spacebelow"><input type="radio" name="runbefore" value="N" style="width:50px;" id="ef_runbefore_no" '.(($_POST['runbefore']=='N')?"checked":"").' /></td>
	</tr>

	<tr>
		<td colspan="2"><label for="ef_previous" style="width:100%;">If you have previously participated in any fundraising events for charity (for example, marathon, overseas trek, bungee jump) please tell us about it</label></td>
	</tr><tr>
		<td class="spacebelow"><label for="dummy" style="visibility:hidden;"></label></td>
		<td class="spacebelow"><textarea name="previous" rows="4" cols="60" id="ef_previous">'.$_POST['previous'].'</textarea></td>
	</tr>
	
	<tr>
		<td class="spacebelow"><label for="ef_raised">How much did you raise?</label></td>
		<td class="spacebelow"><input type="text" name="raised" id="ef_raised" value="'.$_POST['raised'].'"></td>
	</tr>

	<tr>
		<td class="spacebelow"><label for="ef_prof">Please tell us your profession</label></td>
		<td class="spacebelow"><input type="text" name="profession" id="ef_prof" value="'.$_POST['profession'].'"></td>
	</tr>
	
	<tr>
		<td colspan="2"><label style="width:100%;">Does your company have a Matched Giving Scheme?</label></td>
	</tr><tr>
		<td><label class="light right" for="ef_matchgive_yes">Yes</label></td>
		<td><input type="radio" name="matchgive" value="Y" style="width:50px;" id="ef_matchgive_yes" '.(($_POST['matchgive']=='Y')?"checked":"").' /></td>
	</tr><tr>
		<td class="spacebelow"><label class="light right" for="ef_matchgive_no">No</label></td>
		<td class="spacebelow"><input type="radio" name="matchgive" value="N" style="width:50px;" id="ef_matchgive_no" '.(($_POST['matchgive']=='N')?"checked":"").' /></td>
	</tr>

	<tr>
		<td valign="top"><label for="ef_plans">Please outline your fundraising plans</label></td>
		<td><textarea name="plans" rows="4" cols="60" id="ef_plans">'.$_POST['plans'].'</textarea></td>
	</tr>
	</table>

</fieldset>
<h3>Running for AMREF, Terms and Conditions</h3>

<h4>Minimum fundraising commitment</h4>
<p><strong>1. London Marathon Golden Bond Place</strong><br />
AMREF has a limited number of Golden Bond places.  To request a place, applicants must pledge to run the Flora London Marathon for AMREF and to raise in sponsorship a minimum of &pound;2,000, which must be paid to AMREF (via AMREF\'s stipulated payment methods) no later than 1 month after the date of the marathon.  Your application will be subject to a selection process, so that we can ensure that all AMREF Golden Bond places are used to the best possible effect (as determined by AMREF).</p>
<p><strong>2. New York Marathon Golden Bond Place</strong><br />
AMREF has a limited number of Guaranteed Places.  To request a place, applicants must pledge to run the New York Marathon for AMREF and to raise in sponsorship a minimum of &pound;1,500, which must be paid to AMREF (via AMREF\'s stipulated payment methods) no later than 1 month after the date of the marathon.  Your application will be subject to a selection process, so that we can ensure that all AMREF Guaranteed Places are used to the best possible effect (as determined by AMREF). </p>
<p><strong>3. Own place runner</strong><br />
As a representative of AMREF we will provide you with all the support we can, as well as an AMREF T-shirt or running vest dependant on how much you pledge to raise, in return we ask you commit to raising the full amount pledged on this application form.</p>

<h4>Deposit</h4>
<p>We ask that you pay a deposit to AMREF, as a guarantee that you will raise, or exceed, the amount pledged on your application form.  AMREF will refund the deposit to you no later than 28 days after you have paid in full your minimum sponsorship.  AMREF reserves the right to retain all (or part) of your deposit if you pay less than the minimum sponsorship amount.</p>
<p><strong>1. London Marathon Golden Bond Place</strong><br />
For members of the AMREF team running the London Marathon we ask you to pay a deposit of &pound;300, please make cheques payable to AMREF UK</p>
<p><strong>2. New York Marathon Golden Bond Place</strong><br />
For members of the AMREF team running the New York Marathon we ask you to pay a deposit of &pound;250, please make cheques payable to AMREF UK</p>
<p>If your application for a Golden Bond place is successful, AMREF will notify you in writing and will advise as to the payment method of your deposit and Bond. You must make payment of your deposit within 10 working days of notification, otherwise you may risk losing your Golden Bond place to another applicant. </p>
<p>If your application for a Golden Bond place is not immediately successful, AMREF will notify you in writing that you have been placed on our waiting list for a Golden Bond place. You must contact AMREF no longer than 2 weeks after the ballot results are announced if you still require a Golden Bond place with AMREF, or if you have gained a place through the London Marathon\'s general ballot and therefore no longer wish to be considered for an AMREF Golden Bond place. You risk losing your place on the waiting list if you fail to contact us with in these two weeks.</p>

<h4>Paying your sponsorship funds to AMREF</h4>
<p>Any existing regular donations which you make to AMREF do not form part of the total raised through sponsorship. </p>
<p>AMREF will advise you on the recommended methods of paying in your sponsorship.  This is to ensure that AMREF can easily trace funds that you pay in.  The amount which AMREF deems to have been raised by you will be the amount that AMREF has recognised as having been paid in to its bank account by you.</p>
<p>Payment deadline for sponsorship is 1 month after the date of your run.  If you need more time to collect sponsorship, please let AMREF know in writing in advance of this date.  AMREF will refund your &pound;300 deposit within 28 days of receiving sponsorship payments which equal or exceed the amount that you pledged.</p>
<p>Monies reclaimed by AMREF from the Inland Revenue for Gift Aid (or any other charity tax scheme) <strong>do not</strong> count towards the total value of the sponsorship you raise.  This is due to the delay in receiving the Gift Aid and the fact that this is not guaranteed to us. </p>

<h4>Canceling a Golden Bond place</h4>
<p>If you decide not to run for AMREF, or choose to run for another charity, you must inform AMREF as soon as possible in order that AMREF can re-allocate your place to a runner on its waiting list.  AMREF will take a &pound;50 administration fee and will refund the remainder to you only if we are able to find a replacement runner and only once all other AMREF Golden Bond places have been filled. If we, or you, cannot find a replacement, you will forfeit the remainder of your deposit.</p>

<h4>Deferring your place because of injury</h4>
<p>If you sustain a training injury, please contact AMREF in writing before the race day. It is important that you do not take part in the marathon unless you are completely fit to do so. AMREF will then advise you whether it is possible defer your place, your deposit, and your fundraising commitment to the following year. However, you must agree to abide by all terms and conditions which AMREF publishes for the following year\'s event.</p>

<h4>Agreement</h4>
<p>Please tick the agreement box below to confirm you have understood and accept the terms and conditions of applying for a Golden Bond place with AMREF:</p>
<p><input type="checkbox" name="confirm" style="float:none;width:auto;"/> I confirm that I have read, understood and accepted the above terms and conditions. (Needs a tick box, can we set it so it is not possible to complete application with out ticking this box please)</p>
<p><input type="submit" name="action" value="Submit application" /></p>
</fieldset>
</form>
', $content);

}

?>

