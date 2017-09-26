<?

	/*
	
		FORMDATE: a class that works date/time  and palces them into <select> tags
		
		benefits: 
					works out current time/date
					pre selects values if user has submitted the form previously.
		limitations: 
					doesn't show errors is user select non-existent date e.g. 30/02/09
					doesn't work our current time in minutes and feault them to the closest interval e.g. 13 minutes should be rounded up to 15 or 10
					doens't (yet) allow the user to set a default in the function
					age limiting is the best, it only works on year.
					no error reporting
	
	*/


	class formDate{
		
		public function getYear($selectName,$currentValue, $totalyears,$tense='future',$agelimit = NULL){
		
			/*
			
				Work out the year and create a drop down menu from it
				
				acceptable variables:
				selectName = name/id of the <select element> - REQUIRED
				totalyears = how many year you want in the form e.g. 3 - REQUIRED
				tense - is the form to show years in the future or past. Future is the default. - NOT REQUIRED
				agelimit = does the user have to be over a certain age?
		
			*/			
						
			// start by opening the <select> element
			$year = '<label for="'.$this->createSelectId($selectName).'" class="formdate-label hide">Year</label>'."\n\t";
			$year .= '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="date year">'."\n\t";
			
			
			// work if we're looking dates in the future
			if($tense == 'future'){
				$startyear = date('Y');
				$endyear = date('Y')+$totalyears;
			}
			// or dates in the past
			else if($tense =='past'){
				$startyear = date('Y')-$totalyears;
				$endyear = date('Y');
				// check for an age limit & only show years that are within the allowed range
				if($agelimit != NULL){
					$endyear =  $endyear-$agelimit;
				}
			}
			
			// then create the <option>s
			for ($i = $startyear; $i <= $endyear; $i++){
				unset($selected);
			// check if the form has been posted and set the selected <option> to the user selection
				if($currentValue == $i){
					$selected = ' selected="selected"';
				}
				$year .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n\t";
			}
			// close off the <select> element
			$year .= "</select>\n";
			// return the value
			return $year;
		}
		// end getYear function
		
		
		
		public function getMonth($selectName, $currentValue){
		
			/*
			
				Work out the month and create a drop down menu from it
		
			*/

			// start by opening the <select> element
			$month = '<label for="'.$this->createSelectId($selectName).'" class="formdate-label hide">Month</label>'."\n\t";
			$month .= '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="date month">'."\n\t";
			for ($i = 1; $i <= 12; $i++) {
				unset($selected);
				
				// values
				$monthName = date('F', mktime(12, 0, 0, $i, 1, date('Y')));
				$monthValue = date('m', mktime(12, 0, 0, $i, 1, date('Y')));
			
				if($currentValue == $monthValue){
					$selected = ' selected="selected"';
				}
				
				$month .= '  <option value="'.$monthValue.'"'.$selected.'>'.$monthName.'</option>'."\n\t";
			}
			
			// close off the <select> element
			$month .= "</select>\n";
			// return the value
			return $month;
		}
		// end getMonth function
		
		
		
		public function getDay($selectName, $currentValue){
		
			/*
			
				Work out the day and create a drop down menu from it
		
			*/
			
		
			// start by opening the <select> element
			$day = '<label for="'.$this->createSelectId($selectName).'" class="formdate-label hide">Day</label>'."\n\t";
			$day .= '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="date day">'."\n\t";

			for ($i = 1; $i <= 31; $i++) {
				unset($selected);
				// day value = add leading zero if digit is only 1-9
				$dayValue = (strlen($i) == 1) ? '0'.$i : $i;


				if($currentValue == $dayValue){
					$selected = ' selected="selected"';
				}

				
				$day .= '  <option value="'.$dayValue.'"'.$selected.'>'.$dayValue.'</option>'."\n\t";
			}
			
			// close off the <select> element
			$day .= "</select>\n";
			// return the value
			return $day;
		}
		// end getDay function
		
		
		public function getHour($selectName,$currentValue){
			/*
			
				Work out the hour and create a drop down menu from it
		
			*/
			
			// start by opening the <select> element
			$hour = '<label for="'.$this->createSelectId($selectName).'" class="formdate-label hide">Hour</label>'."\n\t";
			$hour .= '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="time">'."\n\t";
			
			for($i=0; $i<=23; $i++){
				unset($selected);
				//hour value add leading zero if digit is only 1-9
				$hourValue = (strlen($i) == 1) ? '0'.$i : $i;
				
				// check if the form has been posted and set the selected <option> to the user selection
				if($hourValue == $currentValue){
					$selected = ' selected="selected"';
				}
				
				$hour .= ' <option value="'.$hourValue.'"'.$selected.'>'.$hourValue.'</option>'."\n\t";
			}
			
			
			// close off the <select> element
			$hour .= "</select>\n";
			// return the value
			return $hour;
			
		}
		// end getHour function
		
		public function getMinute($selectName, $currentValue, $interval=15){
			/*
			
				Work out the minute and create a drop down menu from it
		
			*/

			// start by opening the <select> element
			$minute = '<label for="'.$this->createSelectId($selectName).'" class="formdate-label hide">Minute</label>'."\n\t";
			$minute .= '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="time">'."\n\t";
			for($i=0; $i<=59; $i+=$interval){
				unset($selected);
				
				if($i == $currentValue){
					$selected = ' selected="selected"';
				}
				
				// add a preceding zero if the minute is 0-9
				if(strlen($i) == 1){
					$minute .= "  <option value=\"0$i\"$selected>0$i</option>\n\t";
				}
				// otherwise just print as is
				else{
					$minute .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n\t";
				}
				
			}	
			// close off the <select> element
			$minute .= "</select>\n";
			// return the value
			return $minute;
			
		}
		// end getMinute function
		
		public function createSelect($options, $selectName){
			
			// start by opening the <select> element
			$select = '<select name="'.$selectName.'" id="'.$this->createSelectId($selectName).'" class="date">'."\n\t";
			// mix in the <option>s
			$select .= $options;
			// close off the <select> element
			$select .= "</select>\n";
			return $select;
		}
		
		public function createSelectId($selectName){
			return str_replace(']','',str_replace('[','_',$selectName));
		}
		
	}


?>