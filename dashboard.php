<?php
include 'includes/core.php';
if (!isset($user)) {
	header('Location: index.php');
	die();
}

# withdraw
if (isset($_GET['action']) and !isset($_POST['submit'])) {
	if ($_GET['action'] == 'withdraw') {

		# check if user is banned
		if ($user['bot'] !== '0') {
			$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> You have been banned because of ' .$user['bot']. '. If you are not happy with it, please contact ADMIN :)');
		} else {
			if ($user['balance'] >= $config['minium_withdraw']) {
				$api_key = $config['faucethub']['api'];
				$currency = $config['faucethub']['coin'];
				$faucethub = new FaucetHub($api_key, $currency);
				$result = $faucethub->send($user['address'], $user['balance']);

				if($result["success"] === true) {
					$mysqli->query("UPDATE users SET balance = '0' WHERE id = '{$user['id']}'");
					$mysqli->query("INSERT INTO history(owner, ip_address, type, amount, last_claim) VALUES ('{$user['id']}', '$ip', 'Withdraw', '{$user['balance']}', '$time')");
					$user['balance'] = 0;
				}
				$alert = ($config['faucethub']['coin'] == 'DOGE') ? $result['html_coin'] : $result['html'];
			} else {
				$alert = alert('warning', '<i class="fas fa-exclamation-triangle"></i> You need at least ' .reward_display($config['minium_withdraw']). ' satoshi to withdraw');
			}
		}
	}
}

# check current status
$rmn = $user['last_claim']+$config['timer']-$time;
if ($rmn <= 0) {
	$claim = 1;
} else {
	$claim = 0;
}

