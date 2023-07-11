<?php 
include("dbconnect.php");
include("system_id.php");
if(isset($_POST['emailcheck'])){
	include ("DecryptEncrypt.php");
	$emailvalidatesent = false;
	$emailvalidate = strip_tags(mysqli_real_escape_string($con, $_POST['emailvalidate']));

	$query = 'SELECT * FROM tbl_useraccounts';
 	if($is_query_run = mysqli_query($con, $query)){
		while($query_execute=mysqli_fetch_assoc($is_query_run)){
			if($emailvalidate == decryptthis($query_execute['email'], $key) and decryptthis($query_execute['status'], $key) != 'Banned'){
				$user_id = $query_execute['user_id'];
				if(decryptthis($query_execute['status'], $key) == "Active"){
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$user_id', 'Verification Code Sent to Email', now())");
				?>
				<script type="text/javascript">
					alert('Verification Code sent to your Email Address');
				</script>
				<?php
				}elseif(decryptthis($query_execute['status'], $key) == "Inactive"){
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$user_id', 'Verification Code Sent to Inactive Email', now())");
				?>
				<script type="text/javascript">
					alert('Verification Code sent to your Email Address');
				</script>
				<?php
				}
				$emailvalidatesent = true;
			}
			if($emailvalidate == decryptthis($query_execute['email'], $key) and decryptthis($query_execute['status'], $key) == 'Banned'){
				?>
				<script type="text/javascript">
					alert('Your Email is Banned! No Verification Code delivered.');
				</script>
				<?php
				$user_id = $query_execute['user_id'];
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$user_id', 'Banned Account Verification Code Attempt', now())");
				$emailvalidatesent = true;
			}
		}
	}
	if($emailvalidatesent == false){
		?>
		<script type="text/javascript">
			alert("Your Email Address didn't registered into any Account!");
		</script>
		<?php
		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$system_id', 'Unregistered Email Asking for Verification Code', now())");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ITEC100A Accounts</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
</head>
<body>
	<div class="container">
		<div class="row d-flex justify-content-center">
			<div class="col-6">
				<div class="card shadow mt-5">
					<div class="text-center">
						<h3 style="margin: 10px; color: darkblue;"><b>ITEC100A ACCOUNT</b></h3>
					</div>
					<div class="container text-center">
						<div class="login-form">
							<form class="needs-validation" method="post" autocomplete="off">
								<div class="form-group">
									<label>Enter your Email Address</label>
									<input type="email" class="form-control" placeholder="Email Address" name="emailvalidate" required>
								</div>
								<input type="submit" name="emailcheck" class="btn btn-success btn-block my-3" value="Send Verification Code">
								<div class="register-link m-t-15 text-center">
									<p>Login into account? <a href="index.php">Log In Here</a></p>
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