<?php

/*
	
	 Campaign Stats Class
	  
	  Created: 23/06/20079 
	  Created by: by Steven Cook  steven.cook@ichameleon.com
	  
	  
	  Table of contents
	  
	  includes:
	  # newsletter config
	  # isValid
	  # reuse
	  # createNew
	  # subscribe
	  # unsubscribe
	  # update
	  # validate
	  # validateSubject
	  # validateHTMLText
	  # Email design
	  	- getCSS
		- getHTMLHeader
		- GEtHTMLHEader
		- convertImages
		- convertLinks
		- getBodyText
		-setUnsubscribe
		- getBodyTExt
		- getHTMLEmail
		- getPlainEmail
	  # Preferences
	  
	
	*/



class campaignstats{

	private $newsletterId; 
	private $campaignId; 
	
	private $opend; 
	private $bounced; 
	private $sent; 
	private $uniqueOpened; 
	
	private $data; 
	
	public function __construct($newsletterId,$campaignId){
		//get database object 
		global $db; 
		//set newletterId; 
		$this->newsletterId = $newsletterId; 
		//set campaignId
		$this->campaignId = $campaignId;
		//set the data array 
		$this->setData();

	}
	
	//set var
	private function __set($name, $value){
       	 $this->$name = $value;		
	}

	//get var
	public function __get($name) {
           return $this->$name;
	}
	
	//set data array 
	private function setData(){
		$query="SELECT * FROM campaign_stats WHERE campaign_id=".$this->campaignId; 
		
		if ($row=$db->get_row($query)) {			
			$this->data = $row; 			
		}
		return false;
	}	
	
	//get data array
	public function getData(){
		return $this->data;
	}
	


}











?>