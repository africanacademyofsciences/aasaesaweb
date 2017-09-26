<?	
	class Website {
		public $config;
	
		public function __construct() {
			// This is loaded when the class is created	
		
		}
		
		public function getSiteConfig(){
			global $db;
			$tmparray = array();
			$query = "SELECT name, value FROM config";
			$results = $db->get_results($query,"ARRAY_A");
			foreach($results as $result){
				$this->config[$result['name']] = $result['value'];
			}
			if(sizeof($this->config)>0){  //// can we just check for the existence of config instead of sizeof??
				return $this->config[$name];
			}
			else{
				return false;
			}
		}
		
		public function getBodyID($url){
			// get a nice id for the <body> tag e.g. <body id="www-example-com">
			global $body_id;
			$body_id = str_replace('.','-',$url);	
			return $body_id;
		}
	}
	
?>