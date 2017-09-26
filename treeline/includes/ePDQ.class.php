<?php
/**
 * ePDQc.php - ePDQ MPI Interface (PHP5)
 *
 * @author Stephen Smith stephen.smith@aqnet.co.uk
 * @copyright Aqua Technologies Limited 2007
 * @link http://www.aqnet.co.uk/
 * @version 1.00.00, 2007-01-12
 * @todo none
 * History:
 *
 * License:
 * 	This software is strictly license according to the terms of sale
 *  and our general terms published at http://www.aqnet.co.uk/terms.
 *  The software may be modified for the purpose for which it was purchased
 *  but may, under no circumstances, be reproduced or otherwise redistributed.
 */

class ePDQc {

	/*Store Details*/
	private $m_Currency;
	private $m_ClientId;
	private $m_Username;
	private $m_Password;
	private $m_Mode;
	private $m_PostOrderDocTo;

	/*Transaction details*/
	private $m_StartDate;
	private $m_EndDate;
	private $m_IssueNumber;
	private $m_Cvv2;
	private $m_Cvv2Ind;
	private $m_Amount;
	private $m_OrderId;
	private $m_EmailAddress;

	/*AVS (billing address verification)*/
	private $m_Street1;
	private $m_PostCode;

	/*Payer Authentication / "Verified by VISA"*/
	private $m_PayerSecurityLevel;
	private $m_PayerTxnId;
	private $m_PayerAuthenticationCode;

	private $m_ReTxnId;

	/*Transaction Types*/
	private $m_TransactionType;
	private $m_SupportedTransactionTypes;

	/*XML Documents*/
	private $m_OrderDoc;
	private $m_ResponseDoc;

	/*Results*/
	private $m_CcErrorCode;
	private $m_CcReturnMessage;
	private $m_AuthCode;
	private $m_TransactionId;
	private $m_msgText;


	function __construct($m_Currency = "826", $m_ClientID = "26138", $m_Username = "minesadvisorygroup", $m_Password = "mag1083008"){
		/*Settings*/
		//$this->m_PostOrderDocTo = "https://secure2.epdq.co.uk:11500";
		//$this->m_PostOrderDocTo = "https://www.cpi.hsbc.com/servlet";
		$this->m_PostOrderDocTo = "https://www.secure-epayments.apixml.hsbc.com";

		/*Store specific parameters*/

		$this->m_Currency = $m_Currency;
		$this->m_ClientId = $m_ClientID;
		$this->m_Username = $m_Username;
		$this->m_Password = $m_Password;

        $this->m_SupportedTransactionTypes = array(
          "Auth", "PreAuth", "PostAuth", "Credit", "Void", "RePreAuth", "ReAuth" );


        // Default transaction type is the first supported
        $this->m_TransactionType = $this->m_SupportedTransactionTypes[0];


        //  Set the processing mode
        //  P - Live
        //  Y - Development, always return authorised
        //  N - Development, always return failed
        //  R - Development, return random results
        //  FN - Fraudshield reject
        //  FY - Fraudshield accept
        $this->m_Mode = "P";

        // Cvv Indicator
        //  0 - The store does not support or is incapable of submitting CVM values
        //  1 - The CVM is present and submitted
        //  2 - CVM not present on card as per customer
        //  3 - CVM present but illegible
        //  4 - The processor does not support or is incapable of submitting CVM values
        //  5 - CVM value intentionally not provided
        //  If set to 2,3 or 5 the Cvv2 field MUST be an empty string otherwise transaction will fail
        $this->m_Cvv2Ind = "1"; // default to CVM not present on card as we want to use CVM validation

        $this->m_CardNumber = "";
        $this->m_StartDate = "";
        $this->m_EndDate = "";
        $this->m_IssueNumber = "";
        $this->m_Cvv2 = "";
        $this->m_Amount = "";
        $this->m_OrderId = "";
        $this->m_EmailAddress = "";

		// name
		$this->m_FirstName = "";
		$this->m_LastName = "";

		// address
		$this->m_Street1 = "";
		$this->m_Street2 = "";
		$this->m_City = "";
		$this->m_StateProv = "";
		$this->m_Country = "";
		$this->m_PostalCode = "";

        $this->m_PayerSecurityLevel = "";
        $this->m_PayerTxnId = "";
        $this->m_PayerAuthenticationCode = "";
        $this->m_CardholderPresentCode = "";

        $this->m_ReTxnId = "";
	}


