<?php

class Form {

	public $id;
	public $name, $title;
	public $description;
	public $msv;
	public $valid;
	public $successmsg, $user_email;
	public $cust_email, $cust_email_addr; 
	private $data_id;
	public $enctype;
	
	public $totalresults, $perpage, $dataperpage;

	public $errormsg = array();
	public $field = array();
	
	public function Form($id=0, $msv=0) {
		global $site;

		$this->msv=$msv?$msv:$site->id;
		
		$this->perpage = 4;
		$this->dataperpage = 20;
		$this->field = new Field();
		
		if ($id) $this->loadByID($id);
		return true;
	
	}


	// ***********************************************************
	// DATA MANAGEMENT FUNCTIONS
	public function create($data) {
		global $db, $site, $user;
		// Make sure we have a unique and valid title for this new form
		if (!$data['title']) {
			$this->errormsg[]="You must specify a unique form title";
			return false;
		}
		
		$name = strtoupper(@generateName($data['title'], "forms"));
		//print "created name($name) from(".$data['title'].")<br>\n";
		if ($id = $this->loadByName($name, $site->id)) {
			$this->errormsg[]="A form named $name already exists";
			return 0;
		}
		
		$query = "INSERT INTO forms (
				msv, name, title, description, 
				date_created, user_created, msg, 
				user_email, cust_email
			)
			VALUES
			(
				".$site->id.", '$name', '".$db->escape($data['title'])."', '".$db->escape($data['description'])."', 
				NOW(), ".($user->id+0).", '".$db->escape($data['success-msg'])."', 
				".($data['user_email']+0).", '".($data['cust_email'])."'
			)
			";
		//print "$query<br>\n";
		if ($db->query($query)) {
			return $db->insert_id;
		}
		return 0;
	}
	// ***********************************************************
	public function update($data) {
		global $db;
		if (!$this->id) return false;
		$query = "UPDATE forms SET 
			description='".$db->escape($data['description'])."',
			msg='".$db->escape($data['success-msg'])."',
			user_email = ".($data['user_email']+0).",
			cust_email = '".($data['cust_email'])."'
			WHERE id=".$this->id;
		//print "$query<br>\n";
		$db->query($query);
		return true;
	}
	// ***********************************************************
	public function duplicate($old_form_id, $data) {
		global $db;
		//print "copy blocks from ($old_form_id) to (".$new_form_id.")<br>\n";
		
		$new_form_id = $this->create($data);
		if (!$new_form_id) {
			$this->errormsg[]="Failed to create a new form";
			return false;
		}
		
		$block_query = "SELECT id, title, sort_order, `status` 
			FROM forms_blocks 
				WHERE form_id=$old_form_id
				AND `status`<>'X' AND title IS NOT NULL
				AND title<>'' ";
		//print "$block_query<br>\n";
		if ($blocks = $db->get_results($block_query)) {
			foreach ($blocks as $block) {
				$query = "INSERT INTO forms_blocks (form_id, title, sort_order, status)
					VALUES 
					($new_form_id, '".$db->escape($block->title)."', ".$block->sort_order.", '".$block->status."') ";
				//print "$query<br>\n";
				if ($db->query($query)) {
					// Copy all fields for this block
					$new_block_id = $db->insert_id;
					if ($new_block_id>0) {
						$field_query = "SELECT id, label, type, `status`, sort_order, name, required
							FROM forms_fields 
							WHERE block_id=".$block->id;
						//print "$field_query<br>\n";
						if ($fields = $db->get_results($field_query)) {
							foreach ($fields as $field) {
								$query = "INSERT INTO forms_fields (block_id, label, type, 
									`status`, sort_order, 
									name, required)
									VALUES 
									($new_block_id, '".$db->escape($field->label)."', '".$field->type."', 
									'".$field->status."', ".$field->sort_order.", 
									'".$field->name."', ".$field->required.")
									";
								//print "$query<br>\n";
								if ($db->query($query)) {
									$new_field_id = $db->insert_id;
									if ($field->type=="radio" || $field->type=="select") {
										//print "collect options for this field too<br>\n";
										$option_query = "SELECT value, title FROM sites_options WHERE name='field-".$field->id."'";
										//print "$option_query<Br>\n";
										if ($options = $db->get_results($option_query)) {
											foreach ($options as $option) {
												$query = "INSERT INTO sites_options (value, name, title) 
													VALUES (".$option->value.", 'field-".$new_field_id."', '".$db->escape($option->title)."') ";
												//print "$query<br>\n";
												if (!$db->query($query)) return false;
											}
										}
									}
								}
								else {
									//print "Failed to create field";
									return false;
								}
							}
						}
					}
					else return false;	// Failed to create new block					
				}
				else {
					//print "Failed to create new block ??";
					return false;	// Failed to add block
				}
			}
			return $new_form_id;
		}
		// Nothing to copy
		else {
			//$this->errormsg[]="This form has no fields to copy but has been created ok";
			return $new_form_id; 
		}
		return false;
	}
	// ***********************************************************
	public function delete() {
		global $db;
		if (!$this->id) return false;
		$query = "UPDATE forms SET `status`='X' WHERE id=".$this->id;
		return $db->query($query);
	}
	// ***********************************************************



	// ***********************************************************
	// FORM LOADING FUNCTIONS 
	public function loadByName($name, $msv=0) {
		global $db, $site;
		$msv = $msv?$msv:$this->msv;
		
		$query = "SELECT id FROM forms WHERE name='$name' AND msv=".$msv;
		//print "$query<br>\n";
		if ($id=$db->get_var($query)) {
			$this->loadByID($id);
			return $id;
		}
		return false;
	}
	public function loadByID($id) {
		global $db, $site;
		
		if (!$id) return false;
		
		$query = "SELECT * FROM forms WHERE id=$id AND msv=".$this->msv." LIMIT 1";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			$this->id = $row->id;
			$this->name = $row->name;
			$this->title = $row->title;
			$this->description = $row->description;
			$this->msv = $row->msv;
			$this->valid = $row->valid;
			$this->successmsg = $row->msg;
			$this->user_email = $row->user_email;
			$this->cust_email = $row->cust_email;
		}
	}
	// ***********************************************************


	// ***********************************************************
	// DATA PROCESSING FUNCTIONS
	public function processData($data) {
		global $db, $page, $captcha;
		$insert = array();
		reset($this->errormsg);
		$clearfield = array();
		$data_id = $data['data_id']+0;
		if (!$data['fid']) $this->errormsg[]="No form ID was found";
		else if ($results = $this->getFieldList($data_id)) {
			
			//print "<!-- process fields -->\n";
			foreach ($results as $result) {
				//print "<!-- Process fields (".print_r($result, true).") -->\n";
				$value = $data[$result->name];
				$clearfield[] = $result->name;
				//print "<!-- ".$result->name." = $value -->\n";
				if ($result->type=="captcha") {
					if (!$captcha->valid) $this->errormsg[]="You must enter the confirmation text correctly.";
				}
				else if ($result->required==1 && $result->type=="file") {
					if (!$_FILES[$result->name]['tmp_name']) {
						$this->errormsg[] = "The file for ".$result->label." must be uploaded";
						//$this->errormsg[] = "Got[".$result->name."] files[".print_r($_FILES[$result->name], 1)."]";
					}
				}
				else if ($result->required==1 && !$value && $result->type!="paragraph") {
					if ($result->type=="checkbox") $this->errormsg[]="You must tick the box entitled : ".$result->label;
					else $this->errormsg[]="You must enter a value for : ".$result->label;
				}
				else if (substr($result->name, 0, 4) == "CONF") {
					//print "<!-- Process fields (".print_r($result, true).") -->\n";
					$fcheck = substr($result->name, 4);
					//print "<!-- Get field($fcheck) -->\n";
					//print "<!-- from data(".print_r($results, 1)." -->\n";
					foreach ($results as $r2) {
						if ($r2->name == $fcheck) {
							//print "<!-- Confirm field (".print_r($result, true).") -->\n";
							$confvalue = $data[$r2->name];
							//print "<!-- Confirm ($value) == ($confvalue) -->\n";
							if ($value != $confvalue) $this->errormsg[] = $r2->label." does not match: ".$result->label;
						}
					}
					
				}
				
				if (!count($this->errormsg)) {
					// Looks like the data is valid
					//print "<!-- data looks good -->\n";
					$ins_value = '';
					
					if ($result->type=="paragraph" || $result->type=="captcha") ;
					else {
					
						if ($result->type=="radio") $ins_value=$value[0];
						else if ($result->type=="checkbox") $ins_value = ($value+0);
						else if ($result->type=="file") {
							$value = $_FILES[$result->name];
							if ($value['name']) {
								switch($value['type']) {
									case 'application/pdf':
									case 'application/x-pdf':
									case "application/acrobat":
										$extension = 'pdf';
										break;
									case 'application/msword':
									case 'application/vnd.ms-word':
										$extension = 'doc';
										break;
									case 'application/excel':
									case 'application/vnd.ms-excel':
									case 'application/x-excel':
									case 'application/x-msexcel':
										$extension = 'xls';
										break;
									case 'image/jpeg':
										$extension = "jpg";
										break;
									default :
										$this->errormsg[]=$result->label." filetype[".$value['type']."] is not supported you must only upload a Word or PDF document or a JPG image file";
										break;
								}
								if ($extension) {
									$destname = "F".$this->id.date("dmHi")."_".$db->escape($value['name']);
									$destfile = $_SERVER['DOCUMENT_ROOT']."/silo/files/forms/".$destname;
									if (move_uploaded_file($value['tmp_name'], $destfile)) {
										$ins_value = $destname;
									}
									else $this->errormsg[]="Failed to copy file from: ".$result->label;
								}
							}						
						}
						else {
							if (is_array($value)) {
								$value = implode(", ", $value);
								$value = str_replace("---", "", $value);
							}
							//print "<!-- add value($value) -->\n";
							$ins_value = $db->escape(str_replace('"', "'", $value));
						}
						
						$insert[]="REPLACE INTO forms_values (data_id, field_id, value)
							VALUES
							(@@DATA_ID@@, ".$result->id.", '".$ins_value."')
							";
						if ($result->name=="EMAIL" && $data['EMAIL']) {
							if (!is_email($data['EMAIL'])) $this->errormsg[]="Your email address is not valid";
							else $this->cust_email_addr = $data['EMAIL'];
						}
						else if ($result->name=="EMAILADDRESS" && $data['EMAILADDRESS']) {
							if (!is_email($data['EMAILADDRESS'])) $this->errormsg[]="Your email address is not valid";
							else $this->cust_email_addr = $data['EMAILADDRESS'];
						}
	
						if ($result->name=="SIGNUP" && $value==1) {
							if ($data['EMAIL']) {
								if (is_email($data['EMAIL'])) {
									// Add to newsletter ??? how
									$this->signup ($data);
								}
							}
							else if (isset($data['EMAIL'])) $this->errormsg[]="You have opted to recieve news and updates from the site but have not entered an email address";
						}
					}
				}
			}
			// If no problems with the data and we have some queries to run ....
			if (count($insert) && !count($this->errormsg)) {
				//print "<!-- need to run some inserts (".print_r($insert, 1).")-->\n";
				if (!$data_id) {
					$query = "INSERT INTO forms_data (form_id, member_id, guid)
					VALUES
					(".($data['fid']+0).", ".($data['member_id']+0).", '".$page->getGUID()."')
					";
					//print "$query<br>\n";
					if ($db->query($query)) {
						$data_id = $db->insert_id;
						$this->data_id = $data_id;
					}
				}
				if (!data_id) $this->errormsg[]="Failed to get data ID to insert form data";
				else {
					foreach ($insert as $tmp) {
						$tmp = preg_replace(array("/\r/", "/\n/"), "", str_replace("@@DATA_ID@@", $data_id, $tmp));
						$tmp1 = testinject($tmp);
						if ($tmp == $tmp1)  {
							//print $tmp."<br>\n";
							if (!$db->query($tmp)) $this->errormsg[]="Failed query($query)";
						}
						else print "<!-- inject?? ($tmp) -->\n";
					}
					//print "<!-- Posted form clear(".print_r($clearfield, 1).") -->\n";
					foreach ($clearfield as $f) {
						//print "<!-- Clear($f) -->\n";
						if (isset($_POST[$f])) unset($_POST[$f]);
					}
				}
			}
		}
		return $data_id;
	}

	
	
	public function sendData($data_id) {
		global $db, $site;
		//print "sD($data_id)<br>\n";

		//include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletter/includes/newsletter.class.php");
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
		$newsletter= new Newsletter();
		
		$data=array();
		$data['FORM_TITLE'] = $this->title;
		$data['REFERENCE_NUMBER'] = $this->data_id;
		
		if ($this->user_email) {
			//print "send $data_id to user(".$this->user_email.")<Br>\n";
	
			$query = "SELECT email FROM users WHERE id=".$this->user_email;
			//print "$query<br>\n";
			$data_email = $db->get_var($query);
			
			if ($data_email && is_email($data_email)) {
				// Get the field listing 
				$query = "SELECT distinct ff.`label`, fv.`value`, ff.`type`
					FROM 
					forms_data fd
					LEFT JOIN forms_values fv ON fd.id=fv.data_id
					LEFT JOIN forms_fields ff ON fv.field_id=ff.id
					LEFT JOIN forms_blocks fb ON fb.id = ff.block_id
					WHERE fv.id is not null 
					AND fd.form_id=".$this->id."
					AND fv.data_id=".$data_id."
					ORDER BY fb.sort_order, ff.sort_order ";
				//print "$query<br>\n";
				if ($results = $db->get_results($query)) {
					foreach ($results as $result) {
						$tmp = $result->value;
						if ($result->type=="file") $tmp = '<a href="'.$site->root.'/silo/files/forms/'.$result->value.'">'.$result->value.'</a>';
						else if ($result->type=="checkbox") $tmp=$tmp==1?"Y":"N";
						$tmp = str_replace("\\n", ", ", $tmp);
						$tmp1 = testinject($tmp);
						if ($tmp1 == $tmp) {
							$data['FORM-DATA'].='<p>'.$result->label.": ".$tmp1."</p>\n";
						}
						else print "<!-- Inject($tmp) -->\n";
					}
				}
				
				//print "<!-- send($data_email, FORM-DATA, ".print_r($data, true).") -->\n";
				$newsletter->sendText($data_email, "FORM-DATA", $data);
				if ($site->id==18) $newsletter->sendText("phil@treelinesoftware.com", "FORM-DATA", $data);
			}
			//else print "failed to get admin email address for user (".$this->user_email.")<br>\n";			
		}
		
		// Do we need to sent an email to the customer?
		if ($this->cust_email) {
			//print "<!-- Send data(".print_r($data, 1).") to visitor....(".$this->cust_email_addr.") -->\n";
			$newsletter->sendText($this->cust_email_addr, "FORM-CUSTOM-".$this->cust_email, $data);
				
		}
	}
	// ***********************************************************



	// ***********************************************************
	// FORM BUILDING FUNCTIONS
	public function addBlock($data) {
		global $db, $site;
		if (!$this->id) return false;
		$query = "SELECT MAX(sort_order)
			FROM forms f
			LEFT JOIN forms_blocks fb ON f.id=fb.form_id
			WHERE f.msv=".$site->id;
		$pos = $db->get_var($query)+1;
		//print "$query<br>\n";
		$query = "INSERT INTO forms_blocks (form_id, title, sort_order)
			VALUES 
			(".$this->id.", '".$db->escape($data['title'])."', ".($pos+0).")";
		//print "$query<br>\n";
		return $db->query($query);
	}
	// ***********************************************************
	public function updateBlock($block_id, $data) {
		global $db, $site;
		if (!$block_id) return false;
		$query = "UPDATE forms_blocks SET title='".$db->escape($data['title'])."' WHERE id=".$block_id;
		$success = $db->query($query);
		return !$db->last_error;
	}
	// ***********************************************************
	public function deleteBlock($block_id) {
		global $db;
		if (!$block_id) return false;
		$query = "SELECT id FROM forms_fields WHERE block_id=$block_id";
		if ($results =$db->get_results($query)) {
			foreach($results as $result) {
				$this->deleteField($result->id);
			}
		}
		$query = "UPDATE forms_blocks SET `status`='X' WHERE id = $block_id";
		return $db->query($query);
	}
	// ***********************************************************
	public function addField($block_id, $data) {
		global $db, $site;
		if (!$block_id) return false;
		$query = "SELECT MAX(ff.sort_order)
			FROM forms_blocks fb
			LEFT JOIN forms_fields ff ON fb.id=ff.block_id
			WHERE fb.id=".$block_id;
		$pos = $db->get_var($query)+1;
		$fieldName = strtoupper(_generateName($data['name']));
		//print "Got fieldname($fieldName) from gN(".$data['name'].")<br>\n";
		//print "$query<br>\n";
		$query = "INSERT INTO forms_fields (block_id, label, name, type, required, sort_order, `status`)
			VALUES 
			(".$block_id.", '".$db->escape($data['label'])."', 
			 '$fieldName', '".$data['type']."', 
			 ".($data['required']+0).", ".($pos+0).", '".($data['hidden']?'H':'A')."')";
		//print "$query<br>\n";
		return $db->query($query);
	}
	// ***********************************************************
	public function updateField($field_id, $data) {
		global $db, $site;
		if (!$field_id) return false;
		//print_r($data);
		$query = "UPDATE forms_fields 
			SET label='".$db->escape($data['label'])."', 
			type='".$data['type']."', required=".($data['required']+0).",
			`status`='".($data['hidden']?'H':'A')."'
			WHERE id=$field_id";
		//print "$query<br>\n";
		if (!$db->query($query)) {
			if ($db->last_error) return false;
		}
		return true;
	}
	// ***********************************************************
	public function deleteField($field_id) {
		global $db;
		$query = "UPDATE forms_fields SET `status`='X' WHERE id=$field_id";
		return $db->query($query);
	}	
	// ***********************************************************




	// ***********************************************************
	// FORM LISTING FUNCTIONS
	public function getFormsList($search, $form_id=0) {
		global $db, $site;
		//print "gFL($search)<br>\n";
		
		if ($search) $where.="AND title LIKE '%$search%' ";
		if ($form_id>0) $where.="AND id=$form_id ";
		$order = "ORDER BY f.title ASC ";
		$query = "SELECT f.id, f.title, f.description, (SELECT count(*) FROM forms_blocks fb WHERE fb.form_id=f.id and fb.`status`='A') AS block_count
			FROM forms f
			WHERE f.msv=".$site->msv." AND f.`status`='A' ";
		//print "$query<br>\n";
		$query.= $where;
		//print "Get form total - $query<br>\n";
		
		$db->query($query);
		$this->totalresults = $db->num_rows;
		//$this->setTotalPages($db->num_rows);	
		$db->flush();

		$limits = "LIMIT ".getQueryLimits($this->perpage, $this->thispage);
		$query.= $order.$limits;
		//print "Actually get data $query<br>\n";
		return $db->get_results($query);
	
	}
	
	public function drawFormsList($page=1, $search, $form_id=0) {
	
		global $help;
		$html = '';
		$no_link = '<span class="no-action"></span>';
		$this->thispage = $page;
		
		if ($results = $this->getFormsList($search, $form_id)) {
		
			foreach ($results as $result) {
				$deletelink = $editlink = $editformlink = $previewlink = $duplicatelink = $datalink = $no_link;
	
				$previewlink = '<a '.$help->drawInfoPopup("Preview this form").' class="preview" href="/treeline/forms/?fid='.$result->id.'&amp;action=preview">Preview</a>';
				$deletelink = '<a '.$help->drawInfoPopup("Delete this form").' class="delete" href="/treeline/forms/?fid='.$result->id.'&amp;action=delete&amp;page='.$page.'">Delete</a>';
				$editlink = '<a '.$help->drawInfoPopup("Edit form details").' class="edit" href="/treeline/forms/?fid='.$result->id.'&amp;action=edit">Edit</a>';
				$editformlink = '<a '.$help->drawInfoPopup("Edit the form").' class="edit-form" href="/treeline/forms/?fid='.$result->id.'&amp;action=editform">Edit form</a>';
				$duplicatelink = '<a '.$help->drawInfoPopup("Duplicate this form").' class="reuse" href="/treeline/forms/?fid='.$result->id.'&amp;action=duplicate">Duplicate</a>';
				$datalink = '<a '.$help->drawInfoPopup("Download data").' class="event-links" href="/treeline/forms/?fid='.$result->id.'&amp;action=download&refresh=1">Download</a>';

				$html.='<tr>
	<td>'.$result->title.'</td>
	<td>'.$result->block_count.'</td>
	<td nowrap class="action">
	'.$previewlink.$editlink.$editformlink.$duplicatelink.$deletelink.$datalink.'
	</td>
<tr>
';

			}
			if ($html) {
				$caption=$pagination='';
				if (!$form_id) {
					$caption = '<caption>'.getShowingXofX($this->perpage, $this->thispage, sizeof($results), $this->totalresults).'</caption>';
					$pagination = drawPagination($this->totalresults, $this->perpage, $this->thispage, "?keywords=".$search);
				}
				$html = '<table class="tl_list">
'.$caption.'
<tr>
	<th scope="col">Form title</th>
	<th scope="col">Blocks</th>
	<td scope="col">Manage form</th>
</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
'.$pagination.'
';
			}
			return $html;
			
		}
		return;
			
	}



	// Function to pull a list of valid fields with all field data
	public function getFieldList($data_id) {	
		global $db;
		//print "gFL($data_id)<br>\n";
		$query = "SELECT fb.title, ff.*".($data_id?",fv.value":"")." FROM 
			forms_blocks fb 
			LEFT JOIN forms_fields ff ON fb.id=ff.block_id ";
		if ($data_id) $query.="LEFT JOIN forms_values fv ON ff.id=fv.field_id ";
		$query.="WHERE fb.form_id=".$this->id." AND ff.`status`='A' ";
		if ($data_id) $query.="AND fv.data_id=$data_id ";
		$query.="ORDER BY fb.sort_order, ff.sort_order";
		//print "$query<br>\n";

		return $db->get_results($query);
	}
	
	// ***********************************************************



	// Add this person to some newsletter group???
	// its a bit of a fix but set up the post fields so the newsletter class can understand them
	private function signup($data) {
		global $db, $site;
		
		// See if we have any groups to subscribe to?
		// if so just subscribe to the first group found.
		$query = "SELECT preference_id FROM newsletter_preferences 
			WHERE site_id=".$site->id."
			AND deleted=0 
			LIMIT 1 ";
		//print "$query<br>\n";
		$group = $db->get_var($query);
		//print "subscribe to group($group)<br>\n";
		if ($group>0) {
		
			include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
			include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
		
			$_POST['email']=$_POST['EMAIL'];
			$_POST['preference']=$group;
			$_POST['allpref']=$group.",";
			
			if ($_POST['NAME']) $_POST['name']=$_POST['NAME'];
			else if ($_POST['FULLNAME']) $_POST['name']=$_POST['FULLNAME'];
			else if ($_POST['FULL_NAME']) $_POST['name']=$_POST['FULL_NAME'];
			else {
				if ($_POST['FIRSTNAME']) $_POST['name']=$_POST['FIRSTNAME']." ";
				if ($_POST['SURNAME']) $_POST['name'].=$_POST['SURNAME']." ";
			}
			Newsletter::subscribe(true);
		}
	}


	// ***********************************************************
	// DATA LISTING FUNCTIONS
	public function getDataList($table, $sfield, $ssearch) {
		global $db, $site;
		//print "gFL($search)<br>\n";
		
		if ($sfield && $ssearch) $where.="$sfield LIKE '%$ssearch%' ";
		$order = "ORDER BY added DESC ";
		$query = "SELECT DATE_FORMAT(added, '%d %b %Y') AS nicedate, t.* FROM $table t ";
		if ($where) $query .="WHERE $where ";

		//print "Get form total - $query<br>\n";
		$db->query($query);
		$this->totalresults = $db->num_rows;
		$db->flush();

		$limits = "LIMIT ".getQueryLimits($this->dataperpage, $this->thispage);
		//print "Got ".$this->totalresults." results. Limits -> $limits<br>\n";
		$query.= $order.$limits;
		//print "Actually get data $query<br>\n";
		return $db->get_results($query);
	
	}
	
	public function drawDataList($table, $page=1, $sfield='', $ssearch='') {
		global $help;
		//print "dDL($table, $page, $sfield, $ssearch)<br>\n";
		$html = '';
		$no_link = '<span class="no-action"></span>';
		$this->thispage = $page;
		
		$recno = 0;
		$fieldcount = 6;
		$maxlen = 20;
		if ($results = $this->getDataList($table, $sfield, $ssearch)) {
		
			foreach ($results as $result) {
				if ($this->testing) print "result(".print_r($result, 1).")<br>\n";
				$i = 0;
				$header = $row = '';
				$row = '<td valign="top" nowrap>'.$result->nicedate.'</td>'."\n";
				$header = '<th valign="top" scope="col">Date</th>'."\n";
				foreach ($result as $f=>$v) {
					if ($i<$fieldcount) {
						//print "Got f($f) v($v)<br>\n";
						if ($f=="added") ;
						else if ($f=="id");
						else if ($f=="nicedate");
						else {
							if (!$recno) $header .= '<th valign="top" scope="col">'.ucfirst(str_replace("-", " ", $f)).'</th>'."\n";
							$row .= '<td valign="top">'.(strlen($v)>$maxlen?substr($v, 0, $maxlen)."..":$v).'</td>'."\n";
						}
					}
					$i++;
				}
				if ($header) $headerrow  = '<tr>'.$header.'<th scope="col">Manage record</th></tr>';
				if ($row) {
				
					$deletelink = $editlink = $editformlink = $previewlink = $duplicatelink = $datalink = $no_link;
		
					$previewlink = '<a '.$help->drawInfoPopup("View record").' class="preview" href="/treeline/forms/?fid='.$this->id.'&amp;table='.$table.'&amp;did='.$result->id.'&amp;action=view">Preview</a>';
					//$deletelink = '<a '.$help->drawInfoPopup("Delete this form").' class="delete" href="/treeline/forms/?fid='.$result->id.'&amp;action=delete&amp;page='.$page.'">Delete</a>';
					//$editlink = '<a '.$help->drawInfoPopup("Edit form details").' class="edit" href="/treeline/forms/?fid='.$result->id.'&amp;action=edit">Edit</a>';
					//$editformlink = '<a '.$help->drawInfoPopup("Edit the form").' class="edit-form" href="/treeline/forms/?fid='.$result->id.'&amp;action=editform">Edit form</a>';
					//$duplicatelink = '<a '.$help->drawInfoPopup("Duplicate this form").' class="reuse" href="/treeline/forms/?fid='.$result->id.'&amp;action=duplicate">Duplicate</a>';
					//$datalink = '<a '.$help->drawInfoPopup("Download data").' class="event-links" href="/treeline/forms/?fid='.$result->id.'&amp;action=download&refresh=1">Download</a>';
	
					$html.='<tr>
	'.$row.'
	<td nowrap class="action">
	'.$previewlink.'
	</td>
<tr>
';
				}
			}
			if ($html) {
				//print "Show (".$this->dataperpage." of ".$this->thispage." size(".sizeof($results).") total(".$this->totalresults.")<br>\n";
				$caption=$pagination='';
				$caption = '<caption>'.getShowingXofX($this->dataperpage, $this->thispage, sizeof($results), $this->totalresults).'</caption>';
				$pagination = drawPagination($this->totalresults, $this->dataperpage, $this->thispage, "?fid=".$this->id."&action=download&table=".$table);

				$html = '<table class="tl_list">
'.$caption.'
'.$headerrow.'
</thead>
<tbody>
'.$html.'
</tbody>
</table>
'.$pagination.'
';
			}
			return $html;
			
		}
		return;
			
	}


	public function drawData($table, $did) {
		global $db, $site;
		//print "dD($table, $did)<br>\n";
		
		$query = "SELECT DATE_FORMAT(added, '%d %b %Y') AS nicedate, t.* FROM $table t WHERE id=".$did;
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {	
			foreach ($results as $result) {
				//print "result(".print_r($result, 1).")<br>\n";
				foreach ($result as $f=>$value) {
					$field = ucfirst(str_replace("-", " ", $f));
					if ($f=="nicedate");
					else {
						$html .= '<tr>
							<td class="field">'.$field.'</td>
							<td class="value">'.$this->formatValue($value).'</td>
						</tr>
						';
					}
					//print "Got f($f) field($field) v($value)<br>\n";
					$i++;
				}
			}
		}
		else $err = "Failed to get data for table($table) record($did)";
		if ($html) return '<table class="tl_list">'.$html.'</table>'."\n";
		return '<p class="error">'.$err.'</p>'."\n";
	}
	
	private function formatValue($v) {

		if (substr(strtolower($v), 0, 7)=="http://") $v = '<a href="'.$v.'" target="_blank">'.$v.'</a>'."\n";
		return $v;	
		
	}
	
}

