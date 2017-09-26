<?php

	/*
	
		TABLES FUNCTIONS
		
		Everythign to do with bulidng tables
		
		written by: Phil Thompson . phil.thompsn@ichameleon.com
		when: 04/08/2007
		
		edited by:
		when: 
		
		
	
	
	*/
	
	function table($columns, $class = '', $id = ''){
		/* 
		FUNCTION:>
		Create a table
		EXAMPLE USAGE: echo table(array(),'treeline');
		*/
		// if there's a class than add it */
		$class = ($class) ? ' class="'.$class.'"' : '';
		
		// if there's a id than add it */
		$id = ($id) ? ' id="'.$id.'"' : '';
		
        $html = '<table'.$class.$id.'>'."\n\t";
		$html .= '<thead>'."\n\t";
		$html .= '<tr>'."\n\t";
		foreach($columns as $column){
			$html .= '<th scope="col">'.$column.'</th>'."\n\t";
		}
		$html .= '</tr>'."\n\t";
		$html .= '</thead>'."\n\t";
		$html .= '<tbody>'."\n\t";
		//;
		return $html;
	}
	
	function closeTable(){
		//
		
		$html = '</tbody>'."\n\t";
		$html .= '</table>'."\n\t";
		
		return $html;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tables</title>
</head>
<body>
<?=table(array('Column 1', 'Column 2', 'Column 3'),'treeline')?>
<tr>
	<td>a</td>
    <td>b</td>
    <td>c</td>
</tr>
<?=closeTable()?>
</body>
</html>