	function __toString(){
		return "Card Number: ".(string)$this->m_CardNumber. "<br />". 
			   "End date: ".(string)$this->m_EndDate."<br />".
			   "Amount: ".(string)$this->m_Amount."<br />".
			   "Order id: ".(string)$this->m_OrderId."<br />".
			   "Email Addy: ".(string)$this->m_EmailAddress."<br />".
			   "Currency: ". (string)$this->m_Currency."<br />
			   End.";
	}
	
	function getClientId(){
		return (int) $this->m_ClientId;
	}

	function setClientId($val){
		$this->m_ClientId = (string) $val;
	}

	function getCurrency(){
		return (int) $this->m_ClientId;
	}

	function setCurrency($val){
		$this->m_Currency = (string) $val;
	}

	function getAmount(){
		return (int) $this->m_Amount;
	}

	function setAmount($val){
		$this->m_Amount = (string) $val;
	}

	function getUsername(){
		return $this->m_Username;
	}

	function setUsername($val){
		$this->m_Username = $val;
	}

	function getPassword(){
		return $this->m_Password;
	}

	function setPassword($val){
		$this->m_Password = $val;
	}

	function getMode(){
		return $this->m_Mode;
	}

	function setMode($val){
		if(!in_array($val,array("P","N","R","Y"))){
			throw new Exception("ePDQ:setMode(): Invalid processing mode - use P for live, R for test random, Y for test approve or N for test fail (default R)");
		}
		$this->m_Mode = $val;
	}

	function getCardNumber(){
		return $this->m_CardNumber;
	}

	function setCardNumber($val){
		$len = strlen($val);
		$newval = "";
		for($i = 0;$i < $len;$i++){
			if(ctype_digit($val{$i})){
				$newval .= $val{$i};
			}
		}

		if(!$this->IsCreditCard("",$newval)){
			throw new Exception("ePDQ:setCardNumber(): Invalid card number - the credit card number has failed the Luhn 10 validation");
		}
		$this->m_CardNumber = $newval;
	}

	function getStartDate(){
		return $this->m_StartDate;
	}

	function setStartDate($val){
		if(preg_match("/\\d\\d\/\\d\\d/",$val)){
			$this->m_StartDate = $val;
		}else{
			throw new Exception("ePDQ:setStartDate(): Invalid start date - please provide in the format mm/yy");
		}
	}

	function getEndDate(){
		return $this->m_EndDate;
	}

	function setEndDate($val){
		if(preg_match("/\\d\\d\/\\d\\d/",$val)){
			$this->m_EndDate = $val;
		}else{
			throw new Exception("ePDQ:setEndDate(): Invalid end date - please provide in the format mm/yy");
		}
	}

	function getIssueNumber(){
		return $this->m_IssueNumber;
	}

	function setIssueNumber($val){
		$len = strlen($val);
		if($len > 2 || $len < 1 || !is_numeric($val)){
			throw new Exception("epdq:IssueNumber(): Invalid issue number - please provide a numeric value of 1 or 2 characters");
		}else{
			$this->m_IssueNumber = $val;
		}
	}

	function getCvv2(){
		return $this->m_Cvv2;
	}

	function setCvv2($val){
		$len = strlen($val);
		if($len > 4 || $len < 1 || !is_numeric($val)){
			throw new Exception("ePDQ:Cvv2(): Invalid Cvv Number - please provide a numeric value of 1 or 4 characters");
		}else{
			$this->m_Cvv2 = $val;
			$this->m_Cvv2Ind = "1";
		}
	}