class Field {

	public $id;
	public $block, $label, $name, $type, $required, $status;
	public $enctype;
	
	public function loadByID($field_id) {
		global $db;
		if (!$field_id) return false;
		$query = "SELECT * FROM forms_fields WHERE id=".$field_id;
		//print "$query<br>\n";
		if ($row=$db->get_row($query)) {
			//print "loading field data<br>\n";
			$this->block=$row->block_id;
			$this->label=$row->label;
			$this->name=$row->name;
			$this->type=$row->type;
			$this->status=$row->status;
			$this->required=$row->required;
			return true;
		}
		return false;
	}

	
	public function drawField($data) {
		//print "dF(".$data->type.")<br>\n";
		$wrap = "field";
		switch ($data->type) {
			case 'text': $html.=$this->drawTextInput($data); $wrap="div"; break;
			case 'textarea': $html.=$this->drawTextArea($data); $wrap="div"; break;
			case 'checkbox': $html.=$this->drawCheckbox($data); break;
			case 'select': $html.=$this->drawSelect($data); $wrap="div"; break;
			case 'radio': $html.=$this->drawRadio($data); break;
			case 'paragraph': $html.=$this->drawParagraph($data); break;
			case 'captcha': $html.=$this->drawCaptcha($data); break;
			case 'file': 
				$this->enctype="form-data";
				$html.=$this->drawFile($data); 
				break;
			default : 
				print "FORMS: Can't draw a ".$data->type."<br>\n";
				break;
		}
		if ($html) {
			if ($wrap=="field") $html = '<fieldset class="fb-'.$data->type.'">'.$html.'</fieldset>';
			else if ($wrap=="div") $html = '<div class="form-group fb-'.$data->type.'">'.$html.'</div>';
		}
		return $html;	
	}
	
