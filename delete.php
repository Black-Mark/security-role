<?php
include("dbconnect.php");
include("session.php");

$id = strip_tags(mysqli_real_escape_string($con, $_GET['id']));
mysqli_query($con, "DELETE FROM tbl_useraccounts WHERE user_id='$id'");

$log_id = $_SESSION['aid'];
$log = 'Account ID '.$id.' has Permanently Removed';
mysqli_query($con, "INSERT INTO tbl_useraccounts_records VALUES('', '$log_id', '$log', now())");
?>
<script type="text/javascript">
	alert("The Account is Permanently Deleted From the Records!");
	window.location="home.php";
</script>