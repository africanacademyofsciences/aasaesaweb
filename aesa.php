<?php
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
?>
<div class="content">



	<div class="main-content">
		
		<div class="container">

			<div class="col-xs-12">
			
				<style>
			.main-content
			{
				margin-top: 100px !important;
			}

.green
{
	color: #51922C !important;
}
.blue
{
	color: #3498DB !important;
}

.green-background
{
	background-color: #E1EFD7;
}
.green-background:hover
{
	background-color: #9EC681;
}

.blue-background
{
	background-color: #8BC4EA;
}
.blue-background:hover
{
	background-color: #63A9D7;
}


	.image-caption
	{
		background-color: #8BC4EA; 
		opacity:0.7; 
		position: relative; 
		left: 0px;
		font-size: 20pt;
		padding: 0px 20px;
	}
	
	.bk-image
	{
		background-image: url("<?=$site->path?>images/18/homepage-image.jpg");
		background-repeat: no-repeat;
		background-size: cover;
	}
	
	.bk-image
	{
		height: 350px;
		.image-caption
		{
			 bottom: -260px;
		}
	}
	@media (min-width: 768px) 
	{
		.bk-image 
		{
			height: 400px;
		}
		
		.image-caption
		{
			 bottom: -310;
		}
	}
	@media (min-width: 992px) 
	{
		.bk-image
		{
			height: 450px;
		}
		.image-caption
		{
			 bottom: -360px;
		}
	}
	@media (min-width: 1200px) 
	{
		.bk-image
		{
			height: 500px;
		}
		.image-caption
		{
			 bottom: -510px;
		}
	}
	@media (min-width: 1500px) 
	{
		.bk-image
		{
			height: 600px;
		}
		.image-caption
		{
			 bottom: -509px;
		}
	}	
</style>
<section style="background-color: #FFFFFF; padding-bottom: 0px;">

<img src="<?=$site->path?>images/18/aesa-logo.jpg" style="height: 70px;">

</section>
<section class="" style="background-color: #FFFFFF; padding-top: 0px;">
<div class="bk-image">
            <div class="image-caption" style="">
				<p style="opacity: 1; margin-bottom: 0px;">
					The Alliance for Excelerating Excellence in Science in Africa (AESA) is an
					initiative established by AAS and NEPAD  to develop science strategies and 
					fund research in Africa.
				</p>

			</div>
</div>
</section>
<section style="background-color: #FFFFFF;">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
				<div class="row">
				<div class="col-xs-4">
					<div class="col-xs-12 green-background">
						<h2 class="green">Programmes</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="col-xs-12 blue-background">
						<h2 class="blue">Partners</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="col-xs-12 green-background">
						<h2 class="green">Research</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				</div>
				<div class="row" style="padding-top: 16px;">
				<div class="col-xs-4">
					<div class="col-xs-12 green-background">
						<h2 class="green">Countries</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="col-xs-12 blue-background">
						<h2 class="blue">Standards</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="col-xs-12 green-background">
						<h2 class="green">About</h2>
						<p>Aegre perspicax umbraculi praemuniet fragilis rures. Fiducia suis deciperet
						matrimonii, ut pessimus pretosius syrtes fermentet umbraculi, etiam Medusa 
						fortiter miscere cathedras. Aegre quinquennalis rures vocificat satis saetosus 
						umbraculi, ut cathedras miscere.</p>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
</section>

			</div>
			
		</div>
		
	</div>

</div>
<?php

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>