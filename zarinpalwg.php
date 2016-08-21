<?php
/*
Plugin Name: WHMCS ZarinPal Payment Module
Authors: Amir Keshavarz, Masoud Amini
Version: 0.9
*/

function redirect($url)
{
	if (!headers_sent())
	{
		header('Location: ' . $url);
		exit();
	}
}

function getVar($var)
{
	if (!isset($_POST[$var])) exit();
	return $_POST[$var];
}

$currencies = getVar('currencies');
$raw_amount = getVar('amount');
$afp = getVar('afp');
$mirrorname = getVar('mirrorname');
$systemurl = getVar('systemurl');
$invoiceid = getVar('invoiceid');
$merchantID = getVar('merchantID');
$email = getVar('email');
$cellnum = getVar('cellnum');

$amount = ($currencies == 'Rial') ? round((int) $raw_amount / 10) : (int) $raw_amount;
$fee = ($afp == 'on') ? round($amount * 0.01) : 0;

switch ($mirrorname)
{
	case 'آلمان':
		$mirror = 'de';
		break;
	case 'ایران':
		$mirror = 'ir';
		break;
	default:
		$mirror = 'de';
		break;
}

$callback_url = $systemurl . '/modules/gateways/callback/zarinpalwg.php?invoiceid=' . $invoiceid . '&Amount=' . $amount;

try  {
	$client = new SoapClient('https://' . $mirror . '.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
	$result = $client->PaymentRequest([
		'MerchantID' 	=> $merchantID,
		'Amount' 		=> $amount + $fee,
		'Description' 	=> 'Invoice ID: ' . $invoiceid,
		'Email' 		=> $email,
		'Mobile' 		=> $cellnum,
		'CallbackURL' 	=> $callback_url
		]);
} catch (Exception $e) {
	echo '<h2>وقوع وقفه!</h2>';
	echo $e->getMessage();
}

if($result->Status == 100){
	$url = 'https://www.zarinpal.com/pg/StartPay/' . $result->Authority;
	redirect($url);
} else {
	echo "<h2>وقوع خطا در ارتباط!</h2>" .'کد خطا' . $result->Status;
}

?>
