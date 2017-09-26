<?php
class Treeline_object
{
	public $fields = array();		// An array of database fields that this object has
	private $backup_keys = array();		// Used to create the SQL queries, can be ignored

	// The table where this object will get saved
	public $table_name = '';
	
	// -------------------- Getters --------------------
	
	public function get($field)			{return $this->fields[$field];}
	public function get_fields()		{return $this->fields;}
	public function get_table_name()	{return $this->table_name;}
	
	// -------------------- Setters --------------------
	
	public function set($field, $value)			{$this->fields[$field] = $value;}
	public function set_table_name($table_name)	{$this->table_name = $table_name;}
	public function set_fields($array)
	{
		foreach ($array as $key => $value)
		{
			//print "check if ($key) is allowable(";
			//print_r($this->backup_keys);
			//p/rint ")<br>";
			if (in_array($key, $this->backup_keys))
			{
				//print "aDD $key -- $value<br>";
				$this->fields[$key] = $value;
			}
		}
	}
	
	// -------------------- Magic --------------------
	
	function __construct($id = null)
	{
		$this->backup_keys = array_keys($this->fields);
	
		// If we are constructed with an id
		if ($id)
		{
			// Try to load the objects details straight away
			$this->load($id);
		}
	}

	
	/* -------------------------------------------------
	Load this object details from the database into
	the fields array.
	------------------------------------------------- */
	public function load($id = null)
	{
		global $db;
		
		if ($id) $this->fields['id'] = $id;
		
		return $this->fields = $db->get_row
		("
			SELECT * FROM {$this->table_name}
			WHERE id = ".$this->db_safe($this->fields['id'])."
		",
		ARRAY_A);
	}
	
	/* -------------------------------------------------
	Save this object into the database.
	------------------------------------------------- */
	public function save()
	{
		if ($this->fields['id'])
		{
			return $this->update_fields();
		}
		else
		{
			return $this->insert_fields();
		}
	}
	
	/* -------------------------------------------------
	Delete this object from the database
	------------------------------------------------- */
	public function delete()
	{
		global $db;
		
		$id = $this->db_safe($this->fields['id']);

		if ($id)
		{
			return $db->query
			("
				DELETE FROM {$this->table_name}
				WHERE id = $id
			");
		}
	}
	
	/* -------------------------------------------------
	Insert this object into the database.
	------------------------------------------------- */
	private function insert_fields()
	{
		global $db;
	
		$fields	= $this->db_safe($this->fields);
		
		$columns= '('.	implode(', ', array_keys($this->fields)).')';
		$data	= "('".	implode("', '", $fields)				."')";
	
		// Return whether or not the object was inserted
		$query = "INSERT INTO {$this->table_name} $columns VALUES $data";
		//print "$query<br>";
		$saved = $db->query($query);

		// Update object to have an ID
		if ($saved) $this->fields['id'] = $db->insert_id;
		
		return $saved;
	}
	
	/* -------------------------------------------------
	Update this object in the database.
	------------------------------------------------- */
	private function update_fields()
	{
		global $db;
		
		$fields = $this->db_safe($this->fields);
	
		if (!($id = $this->fields['id'])) return false;	// No ID, can't update
		else unset($fields['id']);						// ID specified, can't overwrite it though
		
		// Go through each of the save-able fields and build up the query
		foreach ($fields as $key => $field)
		{
			$data .= "$key = '$field', ";
		}
		$data = rtrim($data, ', ');
		
		// Return whether or not the object was updated		
		$query = "UPDATE {$this->table_name} SET $data WHERE id = $id";
		//print "run q($query)<br>";
		$db->query($query);
		$r = (boolean)$db->rows_affected;
		return $r;
	}
	
	
	/* -------------------------------------------------
	Accept a field, and a value and return whether or not
	there is an object in the table that exists with
	those details.
	------------------------------------------------- */
	public function exists_by($field, $value)
	{
		global $db;
		
		$id		= $this->db_safe($this->fields['id']);
		$field	= $this->db_safe($field);
		$value	= $this->db_safe($value);
		
		if ($id) $not_self = " AND id != $id";
		
		return $db->get_var
		("
			SELECT IF (COUNT(id) >= 1, 1, 0)
			FROM {$this->table_name}
			WHERE $field = '$value'
			".$not_self."
		");
	}
	
	/* -------------------------------------------------
	Accept a string or an array, and make each value
	safe for saving in the database.
	------------------------------------------------- */
	public function db_safe($data)
	{
		global $db;
	
		if (is_string($data))
		{
			$data = $db->escape($data);
		}
		elseif (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$value = $this->db_safe($value);
			}
		}
		return $data;
	}

}
?>