<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file ps_fetch.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/15 15:27:10
 * @brief 
 *  
 **/

//require("/home/users/pancheng/pancheng-src/offline/models/service/fasttask/TitlePs.php");

$type = 0;
$file = $argv[1];

$fn = fopen($file, "r");
while ($line = fgets($fn)) {
    $line = trim($line);
    $tokens = explode("\t", $line);
    echo "资源关键词：$tokens[0]\t链接：$tokens[1]\n";
    echo "序号\t标题\n";
    $obj = new Service_Fasttask_ContentPs($tokens[0], $type, $tokens[1]);
    $ret = $obj->run();
    foreach ($ret as $index => $item) {
        echo "$index";
        foreach ($item as $value) {
            echo "\t$value";
        }
        echo "\n";
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
