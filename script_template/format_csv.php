<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file format_csv.php
 * @author pancheng(com@baidu.com)
 * @date 2016/12/19 16:33:51
 * @brief 
 *  
 **/

$fn = fopen($argv[1], "r");
$fd = fopen($argv[2], "w");
fwrite($fd, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($fd, array('序号', '检索资源名', '知道标题', '回帖用户名', '附件/链接判断', '判断结果'));
$index = 0;
while ($line = fgets($fn)) {
    $line = trim($line);
    if (empty($line)) { continue; }
    $tokens = explode("\t", $line);
    $index ++;
    fputcsv($fd, array($index, $tokens[0], $tokens[2], $tokens[5], $tokens[7], $tokens[count($tokens) - 1]));
}
fclose($fd);
fclose($fn);

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
