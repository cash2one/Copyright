<?php

while ($line = fgets(STDIN)){
    $line = trim($line);
	$tokens = explode("\t", $line);
	$content = $tokens[6];
	$flag = false;
	$match = '';
    $intRet = preg_match('/\<file fsid.*?\/>/i', $strCon, $arrMat);
	if ($intRet > 0) {
    	$match = $arrMat[0];
        $flag = true;
    	/*
		下面这段用来提取<file>中的url和name
		$intRet = preg_match('/name="(.*?)"/', $piracyAttach, $arrMat);
        $piracyAttachName = $arrMat[1];
        $intRet = preg_match('/link="(.*?)"/', $piracyAttach, $arrMat);
        $piracyAttachLink = 'http://pan.baidu.com' . $arrMat[1];
    	*/
	}	
	if ($flag == false) {
		 $hasUrl = preg_match('/http[s]?\:\/\/[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+|www\.[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+/i', $content, $matches);
		if ($hasUrl) { $flag = true; $match = $matches[0]; }
	}
	if ($flag) {
		print "$line\t$match\n";
		continue;
	}
}

?>
