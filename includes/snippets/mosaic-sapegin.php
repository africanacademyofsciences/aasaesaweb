<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script src="/includes/sapegin/jquery.mosaicflow.min.js"></script>
<style type="text/css">
.mosaic-info {
    clear: both;
    width: 100%;
}
.mosaicflow {
	width: 100%;
}
.mosaicflow__column {
	float:left;
	}

.mosaicflow__item {
    position:relative;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
    .mosaicflow__item img {
        display:block;
        width:100%;
        max-width:500px;
        height:auto;
        border: 0px solid #fff;
    }

    .mosaicflow__item p {
            position:absolute;
            bottom:0;
            left:0;
            width:96%;
            margin:0;
            padding:2%;
            background:hsla(0,0%,0%,.5);
            color:#fff;
            font-size:14px;
            text-shadow:1px 1px 1px hsla(0,0%,0%,.75);
            opacity:0;
            -webkit-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
               -moz-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
                 -o-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
                    transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
            }
    .mosaicflow__item:hover p {
            opacity:1;
            }
</style>

<div class="mosaic-info">
    <h1>Original mosaic example</h1>
    <p>This mosaic is based on the mosaic flow system here: http://sapegin.github.io/jquery.mosaicflow/</p>
</div>

<div class="mosaicflow">
    
    <?php
    //print "GOt tiles(".print_r($tiles, 1).")<br>\n";
    foreach($tiles as $tile) {
        if ($tile['image']) {
            ?>
            <div class="mosaicflow__item">
                <?php
                if ($tile['link']) {
                    ?>
                    <a href="<?=$tile['link']?>">
                    <?php
                }
                ?>
                <img width="500" height="500" src="<?=$tile['image']?>" alt="<?=$tile['title']?>">
                <?php
                if ($tile['link']) {
                    ?>
                    </a>
                    <?php
                }
                if ($tile['description']) {
                    ?>
                    <p><?=$tile['description']?></p>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }
    ?>

</div>
