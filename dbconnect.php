<?php
//dbconnect.php
$con = mysqli_connect("localhost", "root","");

if (!$con) {
	die("Cannot connect: " .  mysql_error());
}

mysqli_select_db($con, "itec100a") or die("No Database Selected");
 ?>