<?php

	/*
	
		FORMS FUNCTIONS
		
		Everythign to do with bulidng forms
		
		written by: Phil Thompson . phil.thompsn@ichameleon.com
		when: 04/08/2007
		
		edited by:
		when: 
		
		form
		fieldset
		label
		input
		textarea
		select
		button
	
	
	*/
	
	function form($id='', $method = 'post', $action = '', $class = ''){
		/* 
		FUNCTION:>
		Open a form tag 
		EXAMPLE USAGE: echo form('contactForm','post','/page.php','class');
		*/
		// if there's a class than add it */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		// if there's a id than add it */
		$id = ($id) ? ' id="'.$id.'"' : '';
		
        $html = '<form'.$id.$class.' action="'.$action.'" method="'.strtolower($method).'">'."\n\t";
		//;
		return $html;
	}
	
	function fieldset($legend = '', $class = '', $id =''){
		/* 
		FUNCTION:> Fieldset
		Open a fieldset tag: All form should have every wrapped inside at least one fieldset 
		EXAMPLE USAGE: echo fieldset('Start date','date','start_date');
		
		*/
		
		// if there's a class than add it */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		// if there's a id than add it */
		$id = ($id) ? ' id="'.$id.'"' : '';
		
        $html = '<fieldset'.$class.$id.'>'."\n\t";
		
		/*  If there's a <legend> then show it. */
		$html .= ($legend) ? '<legend>'.$legend.'</legend>'."\n\t" : '';
		
		
		return $html;
	}
	
	function label($for, $text, $class = ''){
		/* 
		FUNCTION:> Label
		Add a label tag
		EXAMPLE USAGE: echo label('$title','Title','required');
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		$html = '<label for="'.$for.'"'.$class.'>'.$text.'</label>'."\n\t";
		
		return $html;
	
	}
	
	function input($type, $name, $id, $value = '', $class = ''){
		/* 
		FUNCTION:> Input
		Add an input tag
		EXAMPLE USAGE: echo input('text','title','title',$_POST['title'],'required');
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		$html = '<input type="'.$type.'" name="'.$name.'" id="'.$id.'"'.$class.' value="'.$value.'" />'."\n\t";
		
		
		return $html;
	
	}
	
	function textarea($name, $id, $value = '', $class = '', $rows = 5, $cols= 5){
		/* 
		FUNCTION:> Textarea
		Add an textaear tag
		EXAMPLE USAGE: echo textarea('title', 'title', $_POST['title'], 'required', 10, 20);
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		$html = '<input type="'.$type.'" name="'.$name.'" id="'.$id.'"'.$class.' value="'.$value.'" />'."\n\t";
		
		
		return $html;
	
	}
	
	function checkbox($name, $options, $currentValue = '', $class = ''){
		/* 
		FUNCTION:> Checkbox
		Add an checkbox button input tags
		EXAMPLE USAGE: echo radio('radio',array("Option 1"=>"1","Option 2"=>"2"),$_POST['radio']);
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' '.$class.'' : '';
		
		
		$html = '';
		
		
		foreach($options as $option => $value){
			unset($selected);
			$selected = ($value == $currentValue) ? ' checked="checked"' : '';
			$html .= '<input type="checkbox" name="'.$name.'" id="'.$name.'_'.$value.'" value="'.$value.'" class="checkbox'.$class.'"'.$selected.' />'."\n\t";
			$html .= '<label for="'.$name.'_'.$value.'" class="checklabel">'.$option.'</label>'."\n\t";
		}
		
		
		return $html;
	
	}
	
	function radio($name, $options, $currentValue = '', $class = ''){
		/* 
		FUNCTION:> Radio
		Add an radio button input tags
		EXAMPLE USAGE: echo radio('radio',array("Option 1"=>"1","Option 2"=>"2"),$_POST['radio']);
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' '.$class.'' : '';
		
		
		$html = '';
		
		
		foreach($options as $option => $value){
			unset($selected);
			$selected = ($value == $currentValue) ? ' checked="checked"' : '';
			$html .= '<input type="radio" name="'.$name.'" id="'.$name.'_'.$value.'" value="'.$value.'" class="checkbox'.$class.'"'.$selected.' />'."\n\t";
			$html .= '<label for="'.$name.'_'.$value.'" class="checklabel">'.$option.'</label>'."\n\t";
		}
		
		
		return $html;
	
	}
	
	function select($name, $id, $options, $showChoose = '', $currentValue = '', $class =''){
		/* 
		FUNCTION:> Select
		Add an select
		EXAMPLE USAGE: echo select('options','options',array("Option 1"=>"1","Option 2"=>"2"),$_POST['options']);
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		
		$html = '<select name="'.$name.'" id="'.$id.'"'.$class.'>'."\n\t";
		/*  If there's a class then show it then show it. */
		$html .= ($showChoose) ? ' <option value="">'.$showChoose.'</option>'."\n\t" : '';
		
		foreach($options as $option => $value){
			unset($selected);
			$selected = ($value == $currentValue) ? ' selected="selected"' : '';
			$html .= '<option value="'.$value.'"'.$selected.'>'.$option.'</option>'."\n\t";
		}
		$html .= '</select>'."\n\t";
		
		
		return $html;
	
	}
	
	
	function button($type = 'submit', $value, $name, $class, $id = ''){
		/* 
		FUNCTION:> Button
		Add an button tag
		EXAMPLE USAGE: echo button('submit','Go!','submit','submit button');
		
		*/
		/*  If there's a class then show it then show it. */
		$class = ($class) ? ' class="'.$class.'"' : '';
		// if there's a id than add it */
		$id = ($id) ? ' id="'.$id.'"' : '';
		
		$html = '<button type="'.$type.'" name="'.$name.'"'.$class.$id.'>'.$value.'</button>'."\n\t";
		
		
		return $html;
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Forms</title>
</head>
<body>
<?=form()?>
<?=fieldset('legend','class','id')?>
<?=label('title','Title:','required')?>
<?=input('text','title','title',$_POST['title'],'required')?>
<?=label('options','Options:','required')?>
<?=select('options','options',array("Option 1"=>"1","Option 2"=>"2"),'Choose',$_POST['options'],'required')?>
<?=fieldset('Important')?>
<?=checkbox('terms',array("Terms"=>1, "Terms x"=>2),$_POST['terms'],'required')?>
<?=fieldset('Radio')?>
<?=radio('radio',array("Radio 1"=>"1","Radio 2"=>"2"),$_POST['radio'])?>
</fieldset>
</fieldset>
<?=fieldset('','buttons')?>
<?=button('submit','Go!','submit','submit button')?>
</fieldset>
</fieldset>
</form>
</body>
</html>