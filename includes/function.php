<?php 
function get_ip() {
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
		$ipList                          = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['HTTP_X_FORWARDED_FOR'] = array_pop($ipList);
	} else {
		$_SERVER['HTTP_X_FORWARDED_FOR'] = false;
	}
	if (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		return $_SERVER['REMOTE_ADDR'];
	}
	return $_SERVER['REMOTE_ADDR'];
}


function nastyhost($ip){
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, "10");
	curl_setopt($ch, CURLOPT_URL, "http://v1.nastyhosts.com/".$ip);
	$response=curl_exec($ch);
	
	curl_close($ch);
	$nastyArray = json_decode($response);
	if($nastyArray->suggestion == "deny"){
		return 'deny';
	} else {
		return 'ok';
	}
}

function get_token($length) {
	$str = "";
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	return $str;
}

function alert($type, $content) {
	if ($type == 'send') {
		return $content;
	}
	return '<div class="alert alert-' .$type. '">' .$content. '</div>';
}

function CaptchaCheck($response) {
  global $config;
  $Captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
  $Captcha_data = array('secret' => $config['recaptcha']['privatekey'], 'response' => $response);
  $Captcha_options = array(
     'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($Captcha_data),
      ),
  );
  $Captcha_context  = stream_context_create($Captcha_options);
  $Captcha_result = file_get_contents($Captcha_url, false, $Captcha_context);
  $check = json_decode($Captcha_result)->success;
  if ($check) {
  	return true;
  }
  return false;
}

function banned_address($address) {
	global $mysqli;
	$check = $mysqli->query("SELECT COUNT(id) FROM banned_address WHERE address = '$address'")->fetch_row()[0];
	if ($check >= 1) {
		return 'deny';
	}
}

function get_link(){
	global $link;
	global $link_default;
	global $mysqli;
	global $user;
	global $ip;
	$key = get_token(15);
	$mysqli->query("INSERT INTO link (address, linkkey, ip) VALUES ('{$user['address']}', '$key', '$ip')");
	for ($i=1; $i <= count($link) ; $i++) { 
		if (!isset($_COOKIE[$i])) {
			setcookie($i, 'fuck cheater :P', time() + 86400);
			$url = $link[$i];
			break;
		}
	}
	if (!isset($url)) {
		$url = $link_default;
	}
	$full_url = str_replace("{key}",$key,$url);
	$short_link = @file_get_contents($full_url);
	return $short_link;
}

function reward_display($reward) {
	global $config;
	$real = ($config['faucethub']['coin'] == 'DOGE') ? $reward/100000000 : $reward;
	$coin = ($config['faucethub']['coin'] == 'DOGE') ? 'DOGE' : 'satoshi (' .$config['faucethub']['coin']. ')';
	return $real. ' ' .$coin;
}
?>