	public function drawTextInput($data) {
		//print "<!-- dTI(".print_r($data, 1).") -->\n";
		$placeholder = $this->parseLinks($data->label);
		$placeholder = '';
		$html = '
		<label for="f_'.$data->name.'" class="'.($data->required?"required":"").'">'.$this->parseLinks($data->label).'</label>
		<input type="text" class="form-control text" placeholder="'.$placeholder.'" id="f_'.$data->name.'" name="'.$data->name.'" value="'.($_POST[$data->name]?$_POST[$data->name]:$data->value).'" />
		';
		return $html;
	}
	public function drawTextArea($data) {
		$html = '
		<label for="f_'.$data->name.'">'.$this->parseLinks($data->label).'</label>
		<textarea name="'.$data->name.'" class="form-control" id="f_'.$data->name.'">'.($_POST[$data->name]?$_POST[$data->name]:$data->value).'</textarea>
		';
		return $html;
	}
	public function drawCheckbox($data) {
		if (isset($_POST[$data->name])) $cur_value=$_POST[$data->name];
		else $cur_value=$data->value;
		$html = '
		<label for="f_'.$data->name.'" class="checkbox">
			<input class="checkbox" type="checkbox" id="f_'.$data->name.'" value="1" name="'.$data->name.'" '.($cur_value==1?'checked="checked"':"").' /> '.$this->parseLinks($data->label).'
		</label>
		';
		return $html;
	}
	public function drawSelect($data) {
		global $db, $site;
		//print_r($data);
		if ($data->name=="COUNTRY") $query = "SELECT title, title FROM store_countries ORDER BY title";
		else if ($data->name=="YEAROFBIRTH") {
			$query = '';
			$thisyear = date("Y", time());
			$range = $data->default+0;
			if (!$range) $range = 100;
			for ($i=$thisyear; $i>($thisyear-$range); $i--) {
				$html .= '<option value="'.$i.'">'.$i.'</option>';
			}
		}
		else if ($data->name=="EXPERTISE" || $data->name=="DISEASES") {
			if ($data->name=="EXPERTISE") {
				//$html = "<option>Get expertise fields</option>";
			}
			else if ($data->name=="DISEASES") {
				//$html = "<option>Get disease fields</option>";
			}
			$query = "SELECT fc.title as cattitle, 
				fs.title as subcattitle
				FROM forms_mselect fm 
				INNER JOIN forms_cats fc ON fc.mid = fm.id
				LEFT JOIN forms_scats fs ON fs.cid = fc.id
				WHERE fm.title = '".$data->name."'
				AND fm.msv = ".$site->id."
				ORDER BY fc.sort_order ASC, fs.sort_order ASC
				";
			//$html .= '<option>'.$query.'</option>'."\n";
			//print ("Posted(".print_r($_POST, 1).")<br>\n");
			//print "Got ".$data->name." -> (".print_r($_POST[$data->name], 1).") <br>\n";
			
			if ($results = @$db->get_results($query)) {
				foreach ($results as $result) {
					if ($result->cattitle) {
						if ($result->subcattitle) {
							if ($curcat != $result->cattitle || !$curcat) {
								$curcat = $result->cattitle;
								$html .= '<option disabled>'.$curcat.'</option>'."\n";
							}
							//print "Got ".$data->name." -> (".$_POST[$data->name].") <br>\n";
							$selected = false;
							if (is_array($_POST[$data->name])) {
								foreach ($_POST[$data->name] as $item) {
									//print "Item(---".$result->subcattitle.") == (".trim($item).")<br>\n";
									if ("---".$result->subcattitle == $item) {
										//print "This item is selected<br>\n";
										$selected = true;
									}
								}
							}
							$html .= '<option'.($selected?' selected="selected"':"").'>---'.$result->subcattitle.'</option>'."\n";
						}
						else {
							$html .= '<option>'.$result->cattitle.'</option>'."\n";
						}
					}
				}
			}
			else $html .= '<option>No results found</option>';
			$multiple = "MULTIPLE";
			$xclass=" f-expertise";
			$xselect = " (hold down CTRL and click or CMD and Click to select more than one item)";
			$query = "";
		}
		else $query = "SELECT value, title FROM sites_options WHERE name='field-".$data->id."' ORDER BY title";
		if ($query) {
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					$tmp_value = $result->value>0?$result->value:$result->title;
					$html.='<option value="'.$tmp_value.'"'.((isset($_POST[$data->name])?$_POST[$data->name]:$data->value)==$tmp_value?' selected="selected"':'').'>'.$result->title.'</option>';
				}
			}
		}
		if ($html) $html = '
			<label for="f_'.$data->name.'">'.$this->parseLinks($data->label).'</label>
			<select name="'.($data->name.($multiple?"[]":"")).'" id="f_'.$data->name.'" class="form-control'.$xclass.'" '.$multiple.'>
				<option value="">Select'.$xselect.'</option>
				'.$html.'
			</select>
			';
		return $html;

		/*
		global $db;
		//print_r($data);
		$query = "SELECT value, title FROM sites_options WHERE name='field-".$data->id."' ORDER BY title";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$tmp_value = $result->value>0?$result->value:$result->title;
				$html.='<option value="'.$tmp_value.'"'.((isset($_POST[$data->name])?$_POST[$data->name]:$data->value)==$tmp_value?' selected="selected"':'').'>'.$result->title.'</option>';
			}
		}
		if ($html) $html = '
			<label for="f_'.$data->name.'">'.$this->parseLinks($data->label).'</label>
			<select name="'.$data->name.'" id="f_'.$data->name.'">
				<option value="">Select</option>
				'.$html.'
			</select>
			';
		return $html;
		*/
	}
	public function drawRadio($data) {
		global $db;
		if (isset($_POST[$data->name])) $cur_value=$_POST[$data->name];
		else $cur_value[0]=$data->value;
		//print_r($data);
		$query = "SELECT value, title FROM sites_options WHERE name='field-".$data->id."' ORDER BY title";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$tmp_value = $result->value>0?$result->value:$result->title;
				$tmp_id = generateName($result->title);
				//print "got cur_value(".print_r($cur_value, true).") tmp($tmp_value)<br>\n";
				$html.='
				<label class="" for="f_'.$tmp_id.'">
				<input class="" id="f_'.$tmp_id.'" type="radio" name="'.$data->name.'[]" value="'.$tmp_value.'"'.($cur_value[0]==$tmp_value?' checked="checked"':'').'> '.$this->parseLinks($result->title).'
				</label>
				';
			}
		}
		if ($html) $html = '<fieldset class="radio">
			<p class="fb-para">'.$data->label.'</p>
			'.$html.'
			</fieldset>
			';
		return $html;
	}
	public function drawFile($data) {
		$html = '
		<label for="f_'.$data->name.'">'.$this->parseLinks($data->label).'</label>
		<input type="file" class="text" id="f_'.$data->name.'" name="'.$data->name.'" />
		';
		return $html;
	}
	public function drawCaptcha($data) {
		global $captcha;
		if (is_object($captcha)) {
			$html = '<label for="f_'.$data->name.'">'.$this->parseLinks($data->label).'</label>'.$captcha->drawForm();
		}
		return $html;
	}
	public function drawParagraph($data) {
		$html = '<p class="fb-para">'.$this->parseLinks($data->label).'</p>';
		return $html;
	}
	
	
	// Replace any link placeholders in the label text
	public function parseLink($href, $title) {
		$window="self";
		if ($offset = strpos($title, ";")) {
			$window= (substr($title, $offset+1)=="new")?"blank":$window;
			$title = substr($title, 0, $offset);
		}
		//print "parseLink($href, $title, $window)<br>\n";
		if ($href && $title && $window) return '<a href="'.$href.'" target="_'.$window.'">'.$title.'</a>';
		return '';
	}
	public function parseLinks($text) {
		$new_txt=$text;
		while (preg_match("/\[(.*?)=(.*?)\]/", $new_txt, $reg) ) {
			$new_txt = str_replace("[".$reg[1]."=".$reg[2]."]", $this->parseLink($reg[1], $reg[2]), $new_txt);
		}
		return $new_txt;
	}


	// Check if $name has already been used as an ID in this form
	public function checkName($form_id, $name) {
		global $db;
		$tmp_name=_generateName($name);
		$query = "SELECT ff.id FROM forms f
			LEFT JOIN forms_blocks fb ON f.id=fb.form_id
			LEFT JOIN forms_fields ff ON fb.id=ff.block_id
			WHERE ff.name='$tmp_name' AND f.id=".$form_id;
		//print "$query<br>\n";
		if ($db->get_var($query)) return false;
		//print "checkName OK<br>\n";
		return true;
	}
}

?>