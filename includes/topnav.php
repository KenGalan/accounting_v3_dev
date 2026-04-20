<!-- Page Loader -->
<div class="page-loader-wrapper">
	<div class="loader">
		<div class="preloader">
			<div class="spinner-layer pl-indigo">
				<div class="circle-clipper left">
					<div class="circle"></div>
				</div>
				<div class="circle-clipper right">
					<div class="circle"></div>
				</div>
			</div>
		</div>
		<p>Please wait...</p>
	</div>
</div>
<!-- #END# Page Loader -->
<!-- Top Bar -->
<nav class="navbar ">
	<div class="container-fluid navbar-customize" style="background-color: #7C7BAD !important;">
		<div class="navbar-header">
			<a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
			<a href="javascript:void(0);" class="bars"></a>
			<a id="toggleSidebarBtn" style="/*color: #563e3e;*/ letter-spacing:6px; background-color: #7C7BAD !important;" class="navbar-brand font-bold">☰ A/P DISTRIBUTION</a>
			<!-- <a id="toggleSidebarBtn" style="/*color: #563e3e;*/ letter-spacing:6px; background-color: #7C7BAD !important;" class="navbar-brand font-bold" href="<?php $_SERVER['PHP_SELF'] ?>index.php">A/P DISTRIBUTION</a> -->
		</div> <!-- NAVBAR HEADER -->
		<div class="collapse navbar-collapse" id="navbar-collapse">
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown" style="margin-top: -13.5px">
					<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
						<div class="image">
							<?php
							$image  = "";
							if ($_SESSION['ppc']['avatar'] == "") {
								$image = "public/theme/images/user.png";
							} else {
								$image = "http://hris.teamglac.com/" . $_SESSION['ppc']['avatar'];
							}
							?>
							<img src="<?php echo $image; ?>" width="48" height="48" alt="User" style="border-radius: 50%;" />
							<b style='color: white;'><?php echo strtoupper($_SESSION['ppc']['fullname']); ?> &nbsp;
								| &nbsp; <a href="logout.php" style=' color: white;'>Sign Out</a> </b>
						</div>
					</a>
				</li>
			</ul>
		</div>
		<!--  <div class="collapse navbar-collapse" id="navbar-collapse"></div> --> <!-- NAVBAR BUTTONS -->
	</div> <!-- CONTAINER FLUID -->
</nav>