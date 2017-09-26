<?php
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'] . '/treeline/includes/securimage.class.php');

// Reporting errors and trying to display
// the image will not show any image
//ini_set('display_errors', 1);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);

$img = new securimage();

//size configuration
$img->image_height = 50; //the height of the captcha image
$img->image_width = 250; //the width of the captcha image


//background configuration
$img->draw_lines = true; //whether to draw horizontal and vertical lines on the image
$img->draw_lines_over_text = true; //whether to draw the lines over the text
$img->draw_angled_lines = true; //whether to draw angled lines on the image

$img->image_bg_color = '#ffffff'; //the background color for the image
$img->line_color = '#cccccc'; //the color of the lines drawn on the image
$img->line_distance = 15; //how far apart to space the lines from eachother in pixels
$img->line_thickness = 1; //how thick to draw the lines in pixels
$img->arc_line_colors = '#006699,#999999,#cccccc, '; //the colors of arced lines


//text configuration
$img->use_gd_font = false; //whether to use a gd font instead of a ttf font
$img->use_multi_text = true; //whether to use multiple colors for each character
$img->use_transparent_text = true; //whether to make characters appear transparent
$img->use_word_list = false; //whether to use a word list file instead of random code

$img->charset = 'ABCDEFGHKLMNPRSTUVWYZ23456789'; //the character set used in image
$img->code_length = 5; //the length of the code to generate
$img->font_size = 30; //the font size
$img->gd_font_size = 30; //the approxiate size of the font in pixels
$img->text_color = '#000000'; //the color of the text - ignored if $_multi_text_color set
$img->multi_text_color = '#006699,#666666,#333333'; //the colors of the text
$img->text_transparency_percentage = 45; //the percentage of transparency, 0 to 100
$img->text_angle_maximum = 21; //maximum angle of text in degrees
$img->text_angle_minimum = -21; //minimum angle of text in degrees
$img->text_maximum_distance = 50; //maximum distance for spacing between letters in pixels
$img->text_minimum_distance = 40; //minimum distance for spacing between letters in pixels
$img->text_x_start = 10; //the x-position on the image where letter drawing will begin

//filename and/or directory configuration
$img->audio_path = 'audio/'; //the full path to wav files used
$img->gd_font_file = ''; //the gd font to use
$img->ttf_file = $_SERVER['DOCUMENT_ROOT']."/behaviour/facelift1.2/fonts/Bentham.otf"; //the path to the ttf font file to load
$img->wordlist_file = ''; //the wordlist to use 

//$img->show(); // alternate use:  $img->show('/path/to/background.jpg');
$img->show($_SERVER['DOCUMENT_ROOT'].'/silo/tmp/securbg.png');
?>