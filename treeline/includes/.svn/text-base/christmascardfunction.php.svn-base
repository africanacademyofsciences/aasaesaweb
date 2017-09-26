<?php

/**
 * @author chrisntr
 * @copyright 2007
 */

function BuildChristmasXMLDoc($details){
		$dom = new DomDocument();
		$oAMREF = $dom->appendChild($dom->createElement("AMREF"));
		$oWeborder = $oAMREF->appendChild($dom->createElement("weborder"));

		/*Start Customer Node*/
		$oCustomer = $oWeborder->appendChild($dom->createElement("customer"));
		
		//Order ID
		$oCustomer->appendChild($dom->createElement("URN",$details["URN"]));
		
		//Source Code
		$oCustomer->appendChild($dom->createElement("SourceCode",$details["SourceCode"]));
		
		//Media Type: WEB or PHONE or 
		$oCustomer->appendChild($dom->createElement("MediaType",$details["MediaType"]));
		
		//Title (Mr)
		$oCustomer->appendChild($dom->createElement("Title",$details["Title"]));
		
		//Forename (John)
		$oCustomer->appendChild($dom->createElement("Forename",$details["Forename"]));
		
		//Surname (Doe)
		$oCustomer->appendChild($dom->createElement("Surname",$details["Surname"]));
		
		//Salutation (Dear John Doe)
		$oCustomer->appendChild($dom->createElement("Salutation",$details["Salutation"]));
		
		//Company (A Company)
		$oCustomer->appendChild($dom->createElement("Company",$details["Company"]));
		
		//Address 1 (10 The Street)
		$oCustomer->appendChild($dom->createElement("Add1",$details["Add1"]));
		
		//Address 2 (The Road)
		$oCustomer->appendChild($dom->createElement("Add2",$details["Add2"]));
		
		//Address 3
		$oCustomer->appendChild($dom->createElement("Add3",$details["Add3"]));
		
		//Address 4
		$oCustomer->appendChild($dom->createElement("Add4",$details["Add4"]));
		
		//Address 5
		$oCustomer->appendChild($dom->createElement("Add5",$details["Add5"]));
		
		//Town (Atown)
		$oCustomer->appendChild($dom->createElement("Town",$details["Town"]));
		
		//County (Acounty)
		$oCustomer->appendChild($dom->createElement("County",$details["County"]));
		
		//Postcode (RH1 1NN)
		$oCustomer->appendChild($dom->createElement("Postcode",$details["Postcode"]));
		
		//Country (United Kingdom)
		$oCustomer->appendChild($dom->createElement("Country",$details["Country"]));
		
		//Telephone Number (01234 567890)
		$oCustomer->appendChild($dom->createElement("Telephone",$details["Telephone"]));
		
		//Mobile Number (07775 123456)
		$oCustomer->appendChild($dom->createElement("Mobile",$details["Mobile"]));
		
		//Fax Number (01234 567891)
		$oCustomer->appendChild($dom->createElement("Fax",$details["Fax"]));
		
		//Amount - Probably needs converting from pennies. (12.00)
		$oCustomer->appendChild($dom->createElement("Amount",$details["Amount"]));
		
		//Postage and Packaging Amount. (2.00)
		$oCustomer->appendChild($dom->createElement("PandP",$details["PandP"]));
		
		//Optional Donation Amount (0.00)
		$oCustomer->appendChild($dom->createElement("Donation",$details["Donation"]));
		
		//Paytype (CC)
		$oCustomer->appendChild($dom->createElement("Paytype",$details["Paytype"]));
		
		//Delivery Title (Mr)
		$oCustomer->appendChild($dom->createElement("DelTitle",$details["DelTitle"]));
		
		//Delivery Forename (John)
		$oCustomer->appendChild($dom->createElement("DelForename",$details["DelForename"]));
		
		//Delivery Surname (Doe)
		$oCustomer->appendChild($dom->createElement("DelSurname",$details["DelSurname"]));
		
		//Delivery Salutation (Dear John Doe)
		$oCustomer->appendChild($dom->createElement("DelSalutation",$details["DelSalutation"]));
		
		//Delivery Company 
		$oCustomer->appendChild($dom->createElement("DelCompany",$details["DelCompany"]));
		
		//Delivery Add 1
		$oCustomer->appendChild($dom->createElement("DelAdd1",$details["DelAdd1"]));
		
		//Delivery Add 2
		$oCustomer->appendChild($dom->createElement("DelAdd2",$details["DelAdd2"]));
		
		//Delivery Add 3
		$oCustomer->appendChild($dom->createElement("DelAdd3",$details["DelAdd3"]));
		
		//Delivery Add 4
		$oCustomer->appendChild($dom->createElement("DelAdd4",$details["DelAdd4"]));
		
		//Delivery Add 5
		$oCustomer->appendChild($dom->createElement("DelAdd5",$details["DelAdd5"]));
		
		//Delivery Town (Atown)
		$oCustomer->appendChild($dom->createElement("DelTown",$details["DelTown"]));
		
		//Delivery County (Acounty)
		$oCustomer->appendChild($dom->createElement("DelCounty",$details["DelCounty"]));
		
		//Delivery Postcode (RH1 1NN)
		$oCustomer->appendChild($dom->createElement("DelPostcode",$details["DelPostcode"]));

		//Delivery Country (United Kingdom)
		$oCustomer->appendChild($dom->createElement("DelCountry",$details["DelCountry"]));
		
		
		$oOrder = $oCustomer->appendChild($dom->createElement("Order"));

		/*Start OrderItem 1 Node*/
		$oOrderItem = $oOrder->appendChild($dom->createElement("OrderItem"));
		
		//Item 1 ID
		$oOrderItem->appendChild($dom->createElement("ItemID",$details["ItemID1"]));
		
		//Item 1 Cost
		$oOrderItem->appendChild($dom->createElement("ItemCost",$details["ItemCost1"]));
		
		//Item 1 Qty
		$oOrderItem->appendChild($dom->createElement("ItemQty",$details["ItemQty1"]));
		
		/*Start OrderItem 2 Node*/
		$oOrderItem2 = $oOrder->appendChild($dom->createElement("OrderItem"));
		
		//Item 2 ID
		$oOrderItem2->appendChild($dom->createElement("ItemID",$details["ItemID2"]));
		
		//Item 2 Cost
		$oOrderItem2->appendChild($dom->createElement("ItemCost",$details["ItemCost2"]));
		
		//Item 2 Qty
		$oOrderItem2->appendChild($dom->createElement("ItemQty",$details["ItemQty2"]));
		
		$dom->formatOutput = true;
		
		return $dom->saveXML();	
	}

?>