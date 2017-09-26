<?php
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/mosaic.class.php");
$mos = new Mosaic($site->id);
$mos->loadByID($mid);
$tiles = $mos->loadTiles();
//print "Loaded mos(".print_r($mos, 1).")<br>\n";

if ($mos->type=="sapegin") {
    ob_start();
    ?>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <?php
    include_once($_SERVER['DOCUMENT_ROOT']."/includes/snippets/mosaic-sapegin.php");
    ?>

    <br /><br  /><br />

    <?php
    $replace = ob_get_contents();
    ob_end_clean();
}
else if ($mos->type="gridGallery") {
    ob_start();
    include_once($_SERVER['DOCUMENT_ROOT']."/includes/snippets/mosaic-gridgallery.php");
    ?>
    <br /><br  /><br />
    <?php
    $replace = ob_get_contents();
    ob_end_clean();
}


$replace .= '<div class="clearfix"></div>'."\n";

?>
