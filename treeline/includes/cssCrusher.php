<?
	$type = ($_GET['type']>'') ? $_GET['type'] : false;
	$admin = ($_GET['admin']) ? $_GET['admin'] : NULL;

	if( $type == 'css' ){
		header('Content-type: text/css');
		$dir = ($admin) ? 'treeline/style' : 'style';
	}else if($type == 'js'){
		header('Content-type: text/javascript');
		$dir = ($admin) ? 'treeline/behaviour' : 'behaviour';
	}	


	if( $type > '' ){
	
	
	
	header('Pragma: public',true);
	header('Vary: User-Agent',true);
	header('X-Powered-By: Treeline Intranet',true);

	
	// If the server supports it, use GZIP compression
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip")>0){
		header("Content-Encoding: gzip, deflate",true);
	}
	// set the expiry date of the page to be a year from now - this ensures that the cache will stay until the page has been updated.
	header('Expires: '. date('D, j M Y H:i:s',mktime(date('H'),date('i'),01,date('n'),date('j'),date('Y')+1)) .' GMT');
	// This tells the client to use their private local cache - you'll often see this set to cache-control: no-cache
	header('Cache-control: public');
	
	
	
		$params = $_GET['params']>'' ? $_GET['params'] : false;
		$params = explode(',',$params[0]);
	  
	  	if( $type=='css' ){
			ob_start("compress");
		}else{
			ob_start();
		}
		  
		  
		if( count($params)>0 ){
			foreach( $params as $item ){
				//ob_start("compress");
				echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'. $dir .'/'. $item .'.'. $type);
				echo "\n\n\n";
			}
		}
		  
		ob_end_flush();
	}else{
		echo 'no type specified';
	}



  	function compress($buffer) {
		// remove comments
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		// remove tabs, spaces, newlines, etc.
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		return $buffer;
	}

?>