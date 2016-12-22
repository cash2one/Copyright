<?php

	
$g_arr_words = array();

/**
 * @param
 * @return
 */ 
function read_words() {
	global $g_arr_words;

    $file = fopen('{words.txt}', 'r');
	try{	
		while (!feof($file)){
			$line = trim(fgets($file));
			if (strlen($line) == 0){
				break;
			}
			$g_arr_words[] = $line;
		}
	}catch(Exception $e){
		echo $e->getMessage();
	}	
	fclose($file);
}


read_words();
$in = fopen("php://stdin", 'r');
while ($line = fgets($in)){
    $line = trim($line);
	$tokens = explode("\t", $line);
	$title = $tokens[1];
	$flag = false;
	foreach ($g_arr_words as $key) {
		if (mb_strpos($title, $key, 0, 'UTF-8') !== false) {
			$flag = true;
			$match = $key;
			break;
		}	
	}
	if ($flag) {
		print "$match\t$line\n";
		continue;
	}
}


?>
