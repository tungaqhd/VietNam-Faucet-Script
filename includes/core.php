<?php
session_start();
include 'config.php';
include 'function.php';
include 'faucethub.php';
include 'solvemedia.php';
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();

# get user info
if (isset($_SESSION['address'])) {
	$address = $mysqli->real_escape_string(preg_replace("/[^ \w]+/", "",trim($_SESSION['address'])));
	$check = $mysqli->query("SELECT * FROM users WHERE address = '$address' LIMIT 1");
	if ($check->num_rows == 1) {
		$user = $check->fetch_assoc();
	} else {
		die('Fuck ! Please login firstly');
	}
}

if (!isset($_SESSION['wrong'])) {
	$_SESSION['wrong-token'] = 0;
	$_SESSION['wrong-captcha'] = 0;
	$_SESSION['wrong-key'] = 0;
	$_SESSION['wrong'] = 0;
}
echo "<!-- 
## Name: VietNam Faucet Script 
## Version: 1
## IF YOU WANT TO BUY IT, CONTACT tungaqhd@gmail.com
-->";
?>