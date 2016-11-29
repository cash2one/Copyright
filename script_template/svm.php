<?php
$fn = $argv[1];
$fd = fopen($fn,'r');
while($ln = trim (fgets($fd)))
{
	$arrTmp = explode("\t", $ln);
	$title = $arrTmp[2];//utf8
	$input['pid'] = '{type}';
	$input['line'] = $title;
	$postdata = json_encode($input);
	$ch = curl_init();
	//curl_setopt ($ch, CURLOPT_URL, 'http://10.199.21.28:2010/SVMService/svm_infer');
	curl_setopt ($ch, CURLOPT_URL, 'http://10.100.18.62:2010/SVMService/svm_infer');
	//curl_setopt ($ch, CURLOPT_URL, 'http://10.195.81.49:22903/DnnService/DnnInf');
	$header = array(
		"Content-Type: application/json",
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	$curlresult = curl_exec($ch);
	curl_close($ch);
	$t = json_decode($curlresult, true);
	echo "$ln\t{$t['score']}\n";
	usleep(10);
}