	function getOrderId(){
		return $this->m_OrderId;
	}

	function setOrderId($val){
		$len = strlen($val);
		if($len > 36 || $len < 1){
			throw new Exception("epdq:Order(): Invalid order - please provide a valid order id of between 1 and 36 characters");
		}else{
			$this->m_OrderId = $val;
		}
	}

	function getEmailAddress(){
		return $this->m_EmailAddress;
	}

	function setEmailAddress($val){
		//if(preg_match("/^([a-zA-Z0-9])+([\\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-]+)+/",$val)){
		if(preg_match("/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/",$val)){
			$this->m_EmailAddress = $val;
		}else{
			throw new Exception("ePDQ:EmailAddress(): Invalid email address - please provide a valid email address");
		}
	}

	function getTransactionType(){
		return $this->m_TransactionType;
	}

	function setTransactionType($val){
		if(!in_array($val,$this->m_SupportedTransactionTypes)){
			throw new Exception("epdq:TransactionType(): Invalid transaction type. Available ones are: " . implode(",",$this->m_SupportedTransactionTypes));
		}
		$this->m_TransactionType = $val;
	}

	function getStreet1(){
		return $this->m_Street1;
	}

	function setStreet1($val){
		if(strlen($val) > 60){
			$val = substr($val,0,60);
		}
		$this->m_Street1 = $val;
	}


	function getStreet2(){
		return $this->m_Street2;
	}

	function setStreet2($val){
		if(strlen($val) > 60){
			$val = substr($val,0,60);
		}
		$this->m_Street2 = $val;
	}
	
	
	function getCity(){
		return $this->m_City;
	}

	function setCity($val){
		if(strlen($val) > 25){
			$val = substr($val,0,25);
		}
		$this->m_City = $val;
	}


	function getStateProv(){
		return $this->m_StateProv;
	}

	function setStateProv($val){
		if(strlen($val) > 25){
			$val = substr($val,0,25);
		}
		$this->m_StateProv = $val;
	}


	function getCountry(){
		return $this->m_Country;
	}

	function setCountry($val){ // needs to be 3-letter ISO code
		if(strlen($val) > 3){
			$val = substr($val,0,3);
		}
		$this->m_Country = $val;
	}



	function getPostalCode(){
		return $this->m_PostalCode;
	}

	function setPostalCode($val){
		$len = strlen($val);
		if($len >= 5 && $len <= 9){
			$this->m_PostalCode = $val;
		}else{
			throw new Exception("epdq:PostalCode(): Must be between 5 and 9 digits.");
		}
	}


	function getFirstName(){
		return $this->m_FirstName;
	}

	function setFirstName($val){
		if(strlen($val) > 32){
			$val = substr($val,0,32);
		}
		$this->m_FirstName = $val;
	}


	function getLastName(){
		return $this->m_LastName;
	}

	function setLastName($val){
		if(strlen($val) > 32){
			$val = substr($val,0,32);
		}
		$this->m_LastName = $val;
	}






	function getCardholderPresentCode(){
		return (int) $this->m_CardholderPresentCode;
	}

	function setCardholderPresentCode($val){
		$val = (string) $val;
		if(strlen($val) > 2){
        	throw new Exception("epdq:CardholderPresentCode(): Must be 1 or 2 digits.");
		}else{
			$this->m_CardholderPresentCode = $val;
		}
	}

	function getPayerSecurityLevel(){
		return (int) $this->m_PayerSecurityLevel;
	}

	function setPayerSecurityLevel($val){
		$val = (string)$val;
		$len = strlen($val);
		if($len > 2){
			throw new Exception("ePDQ:PayerSecurityLevel(): Must be 1 or 2 digits.");
		}else{
			$this->m_PayerSecurityLevel = $val;
		}
	}

	function getPayerTxnId(){
		return $this->m_PayerTxnId;
	}

	function setPayerTxnId($val){
		$this->m_PayerTxnId = $val;
	}

