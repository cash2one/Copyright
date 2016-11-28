<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Waiter.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/28 15:56
 * @brief
 *
 **/
class Action_Waiter extends Service_Action_Abstract
{

    /**
     * @param
     * @return errno, result，是个数组，里面包含job_process ,当job_process==100的时候 返回job_result_file 和job_stat
     */
    public function invoke()
    {
        //jobid （get）

        //$this->jsonResponse($result);

    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
