<?php

// WORK OUT FONT SIZE
// if the cookie exists
 if(isset($_COOKIE['fontsize']) ) {
  $fontsize = $_COOKIE['fontsize'];
  $fontclass = ' '.$fontsize;
   if($fontsize != 'size1'){
  	$fontsize = '<link href="/css/fontsize/'.$fontsize.'.css" rel="stylesheet" type="text/css" />'."\n";
  }
  else{
  	$fontsize = '';
  }
 }
 // else show the default
 else{
  	$fontsize = '';
 	$fontclass = ' size1';
 }

$fontnav = '<ul id="font">
     <li id="size1"><a href="/inc/fontsize.php?size=size1" rel="nofollow">A</a></li>
     <li id="size2"><a href="/inc/fontsize.php?size=size2" rel="nofollow">A</a></li>
     <li id="size3"><a href="/inc/fontsize.php?size=size3" rel="nofollow">A</a></li>
     <li id="size4"><a href="/inc/fontsize.php?size=size4" rel="nofollow">A</a></li>
	 <li id="size5"><a href="/inc/fontsize.php?size=size5" rel="nofollow">A</a></li>
    </ul>
	';
	
?>