

<?php
//echo print_r($site, 1);
//print '<!-- TEST TEST'.$site->id.' -->';
$menu->drawMega($pageGUID);
?>
    
    
<!--	Main navigation 
====================================== -->
<header>
	<div class="container">
		<nav class="navbar navbar-inverse">
			<div class="navbar-header">
				<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?=$site->link?>"><img src="http://aasciences.ac.ke/includes/html/images/18/aesa-logo.png" alt="African Academy of Sciences"></a>
			</div>
			
			
			<div class="collapse navbar-collapse js-navbar-collapse">
				<ul class="nav navbar-nav">
				
                	<li class="home">
                    	<a href="<?=$site->link?>"><i class="fa fa-home"> </i></a>
                    </li>

					<?php
					//print "Got mis(".print_r($mis, 1).") <br>\n";
					if (count($mis)) {
						foreach ($mis as $i=>$mi) {
							if ($mi['anchor']) {
							//print "Got mi(".print_r($mi, 1).") at ind($i)<br>\n";
								?>
								<li class="">
									<a href="#<?=$mi['anchor']?>"><?=($mi['title']?$mi['title']:$mi['anchor'])?></a>
								</li>
								<?php
							}
						}
					}
					?>

                    <!--
					<li class="dropdown mega-dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Programmes</a>
						
						<ul class="dropdown-menu mega-dropdown-menu row">
							<li class="col-sm-3">
								<ul>
									<li class="dropdown-header">Circle</li>
									<li><a href="#">Its scope</a></li>
									<li><a href="#">Partners</a></li>
									<li><a href="#">Fellows</a></li>
									<li><a href="#">Success stories</a></li>
									<li><a href="#">How to get involved</a></li>
								</ul>
							</li>
							<li class="col-sm-3">
								<ul>
									<li class="dropdown-header">AESA</li>
									<li><a href="#">What it is</a></li>
									<li><a href="#">Its scope</a></li>
									<li><a href="#">Programmes</a></li>
									<li><a href="#">Partners</a></li>
									<li><a href="#">Grantees</a></li>
									<li><a href="#">Governance</a></li>
									<li><a href="#">How to get involved</a></li>
								</ul>
							</li>
							<li class="col-sm-3">
								<ul>
									<li class="dropdown-header">CBRM</li>
									<li><a href="#">Its scope</a></li>
									<li><a href="#">Partners</a></li>
									<li><a href="#">Mentees</a></li>
									<li><a href="#">Success stories</a></li>
									<li><a href="#">How to get involved</a></li>
									
								</ul>
							</li>
							
							<li class="col-sm-3">
								<ul>
									<li class="dropdown-header">Science Equipment Policy project</li>
									<li><a href="#">Its scope</a></li>
									<li><a href="#">Progress</a></li>
									<li><a href="#">Partners</a></li>
									<li><a href="#">How to get involved</a></li>
									
								</ul>
							</li>
						</ul>
						
					</li>
                    -->
					
				</ul>
                
				
			</div><!-- /.nav-collapse -->
		</nav>
	</div>
</header>

