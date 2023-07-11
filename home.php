<?php
include("dbconnect.php");
include("DecryptEncrypt.php");
include("session.php");
include("system_id.php");

$query = 'SELECT * FROM `tbl_useraccounts`';
if ($query_run = mysqli_query($con, $query)) {
	while($query_execute=mysqli_fetch_assoc($query_run)){
		if ($_SESSION['aid'] == $query_execute['user_id']) {
			$sess_id = $query_execute['user_id'];
			$sess_username = decryptthis($query_execute['username'], $key);
			$sess_email = decryptthis($query_execute['email'], $key);
			$sess_rank = decryptthis($query_execute['rank'], $key);
			$sess_password = decryptthis($query_execute['password'], $key);
			$sess_status = decryptthis($query_execute['status'], $key);
		}
	}
}

if(isset($_POST['userapply_changes'])){
	$app_pass = strip_tags(mysqli_real_escape_string($con, $_POST['app_pass']));
	$new_username = strip_tags(mysqli_real_escape_string($con, $_POST['new_username']));
	$new_password = strip_tags(mysqli_real_escape_string($con, $_POST['new_password']));
	$confirm_new_password = strip_tags(mysqli_real_escape_string($con, $_POST['confirm_new_password']));	

	$usernamenew = false;
	$passwordnew = false;
	$errors = false;

	if ($sess_password == $app_pass) {
		if ($sess_username != $new_username) {
			$usernamenew = true;			
		}

		if ($new_password == $confirm_new_password) {
			if ($new_password != '') {
				if(!preg_match("/[A-Za-z]/", $new_password)) { // Letters
 					echo '<script>alert("The password does not contain letter/s.")</script>';
 					$errors = true;
 				}
 				if (!preg_match("/\W/", $new_password)) { 
 					echo '<script>alert("The password does not contain special characters.")</script>';
 					$errors = true;
 				}
 				if (!preg_match("/\d/", $new_password)) {
 					echo '<script>alert("Password does not contain number/s.")</script>';
 					$errors = true;
 				}
 				if (strlen($new_password) < 8) {
 					echo '<script>alert("The password must be at least 8 alphanumeric characters.")</script>';
    			    $errors = true;
    			}

    			if ($errors == false) {
    				$passwordnew = true;
    			}
			}
		}else{
			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Failed to Apply Changes, New Password does not match', now())");
		?>
	 		<script type="text/javascript">
	 			alert("Changes doesn't apply! Password does not match!");
	 		</script>
	 	<?php
		}

		if ($usernamenew == true and $passwordnew == true) {
			$sess_username = $new_username;
			$sess_password = $new_password;
			$new_username = encryptthis($new_username, $key);
			$new_password = encryptthis($new_password, $key);
			mysqli_query($con, "UPDATE tbl_useraccounts SET username='$new_username' WHERE user_id='$sess_id' ");
			mysqli_query($con, "UPDATE tbl_useraccounts SET password='$new_password' WHERE user_id='$sess_id' ");
			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Successfully Change Account Username and Password', now())");
			?>
	 		<script type="text/javascript">
	 			alert("Account Username and Password has Successfully Changed!");
	 		</script>
	 		<?php
		}elseif ($usernamenew == true and $passwordnew == false) {
			$sess_username = $new_username;
			$new_username = encryptthis($new_username, $key);
			mysqli_query($con, "UPDATE tbl_useraccounts SET username='$new_username' WHERE user_id='$sess_id' ");
			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Successfully Change Account Username', now())");
			?>
	 		<script type="text/javascript">
	 			alert("Account Username has Successfully Changed!");
	 		</script>
	 		<?php
		}elseif($usernamenew == false and $passwordnew == true){
			$sess_password = $new_password;
			$new_password = encryptthis($new_password, $key);
			mysqli_query($con, "UPDATE tbl_useraccounts SET password='$new_password' WHERE user_id='$sess_id' ");
			mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Successfully Change Account Password', now())");
			?>
	 		<script type="text/javascript">
	 			alert("Account Password has Successfully Changed!");
	 		</script>
	 		<?php
		}else{
			if ($errors) {
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Password Requirement does not meet', now())");
			}else{
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'No Changes Made', now())");
	 		}
	 	?>
	 		<script type="text/javascript">
	 			alert("No Changes Applied!");
	 		</script>
	 	<?php
		}
	}else{
		if($app_pass != ''){
		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Failed to Apply Changes, Incorrect Password', now())");
	?>
	 	<script type="text/javascript">
	 		alert("Changes doesn't apply! Incorrect Password!");
	 	</script>
	 <?php
		}
	}
}