	function getReTxnId(){
		return $this->m_ReTxnId;
	}

	function setReTxnId($val){
		$count = 0;
		$len = strlen($val);
		for($i = 0;$i < $len;$i++){
			if($val{$i} != '-'){
				$count++;
			}
		}

		if($count > 32){
        	throw new Exception("epdq:ReTxnId(): Must not be more than 32 characters.");
		}

		$this->m_ReTxnId = $val;
	}

	function getCcErrorCode(){
		return $this->m_CcErrorCode;
	}

	function getCcReturnMsg(){
		return $this->m_CcReturnMsg;
	}

	function getAuthCode(){
		return $this->m_AuthCode;
	}

	function getTransactionId(){
		return $this->m_TransactionId;
	}

	function getMsgText(){
		return $this->m_msgText;
	}

	function getOrderDoc(){
		return $this->m_OrderDoc;
	}

	function getResponseDoc(){
		return $this->m_ResponseDoc;
	}

	function ProcessTransaction(){
		$this->m_CcErrorCode = "";
		$this->m_CcReturnMsg = "";
		$this->m_AuthCode = "";
		$this->m_TransactionId = "";
		$this->m_msgText = "";

		switch ($this->m_TransactionType){
			case "Auth":
			case "PreAuth":
				if(empty($this->m_CardNumber) || empty($this->m_EndDate) || empty($this->m_Amount) || empty($this->m_OrderId) || empty($this->m_EmailAddress)){
                    throw new Exception("epdq:ProcessTransaction(): Error processing Auth/PreAuth transaction - a transaction can only be processed if at Card Number, End Date, Amount, Order ID and Email Address are provided");
				}
				break;

			case "PostAuth":
			case "Credit":
			case "Void":
				if(empty($this->m_OrderId)){
                    throw new Exception("epdq:ProcessTransaction(): Error processing transaction - this transaction type requires at least an OrderId to be processed");
				}
				break;

			case "ReAuth":
			case "RePreAuth":
				if(empty($this->m_OrderId) || empty($this->m_ReTxnId)){
                    throw new Exception("epdq:ProcessTransaction(): Error processing transaction - this transaction type requires at least an OrderId and a ReTxnId to be processed");
				}
				break;

			default:
                throw new Exception("epdq:ProcessTransaction(): Error processing transaction - wrong transaction type");
				break;

		}

		$this->m_OrderDoc = $this->BuildOrderDoc();

		try{
			NetLib::requestPost($this->m_PostOrderDocTo,$this->m_OrderDoc,array($this,'postCallback'));
		}catch (Exception $e){
			throw new Exception("ePDQ:ProcessTransaction(): Error processing transaction - failed to communicate successfully with ePDQ");
		}

	}

