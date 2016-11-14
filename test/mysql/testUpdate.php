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
    //随便获取一个jobid；
    $mysqInstance = new Service_Dao_Mysql();
    $ret = $mysqInstance->query('select jobid from full_task limit 1');
    $jobid = $ret[0]['jobid'];


    $job_process = rand(1,99);
    $row = array('status'=>1,'job_process'=>$job_process);


    $obj = new Service_Page_FullTask();
    $ret = $obj->updateTable($jobid,$row);
    var_dump($ret);

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
