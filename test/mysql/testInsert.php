<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file testInsert.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/14 19:08
 * @brief
 *
 **/
Bd_Init::init('copyright');
test();

/**
 * @param
 * @return
 */
function test()
{
    $obj = new Service_Page_FullTask();

    $jobid = md5(time());
    $uid = 123;
    $file = 'ftp://abc.com/789.file';
    $mode = 0;
    $type = 0;
    $scope = 0;
    $ret = $obj->createJob($jobid, $uid, $file, $mode, $type, $scope);
    var_dump($ret);
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
