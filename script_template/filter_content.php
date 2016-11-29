<?php

while ($line = fgets(STDIN)){
    $line = trim($line);
	$tokens = explode("\t", $line);
	$content = $tokens[6];
	$flag = false;
	$match = '';
	if (mb_strpos($content, '<file ', 0, 'UTF-8') !== false) {
		$match = '<file>';
		$flag = true;
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
