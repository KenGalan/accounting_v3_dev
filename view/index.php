<style>
	.login100-form-title::before{
		background-color: #7C7BAD !important;
	}
	.login100-form-btn{
		background-color: #7C7BAD !important;
	}
</style>
<div class="limiter">
	<div class="container-login100">
		<div class="wrap-login100">
			<div class="login100-form-title" style="background-color: #7C7BAD !important;">
				<span class="login100-form-title-1">
					A/P DISTRIBUTION
				</span>
			</div>
			<form class="login100-form validate-form" action="ajax/login/hris_login.php" method="post">
				<div class="wrap-input100 validate-input m-b-26" data-validate="Username is required">
					<span class="label-input100">Username</span>
					<input class="input100" type="text" name="txtuname" id="username" autocomplete="off" placeholder="Enter username">
					<span class="focus-input100"></span>
				</div>
				<div class="wrap-input100 validate-input m-b-18" data-validate="Password is required">
					<span class="label-input100">Password</span>
					<input class="input100" type="password" name="txtpass" id="password" placeholder="Enter password">
					<span class="focus-input100"></span>
				</div>
				<div class="container-login100-form-btn">
					<button class="login100-form-btn" name="btnlogin" id="btnlogin">
						Login
					</button>
				</div>

				<br /><br /><br /><br />

				<?php
				if (isset($_REQUEST['incorrect']) && $_REQUEST['incorrect'] == 1) {
					$labas = "Wrong username or password";
				} else if (isset($_REQUEST['incomplete']) && $_REQUEST['incomplete'] == 1) {
					$labas = "Please complete input"; 
				} else if (isset($_REQUEST['notallowed']) && $_REQUEST['notallowed'] == 1) {
					$labas = "You are not allowed to enter. Please contact MIS(267) or SYSTEMS(314) to request access.";
				}
				else {
					$labas = "";
				}
				?>
				<div class="warningtext"><?php echo $labas ?></div>
			</form>
		</div>
	</div>
</div>