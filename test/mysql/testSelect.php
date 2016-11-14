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
page1();
page2();

/**
 * @param
 * @return
 */
function page1()
{
    $uid = 123;
    $pageIndex =1;
    $pageCount = 3;

    $obj = new Service_Page_FullTask();
    $ret = $obj->getJobs($uid,$pageIndex,$pageCount);
    var_dump($ret);

}

/**
 * @param
 * @return
 */
function page2()
{
    $uid = 123;
    $pageIndex =2;
    $pageCount = 3;

    $obj = new Service_Page_FullTask();
    $ret = $obj->getJobs($uid,$pageIndex,$pageCount);
    var_dump($ret);

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
