<?php
include("dbconnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>ITEC100A Account</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="row d-flex justify-content-center">
			<div class="col-6">
				<div class="card shadow mt-5">
					<div class="text-center">
						<h3 style="margin: 10px; color: darkblue;"><b>ITEC100A ACCOUNT</b></h3>
					</div>
					<div class="container">
						<div class="login-form">
							<form class="needs-validation" action="Login.php" method="post" autocomplete="off">
								<div class="form-group">
									<label>Email Address</label>
									<input type="text" class="form-control" placeholder="Email Address" name="email" autofocus required>
								</div>
								<div class="form-group">
									<label>Password</label>
									<input type="password" class="form-control" placeholder="Enter your Password" name="password" required>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox"> Remember Me
									</label>
									<label class="float-right">
										<a href="ForgotPassword.php">Forgot Password?</a>
									</label>
								</div>
								<div class="form-group">
									<center>
									<?php $captcha_chance = rand(1, 10);
									if ($captcha_chance > 8) { ?>
										<!--<label><b>CAPTCHA!</b></label><br>-->
										<label>Are you a human? Answer this.</label><br>
										<?php
										$oprnd = rand(0,2);
										$num1 = 0;
										$num2 = 0;
										$op = "";
										$correct = 0;
										if($oprnd == 0){
											$num1 = rand(1,25);
											$num2 = rand(1,25);
											$op = "+";
											$correct = $num1 + $num2;
										}elseif($oprnd == 1){
											$num1 = rand(1,25);
											$num2 = rand(1,25);
											$op = "-";
											while($num1 < $num2){
												$num2 = rand(1,25);
											}
											$correct = $num1 - $num2;
										}elseif($oprnd == 2){
											$num1 = rand(1,12);
											$num2 = rand(1,10);
											$op = "*";
											$correct = $num1 * $num2;
										}
										?>
										<div class="input-group mb-3">
      										<div class="input-group-prepend">
        										<span class="input-group-text"><b><?php echo $num1; ?></b></span>
        										<span class="input-group-text"><b><?php echo $op; ?></b></span>
        										<span class="input-group-text"><b><?php echo $num2; ?></b></span>
      										</div>
      										<input type="number" class="form-control" name="answer" placeholder="Enter Your Answer" onkeypress="return event.charCode >= 48 && event.charCode <= 57;" min="0" required>
    									</div>
										<input type="hidden" name="correct" value="<?php echo $correct; ?>" required>
									<?php }else{ ?>
										<input type="hidden" name="correct" value="0">
										<input type="hidden" name="answer" value="0">
									<?php } ?>
									</center>
								</div>
								<input type="submit" class="btn btn-success btn-block my-3" value="Log In" name="login">
								<input type="reset" class="btn btn-success btn-block my-3"name="reset" value="Clear">
								<div class="register-link m-t-15 text-center">
									<p>Don't have account? <a href="SignUp.php">Sign Up Here</a></p>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>