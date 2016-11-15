<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Query.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/9 15:15
 * @brief 
 *  
 **/
class Action_Query extends Service_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function invoke()
    {
        $request = Saf_SmartMain::getCgi();
        $httpGet = $request['get'];
        $pageIndex = intval($httpGet['pageIndex']);
        $pageCount = intval($httpGet['pageCount']);
        if(isset($httpGet['status']))
        {
            $status = intval($httpGet['status']);
        }
        $uid = $this->getUid();

        // get jobs from mysql deps on uid, pageIndex, pageCount
        $obj = new Service_Page_FullTask();
        $ret = $obj->getJobs($uid, $pageIndex, $pageCount,$status);
        $this->jsonResponse($ret);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
