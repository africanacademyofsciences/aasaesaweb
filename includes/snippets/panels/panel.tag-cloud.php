<?php
if (!is_object($tags)) global $tags;
?>

<div class="panel-heading">
Tags
</div>
<div class="panel-body">
   	<?=$tags->drawTagCloud()?>
</div>
