<?php 
//login.php
include("dbconnect.php");
include("system_id.php");
include("DecryptEncrypt.php");
@ob_start();
session_start();

if (isset($_REQUEST['login'])) {
	$email = strip_tags(mysqli_real_escape_string($con, $_POST['email']));
	$password = strip_tags(mysqli_real_escape_string($con, $_POST['password']));
	$correct = strip_tags(mysqli_real_escape_string($con, $_POST['correct']));
	$answer = strip_tags(mysqli_real_escape_string($con, $_POST['answer']));

	$query = 'SELECT * FROM `tbl_useraccounts`';
	$is_query_run = mysqli_query($con, $query);
	$query_execute=mysqli_fetch_assoc($is_query_run);

	$UserIn = false;
	if ($correct != $answer) {
		header("location: index.php");
		exit();
	}

	$query = 'SELECT * FROM `tbl_useraccounts`';
	if ($is_query_run = mysqli_query($con, $query)) {
		while($query_execute=mysqli_fetch_assoc($is_query_run)){
			$mail = decryptthis($query_execute['email'], $key);
			$pass = decryptthis($query_execute['password'], $key);
			$stat = decryptthis($query_execute['status'], $key);
			$idaccount = $query_execute['user_id'];
			if ($email == $mail AND $password == $pass and $stat == "Active") {
				$logs = mysqli_query($con, "SELECT * FROM tbl_useraccounts WHERE user_id='$idaccount'");	
				if($logs){
					if (mysqli_num_rows($logs) > 0) {
						mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Logged In', now())");
						$UserIn = true;
						session_regenerate_id();
						$member = mysqli_fetch_assoc($logs);
						$_SESSION['aid'] = $member['user_id'];
						session_write_close();
						header("location: home.php");
						exit();
					}
				}else{
					$idaccount = $query_execute['user_id'];
					mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Log In Failure', now())"); 
				?>
					<script type="text/javascript">
						alert("You are not allowed to enter the site");
						window.location = "index.php";
					</script>
				<?php
				}		
			}
		}
	}
}
if (!$UserIn) { 
	$found = false;
	$query = 'SELECT * FROM `tbl_useraccounts`';
	if ($is_query_run = mysqli_query($con, $query)) {
		while($query_execute=mysqli_fetch_assoc($is_query_run)){
			$mail = decryptthis($query_execute['email'], $key);
			$pass = decryptthis($query_execute['password'], $key);
			$stat = decryptthis($query_execute['status'], $key);
			$idaccount = $query_execute['user_id'];
			if ($email == $mail and $password != $pass and $stat == "Active") {
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Log In Failure, Incorrect Password', now())");
				$found = true;
			}elseif($email == $mail and $stat == "Banned"){
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Banned Account Log In Fails', now())"); 
				?>
					<script type="text/javascript">
						alert("Your account was Banned!");
						alert("You are not allowed to enter the site");
						window.location = "index.php";
					</script>
				<?php
				$found = true;
			}elseif($email == $mail and $stat == "Inactive"){
				mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Inactive Account Log In Fails', now())"); 
				?>
					<script type="text/javascript">
						alert("You are not allowed to enter the site");
						window.location = "index.php";
					</script>
				<?php
				$found = true;
			}
		}
	}

	if($found == false){
		mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$system_id', 'Log In Failure, Unexisting Email', now())");
	}
	?>
	<script type="text/javascript">
		alert("You are not allowed to enter the site");
		window.location = "index.php";
	</script>
<?php
}
?>