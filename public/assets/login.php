<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');

if(is_logged_in()) {
	header('location:/');
	exit();
}

$pw = _request('pw');

if($pw && $pw != '') {
	$validated = validate_password($pw);

	if($validated) {
		exit('success');
	} else {
		exit('Incorrect Password');
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo getenv('APP_NAME'); ?></title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.responsive.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.scroller.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/iziToast.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/ui.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/materialdesignicons.min.css">
	<link rel="icon" sizes="32x32" href="/assets/images/32x32.png">
	<link rel="icon" sizes="64x64" href="/assets/images/64x64.png">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet"> 
	<style>
		body {
			font-family: 'Poppins', sans-serif;
			background-color: #fff;
		}

		h1,h2,h3 {
			font-weight: bold;
		}

		tr {
			cursor: pointer;
		}

		.form-control {
			margin-top: 8px;
		}

		.map-container {
			overflow: hidden;
			padding-bottom: 56.25%;
			position: relative;
			height: 0;
		}

		.map-container iframe {
			left: 0;
			top: 0;
			height: 100%;
			width: 100%;
			position: absolute;
		}

		.navbar {
			top: 0px;
			left: 0px;
			height: 114px;
			background: #3F51B5 0% 0% no-repeat padding-box;
			box-shadow: 0px 3px 6px #00000029;
			opacity: 1;
			border: none;
			position: relative;
			border-radius: 0;
		}

		.nav-inner {
			max-width: 1500px;
			display: flex; height: 100%;
			width: 100%;
			flex-direction: row;
			align-items: center;
			position: relative;
		}

		#logout-btn {
			color: #fff;
			font-size: 17px;
			position: absolute;
			right: 0;
			padding: 8px;
			cursor: pointer;
		}

		.door-icon {
			width: 19px;
			height: 19px;
		}

		.card {
			border: 1px solid #DDDDDD;
			background-color: #fff;
			border-radius: 3px;
			padding: 20px;
			box-shadow: 0px 3px 6px #00000029;
		}

		#login-btn
		{
			background-color: #3F51B5;
			color: #fff;
			border: none;
			width: 188px;
			height: 52px;
			box-shadow: 0px 3px 6px #00000029;
			font-size: 15px;
		}

		#login-btn:hover
		{
			background-color: #2F41A5;
		}

		.footer-icon {
			width: 18px; height: 18px;
			margin-right: 26px;
			transition: .25s ease;
			cursor: pointer;
		}

		.footer-icon:hover {
			transition: .3s ease;
			transform: scale(1.3);
		}

		#password-input {
			border: none;
			border-bottom: 1px solid #000; width: 100%;
			max-width: 380px;
			background: transparent;
			outline: none;
			padding-bottom: 10px;
			padding-top: 10px;
		}

		<?php
		for($i = 5; $i <= 200; $i += 5) {
			echo ".pt".$i."{ padding-top: ".$i."px; }\n";
			echo ".pb".$i."{ padding-bottom: ".$i."px; }\n";
		}
		?>

	</style>
</head>
<body>
	<div style="position: relative; width: 100%; height: 100vh; display: flex; justify-content: center; align-content: center;">
		<img src="/assets/images/logo-black.png" style="width: auto; height: 48px; position: absolute; left: 40px; top: 20px; cursor: pointer;" onclick="window.location.href = '/'">

		<div style="position: absolute; left: 40px; bottom: 20px;">&copy;ERSSH V Ltd. <?php echo date('Y'); ?>
		</div>

		<div style="position: absolute; right: 40px; bottom: 20px;">
			<img src="/assets/images/telegram.svg" class="footer-icon" onclick="window.open('https://t.me/swstoken')">
			<img src="/assets/images/twitter.svg" class="footer-icon" onclick="window.open('https://twitter.com/swstoken')">
			<img src="/assets/images/instagram.svg" class="footer-icon" onclick="window.open('https://instagram.com/swstoken/?hl=en')">
			<img src="/assets/images/youtube.svg" class="footer-icon" onclick="window.open('https://www.youtube.com/channel/UC-NcJ4R7_V8W_3nVkCAswbQ')">
		</div>

		<div style="position: absolute; z-index: 1; width: 100%; max-width: 800px; height: 100%; max-height: 630px; top: calc(50% - 315px); background-image: url('/assets/images/heart-min.png'); background-size: cover; background-position: -25px; text-align: center;">
			<h1 class="pt60">SWS LP Report</h1>
			<p style="font-size: 17px;">Enter password to login</p>

			<form action="/login" method="post" id="login-form">
				<div style="display: block; width: 100%;" class="pt40">
					<input type="password" name="password" id="password-input" placeholder="Password" required>
				</div>
				<div style="display: block; width: 100%; height: 100px;" class="pt40">
					<button id="login-btn">Login</button>
				</div>
			</form>
		</div>
	</div>


<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/moment.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/ui.js"></script>
<script src="/assets/js/iziToast.min.js"></script>
<script>

$("#login-form").submit(function(event) {
	event.preventDefault();
	console.log(this);
	var pw = $("#password-input").val();

	$.ajax({
		url: '/login',
		method: 'post',
		data: {
			pw: pw
		}
	})
	.done(function(res) {
		if(res == 'success') {
			window.location.href = '/';
		} else {
			alert(res);
			$("#password-input").val('').focus();
		}
	});
});

</script>
</body>
</html>
