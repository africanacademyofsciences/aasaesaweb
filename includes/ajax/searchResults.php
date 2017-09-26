<?php
		
	if ($searchAllowed && 0) {		
		$results = $member->getAll($orderBy, $search, $status, $currentPage, $perPage);
		$total = $member->getTotal($search, $status);
		if($results){ // results exists
		?> 
			<ul style="clear:left;">
			<?php
				foreach($results as $result){ // lop through and show results
				?>
					<li><a href="<?=$page->drawLinkByGUID($page->getGUID()).$result->member_id; ?>/"><?php echo $result->firstname; ?> <?php echo $result->surname; ?></a></li>
				<?php
				} // end loop
			?>
			</ul>
			<?php
			echo drawPagination($total, $perPage, $currentPage);
		}
		else{ // results
			?>
			<p>There are no members</p>
			<?php
		}
	}
	else {
		?>
        	<h3 style="clear:left;">Welcome to the <?=$site->title?> members area</h3>
        	<p>Please use the links on the right to manage your account</p>
        <?php
	}
?>