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

$type = {type};
$file = $argv[1];

$fn = fopen($file, "r");
while ($line = fgets($fn)) {
    $line = trim($line);

    echo "资源关键词：$line\n";
    echo "序号\t标题\n";
    $obj = new Service_Fasttask_TitlePs($line, $type);
    for ($pn = 0; $pn < 5; $pn ++) {
        $ret = $obj->run($pn, 0, 10);
        foreach ($ret as $index => $item) {
            echo "$index";
            foreach ($item as $value) {
                echo "\t$value";
            }
            echo "\n";
        }
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
