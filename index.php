<?php 
include 'includes/core.php';
if (isset($user)) {
	header("Location: dashboard.php");
}
if (isset($_GET['r'])) {
	setcookie('r', $_GET['r']);
}
if (isset($_POST['address'], $_POST['token'])) {
	if ($_POST['token'] !== $_SESSION['token']) {
		$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Token !');
	} elseif (strlen($_POST['address']) < 10 or strlen($_POST['address']) > 50) {
		$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Address !');
	} else {
		$address = $mysqli->real_escape_string(preg_replace("/[^ \w]+/", "",trim($_POST['address'])));

		# check referral
		if(isset($_COOKIE['r'])){
			if(is_numeric($_COOKIE['r'])){
				$referID2 = $mysqli->real_escape_string($_COOKIE['r']);
				$AddressCheck = $mysqli->query("SELECT COUNT(id) FROM users WHERE id = '$referID2'")->fetch_row()[0];
				if($AddressCheck == 1){
					$referID = $referID2;
				} else {
					$referID = 0;
				}
			} else {
				$referID = 0;
			}
		} else {
			$referID = 0;
		}

		$check = $mysqli->query("SELECT * FROM users WHERE address = '$address' LIMIT 1");
		if ($check->num_rows == 0) {
			$mysqli->query("INSERT INTO users (address, ip_address, balance, last_active, last_claim, joined, bot, referred_by) VALUES ('$address', '$ip', '0', $time, '0', $time, '0', '$referID')");
		} else {
			$mysqli->query("UPDATE users SET ip_address = '$ip', last_active = '$time' WHERE address = '$address'");
		}
		$multi = $mysqli->query("SELECT COUNT(id) FROM users WHERE ip_address = '$ip'")->fetch_row()[0];
		if ($multi > 1) {
			$mysqli->query("UPDATE users SET bot = 'Multiple Accounts' WHERE ip_address = '$ip'");
		}
		$_SESSION['address'] = $address;
		header("Location: dashboard.php");
		die();
	}
}
$_SESSION['token'] = get_token(100);
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
	<title><?=$config['name']?> - <?=$config['description']?></title>
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<a class="navbar-brand" href="index.php"><i class="fas fa-tint"></i> <?=$config['name']?></a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation" style="">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarColor01">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#"><i class="far fa-envelope-open"></i> Contact</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#"><i class="fas fa-info"></i> About</a>
				</li>
			</ul>
		</div>
	</nav>
	<div class="ads">
		<?=$ad['top']?>
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-3 ads">
				<?=$ad['left']?>
			</div>
			<div class="col-md-6">
				<h1 class="ribbon"><?=$config['name']?></h1>
				<div class="alert alert-warning text-center"><i class="fas fa-angle-right"></i> This faucet requires a FaucetHub Account to claim <i class="fas fa-angle-left"></i></div>
				<div class="alert alert-success text-center"><i class="fas fa-gift"></i> Claim <?=reward_display($config['reward'])?> every <?=floor($config['timer']/60)?> minutes !</div>
				<?php echo (isset($alert)) ? $alert : ''; ?>
				<form action="" method="post">
					<div class="form-group">
						<label for="address"><span class="badge badge-success">Your Address:</span></label>
						<div class="form-group">
							<div class="input-group mb-3">
								<div class="input-group-prepend">
									<span class="input-group-text"><img src="template/img/wallet.png" width="40px" height="40px"></span>
								</div>
								<input type="text" name="address" id="address" class="form-control">
							</div>
						</div>
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<div class="form-group ads">
							<?=$ad['bottom-1']?>
						</div>
						<button class="btn btn-lg btn-block btn-success"><i class="fas fa-sign-in-alt"></i> Login</button>
						<div class="ads">
							<?=$ad['footer']?>
						</div>
					</div>
				</form>
			</div>
			<div class="col-md-3 ads">
				<?=$ad['right']?>
			</div>
		</div>
		<div class="row" style="max-width: 75%;margin: 0px auto;">
			<div class="hr-sect"><h3><i class="far fa-lightbulb"></i> Useful Information</h3></div>
			<div class="col-md-4">
				<h2 style="text-align: right;">What is Bitcoin ?</h2>
				<img src="template/img/bitcoins.png" style="max-width: 100%;float: right;">
			</div>
			<div class="col-md-8">
				<p>Bitcoin (<i class="fab fa-bitcoin"></i>) is a cryptocurrency and worldwide payment system. It is the first decentralized digital currency, as the system works without a central bank or single administrator. The network is peer-to-peer and transactions take place between users directly, without an intermediary. These transactions are verified by network nodes through the use of cryptography and recorded in a public distributed ledger called a blockchain. Bitcoin was invented by an unknown person or group of people under the name Satoshi Nakamoto and released as open-source software in 2009.

				Bitcoins are created as a reward for a process known as mining. They can be exchanged for other currencies, products, and services. As of February 2015, over 100,000 merchants and vendors accepted bitcoin as payment. Research produced by the University of Cambridge estimates that in 2017, there were 2.9 to 5.8 million unique users using a cryptocurrency wallet, most of them using bitcoin.</p>
			</div>
		</div>
	</div>
	<footer class="inverse text-center">
		&copy 2018 <a href="<?=$config['url']?>"><?=$config['name']?></a>  
	</footer>
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	<script src="template/js/adb.js"></script>
</body>
</html>