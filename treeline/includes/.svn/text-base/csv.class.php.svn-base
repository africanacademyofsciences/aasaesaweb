<?php

class CSV {

	public $query;
	public $fields = array();
	public $table, $order, $filename;
	public $errmsg;
	public $num_rows;
	
	public function CSV($query='', $run=false, $filename='') {
		$errmsg = array();
		if ($query) $this->setQuery($query);
		if ($filename) $this->setFilename($filename);
		if ($run && $query) $this->generateCSV();
	}
	
	public function setTable($table) {
		$this->table = $table;
	}
	public function setOrder($order) {
		$this->order = $order;
	}
	
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	public function getFilename() {
		return $this->filename;
	}
	
	public function getRecordCount() {
		return $this->num_rows;
	}
	
	// Query manipulation functions
	public function setQuery($query) {
		$this->query = $query;
	}
	public function getQuery() {
		return $this->query;
	}
	
	
	// Generate a query from a tablename
	public function createQuery() {
		global $db;
		$query = '';
		if (!$this->table) {
			$this->errmsg[]="Can create query as no table specified";
			return false;
		}
		
		$query = "show columns from ".$this->table;
		if ($results=$db->get_results($query)) {
			$query = '';
			foreach ($results as $result) {
				$field = strtolower($result->Field);
				$query.="`$field` AS `$field`, ";
			}
		}
		
		if ($query) {
			$query = "SELECT ".substr($query, 0, -2)." FROM ".$this->table." ";
			if ($this->order) $query.="ORDER BY ".$this->order;
			$this->setQuery($query);
		}
		
	}
	
	
	public function setFields($fields=array()) {
		if (count($fields)) $this->fields = $fields;
	}
	
	// Get a field listing from a query	
	public function generateFields($query = '') {
		//print "gF($query)<br>\n";
		
		// Were we passed a query, if not use the current query
		if (!$query) $query = $this->query;
		
		// Extract field information from the query
		if (preg_match_all("/AS (.*?)[,|\\n| ]/", $query, $reg, PREG_SET_ORDER)) {
			foreach ($reg as $r) {
				$this->fields[]=str_replace(array('`',"\n","\r"), array('', '', ''), $r[1]);
			}
		}
		//print "Got fields ----- <br> \n";
		//foreach ($this->fields as $field) print "F-(".$field.")<br>\n";
		//print " ----- <br>\n";
		return count($this->fields);	
	}


	// Create a generic filename
	public function generateFilename() {
		$prefix = $this->table?$this->table:"csv";
		$this->filename = $prefix."-".date("YmdHis",time()).".csv";
	}

	
	// Generate the listing and create the CSV file	
	public function generateCSV() {
		global $db;
		$this->num_rows = 0;
		$html = $header = '';
		
		// Make sure we have a query to run
		if (!$this->query) {
			if ($this->table) $this->createQuery();
			else {
				$this->errmsg[]="No query has been configured";
				return 0;
			}
		}
		
		// Make sure we have fields to extract;
		if (!count($this->fields)) {
			if (!$this->generateFields()) {
				$this->errmsg[]="No fields found for this query";
				return 0;
			}
		}

		// Generate a filename if not present
		if (!$this->filename) $this->generateFilename();
		
		//print "run query(".$this->query.")<br>";
		
		if ($results = $db->get_results($this->query)) {
			foreach ($results as $result) {
				$row = '';
				foreach ($this->fields as $field) {
					//print "check for field ($field) value=(".$result->{$field}.")<br>\n";
					//print_r($result);
					if (!$this->num_rows) $header.='"'.ucfirst(str_replace("_", " ", $field)).'", ';
					$row.='"'.str_replace('"',"'", $result->{$field}).'", ';
					//print "building row($row)<br>\n";
				}
				//print "got row($row)<br>\n";
				$html.=substr($row, 0, -2)."\n";
				$this->num_rows++;	
			}
			if ($html) {
				$html=substr($header, 0, -2)."\n".$html;
				$filepath = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/".$this->filename;
				//print "try to write to $filepath<br>\n";
				if (file_exists($filepath)) unlink ($filepath);
				if (file_put_contents($filepath, $html)) {
					return $this->filename;
				}
			}
		}
		else {
			$this->errmsg[]="No results returned for this CSV listing";
			$this->errmsg[]=$this->query;
			$this->errmsg[]=print_r($this->fields, true);
		}
		return $this->num_rows+0;
	}

	

}


?>