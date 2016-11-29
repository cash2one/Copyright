<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file title_iknow_statistic.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/25 15:10:21
 * @brief 
 *  
 **/

$file = $argv[1];
$fd = fopen($file, "r");
while ($line = fgets($fd)) {
    $line = trim($line);
    $tokens = explode("\t", $line);
    
}





/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