# check user claim
if (isset($_POST['claim'], $_POST['submit']) and isset($_POST["adcopy_challenge"],$_POST["adcopy_response"]) or isset($_POST['g-recaptcha-response']) and $claim == 1) {
	unset($alert);
	if ($_POST['claim'] !== $_SESSION['claim']) {
		$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Token');
		$_SESSION['wrong-token'] += 1;
	} else {
		if ($_POST['captcha-type'] == 'recaptcha') {
			if (CaptchaCheck($_POST['g-recaptcha-response']) !== true) {
				$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Captcha');
			}
		} elseif ($_POST['captcha-type'] == 'solvemedia') {
			$solvemedia_response = solvemedia_check_answer($config['solvemedia']['V-key'], $ip, $_POST["adcopy_challenge"], $_POST["adcopy_response"], $config['solvemedia']['H-key']);
			if (!$solvemedia_response->is_valid) {
				$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Captcha');
				$_SESSION['wrong-captcha'] += 1;
			}
		} else {
			$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Captcha');
			$_SESSION['wrong-captcha'] += 1;
		}
	}

	if (!isset($alert)) {

		$next_claim = $time - $config['timer'];
		$ipcheck = $mysqli->query("SELECT COUNT(id) FROM history WHERE ip_address = '$ip' AND type = 'Claim' AND last_claim >= $next_claim")->fetch_row()[0];
		if ($ipcheck >= 1) {
			$mysqli->query("UPDATE users SET bot = 'Multiple Accounts' WHERE ip_address = '$ip'");
			$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Claim, Someone Else Claimed With This IP Address.');
		} elseif ($config['nastyhost'] == 'on') {
			$check_nastyhost = nastyhost($ip);
			if ($check_nastyhost == 'deny') {
				$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Your IP Is Banned By NastyHost');
			}
		}

		if (!isset($alert)) {
			if ($config['shortlink'] == 'on') {
				$mysqli->query("UPDATE users SET last_claim = '$time' WHERE address = '{$user['address']}'");
				echo '<script> window.location.href="' .get_link(). '"; </script>';
				die('<center><h4>Redirecting You To URL SHORTENER<br><i class="fas fa-spinner fa-spin"></i></h4></center>');
			}
			$claim = 0;
			$rmn = $config['timer'];
			$mysqli->query("UPDATE users SET balance = balance + '{$config['reward']}', last_claim = '$time' WHERE address = '{$user['address']}'");
			$mysqli->query("INSERT INTO history(owner, ip_address, type, amount, last_claim) VALUES ('{$user['id']}', '$ip', 'Claim', '{$config['reward']}', '$time')");
			$alert = alert('success', '<i class="fas fa-gift"></i> You have claimed ' .reward_display($config['reward']). ' satoshi');
			$user['balance'] += $config['reward'];
			if ($user['referred_by'] !== '0') {
				$ref_reward = floor($config['reward']/100*$config['ref']);
				$mysqli->query("UPDATE users SET balance = balance + '$ref_reward' WHERE id = '{$user['referred_by']}'");
				$mysqli->query("INSERT INTO history(owner, ip_address, type, amount, last_claim) VALUES ('{$user['referred_by']}', '$ip', 'Referral', '$ref_reward', '$time')");
			}
		}
	}
} elseif (isset($_GET['k'])) {
	if (strlen($_GET['k']) == 15) {
		$key = $mysqli->real_escape_string($_GET['k']);
		$check = $mysqli->query("SELECT * FROM link WHERE address = '{$user['address']}' and linkkey = '$key'")->num_rows;
		if ($check == 1) {
			$mysqli->query("DELETE FROM link WHERE linkkey = '$key'");
			$reward = $config['reward']+$config['link_reward'];
			$mysqli->query("UPDATE users SET balance = balance + '$reward' WHERE address = '{$user['address']}'");
			$mysqli->query("INSERT INTO history (owner, ip_address, type, amount, last_claim) VALUES ('{$user['id']}', '$ip', 'Claim', '$reward', '$time')");
			$alert = alert('success', '<i class="fas fa-gift"></i> You have claimed ' .reward_display($reward));
			$user['balance'] += $reward;
			if ($user['referred_by'] !== '0') {
				$ref_reward = floor($reward/100*$config['ref']);
				$mysqli->query("UPDATE users SET balance = balance + '$ref_reward' WHERE id = '{$user['referred_by']}'");
				$mysqli->query("INSERT INTO history(owner, ip_address, type, amount, last_claim) VALUES ('{$user['referred_by']}', '$ip', 'Referral', '$ref_reward', '$time')");
			}
		} else {
			$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Key');
			$_SESSION['wrong-key'] += 1;
		}
	} else {
		$alert = alert('danger', '<i class="fas fa-exclamation-triangle"></i> Invalid Key');
		$_SESSION['wrong-key'] += 1;
	}
} 
$_SESSION['claim'] = get_token(100);
if ($_SESSION['wrong-token'] == 4) {
	$mysqli->query("UPDATE users SET bot = 'Too Much Invalid Token' WHERE id = '{$user['id']}'");
} elseif ($_SESSION['wrong-captcha'] == 4) {
	$mysqli->query("UPDATE users SET bot = 'Too Much Wrong Captcha' WHERE id = '{$user['id']}'");
} elseif ($_SESSION['wrong-key'] == 4) {
	$mysqli->query("UPDATE users SET bot = 'Too Much Wrong Short Link Key' WHERE id = '{$user['id']}'");
}
?>
<!doctype html>
<html lang="en">
<head>
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
	<link rel="stylesheet" type="text/css" href="template/css/timer.css">
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
			<a href="dashboard.php"><button class="btn btn-success info"><i class="fas fa-tachometer-alt"></i> DashBoard</button></a>
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
				<div class="alert alert-success text-center"><i class="fas fa-gift"></i> Claim <?=reward_display($config['reward'])?> every <?=floor($config['timer']/60)?> minutes !</div>
				<div class="form-group">
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-user"></i></span>
						</div>
						<input type="text" name="address" id="address" class="form-control" value="<?=$user['address']?>" disabled="disabled">
						<button class="btn btn-warning info" data-toggle="modal" data-target="#info"><i class="fab fa-bitcoin"></i> <?=reward_display($user['balance'])?></button>

						<div class="modal fade" id="info" tabindex="-1" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-history"></i> Account History</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<table class="table table-striped">
											<thead>
												<tr>
													<th>Type</th>
													<th>Amount</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$UserTransactions = $mysqli->query("SELECT * FROM history WHERE owner = '{$user['id']}' ORDER BY id DESC LIMIT 5");
												while($UT = $UserTransactions->fetch_assoc()){
													echo "<tr>
													<td>".$UT['type']."</td>
													<td>".reward_display($UT['amount'])."</td>
													</tr>";
												} 
												?>
											</tbody>
										</table>
										<a class="btn btn-info btn-lg btn-block" href="?action=withdraw"><i class="far fa-credit-card"></i> Withdraw</a>
									</div>
								</div>
							</div>
						</div>

						<a class="btn btn-info info" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
					</div>
				</div>
				<div class="hr-sect"><h3><i class="fas fa-tint"></i> Faucet</h3></div>
				<?php echo (isset($alert)) ? $alert : ''; ?>
				<?php if ($claim == 1) { ?>
					<div class="ads"><?=$ad['bottom-1']?></div>
					<button class="btn bt-lg btn-block btn-warning" data-toggle="modal" data-target="#claim">Claim Your Coin <i class="fas fa-arrow-alt-circle-right"></i></button>
					<div class="modal fade" id="claim" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLongTitle">Human verification</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<form action="" method="post">
										<center>
											<div class="form-group">
												<label for="captcha-select">Captcha Select</label>
												<select class="form-control" id="captcha-select" name="captcha-type" onchange="captchachange()">
													<option value="recaptcha">Recaptcha</option>
													<option value="solvemedia">Solvemedia</option>
												</select>
											</div>
											<center>
												<div id="recaptcha" style="">
													<div class="g-recaptcha" data-sitekey="<?=$config['recaptcha']['publickey']?>" data-callback="activeclaim"></div>
												</div>
												<div id="solvemedia" style="display: none;">
													<?=solvemedia_get_html($config['solvemedia']['C-key'])?>
												</div>
											</center>
										</center>
										<input type="hidden" name="claim" value="<?=$_SESSION['claim']?>">
										<div class="ads">
											<?=$ad['bottom-2']?>
										</div>
										<button id="button-claim" type="submit" class="btn btn-lg btn-block btn-warning" style="max-width: 300px; margin: 0px auto;" disabled="false"><i class="fas fa-check"></i> Claim</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="text-center">
						<h2>You Have To Wait:</h2> <br>
						<div id='CountDownTimer' data-timer='<?=$rmn?>' style='width: 60%;margin: 0px auto;'></div>
					</div>
				<?php } ?>
				Share this link and earn <?=$config['ref']?>% referral commision: <span class="badge badge-success"><?=$config['url']?>?r=<?=$user['id']?></span>
				<div class="ads">
					<?=$ad['footer']?>
				</div>
				<div class="hr-sect"><h3><i class="fas fa-bullhorn"></i> Announcements</h3></div>
				<div class="list-group">
					<div class="list-group-item list-group-item-action flex-column align-items-start active">
						<div class="d-flex w-100 justify-content-between">
							<h5 class="mb-1"><?=$config['alert-1']['title']?></h5>
						</div>
						<p class="mb-1"><?=$config['alert-1']['content']?></p>
					</div>
					<div class="list-group-item list-group-item-action flex-column align-items-start">
						<div class="d-flex w-100 justify-content-between">
							<h5 class="mb-1"><?=$config['alert-2']['title']?></h5>
						</div>
						<p class="mb-1"><?=$config['alert-2']['content']?></p>
					</div>
				</div>
			</div>
			<div class="col-md-3 ads">
				<?=$ad['right']?>
			</div>
		</div>
	</div>
	<footer class="inverse text-center">
		&copy 2018 <a href="<?=$config['url']?>"><?=$config['name']?></a>  
	</footer>
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script src="template/js/timer.js"></script>
	<script src="template/js/core.js"></script>
	<script src="template/js/adb.js"></script>
	<script type="text/javascript">
		var url = '<?=$config['url']?>';
	</script>
</body>
</html>