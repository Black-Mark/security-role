<?php 
//logout.php
session_start();
include("dbconnect.php");
$idaccount = $_SESSION['aid'];
mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$idaccount', 'Logged Out', now())");
unset($_SESSION['aid']);
?>

<script type="text/javascript">
	alert("You have been logged out of the system");
	window.location = "index.php";
</script>