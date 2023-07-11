<?php
include("dbconnect.php");
include("DecryptEncrypt.php");
include("session.php");

$mid = $_SESSION['aid'];

$session_search_rank = mysqli_query($con, "SELECT * FROM `tbl_useraccounts` WHERE user_id='$mid'");
$session_found_rank=mysqli_fetch_assoc($session_search_rank);
$sess_rank = decryptthis($session_found_rank['rank'], $key);


	if (isset($_REQUEST['update'])) {
		$id = strip_tags(mysqli_real_escape_string($con, $_GET['id']));
		$userid = strip_tags(mysqli_real_escape_string($con, $_POST['userid']));

	 	$sql_old = mysqli_query($con, "SELECT * FROM tbl_useraccounts WHERE user_id = '$id'");
		$rec_old = mysqli_fetch_assoc($sql_old);

		$Ausername = strip_tags(mysqli_real_escape_string($con, $_POST['username']));
	 	$Aemail = strip_tags(mysqli_real_escape_string($con, $_POST['email']));

	 	if ($sess_rank == 'Administrator') {
	 		$Apassword = strip_tags(mysqli_real_escape_string($con, $_POST['password']));
	 		$repassword = strip_tags(mysqli_real_escape_string($con, $_POST['repassword']));
	 	}else{
	 		$Apassword = decryptthis($rec_old['password'], $key);
	 		$repassword = decryptthis($rec_old['password'], $key);
	 	}
	 	
	 	$Arank = strip_tags(mysqli_real_escape_string($con, $_POST['rank']));
	 	$Astatus = strip_tags(mysqli_real_escape_string($con, $_POST['status']));
	 	
	 	if ($sess_rank == 'Administrator') {
	 		$reg_date = $_POST['reg_date'];
	 		$date_sel = $_POST['date_sel'];
	 		$date_old = $_POST['date_old'];
	 	}else{
	 		$reg_date = $rec_old['reg_date'];
	 		$date_sel = "original";
	 		$date_old = $rec_old['reg_date'];
	 	}

	 	$errors = false;
	 	$new_id = false;
	 	$new_username = false;
	 	$new_email = false;
	 	$new_password = false;
	 	$new_rank = false;
	 	$new_status = false;
	 	$new_date = false;

	 	if ($userid != $rec_old['user_id']) {
	 		$new_id = true;
	 	}
	 	if($Ausername != decryptthis($rec_old['username'], $key)){
	 		$new_username = true;
	 	}

	 if($Apassword != decryptthis($rec_old['password'], $key)){
	 	if ($Apassword == $repassword) {
			if ($Apassword != '') {
				if(!preg_match("/[A-Za-z]/", $Apassword)) { // Letters
 					echo '<script>alert("The password does not contain letter/s.")</script>';
 					$errors = true;
 				}
 				if (!preg_match("/\W/", $Apassword)) { 
 					echo '<script>alert("The password does not contain special characters.")</script>';
 					$errors = true;
 				}
 				if (!preg_match("/\d/", $Apassword)) {
 					echo '<script>alert("Password does not contain number/s.")</script>';
 					$errors = true;
 				}
 				if (strlen($Apassword) < 8) {
 					echo '<script>alert("The password must be at least 8 alphanumeric characters.")</script>';
    			    $errors = true;
    			}
			}
		}else{
			$errors = true;
		?>
	 		<script type="text/javascript">
	 			alert("Changes doesn't apply! Password does not match!");
	 		</script>
	 	<?php
		}
		$new_password = true;
	}

	 	if($Aemail != decryptthis($rec_old['email'], $key)){
	 		$new_email = true;
	 	}
	 	if($Arank != decryptthis($rec_old['rank'], $key)){
	 		$new_rank = true;
	 	}
	 	if($Astatus != decryptthis($rec_old['status'], $key)){
	 		$new_status = true;
	 	}

		if($errors == false){
			$username = encryptthis($Ausername, $key);
	 		$email = encryptthis($Aemail, $key);
	 		$password = encryptthis($Apassword, $key);
	 		$rank = encryptthis($Arank, $key);
	 		$status = encryptthis($Astatus, $key);

	 		if ($date_sel == 'current') {
	 			$new_date = true;
	 			mysqli_query($con, "UPDATE tbl_useraccounts SET user_id='$userid', username='$username', email='$email', password='$password', rank='$rank', status='$status', reg_date=now() WHERE user_id='$id' ");
	 		}elseif($date_sel == 'change'){
	 			if ($reg_date != $date_old) {
	 				$new_date = true;
	 			}
	 			mysqli_query($con, "UPDATE tbl_useraccounts SET user_id='$userid', username='$username', email='$email', password='$password', rank='$rank', status='$status', reg_date='$reg_date' WHERE user_id='$id' ");
	 		}elseif ($date_sel == 'original') {
	 			mysqli_query($con, "UPDATE tbl_useraccounts SET user_id='$userid', username='$username', email='$email', password='$password', rank='$rank', status='$status' WHERE user_id='$id' ");
	 		}

	 		if ($new_id) {
	 			$info = 'Account ID '.$id.' has changed to '.$userid;
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_username) {
	 			$info = 'Username has changed from '.decryptthis($rec_old['username'], $key).' to '.$Ausername;
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_email) {
	 			$info = 'Email has changed from '.decryptthis($rec_old['email'], $key).' to '.$Aemail;
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_password) {
	 			$info = 'Account ID '.$userid.' Password Changed';
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_rank) {
	 			$info = 'Rank has changed from '.decryptthis($rec_old['rank'], $key).' to '.$Arank;
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_status) {
	 			$info = 'Status has changed from '.decryptthis($rec_old['status'], $key).' to '.$Astatus;
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		if ($new_date) {
	 			$info = 'Account ID '.$userid.' Registration Date Changed';
		 		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$mid', '$info', now())");
	 		}
	 		?>
	 		<script type="text/javascript">
	 			alert("Successfully Updated");
	 		</script>
	 		<?php
		}
	 }
?>
<!DOCTYPE html>
<html>
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
	<form method="post" autocomplete="off">
		<?php
			$id = strip_tags(mysqli_real_escape_string($con, $_GET['id']));
			$sql = mysqli_query($con, "SELECT * FROM tbl_useraccounts WHERE user_id = '$id'");
			$rec = mysqli_fetch_assoc($sql);
		?>
		<div class="card img-thumbnail">
      		<div class="card-header bg-primary text-center text-white">
      			<h2><strong class="card-text">PERSONAL INFORMATION</strong></h2>
      		</div>
      		<div class="card-body bg-white">
				<table width="100%">
      				<tr>
      					<td><img src="user.png" class="mx-auto d-block rounded" width="50%"></td>
      					<td width="50%">
      						<table class="table">
      							<tr>
      								<td><b>Account ID: </b></td>
      								<td>
      									<input type="text" name="userid_temp" value="<?php echo $rec['user_id']; ?>" required disabled>
      									<input type="hidden" name="userid" value="<?php echo $rec['user_id']; ?>">
      								</td>
      							</tr>
      							<tr>
      								<td><b>Username: </b></td>
      								<td><input type="text" name="username" value="<?php echo decryptthis($rec['username'], $key); ?>" required></td>
      							</tr>
      							<tr>
      								<td><b>Email Address: </b></td>
      								<td><input type="email" name="email" value="<?php echo decryptthis($rec['email'], $key); ?>" required></td>
      							</tr>
      							<tr>
      								<td><b>Password: </b></td>
      								<td><input type="password" name="password" value="<?php echo decryptthis($rec['password'], $key); ?>" required <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>></td>
      							</tr>
      							<tr>
      								<td><b>Confirm Password: </b></td>
      								<td><input type="password" name="repassword" value="<?php echo decryptthis($rec['password'], $key); ?>" required <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>></td>
      							</tr>
      							<tr>
      								<td><b>Rank: </b></td>
      								<td><select name="rank" required>
      									<option hidden><?php echo decryptthis($rec['rank'], $key); ?></option>
      									<option>R1</option>
      									<option>R2</option>
      									<?php if($sess_rank == 'Administrator'){ ?>
      									<option>R3</option>
      									<?php } ?>
      								</select></td>
      							</tr>
      							<tr>
      								<td><b>Status: </b></td>
      								<td><input type="radio" name="status" value="Active" <?php if(decryptthis($rec['status'], $key) == 'Active'){ echo "checked"; }?> required>Active
      									<input type="radio" name="status" value="Inactive" <?php if(decryptthis($rec['status'], $key) == 'Inactive'){ echo "checked"; }?> required>Inactive
      									<input type="radio" name="status" value="Banned" <?php if(decryptthis($rec['status'], $key) == 'Banned'){ echo "checked"; }?> required>Banned
      								</td>
      							</tr>
      							<tr>
      								<td><b>Date Registered: </b></td>
      								<td><input type="datetime-local" name="reg_date" value="<?php echo $rec['reg_date']; ?>" <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>>
      									<input type="datetime-local" name="date_old" value="<?php echo $rec['reg_date']; ?>" hidden>
      								</td>
      							</tr>
      							<tr>
      								<td colspan="2">
      									<input type="radio" name="date_sel" value="original" required <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>> Original Registry Date
      									<input type="radio" name="date_sel" value="current" required <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>> Current Date
      									<input type="radio" name="date_sel" value="change" checked required <?php if($sess_rank != 'Administrator'){ echo "disabled";} ?>> Changed Registry Date
      								</td>
      							</tr>
      						</table>
      					</td>
      				</tr>
      			</table>
			</div>
			<div class="card-footer bg-primary text-center text-white">
      			<p>
      				<a class="btn btn-warning mr-sm-2" href="home.php">Go Back</a>
      				<input type="submit" class="btn btn-success mr-sm-2" name="update" value="Update">
      				<?php if($sess_rank == 'Administrator' and decryptthis($rec['status'], $key) == 'Inactive'){?>
      				<a class="btn btn-danger mr-sm-2" href="delete.php?id=<?php echo $id; ?>" onclick="confirm('Are you sure to delete this record?');">Delete</a>
      				<?php } ?>
      			</p>
      		</div>			
      	</div>
	</form>
</body>
</html>