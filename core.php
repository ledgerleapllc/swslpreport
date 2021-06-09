<?php

// include_once('vendor/autoload.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/../classes/dotenv.php');
// include_once('core/classes/db.php');

$dotenv = new Dotenv($_SERVER['DOCUMENT_ROOT'].'/../.env');
$dotenv->load();
// $db = new DB();

function elog($msg) {
	file_put_contents('php://stderr', print_r($msg, true));
}

function generateHash($length = 10) {
	$seed = str_split(
		'ABCDEFGHJKLMNPQRSTUVWXYZ'.
		'2345678923456789'
	);
	// dont use 0, 1, o, O, l, I
	shuffle($seed);
	$hash = '';

	foreach(array_rand($seed, $length) as $k) {
		$hash .= $seed[$k];
	}

	return $hash;
}

function b_encode($data) {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b_decode($data) {
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function _request($key, $hammer = 0) {
	if(isset($_REQUEST[$key])) {
		if($hammer == 1) {
			$data = $_REQUEST[$key];
			$output = '';
			$length = strlen($data);

			for($i = 0; $i < $length; $i++) {

				if(preg_match("/['A-Za-z0-9.,-@+]+/", $data[$i])) {
					$output .= $data[$i];
				}
			}
			return filter($output);

		} elseif($hammer == 2) {
			$data = $_REQUEST[$key];
			$output = '';
			$length = strlen($data);

			for($i = 0; $i < $length; $i++) {

				if(preg_match("/[A-Za-z0-9]+/", $data[$i])) {
					$output .= $data[$i];
				}
			}
			return filter($output);
		}
		return filter($_REQUEST[$key]);
	}
	return '';
}

function _file($key) {
	if(isset($_FILES[$key])) {
		if(($_FILES[$key]['size'] >= 10485760))
			return '';

		if($_FILES[$key]['error'] != 0)
			return '';

		$ext = '';
		$ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));

		if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'pdf' || $ext == 'png' || $ext == 'gif') {
			return $_FILES[$key];
		}
		return '';
	}
	return '';
}

function filter($string) {
	if(gettype($string) == 'array')
		return $string;

	$string = addslashes(trim($string));
	return htmlentities($string, ENT_COMPAT | ENT_HTML401, 'UTF-8');
}

function getRealIP() {
	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	if($ip == '::1')
		return '127.0.0.1';

	if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
		return '127.0.0.1';

	return $ip;
}

function get_nodes() {
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8088/get_data');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$return = json_decode($result);
	} catch (Exception $e) {
		elog($e);
		$result = array();
	}

	return $result;
}

function poll_nodes() {
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8088/poll_data');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$return = json_decode($result);
	} catch (Exception $e) {
		elog($e);
		$result = array();
	}

	return $result;
}


?>