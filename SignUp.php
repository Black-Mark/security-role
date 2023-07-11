<?php 
include("dbconnect.php");
include("system_id.php");
$user = '';
$mail = '';
if (isset($_REQUEST['register'])) {
	include("DecryptEncrypt.php");
 	$username = strip_tags(mysqli_real_escape_string($con, $_POST['username']));
 	$email = strip_tags(mysqli_real_escape_string($con, $_POST['email']));
 	$password = strip_tags(mysqli_real_escape_string($con, $_POST['password']));
 	$repassword = strip_tags(mysqli_real_escape_string($con, $_POST['repassword']));

 	$errors = false;
 	if($password != $repassword){
 		$user = $_POST['username'];
 		$mail = $_POST['email'];
 		$errors = true;
 	?>
 		<script type="text/javascript">
 			alert("Try again! Password did not match!");
 		</script>
 	<?php 
 	}
 	if(!preg_match("/[A-Za-z]/", $password)) { // Letters
 		echo '<script>alert("The password does not contain letter/s.")</script>';
 		$errors = true;
 	}
 	if (!preg_match("/\W/", $password)) { 
 		echo '<script>alert("The password does not contain special characters.")</script>';
 		$errors = true;
 	}
 	if (!preg_match("/\d/", $password)) {
 		echo '<script>alert("Password does not contain number/s.")</script>';
 		$errors = true;
 	}
 	if (strlen($password) < 8) {
 		echo '<script>alert("The password must be at least 8 alphanumeric characters.")</script>';
        $errors = true;
    }

    $non_dup = true;
    $query = 'SELECT * FROM tbl_useraccounts';
 	if ($is_query_run = mysqli_query($con, $query)) {
		while($query_execute=mysqli_fetch_assoc($is_query_run)){

			$temp_email = decryptthis($query_execute['email'], $key);
			$temp_status = decryptthis($query_execute['status'], $key);
			$temp_password = decryptthis($query_execute['password'], $key);
			$temp_id = $query_execute['user_id'];

			if($temp_email == $email){
				$non_dup = false;
				if($temp_status == 'Active'){
					echo '<script>alert("You already have an account!")</script>';
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$temp_id', 'Existed Account Attempts to Create Another Account but Fails', now())");
				}elseif($temp_status == 'Inactive' and $errors == false){
					$username = encryptthis($username, $key);
 					$password = encryptthis($password, $key);
 					$rank = encryptthis('R1', $key);
 					$status = encryptthis('Active', $key);
					mysqli_query($con, "UPDATE tbl_useraccounts SET username='$username', password='$password', rank='$rank', status='$status' WHERE user_id='$temp_id'");
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$temp_id', 'Account has been Activated', now())");
					?>
					<script type="text/javascript">
						alert("You have successfully registered your account.");
 						window.location = "index.php";
					</script>
					<?php
				}elseif($temp_status == 'Inactive' and $temp_password != $password){
					echo '<script>alert("Your account is Inactive! Wrong Password!")</script>';
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$temp_id', 'Activating Account Fails', now())");
				}elseif($temp_status == 'Banned'){
					echo '<script>alert("Your Account has been Banned!")</script>';
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$temp_id', 'Banned Account Sign Up Attempts', now())");
				}
			}
		}
	}
    if($errors == false and $non_dup == true){
    	$getnum = mysqli_query($con, "SELECT COUNT(*) AS num FROM tbl_useraccounts");
		$num = mysqli_fetch_assoc($getnum);
		$list = mysqli_query($con, "SELECT * FROM tbl_useraccounts");
		$adm_exist = false;
		while($adm = mysqli_fetch_assoc($list)){
			if($adm == 'Administrator'){
				$adm_exist = true;
			}
		}

        $user_id = date("Y").rand(0, 9).rand(0, 9).sprintf("%03d", $num['num']);
	 	$username = encryptthis($username, $key);
 		$email = encryptthis($email, $key);
 		$password = encryptthis($password, $key);
 		$rank = encryptthis('R1', $key);
 		$status = encryptthis('Active', $key);

 		if($num['num'] == 0 and $adm_exist == false){
 			$rank = encryptthis('Administrator', $key);
 			mysqli_query($con, "INSERT INTO tbl_useraccounts VALUES('$user_id', '$username', '$email', '$password', '$rank', '$status', now())");
 			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$user_id', 'Administrator Account Registered', now())");
 		?>
 			<script type="text/javascript">
 				alert("You have successfully registered your account as Administrator.");
 				window.location = "index.php";
 			</script>
 		<?php
 		}else{
 			mysqli_query($con, "INSERT INTO tbl_useraccounts VALUES('$user_id', '$username', '$email', '$password', '$rank', '$status', now())");
 			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$user_id', 'New Account Registered', now())");
 		?>
 			<script type="text/javascript">
 				alert("You have successfully registered your account.");
 				window.location = "index.php";
 			</script>
 		<?php
 		}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>ITEC100A Account</title>
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
					<div class="container">
						<div class="login-form">
							<form class="needs-validation" method="post" autocomplete="off">
								<div class="form-group">
									<label>Username</label>
									<input type="text" class="form-control" placeholder="Enter your Username" name="username" value="<?php echo $user; ?>" autofocus required>
								</div>
								<div class="form-group">
									<label>Email address</label>
									<input type="email" class="form-control" placeholder="Email" name="email" value="<?php echo $mail; ?>" required>
								</div>
								<div class="form-group">
									<label>Password</label>
									<input type="password" class="form-control" placeholder="Password" name="password" required>
								</div>
								<div class="form-group">
									<label>Re-Enter Password</label>
									<input type="password" class="form-control" placeholder="Re-Enter Password" name="repassword" required>
								</div>
									<input type="submit" class="btn btn-success btn-block my-3" value="Register" name="register">
								<div class="register-link m-t-15 text-center">
									<p>Login into account? <a href="index.php">Log In Here</a></p>
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