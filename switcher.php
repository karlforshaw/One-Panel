<?php
	session_start();

	$requested_skin = $_POST['skin'];
	$_SESSION['one_panel_skin'] = $requested_skin;
	echo 'ok';
?>