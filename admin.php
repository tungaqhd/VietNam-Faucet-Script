<?php 
include 'includes/core.php';

function clean($var) {
	global $mysqli;
	$return = $mysqli->real_escape_string($var);
	return $return;
}
$logged = 0;
if (isset($_POST['admin-login']) and $_POST['admin-token'] == $_SESSION['admin-token']) {
	$usn = $_POST['username'];
	$pw = $_POST['password'];
	$admin_login = md5($_POST['username'] . '&' . $_POST['password']);
	if ($admin_login == md5($login)) {
		$_SESSION['admin'] = $admin_login;
		header('Location: admin.php');
	} else {
		$alert = alert('danger', 'Invalid Login');
	}
} elseif (isset($_SESSION['admin'])) {
	if ($_SESSION['admin'] == md5($login)) {
		$logged = 1;
		$last = $time - 86400;
		if (isset($_POST['unban'])) {
			$address = $mysqli->real_escape_string($_POST['unban']);
			$mysqli->query("UPDATE users SET bot = '0' WHERE address = '$address'");
		} elseif (isset($_POST['clean'])) {
			$mysqli->query("DELETE FROM history");
		}
		$totaluser = $mysqli->query("SELECT COUNT(id) FROM users")->fetch_row()[0];
		$totalbanned = $mysqli->query("SELECT COUNT(id) FROM users WHERE bot != '0'")->fetch_row()[0];
		$totalbalance = $mysqli->query("SELECT SUM(balance) FROM users")->fetch_row()[0];
		$newuser = $mysqli->query("SELECT COUNT(id) FROM users WHERE joined > '$last'")->fetch_row()[0];
		$claimnumber = $mysqli->query("SELECT COUNT(id) FROM history WHERE last_claim > '$last'")->fetch_row()[0];
		$totalclaim = $mysqli->query("SELECT SUM(amount) FROM history WHERE type = 'Claim' AND last_claim > '$last'")->fetch_row()[0];
		$totalpayout = $mysqli->query("SELECT SUM(amount) FROM history WHERE type = 'Withdraw' AND last_claim > '$last'")->fetch_row()[0];
		$banned = $mysqli->query("SELECT COUNT(id) FROM users WHERE bot ")->fetch_row()[0];
		$totalbalance = $totalbalance ? $totalbalance : 0;
		$totalclaim = $totalclaim ? $totalclaim : 0;
		$totalpayout = $totalpayout ? $totalpayout : 0;
	} else {
		unset($_SESSION['admin']);
		header('Location: admin.php');
	}
}
$_SESSION['admin-token'] = get_token(100);
?>
<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?=$config['description']?>">
	<meta name="keywords" content="bitcoin faucet, free bitcoin, faucet, cryptocurrency, vietnam faucet script">
	<meta property="og:title" content="<?=$config['name']?>">
	<meta name="og:description" content="<?=$config['description']?>">
	<meta property="og:site_name" content="<?=$config['name']?>">
	<link rel="shortcut icon" href="template/img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="template/img/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="template/css/site.css">
	<link rel="stylesheet" type="text/css" href="template/css/style.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css" integrity="sha384-G0fIWCsCzJIMAVNQPfjH08cyYaUtMwjJwqiRKxxE/rx96Uroj1BtIQ6MLJuheaO9" crossorigin="anonymous">
	<title>Admin Panel - <?=$config['name']?></title>
</head>
<body>
	<h1 class="text-center">Admin Panel</h1>
	<center><a href="logout.php" class="btn btn-danger">Logout</a></center>
	<div class="container">
		<?php if ($logged == 0) { ?>
			<form  action="" method="post">
				<label for="username"><span class="badge badge-success">User Name:</span></label>
				<div class="form-group">
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-user"></i></span>
						</div>
						<input type="text" name="username" id="username" class="form-control">
					</div>
				</div>
				<label for="password"><span class="badge badge-success">Password:</span></label>
				<div class="form-group">
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-lock"></i></span>
						</div>
						<input type="password" name="password" id="password" class="form-control">
					</div>
				</div>
				<input type="hidden" name="admin-token" value="<?=$_SESSION['admin-token']?>">
				<button name="admin-login" class="btn btn-success btn-lg btn-block">Login</button>
			</form>
		<?php } elseif ($logged == 1) { ?>
			<table class="table">
				<thead>
					<tr>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Total Users</td>
						<td><?=$totaluser?></td>
					</tr>
					<tr>
						<td>Current Balance</td>
						<td><?=$totalbalance?> satoshi</td>
					</tr>
					<tr>
						<td>New Users In Last 24 Hours</td>
						<td><?=$newuser?></td>
					</tr>
					<tr>
						<td>Total Claims In Last 24 Hours</td>
						<td><?=$claimnumber?></td>
					</tr>
					<tr>
						<td>Claim Amount In Last 24 Hours</td>
						<td><?=$totalclaim?></td>
					</tr>
					<tr>
						<td>Payouts In Last 24 Hours</td>
						<td><?=$totalpayout?></td>
					</tr>
				</tbody>
			</table>
			<form action="" method="post">
				<button type="submit" name="clean">Clear History</button>
				<p>You should do it when there are too much history logs, it makes faucet load faster.</p>
			</form>
			<h2>Cheater: <?=$totalbanned?></h2>
			<form action="" method="post">
				<label>UnBan User</label>
				<div class="form-group">
					<input type="text" name="unban" class="form-control" autocomplete="off">
				</div>
				<button class="btn btn-info">Submit</button>
			</form>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Address</th>
						<th>Balance</th>
						<th>Reason</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$banned_user = $mysqli->query("SELECT * FROM users WHERE bot != '0' ORDER BY id DESC");
					while($banned_echo = $banned_user->fetch_assoc()){
						echo "<tr><td>".$banned_echo['address']."</td><td>".$banned_echo['balance']."</td><td>".$banned_echo['bot']."</td></tr>";
					} 
					?>
				</tbody>
			</table>
		<?php } ?>
	</div>
	<footer class="inverse text-center">
		&copy 2018 <a href="<?=$config['url']?>"><?=$config['name']?></a>  
	</footer>
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	<script src="template/js/adblock.js"></script>
</body>
</html>