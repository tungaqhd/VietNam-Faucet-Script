<?php 
$dbHost = "localhost";
$dbUser = "root";
$dbPW = "";
$dbName = "faucet";
$mysqli = mysqli_connect($dbHost, $dbUser, $dbPW, $dbName);
if(mysqli_connect_errno()){
 	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

# ADMIN LOGIN
$username = 'admin';
$password = 'admin';
$login = $username . '&' . $password;
# basic setting
$config['name'] = 'VietNam Faucet Script';
$config['description'] = 'Free Bitcoin';
$config['url'] = 'http://bestfaucet.site';

# REWARD SETTING
$config['reward'] = 150;
$config['timer'] = 60;
$config['ref'] = 15;
$config['minium_withdraw'] = 100;

# FAUCETHUB SETTING
$config['faucethub']['api'] = 'YOUR_API_HERE';
$config['faucethub']['coin'] = 'BTC';

# SHORT LINK SETTING
$config['shortlink'] = 'on';
$config['link_reward'] = 0;
$link[1] = 'http://btc.ms/api/?api=86b6c147ce28028e5c7762afce1656f898279889&url=http://bestfaucet.site/verify.php?k={key}&format=text';
$link[2] = 'http://btc.ms/api/?api=86b6c147ce28028e5c7762afce1656f898279889&url=http://bestfaucet.site/verify.php?k={key}&format=text';
$link[3] = 'http://btc.ms/api/?api=86b6c147ce28028e5c7762afce1656f898279889&url=http://bestfaucet.site/verify.php?k={key}&format=text';
$link_default = 'http://btc.ms/api/?api=86b6c147ce28028e5c7762afce1656f898279889&url=http://bestfaucet.site/verify.php?k={key}&format=text';

# RECAPTCHA SETTING
$config['recaptcha']['publickey'] = '';
$config['recaptcha']['privatekey'] = '';

# SOLVEMEDIA CAPTCHA SETTING
$config['solvemedia']['C-key'] = '';
$config['solvemedia']['V-key'] = '';
$config['solvemedia']['H-key'] = '';

# NASTYHOST SETTING
$config['nastyhost'] = 'off';

# ALERT SETTING
$config['alert-1']['title'] = 'Welcome';
$config['alert-1']['content'] = 'Hellow, feel free to test the script and report to me if you found any bugs !';

$config['alert-2']['title'] = 'Price';
$config['alert-2']['content'] = 'You can buy this script for 22 usd, life time support and free instalation !';

# ADVERTISEMENTS SETTING
$ad['top'] = '<img src="http://placehold.it/728x90">';
$ad['left'] = '<img src="http://placehold.it/160x600">';
$ad['right'] = '<img src="http://placehold.it/160x600">';
$ad['bottom-1'] = '<img src="http://placehold.it/300x250">';
$ad['bottom-2'] = '<img src="http://placehold.it/300x250">';
$ad['footer'] = '<img src="http://placehold.it/728x90">';
?>