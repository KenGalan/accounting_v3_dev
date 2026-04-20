<!DOCTYPE html>
<html>
<head>
	<!-- <style>
		section.content{
			margin: 100px 0px 0 5px !important;
		}
	</style> -->
<?php include('includes/head.php'); ?>

</head>
<body class="theme-deep-purple">

<!-- Top Bar -->
<?php 
	include('includes/topnav.php');
?>

<section>
	<?php include('includes/sidebar.php');?>
</section>

<section class="content">
	<?php
		include("controller/" . getFileName());
	?>
</section><!-- Section -->


<?php include('includes/scripts.php'); ?>

</body>
</html>