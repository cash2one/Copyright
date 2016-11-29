<?php
$fn = $argv[1];
$fd = fopen($fn,'r');
$g_arr_not_video = array(
	"小说",
	"txt",
	"TXT", 
	"全文",
	"全本",
	"插曲",
	"歌曲", 
	"铃声",
	"音乐",
	"配乐",
	"mp3",
	"MP3",	
);

/**
 * @param 
 * @return
 */ 
function contains_not_video($title) {
	global $g_arr_not_video;

	foreach($g_arr_not_video as $key) {
		if (mb_strpos($title, $key, 0, 'UTF-8') !== false) {
			return $key;
		}
	}
	return '';
}

$type = '{type}';

while($ln = trim(fgets($fd))) {
	list($start_usec, $start_sec) = explode(" ", microtime());
	$input['pid'] =  'qtag';
	$arr = explode("\t",$ln);
	if ($type == 'film' && ($key = contains_not_video($arr[2])) != '') {
		echo "$ln\t非视频_$key\n";
		continue;
	}

	$input['doc'] = $arr[2];
	$doc_len = mb_strlen($input['doc'],'utf8');
	$postdata = json_encode($input);
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, 'http://10.100.18.62:2011/DnnService/DnnInf');
	$header = array(
		"Content-Type: application/json",
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	$curlresult = curl_exec($ch);
	$t = json_decode($curlresult, true);
	$tags = implode("_",$t['label']);
	usleep(15);
	echo "$ln\t$tags\n";
}

?>

