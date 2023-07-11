<?php 
//session.php
@ob_start();
session_start();
	if (!isset($_SESSION['aid']) || trim($_SESSION['aid'] == '')) {
?>
		<script type="text/javascript">
			alert("Unauthorized Access to the Page.");
			window.location = "index.php";
		</script>
<?php
	}
?>