<?php
/**
 * net.php - Simple network handeling functions
 *
 * @author Aaron Shrimpton aaron.shrimpton@aqnet.co.uk
 * @copyright Aqua Technologies Limited 2006
 * @link http://www.aqnet.co.uk
 * @version 1.00, 2006-04-05
 *
 **/

class NetLib {

	var $errormessage;

	function NetLib(){
		$this->errormessage = "";
	}

	function getErrormessage(){
		return $this->errormessage;
	}

	function addError($error){
		$this->errormessage .= "\n\n" . $error;
	}

    /**
     * formatData - Format data for sending as post or get string.
     *
     * @param  data  Hash of values to be sent to the server.
     **/

    function formatData($data){
        $string = "";
        foreach($data as $key => $value){
            if($string != ""){
                $string .= "&";
            }
            $string .= urlencode($key) . "=" . urlencode($value);
        }
        return $string;
    }

    /**
     * requestPost - Post data to a form using curl.
     *
     * @param  url  The url of the form to post to.
     * @param  data  The data (already formated with format data).
     * @param  callback  A function to process the response data.
     * @param  cdata  Data to be passed to the callback.
     **/

    function requestPost($url,$data,$callback = null,$cdata = null){
        set_time_limit(60);
        $cSession = curl_init();
        curl_setopt($cSession,CURLOPT_URL,$url);
        curl_setopt($cSession,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($cSession,CURLOPT_HEADER,0);
        curl_setopt($cSession,CURLOPT_POST,1);
        curl_setopt($cSession,CURLOPT_POSTFIELDS,$data);
        curl_setopt($cSession,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($cSession,CURLOPT_TIMEOUT,30);

        $response = curl_exec($cSession);
		
		//echo "<br /><!--".$response."--><br />";
		
		if(curl_error($cSession)){
            $this->addError("Payment Server Connectivity Error : " . curl_error($cSession),4);
            return;
        }

        if($callback != null){
            return call_user_func_array($callback,array($cdata,$response));
        }else{
            return $response;
        }
    }
}
?>
