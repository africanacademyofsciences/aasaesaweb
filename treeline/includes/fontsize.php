<?php
if(isset($_GET['size']) ) {
 switch($_GET['size']){
  case 'size1':
   $fontsize = 'size1';
  break;
  case 'size2':
   $fontsize = 'size2';
  break;
  case 'size3':
   $fontsize = 'size3';
  break;
  case 'size4':
   $fontsize = 'size4';
  break;
  case 'size5':
   $fontsize = 'size5';
  break;
  default;
   $fontsize = 'size1';
  break;
 }
}
 
setcookie("fontsize", $fontsize, time()+31536000, '/');
 
$ref = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "/";
 
header("Location: $ref");
 
?>