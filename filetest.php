<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?

$thisfile = $_SERVER['DOCUMENT_ROOT'] .'/test.pdf';

echo $thisfile.'<br />';

if( file_exists($thisfile) ){
	echo 'file exists!<br />';
	//if( $keywords = generateKeywords('doc','test.doc') ){
	if( $keywords = originalGenerateKeywords('pdf','test.pdf') ){
		//echo 'keywords:<br /><pre>'. print_r($keywords,true) .'</pre><br />';
		echo 'keywords:<br /><pre>'. join(' ',$keywords) .'</pre><br />';
	}else{
		echo 'Could not get keywords!<br />';
	}
	
	//echo '<br /><br />RAW:<br />'. file_get_contents($thisfile);
}else{
	
}






function generateKeywords($format,$file) {
	// This checks if the file is a Word document or PDF
	// then runs one of two shell scripts to extract text from them
	// It then reads in the response line by line
	// removes punctuation
	// identifies unique tokens
	// and returns them all in one space-delimited string
	$keywords = array();
	switch( $format ){
		case 'doc':
			/*
			if ($format == 'doc') {
				$command = 'strings '.$file;
			}
			else if ($format = 'pdf') {
				$command = 'pdftotext '.$file .' -'; 
			}	
			exec($command, $lines);
			*/
			$stream = file_get_contents($_SERVER['DOCUMENT_ROOT'] .'/'. $file);
			/*
			foreach ($lines as $line) {
				$stream .= $line;
			}
			*/
			//$punctuation = array('.',',','-','@','\'','"','/',':',';','?','(',')','&');
			$punctuation = array(',','\'','"','/',':',';','?','(',')','&');
			//$stream = str_replace($punctuation,' ',$stream);
			$sticks = preg_split('/\s+/', $stream);
			$uniquewords = array_unique($sticks);
			foreach ($uniquewords as $u) {
				if( $u >' ' ){
					if( $u=='MERGEFORMAT' && $format=='doc' ){
						break;
					}else{
						foreach($punctuation as $p){
							$u = str_replace($p,'',$u);
						}
						$keywords[] = strtolower( htmlentities( trim($u) ) );
					}
				}
			}
		break;
		case 'pdf':

require_once($_SERVER['DOCUMENT_ROOT'] .'/includes/pdfsearch.class.php');

$fp = fopen($_SERVER['DOCUMENT_ROOT'] .'/'. $file, "r");
$content = fread($fp, filesize($_SERVER['DOCUMENT_ROOT'] .'/'. $file));
fclose($fp);

// Allocate class instance
$pdf = new pdf_search($content);
//$keywords = $pdf->getBuffer();

$searchText = 'php';
// And do the search
if ($pdf->textfound($searchText)) {
    echo "We found $searchText.";
}
else {
    echo "$searchText was not found.";
}

$keywords = $pdf->lines;
echo '<pre>'. print_r($lines,true) .'</pre>';

			break;
	}
	
	return $keywords;
}




function originalGenerateKeywords($format,$file) {
	// This checks if the file is a Word document or PDF
	// then runs one of two shell scripts to extract text from them
	// It then reads in the response line by line
	// removes punctuation
	// identifies unique tokens
	// and returns them all in one space-delimited string
	$keywords = array();
	if ($format == 'doc' || $format == 'pdf') {
		if ($format == 'doc') {
			$command = 'strings '.$_SERVER['DOCUMENT_ROOT'] .'/'. $file;
		}
		else if ($format = 'pdf') {
			$command = 'pdftotext '. $_SERVER['DOCUMENT_ROOT'] .'/'. $file .' -'; 
		}
		echo 'command: '	. $command .'<br />';
		exec($command, $lines);
		$stream = '';
		foreach ($lines as $line) {
			$stream .= $line;
		}
		$punctuation = array('.',',','-','@','\'','"','/',':',';','?','(',')','&','\\','[',']','>','<','=','+','{','}','#','|','!','_');
		$stream = str_replace($punctuation,' ',$stream);
		$sticks = preg_split('/\s+/', $stream);
		$uniquewords = array_unique($sticks);
		foreach ($uniquewords as $u) {
				
					if( $u=='MERGEFORMAT' && $format=='doc' ){
						break;
					}else{
						foreach($punctuation as $p){
							$u = str_replace($p,'',$u);
						}
						if( $u >' ' ){
							$keywords[] = strtolower( htmlentities( trim($u) ) );
						}
					}
				
		}
	}
	return $keywords;
}
?>
</body>
</html>