	function BuildOrderDoc(){
		$dom = new DomDocument();
		$oEngineDocList = $dom->appendChild($dom->createElement("EngineDocList"));
		$oDocVer = $oEngineDocList->appendChild($dom->createElement("DocVersion","1.0"));

		/*Start EngineDoc Node*/
		$oEngineDoc = $oEngineDocList->appendChild($dom->createElement("EngineDoc"));
		$oEngineDoc->appendChild($dom->createElement("ContentType","OrderFormDoc"));

		/*Start Users Node*/
		$oUserNode = $dom->createElement("User");
		$oEngineDoc->appendChild($oUserNode);
		$oUserNode->appendChild($dom->createElement("Name",$this->m_Username));
		$oUserNode->appendChild($dom->createElement("Password",$this->m_Password));

		$oClientIDNode = $oUserNode->appendChild($dom->createElement("ClientId",$this->m_ClientId));
		$oClientIDNode->setAttribute("DataType","S32");
		/*End Users Node*/

		/*Start Instructions Node*/
		$oInstructionsNode = $dom->createElement("Instructions");
		$oEngineDoc->appendChild($oInstructionsNode);
		$oInstructionsNode->appendChild($dom->createElement("Pipeline","PaymentNoFraud"));
		/*End Instructions Node*/

		/*Start OrderFormDoc Node*/
		$oOrderNode = $dom->createElement("OrderFormDoc");
		$oEngineDoc->appendChild($oOrderNode);
		$oOrderNode->appendChild($dom->createElement("Mode",$this->m_Mode));
		$oOrderNode->appendChild($dom->createElement("Id",$this->m_OrderId));

		/*Consumer Node*/

		if(!(empty($this->m_EmailAddress)) || !(empty($this->m_Street1)) || !(empty($this->m_PostalCode)) || !(empty($this->m_CardNumber)) || !(empty($this->m_EndDate)) || !(empty($this->m_StartDate)) || !(empty($this->m_IssueNumber)) || !(empty($this->m_Cvv2))){
			$oConsumerNode = $dom->createElement("Consumer");
			$oOrderNode->appendChild($oConsumerNode);
			if(!(empty($this->m_EmailAddress))){
					$oConsumerNode->appendChild($dom->createElement("Email",$this->m_EmailAddress));
			}

			$oBillToNode = $oConsumerNode->appendChild($dom->createElement("BillTo"));
			$oLocationNode = $oBillToNode->appendChild($dom->createElement("Location"));
			if(!(empty($this->m_EmailAddress)) ){
				$oLocationNode->appendChild($dom->createElement("Email",$this->m_EmailAddress));
			}
				$oAddressNode = $oLocationNode->appendChild($dom->createElement("Address"));
				// name
			if(!(empty($this->m_FirstName)) || !(empty($this->m_LastName)) ){
				$oAddressNode->appendChild($dom->createElement("FirstName",$this->m_FirstName));
				$oAddressNode->appendChild($dom->createElement("LastName",$this->m_LastName));
			}
				// address
			if( !(empty($this->m_Street1)) ){
				$oAddressNode->appendChild($dom->createElement("Street1",$this->m_Street1));
			}
			if( !(empty($this->m_Street2)) ){
				$oAddressNode->appendChild($dom->createElement("Street2",$this->m_Street2));
			}
			if( !(empty($this->m_City)) ){
				$oAddressNode->appendChild($dom->createElement("City",$this->m_City));
			}
			if( !(empty($this->m_StateProv)) ){
				$oAddressNode->appendChild($dom->createElement("StateProv",$this->m_StateProv));
			}
			if( !(empty($this->m_Country)) ){
				$oAddressNode->appendChild($dom->createElement("Country",$this->m_Country));
			}
			if( !(empty($this->m_PostalCode)) ){
				$oAddressNode->appendChild($dom->createElement("PostalCode",$this->m_PostalCode));
			}

			/*Start PaymentMech Node*/
			$oPaymentMechNode = $oConsumerNode->appendChild($dom->createElement("PaymentMech"));

			/*Start CreditCard Node*/
			$oCreditCardNode = $oPaymentMechNode->appendChild($dom->createElement("CreditCard"));

			if(!(empty($this->m_CardNumber))){
				$oCreditCardNode->appendChild($dom->createElement("Number",$this->m_CardNumber));
			}

			if(!(empty($this->m_EndDate))){
				$oExpiresNode = $oCreditCardNode->appendChild($dom->createElement("Expires",$this->m_EndDate));
				$oExpiresNode->setAttribute("DataType","ExpirationDate");
				$oExpiresNode->setAttribute("Locale","840");
			}

			if(!(empty($this->m_StartDate))){
				$oStartNode = $oCreditCardNode->appendChild($dom->createElement("StartDate",$this->m_StartDate));
				$oStartNode->setAttribute("DataType","StartDate");
				$oStartNode->setAttribute("Locale","840");
			}

			if(!(empty($this->m_IssueNumber))){
				$oCreditCardNode->appendChild($dom->createElement("IssueNum",$this->m_IssueNum));
			}

			if(!(empty($this->m_Cvv2))){
				$oCreditCardNode->appendChild($dom->createElement("Cvv2Val",$this->m_Cvv2));
			}

			if(!(empty($this->m_CardNumber))){
				$oCreditCardNode->appendChild($dom->createElement("Cvv2Indicator",$this->m_Cvv2Ind));
			}


			/*End CreditCard Node*/

			/*End PaymentMech Node*/
		}

		$oTxnNode = $oOrderNode->appendChild($dom->createElement("Transaction"));

		$oTxnNode->appendChild($dom->createElement("Type",$this->m_TransactionType));

		if(($this->m_TransactionType == "ReAuth" || $this->m_TransactionType == "RePreAuth") && !(empty($this->m_ReTxnId))){
			$oTxnNode->appendChild($dom->createElement("Id",$this->m_ReTxnId));
		}

		if(!(empty($this->m_PayerSecurityLevel) | empty($this->m_PayerAuthenticationCode) | empty($this->m_CardholderPresentCode))){
			$oPayerSecurityLevelNode = $oTxnNode->appendChild("PayerSecurityLevel",$dom->createElement("PayerSecurityLevel",$this->m_PayerSecurityLevel));
			$oPayerSecurityLevelNode->setAttribute("DateType","S32");
			if(!(empty($this->m_PayerTxnId))){
				$oTxnNode->appendChild($dom->createElement("PayerTxnId",$this->m_PayerTxnId));
			}
		}

		if(!(empty($this->m_Amount))){
			$oCurrentTotalsNode = $oTxnNode->appendChild($dom->createElement("CurrentTotals"));
			$oTotalsNode = $oCurrentTotalsNode->appendChild($dom->createElement("Totals"));

			$oTotalNode = $oTotalsNode->appendChild($dom->createElement("Total",$this->m_Amount));
			$oTotalNode->setAttribute("DataType","Money");
			$oTotalNode->setAttribute("Currency",$this->m_Currency);
		}

		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	function postCallBack($data,$response){
		$dom = new DomDocument();
		$this->m_ResponseDoc = $response;
		/*
		echo "<!--//<pre>".print_r($data, true)."</pre>";
		echo "<br />";
		print_r($response);
		echo "//-->";
		exit();
		*/
		$dom->loadXML($response);

		$xp = new DOMXPath($dom);
		
		$ecrs = $xp->query("EngineDoc/OrderFormDoc/Transaction/CardProcResp/CcErrCode");

		if($ecrs){
			$errorcode = $ecrs->item(0);
		}else{
			throw new Exception("ePDQ:postCallBack(): XML Response Failed");
		}

		$this->m_CcErrorCode = (int) $errorcode->nodeValue;

		switch($this->m_CcErrorCode){
			case 1:

				$authnrs = $xp->query("EngineDoc/OrderFormDoc/Transaction/AuthCode");
				$authn = $authnrs->item(0);

				if($authn){
					$this->m_AuthCode = $authn->nodeValue;
				}
			default:
				$retmnrs = $xp->query("EngineDoc/OrderFormDoc/Transaction/CardProcResp/CcReturnMsg");
				$retmn = $retmnrs->item(0);

				if($retmn){
					$this->m_CcReturnMsg = $retmn->nodeValue;
				}


				$tidnrs = $xp->query("EngineDoc/OrderFormDoc/Transaction/Id");
				$tidn = $tidnrs->item(0);

				if($tidn){
					$this->m_TransactionId = $tidn->nodeValue;
				}

				break;
		}
	}

	function isCreditCard($type = "",$number){
		while(strlen($number) < 16){
			$number = "0" . $number;
		}

		$itotal = 0;
		$len = strlen($number);
		for($i = 0;$i < $len;$i++){
			$dig = (int) $number{$i};
			$mult = 1 + (($i + 1) % 2);
			$sum = $dig * $mult;
			if($sum > 9){
				$sum = $sum - 9;
			}
			$itotal += $sum;
		}

		return (($itotal % 10) == 0);
	}
}
?>