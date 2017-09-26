<?php
    foreach($tiles as $tile) {
        //print "tile(".print_r($tile, 1).")<br>\n";
    }    
?>

<!-- STYLE FOR THE PLUGIN -->   
<link rel="stylesheet" href="/includes/urg/plugin/css/gridGallery.css" />
<!-- <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> -->

<style type="text/css">
    div.mosaic-gridGallery {
        clear: both;
    }
    div.grid-loadMore { display: none; }
</style>

<div class="mosaic-gridGallery">

    <h1>Second attempt purchased version</h1>
    <p>This is the ultimate grid responsive system here: http://www.davidbo.dreamhosters.com/plugins/gridGallery/example1.html</p>
    <p>The fact that some images appaer twice is not important however it is no too tricky to find more images, there just are not that many to pick from</p>
    <div id="grid">

        <?php
        foreach ($tiles as $tile) {
            //print "Tile(".print_r($tile, 1).")<br>\n";
            ?>
            <div class="box" <?=($tile['link']?'data-url="'.$tile['link'].'"':'')?> data-category="Images">
                <div data-image="<?=$tile['image']?>"></div>
                <div class="thumbnail-caption">
                      <h3><?=$tile['title']?></h3>
                      <h5><?=$tile['description']?></h5>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div> <!-- #grid -->

<!-- SCRIPTS FOR THE PLUGIN -->
<!-- <script src="/includes/url/plugin/js/jquery-1.9.1.min.js"></script> -->
<script src="/includes/urg/plugin/js/rotate-patch.js"></script>
<script src="/includes/urg/plugin/js/gridGallery.min.js"></script>

<script>

    $('document').ready(function(){
        //INITIALIZE THE PLUGIN
        //$('#grid').grid();
                $('#grid').grid({ lightBox: true, showFilterBar: false, columnMinWidth: 300 })
    });    

</script>

