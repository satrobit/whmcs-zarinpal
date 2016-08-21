<?php
/*
Plugin Name: WHMCS ZarinPal Payment Module
Authors: Amir Keshavarz, Masoud Amini
Version: 0.9
*/

function zarinpalwg_config()
{
	return [
		'FriendlyName' => ['Type' => 'System', 'Value'=>'زرین پال - وب گیت'],
		'merchantID' => ['FriendlyName' => 'merchantID', 'Type' => 'text', 'Size' => '50'],
		'Currencies' => ['FriendlyName' => 'Currencies', 'Type' => 'dropdown', 'Options' => 'Rial,Toman'],
		'MirrorName' => ['FriendlyName' => 'نود اتصال', 'Type' => 'dropdown', 'Options' => 'آلمان,ایران,خودکار', 'Description' => 'چناانچه سرور شما در ایران باشد ایران دا انتخاب کنید و در غیر اینصورت آلمان و یا خودکار را انتخاب کنید'],
		'afp' => ['FriendlyName' => 'افزودن کارمزد به قیمت ها', 'Type' => 'yesno', 'Description' => 'در صورت انتخاب 1 درصد به هزینه پرداخت شده افزوده می شود.']
		];
}

function zarinpalwg_link($params)
{
	$code = '<form method="post" action="./zarinpalwg.php">
        <input type="hidden" name="merchantID" value="' . $params['merchantID'] . '" />
        <input type="hidden" name="invoiceid" value="' . $params['invoiceid'] . '" />
        <input type="hidden" name="amount" value="' . $params['amount'] . '" />
        <input type="hidden" name="currencies" value="' . $params['Currencies'] . '" />
        <input type="hidden" name="afp" value="' . $params['afp'] . '" />
        <input type="hidden" name="systemurl" value="' . $params['systemurl'] . '" />
		<input type="hidden" name="email" value="' . $params['clientdetails']['email'] . '" />
		<input type="hidden" name="cellnum" value="' . $params['clientdetails']['phonenumber'] . '" />
		<input type="hidden" name="mirrorname" value="' . $params['MirrorName'] . '" />
        <input type="submit" name="pay" value=" پرداخت " />
    </form>';

	return $code;
}

?>
