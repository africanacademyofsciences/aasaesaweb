

<?php
//echo print_r($site, 1);
//print '<!-- TEST TEST'.$site->id.' -->';
$menu->drawMega($pageGUID);

print "<!-- main nav for site(".$site->id.") -->\n";
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
                <?php
                if ($site->id == 18)
                {
                ?>
                    <a class="navbar-brand" href="<?=$site->link?>"><img src="/includes/html/images/18/aesa-logo.png" alt="African Academy of Sciences"></a>
                
                <?php
                }
                else
                {
					$sitelogo = $site->path."images/".$site->id."/aas-logo.png";
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$sitelogo)) {
						print "<!-- No site logo(".($_SERVER['DOCUMENT_ROOT'].$sitelogo).") -->\n";
						$sitelogo = $site->path."images/aas-logo.png";
					}
	                ?>
                    <a class="navbar-brand" href="<?=$site->link?>"><img src="<?=$sitelogo?>" alt="African Academy of Sciences"></a>
    	            <?php
                }
                ?>
            </div>
            
            
            <div class="collapse navbar-collapse js-navbar-collapse">
                <ul class="nav navbar-nav">
                
                    <li class="home">
                        <a href="<?=$site->link?>"><i class="fa fa-home"> </i></a>
                    </li>
							
					<?php
                    if ($site->id==19) {
						if (!$_SESSION['member_id']) {
							?>
							<li><a href="<?=$site->link?>member-login/">Login</a></li>
							<?php
						}
						else {
							?>
							<li><a href="<?=$site->link?>member-login/">My account</a></li>
							<?php
						}
					}
					
					if ($site->id==19 && !$_SESSION['member_id']) ;
					else {
						//print "<!-- menu loop -->\n";
						foreach ($menu->items as $item) {
						  //  print "<!-- Site(".$site->id.") Got menu item(".print_r($item, 1).")  -->\n";
							
							//Default to empty
							$link = '';
							$class = '';
							$dataToggle = '';
							
							if ($site->id == 18) print "<!-- title(".$item['title'].") -->\n";
							//Remove ability to open drop down on specific pages. 
							//these use landing pages instead.
							if ($item['title'] == "About" && $site->id == 18)
							{
								$link = $site->link.'about/about/';
							}
							else if ($item['title'] == "Think Tank" && $site->id == 18)
							{
								$link = $site->link.'think-tank/think-tank/';
							}
							else if ($item['title'] == "Research" && $site->id == 18)
							{
								$link = $site->link.'research/research/';
							}
							else if ($item['title'] == "Programmes")
							{
								$link = $site->link.'aesa/';
								$link = "http://aesa.ac.ke";
								$item['title'] = 'AESA';
							}
							else if ($item['title']=="AESA Community of Practice" && $site->id==18) {
								$link = "http://testing.com";
							}
							else if ($item['title'] == "Recognising Excellence")
							{
								$link = $site->link.'recognising-excellence/recognising-excellence/';
							}
							else
							{
								$link = '#';
								$class = "dropdown-toggle";
								$dataToggle = 'data-toggle="dropdown"';
							}
							
							?>
							<li class="dropdown mega-dropdown">
								<a href="<?=$link?>" class="<?=$class?>" <?=$dataToggle?>><?=$item['title']?></a>
								<ul class="dropdown-menu mega-dropdown-menu row">
									<?php
									if ($item['title']=="Test") {
										?>
										<!--<li class="col-sm-3">
											<ul>
												<li class="dropdown-header">Temporary pages</li>
												<li><a href="/includes/html/content.html">Content page</a></li>
												<li><a href="/includes/html/content-alt.html">Content page variation</a></li>
												<li><a href="/includes/html/content-no-sidebar.html">Content page no sidebar</a></li>
												<li><a href="/includes/html/news-index.html">News index</a></li>
												<li><a href="/includes/html/events-index.html">Events index</a></li>
												<li><a href="/includes/html/landing-page.html">Landing page</a></li>
												<li><a href="/includes/html/publications.html">Publications</a></li>
												<li><a href="/includes/html/form.html">Form templates</a></li>
												<li><a href="/includes/html/contact-us.html">Contact us</a></li>
												<li><a href="/includes/html/ushortcodes.html">Shortcodes</a></li>
											
											</ul>
										</li>-->
										<?php
									}
									else if ($item['title']=="About" && $site->id != 18) {
										?>
										<li class="col-sm-3">
											<ul>
												<li class="dropdown-header">Contact us</li>                            
												<li class="hidden-xs">
													<address>
														<ul>
															<li><a href="mailto:<?=$site->contact['email']?>"><i class="ion-ios-email-outline"></i>Email us</a></li>
														</ul>
														<ul>
															<li><i class="ion-ios-navigate-outline"></i>8 Miotoni Lane, Karen</li>
															<li>P.O. Box 24916-00502</li>
															<li>Nairobi, Kenya</li>
														</ul>
														<ul>
															<li><i class="ion-ios-telephone-outline"></i>+254 20 240 5150</li>
															<li>+254 20 806 0674</li>
														</ul>
														<ul>
															<li><i class="ion-iphone"></i>+254 736 888 001</li>
															<li>+254 725 290 145</li>
														</ul>
														<!--
														<ul>
															<li><i class="ion-ios-printer-outline"></i>+254 20 8060674</li>
														</ul>
														-->
														<ul>
															<li><a href="mailto:president@aasciences.ac.ke"><i class="ion-ios-email-outline"></i>Email AAS President</a></li>
														</ul>
													</address>
												</li>
											</ul>
										</li>
										<?php
									}
									echo $item['html'];
	
									if ($item['title']=="About" && $site->id != 18) {
										?>
										<li class="col-sm-3">
											<ul>
												<li class="dropdown-header">Sign in</li>
													<?php
													if ($mode!="edit") {
														include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/memberLogin.php'); 
													}
													?>
													<!--
													<form class="form" role="form" action="<?=$site->link?>member-login/" method="post">
														<div class="form-group form-group-sm">
															<label class="sr-only" for="email">Email address</label>
															<input type="email" class="form-control" id="email" placeholder="Enter email">  
														</div>
														<div class="form-group form-group-sm">
															<label class="sr-only" for="password">Email address</label>
															<input type="password" class="form-control" id="password" placeholder="Enter password">               
														</div>
														<button type="submit" class="btn btn-default btn-block">Sign in</button>
													</form>   
													-->
												<li class="divider"></li>
												<li class="dropdown-header">Member tools</li>
												<?php
												if ($_SESSION['member_id']>0) {
													?>
													<li><a href="<?=$site->link?>member-login/">Member Area</a></li>
													<?php
												}
												else {
													?>
													<li><a href="/recognising-excellence/the-aas-fellowships/recognising-excellence-/">Become a fellow</a></li>
													<li><a href="<?=$site->link?>member-login/?action=forgotten-password">Password reminder</a></li>                                                
													<?php
												}
												?>
												<li class="divider"></li>
												<li><a href="<?=$site->link?>enewsletters/">Subscribe to our eNewsletters</a></li>
											</ul>
										</li>
										<?php
									}
									?>
								</ul>
							</li>
							<?php
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
						

                
						<?php
                    }
                    ?>

                </ul>
                
                
                <?php
                if ($mode!="edit") {
                    ?>
                    <form action="<?=$site->link?>search/" class="navbar-form navbar-right" role="search" id="searchForm" >
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="keywords" placeholder="Search">
                            <span class="input-group-btn">
                                <a class="btn btn-link" type="button" href="javascript:document.getElementById('searchForm').submit();"><i class="ion-ios-search"></i></a>
                            </span>
                        </div>
                    </form>
                    <?php
                }
                ?>
                
            </div><!-- /.nav-collapse -->
		</nav>
	</div>
</header>