if (isset($_POST['acc_remove'])) {
	$rem_pass = strip_tags(mysqli_real_escape_string($con, $_POST['rem_pass']));
	if ($rem_pass == $sess_password) {
		$stating = encryptthis("Inactive", $key);
		mysqli_query($con, "UPDATE tbl_useraccounts SET status='$stating' WHERE user_id='$sess_id'");
		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Account is now Inactive', now())");
		$sess_status = "Inactive";
		?>
	 	<script type="text/javascript">
	 		alert("The Account will be Removed Upon Logging Out!");
	 	</script>
	 <?php
	}else{
		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Removing Account Fail Due to Incorrect Password', now())");
	?>
	 	<script type="text/javascript">
	 		alert("Unable to Remove the Account!");
	 	</script>
	 <?php
	}
}

if (isset($_POST['cancel_remove'])) {
	$stating = encryptthis("Active", $key);
	$sess_status = "Active";
	mysqli_query($con, "UPDATE tbl_useraccounts SET status='$stating' WHERE user_id='$sess_id'");
	mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$sess_id', 'Account is now Active, Cancels Removal', now())");
	?>
	 <script type="text/javascript">
	 	alert("The Removal of the Account is Cancelled!");
	 </script>
	 <?php
}

if (isset($_POST['clear_records'])) {
	mysqli_query($con, "DELETE FROM tbl_useraccounts_records");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ITEC100A ACCOUNTS</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="page-holder bg-cover">
		<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
			<ul class="navbar-nav mr-auto mt-2 mt-lg-0 nav nav-pills" role="tablist">
				<li class="nav-item mr-sm-2">
					<a class="nav-link active" data-toggle="pill" href="#home"><strong>HOME</strong></a>
				</li>
				<li class="nav-item mr-sm-2">
					<a class="nav-link" data-toggle="pill" href="#User_Profile"><strong>USER PROFILE</strong></a>
				</li>
				<li class="nav-item mr-sm-2">
					<a class="nav-link" data-toggle="pill" href="#Account_List"><strong>ACCOUNT LIST</strong></a>
				</li>
				<?php if($sess_rank == 'R3' or $sess_rank == 'Administrator'){ ?>
				<li class="nav-item mr-sm-2">
					<a class="nav-link" data-toggle="pill" href="#management"><strong>MANAGEMENT</strong></a>
				</li>
				<?php } ?>
				<li class="nav-item mr-sm-2">
					<a class="nav-link" data-toggle="pill" href="#Activity_Records"><strong>ACTIVITY RECORD</strong></a>
				</li>
			</ul>
			<form class="form-inline">
				<strong class="text-white mr-sm-5"><?php echo "Welcome, ".$sess_username."!"; ?></strong>
				<a href="logout.php" class="btn btn-outline-danger btn-big"><b>Log Out</b></a>
			</form>
		</nav>
	</div>
	<div class="container">
		<div class="tab-content">
			<div id="home" class="container tab-pane active"><br>
				<h2 align="center">Web Security</h2>
				<p align="center">All about different methods you can use to strenghten the security of your website.</p>
				<hr>
				<p>These are some of the web security measures and methods tht we have learned through the whole semester. Here are also some of the PHP functions and SQL queries to also help solidify your web security.</p>
				<h4>Topic Overview</h4>
				<ul>
  					<li>Login Authentication</li>
  					<li>Access Control</li>
  					<li>Encryption and Decryption</li>
  				</ul>
				<h4>Login Authentication: </h4>
				<p class="indent">Login Authentication is your first line of defense for your web page. Authentication is the process of determining whether someone or something is, in fact, who or what it says it is. Authentication technology provides access control for systems by checking to see if a user's credentials match the credentials in a database of authorized users or in a data authentication server. In doing this, authentication assures secure systems, secure processes and enterprise information security.</p>
				<h4>Access Control</h4>
				<p class="indent">Level of Access or access control refers to the ability to control the level of access that individuals or entities have to a network or system and how much information they applications via communication links. For this, each entity trying to gain access must first be identified or authenticated, so that access rights can be tailored to the individuals. Access control keeps confidential information such as customer data, personally identifiable information, and intellectual property from falling into the wrong hands. It’s a key component of the modern zero trust security framework, which uses various mechanisms to continuously verify access to the company network. Without robust access control policies, organizations risk data leakage from both internal and external sources. </p>
				<h4>Encryption and Decryption</h4>
				<p class="indent">Before we understand Encryption and Decryption we need to know first what is <b>Cryptography</b>. So what is <b>cryptography</b>? Cryptography is used to secure and protect data during communication. It is helpful to prevent unauthorized person or group of users from accessing any confidential data. Encryption and decryption are the two essential functionalities of cryptography.
				A message sent over the network is transformed into an unrecognizable encrypted message known as data encryption. At the receiving end, the received message is converted to its original form known as decryption.</p>
				<p class="indent"><b>Encryption</b>, Encryption is a process which transforms the original information into an unrecognizable form. This new form of the message is entirely different from the original message. That’s why a hacker is not able to read the data as senders use an encryption algorithm. Encryption is usually done using key algorithms.
				Data is encrypted to make it safe from stealing. However, many known companies also encrypt data to keep their trade secret from their competitors.</p>
				<p class="indent"><b>Decryption</b>, Decryption is a process of converting encoded/encrypted data in a form that is readable and understood by a human or a computer. This method is performed by un-encrypting the text manually or by using keys used to encrypt the original data.</p>
				<p class="indent">Some of its importance are: </p>
				<ul>
  					<li>Helps you to protect your confidential data such as passwords and login id</li>
  					<li>Helpful for network communication (like the internet) and where a hacker can easily access unencrypted data.</li>
  					<li>It is an essential method as it helps you to securely protect data that you don’t want anyone else to have access.</li>
  					<li>Helps you to ensure that that the document or file has not been altered</li>
  				</ul>
  				<hr>
  				<p class="indent">Here are some PHP function and SQL queries to use for web security.</p>
  				<p class="indent"><b>PHP strip_tags.</b>It removes HTML, JavaScript or PHP tags from a string. This function is useful when we have to protect our application against attacks such as cross-site scripting.</p>
  				<p class="indent"><b>PHP filter_var function.</b>It is used to validate and sanitize data. Validation checks if the data is of the right type. A numeric validation check on a string returns a false result. Sanitization is removing illegal characters from a string. The code is for the commenting system. It uses the filter_var function and FILTER_SANITIZE_STRIPPED constant to strip tags.</p>
  				<p class="indent"><b>mysqli_real_escape_string funtion</b>. This function is used to protect an application against SQL injection.</p>
  				<p class="indent"><b>MD5 and SHA1</b>. MD5 is the acronym fpt Message Digest 5 and sha1 is the acronym for Secure Hash Alhorithm 1. They are both used to encrypt things. Once a string has been encrypted, it is tedious to decrpyt. MD5 and sha1 are vry useful when storinh passwords in the database.</p>
			</div>
			<div id="User_Profile" class="container tab-pane fade"><br>
				<div class="card img-thumbnail">
      				<div class="card-header bg-primary text-center text-white">
      					<h2><strong class="card-text">PERSONAL INFORMATION</strong></h2>
      				</div>
      				<div class="card-body bg-white">
      					<div class="card-text">
							<ul class="nav nav-tabs" role="tablist">
								<li class="nav-item">
									<a class="nav-link active text-black" data-toggle="tab" href="#View_UserInfo"><b style="color: black;">Info</b></a>
								</li>
								<li class="nav-item">
									<a class="nav-link text-black" data-toggle="tab" href="#Edit_UserInfo"><b style="color: black;">Edit</b></a>
								</li>
								<?php if($sess_rank != 'Administrator'){ ?>
								<li class="nav-item">
									<a class="nav-link text-black" data-toggle="tab" href="#Remove_UserInfo"><b style="color: black;">Remove Account</b></a>
								</li>
								<?php } ?>
							</ul>
						</div>
      				</div>
      				<div class="card-body bg-white">
      					<div class="tab-content">
							<div id="View_UserInfo" class="container tab-pane active"><br>
								<div class="container p-2">
									<table width="100%">
      									<tr>
      										<td><img src="user.png" class="mx-auto d-block rounded" width="50%"></td>
      										<td width="50%">
      											<p><b>Account ID: </b> <?php echo $sess_id; ?></p>
      											<p><b>Username: </b> <?php echo $sess_username; ?></p>
      											<p><b>Email Address: </b> <?php echo $sess_email; ?></p>
      											<p><b>Rank: </b> <?php echo $sess_rank; ?></p>
      										</td>
      									</tr>
      								</table>
								</div>
							</div>
							<div id="Edit_UserInfo" class="container tab-pane"><br>
								<div class="container p-2">
									<form method="post" autocomplete="off">
									<table width="100%">
      									<tr>
      										<td><img src="user.png" class="mx-auto d-block rounded" width="50%"></td>
      										<td width="50%">
      											<p><b>Account ID: </b> <?php echo $sess_id; ?></p>
      											<p><b>Username: </b>  <input type="text" name="new_username" value="<?php echo $sess_username; ?>" required></p>
      											<p><b>Email Address: </b><?php echo $sess_email; ?></p>
      											<p><b>Rank: </b> <?php echo $sess_rank; ?></p>
      											<p><b>New Password: </b> <input type="password" name="new_password"></p>
      											<p><b>Confirm Password: </b> <input type="password" name="confirm_new_password"></p>
    											<div class="card">
    												<div class="card-header">
    													Input your password to Apply Changes.
    												</div>
    												<div class="card-body">
    													<input type="password" name="app_pass" required>
    													<input type="submit" name="userapply_changes" value="Apply Changes" class="btn btn-outline-primary btn-sm">
    												</div>
    											</div>
      										</td>
      									</tr>
      								</table>
      								</form>
								</div>
							</div>
							<?php if($sess_rank != 'Administrator'){ ?>
							<div id="Remove_UserInfo" class="container tab-pane"><br>
								<div class="container p-2 text-center">
									<form method="post" autocomplete="off">
										<center><table class="text-center">
											<?php	
											if ($sess_status == "Active") {
												?>
												<tr><td>Are you sure you want to remove your Account in our Website?</td></tr>
												<tr><td><hr>Enter your Password to Verify:</td></tr>
												<tr><td><input type="password" name="rem_pass" required></td></tr>
												<tr><td>
												<input type="submit" name="acc_remove" value="Yes" class="btn-outline-dark">
												<input type="reset" name="acc_remain" value="No" class="btn-outline-dark">
												</td></tr>
												<?php
											}elseif ($sess_status == "Inactive"){
												?>
												<tr><td><input type="submit" name="cancel_remove" value="Cancel Account Removal" class="btn btn-outline-danger btn-big"></td></tr>
												<?php
											}
											?>
										</table></center>
									</form>
								</div>
							</div>
							<?php } ?>
						</div>
      				</div>
      				<div class="card-footer bg-primary text-center text-white">
      					<p>Date Registered: <?php echo mysqli_fetch_assoc(mysqli_query($con, "SELECT reg_date FROM tbl_useraccounts WHERE user_id = '$sess_id'"))['reg_date']; ?></p>
      				</div>
    			</div>
			</div>
			<div id="Account_List" class="container tab-pane fade"><br>
				<div class="container p-2 my-4 bg-primary text-white">
					<h3 align="center"><b>ITEC100A ACCOUNT LIST</b></h3>
				</div>
				<div class="container">
					<?php if($sess_rank == 'Administrator'){ ?>
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" data-toggle="tab" href="#Decrypted_View">Decrypted View</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#Encrypted_View">Encrypted View</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#EncryptDecrypt_View">Decrypted and Encrypted View</a>
						</li>
					</ul>
					<?php } ?>
					<?php if($sess_rank == 'Administrator'){ ?>
					<div class="tab-content">
						<div id="Decrypted_View" class="container tab-pane active"><br>
					<?php } ?>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
											<th class="text-primary" style="width: 12%;">Account ID</th>
											<?php } ?>
											<th class="text-primary" style="width: 15%;">Username</th>
											<?php if($sess_rank == 'Administrator' or $sess_rank == 'R2' or $sess_rank == 'R3'){ ?>
											<th class="text-primary" style="width: 15%;">Email Address</th>
											<?php } ?>
											<?php if($sess_rank == 'Administrator'){ ?>
											<th class="text-primary" style="width: 15%;">Password</th>
											<?php } ?>
											<th class="text-primary" style="width: 10%;">Rank</th>
											<?php if($sess_rank == 'Administrator'  or $sess_rank == 'R3'){ ?>
											<th class="text-primary" style="width: 10%;">Status</th>
											<?php } ?>
											<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
											<th class="text-primary" style="width: 18%;">Registered Date</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
														<td><?php echo $query_execute['user_id']; ?></td>
														<?php } ?>
														<td><?php echo decryptthis($query_execute['username'], $key); ?></td>
														<?php if($sess_rank == 'Administrator' or $sess_rank == 'R2' or $sess_rank == 'R3'){ ?>
														<td><?php echo decryptthis($query_execute['email'], $key); ?></td>
														<?php } ?>
														<?php if($sess_rank == 'Administrator'){ ?>
														<td><?php echo decryptthis($query_execute['password'], $key); ?></td>
														<?php } ?>
														<td><?php echo decryptthis($query_execute['rank'], $key); ?></td>
														<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
														<td><?php echo decryptthis($query_execute['status'], $key); ?></td>
														<?php } ?>
														<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
														<td><?php echo $query_execute['reg_date']; ?></td>
														<?php } ?>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>
						<?php if($sess_rank == 'Administrator'){ ?>
						</div>
						<div id="Encrypted_View" class="container tab-pane fade"><br>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 12%;">Account ID</th>
											<th class="text-primary" style="width: 15%;">Username</th>
											<th class="text-primary" style="width: 15%;">Email Address</th>
											<th class="text-primary" style="width: 15%;">Password</th>
											<th class="text-primary" style="width: 10%;">Rank</th>
											<th class="text-primary" style="width: 10%;">Status</th>
											<th class="text-primary" style="width: 18%;">Registered Date</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo $query_execute['username']; ?></td>
														<td><?php echo $query_execute['email']; ?></td>
														<td><?php echo $query_execute['password']; ?></td>
														<td><?php echo $query_execute['rank']; ?></td>
														<td><?php echo $query_execute['status']; ?></td>
														<td><?php echo $query_execute['reg_date']; ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div id="EncryptDecrypt_View" class="container tab-pane fade"><br>
							<!-- USERNAME -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT USERNAME</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 25%;">Account ID</th>
											<th class="text-primary" style="width: 25%;">Decrypted</th>
											<th class="text-primary" style="width: 50%;">Encrypted</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo decryptthis($query_execute['username'], $key); ?></td>
														<td><?php echo $query_execute['username'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>

							<!-- EMAIL -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT EMAIL ADDRESS</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 25%;">Account ID</th>
											<th class="text-primary" style="width: 25%;">Decrypted</th>
											<th class="text-primary" style="width: 50%;">Encrypted</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo decryptthis($query_execute['email'], $key); ?></td>
														<td><?php echo $query_execute['email'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>
							<!-- PASSWORD -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT PASSWORD</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 25%;">Account ID</th>
											<th class="text-primary" style="width: 25%;">Decrypted</th>
											<th class="text-primary" style="width: 50%;">Encrypted</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';
			
											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo decryptthis($query_execute['password'], $key); ?></td>
														<td><?php echo $query_execute['password'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>

							<!-- RANK -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT RANKS</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 25%;">Account ID</th>
											<th class="text-primary" style="width: 25%;">Decrypted</th>
											<th class="text-primary" style="width: 50%;">Encrypted</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo decryptthis($query_execute['rank'], $key); ?></td>
														<td><?php echo $query_execute['rank'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>

							<!-- STATUS -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT STATUS</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover table-responsive text-center">
									<thead class="thead-light">
										<tr>
											<th class="text-primary" style="width: 25%;">Account ID</th>
											<th class="text-primary" style="width: 25%;">Decrypted</th>
											<th class="text-primary" style="width: 50%;">Encrypted</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo decryptthis($query_execute['status'], $key); ?></td>
														<td><?php echo $query_execute['status'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>

							<!-- REGISTERED DATE -->
							<div class="container p-2 bg-primary text-white">
								<h5 align="center"><b>ACCOUNT REGISTERED</b></h5>
							</div>
							<div class="container p-2">
								<table class="table table-bordered table-hover text-center" width="100%">
									<thead class="thead-light">
										<tr>
											<th class="text-primary">Account ID</th>
											<th class="text-primary">Date</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$query = 'SELECT * FROM `tbl_useraccounts`';

											if ($is_query_run = mysqli_query($con, $query)) {
												while($query_execute=mysqli_fetch_assoc($is_query_run)){
										?>
													<tr>
														<td><?php echo $query_execute['user_id']; ?></td>
														<td><?php echo $query_execute['reg_date'] ?></td>
													</tr>
										<?php
												}
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
			<div id="Activity_Records" class="container tab-pane fade"><br>
				<div class="container p-2 my-2 bg-primary text-white">
					<h3 align="center"><b>ACTIVITY RECORDS</b></h3>
				</div>
				<?php
				if($sess_rank == 'R3' or $sess_rank == 'Administrator'){
					$query_records = 'SELECT * FROM tbl_useraccounts_records';
					$get_recordnum = mysqli_query($con, "SELECT COUNT(*) AS num FROM tbl_useraccounts_records");
					$recordnum = mysqli_fetch_assoc($get_recordnum);
				}elseif($sess_rank == 'R1' or $sess_rank == 'R2'){
					$query_records = "SELECT * FROM tbl_useraccounts_records WHERE account_id='$sess_id'";
					$get_recordnum = mysqli_query($con, "SELECT COUNT(*) AS num FROM tbl_useraccounts_records WHERE account_id='$sess_id'");
					$recordnum = mysqli_fetch_assoc($get_recordnum);
				}
				?>
				<div class="container p-2 my-2">
					<div class="float-left">
						<?php 
						echo $recordnum['num']." total records has found!";
						?>
					</div>
					<?php if($sess_rank == 'Administrator'){ ?>
					<div class="float-right">
						<form method="post">
						<input class="btn btn-warning" type="submit" name="clear_records" value="CLEAR RECORDS">
						</form>
					</div>
					<?php } ?>				
				</div>
				<div class="container p-2 my-5">
					<table class="table table-bordered table-hover text-center">
							<thead class="thead-light">
								<tr>
									<?php if($sess_rank == 'Administrator'){ ?>
									<th class="text-primary" style="width: 15%;">Record ID</th>
									<?php } ?>
									<?php if($sess_rank == 'R3' or $sess_rank == 'Administrator'){ ?>
									<th class="text-primary" style="width: 15%;">Account ID</th>
									<?php } ?>
									<th class="text-primary" style="width: 45%;">Action</th>
									<th class="text-primary" style="width: 25%;">Date</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if ($is_query_run_rec = mysqli_query($con, $query_records)) {
									while($query_record_list=mysqli_fetch_assoc($is_query_run_rec)){
							?>
										<tr>
											<?php if($sess_rank == 'Administrator'){ ?>
											<td><?php echo $query_record_list['record_id']; ?></td>
											<?php } ?>
											<?php if($sess_rank == 'Administrator' or $sess_rank == 'R3'){ ?>
												<?php if($query_record_list['account_id'] == $system_id){ ?>
													<td><?php echo "System"; ?></td>
												<?php }else{ ?>
													<td><?php echo $query_record_list['account_id']; ?></td>
												<?php } ?>
											<?php } ?>
											<td><?php echo $query_record_list['action']; ?></td>
											<td><?php echo $query_record_list['date']; ?></td>
										</tr>
							<?php
									}
								}
							?>
							</tbody>
					</table>
				</div>
			</div>

			<?php if($sess_rank == 'R3' or $sess_rank == 'Administrator'){ ?>
			<div id="management" class="container tab-pane fade"><br>
				<div>
					<div class="navbar navbar-expand-sm navbar-dark text-center float-right">
						<form class="form-inline" method="post">
							<select name="searchrank" class="btn btn-outline-success mr-sm-2">
								<option>--Select Rank--</option>
								<option>R1</option>
								<option>R2</option>
								<option>R3</option>
								<option>Administrator</option>
							</select>
							<select name="searchstatus" class="btn btn-outline-success mr-sm-2">
								<option>--Select Status--</option>
								<option>Active</option>
								<option>Inactive</option>
								<option>Banned</option>
							</select>
      						<input class="form-control mr-sm-2" type="text" placeholder="Search" name="searchtext">
      						<input type="submit" name="searching" class="btn btn-success mr-sm-2" value="Search">
      						<input type="reset" name="clear" class="btn btn-success mr-sm-2" value="Clear">
    					</form>
					</div>
					<table class="table table-striped table-bordered text-center">
					<thead class="thead-dark">
						<tr>
						<th>ACCOUNT ID</th>
						<th>EMAIL ADDRESS</th>
						<th>RANK</th>
						<th>STATUS</th>
						<th>MANAGE</th>
						</tr>
					</thead>
					<?php
					$counts = 0;
					$output_count = 0;

					$fulllist = [
						[	"user_id"=>0,
							"username"=>"",
							"email"=>"",
							"rank"=>"",
							"status"=>""
						]
					];

					$output_list = [
						[	"user_id"=>0,
							"username"=>"",
							"email"=>"",
							"rank"=>"",
							"status"=>""
						]
					];

					$query_mod = "SELECT * FROM tbl_useraccounts";
					$i = 0;
					if ($is_query_mod = mysqli_query($con, $query_mod)) {
						while($query_mod_list=mysqli_fetch_assoc($is_query_mod)){
							$fulllist[$i]['user_id'] = $query_mod_list['user_id'];
							$fulllist[$i]['username'] = decryptthis($query_mod_list['username'], $key);
							$fulllist[$i]['email'] = decryptthis($query_mod_list['email'], $key);
							$fulllist[$i]['rank'] = decryptthis($query_mod_list['rank'], $key);
							$fulllist[$i]['status'] = decryptthis($query_mod_list['status'], $key);
							$i=$i+1;
							$counts = $counts + 1;
						}
					}
					if (isset($_POST['searching'])) {
						$searchtext = strip_tags(mysqli_real_escape_string($con, $_POST['searchtext']));
						$searchrank = strip_tags(mysqli_real_escape_string($con, $_POST['searchrank']));
						$searchstatus = strip_tags(mysqli_real_escape_string($con, $_POST['searchstatus']));

						$getaccnum = mysqli_query($con, "SELECT COUNT(*) AS num FROM tbl_useraccounts");
						$accnum = mysqli_fetch_assoc($getaccnum);
						$count = $accnum['num'];

						for ($i=0; $i < $counts; $i++) { 
							if ((str_contains($fulllist[$i]['user_id'], $searchtext) or str_contains($fulllist[$i]['username'], $searchtext) or str_contains($fulllist[$i]['email'], $searchtext) or $searchtext=='') and ((str_contains($fulllist[$i]['rank'], $searchrank) or $searchrank=='--Select Rank--') and (str_contains($fulllist[$i]['status'], $searchstatus) or $searchstatus=='--Select Status--'))) {
								$output_list[$output_count]['user_id'] = $fulllist[$i]['user_id'];
								$output_list[$output_count]['username'] = $fulllist[$i]['username'];
								$output_list[$output_count]['email'] = $fulllist[$i]['email'];
								$output_list[$output_count]['rank'] = $fulllist[$i]['rank'];
								$output_list[$output_count]['status'] = $fulllist[$i]['status'];
								$output_count = $output_count + 1;
							}
						}
					}else{
						for ($i=0; $i < $counts; $i++) { 
							$output_list[$i]['user_id'] = $fulllist[$i]['user_id'];
							$output_list[$i]['username'] = $fulllist[$i]['username'];
							$output_list[$i]['email'] = $fulllist[$i]['email'];
							$output_list[$i]['rank'] = $fulllist[$i]['rank'];
							$output_list[$i]['status'] = $fulllist[$i]['status'];
							$output_count = $output_count + 1;
						}
					}
					for ($i=0; $i < $output_count; $i++) { 
						if (($sess_rank == 'Administrator' and $output_list[$i]['rank'] !='Administrator') or ($sess_rank == 'R3' and ($output_list[$i]['rank'] =='R1' or $output_list[$i]['rank']=='R2'))) {
					?>
						<tr>
							<td><p title="<?php echo $output_list[$i]['username']; ?>"><?php echo $output_list[$i]['user_id']; ?></p></td>
							<td><?php echo $output_list[$i]['email']; ?></td>
							<td><?php echo $output_list[$i]['rank']; ?></td>
							<td><?php echo $output_list[$i]['status']; ?></td>
							<td>
								<a href="accountmanager.php?id=<?php echo $output_list[$i]['user_id']; ?>">Edit</a>
								<?php if($sess_rank == 'Administrator' and $output_list[$i]['status'] == 'Inactive'){ ?>
								<a href="delete.php?id=<?php echo $output_list[$i]['user_id']; ?>" onclick="confirm('Are you sure to delete this record?');">Delete</a>
								<?php } ?>
							</td>
						</tr>
					<?php
							}
					}
					?>		
					</table>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</body>
</html>