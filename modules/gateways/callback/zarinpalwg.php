<?php
/*
Plugin Name: WHMCS ZarinPal Payment Module
Authors: Amir Keshavarz, Masoud Amini
Version: 0.9
*/
$con = file_exists('../../../init.php') ? '../../../init.php' : '../../../dbconnect.php';
require $con;

include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

function getVar($var)
{
	if (!isset($_GET[$var])) exit();
	return $_GET[$var];
}


$gatewaymodule = 'zarinpalwg'; // Name of the module

$gateway = getGatewayVariables($gatewaymodule);

if (!$gateway['type']) die('Module Not Activated'); // Checks gateway module is active before accepting callback

/* Get Returned Variables - Adjust for Post Variable Names from your Gateway's Documentation */

$invoiceId = getVar('invoiceid');
$amount = getVar('Amount');
$authority = getVar('Authority');
$status = getVar('Status');


$invoiceId = checkCbInvoiceID($invoiceId, $gateway['name']); // Checks invoice ID is a valid invoice number or ends processing

$caculatedFee = round($amount * 0.01);

$paidFee = ($gateway['afp'] == 'on') ? 0 : $caculatedFee;
$hiddenFee = ($gateway['afp'] == 'on') ? $caculatedFee : 0;

switch($gateway['MirrorName']) {
	case 'آلمان':
		$mirror = 'de';
		break;
	case 'ایران':
		$mirror = 'ir';
		break;
	default:
		$mirror = 'www';
		break;
}

if($status == 'OK') {
	try {
		$client = new SoapClient('https://' . $mirror . '.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
		$resultO = $client->PaymentVerification([
			'MerchantID'	 => $gateway['merchantID'],
			'Authority' 	 => $authority,
			'Amount'	 	 => $amount + $hiddenFee
			]);
		
		$result  = $resultO->Status;
		$transid = $resultO->RefID;

		checkCbTransID($transid); // Checks transaction number isn't already in the database and ends processing if it does

	} catch (Exception $e) {
		echo '<h2>وقوع وقفه!</h2>';
		print_r($e);
	}

} else {
	$resultO = new stdClass();
	$result = -77;
}

$amount = ($gateway['Currencies'] == 'Rial') ? $amount * 10 : $amount;
$paidFee = ($gateway['Currencies'] == 'Rial') ? $paidFee * 10 : $paidFee;

if ($result == 100) {
	addInvoicePayment($invoiceId, $transid, $amount, $paidFee, $gatewaymodule); // Apply Payment to Invoice: invoiceId, transactionid, amount paid, fees, modulename
	logTransaction($gateway['name'], ['Get' => $_GET, 'Websevice' => (array) $resultO], 'Successful'); // Save to Gateway Log: name, data array, status
} else {
	logTransaction($gateway['name'], ['Get' => $_GET, 'Websevice' => (array) $resultO], 'Unsuccessful'); // Save to Gateway Log: name, data array, status
}

Header('Location: ' . $config['SystemURL'] . '/clientarea.php?action=invoices');
    
?>
