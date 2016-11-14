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
        $httpPost = $request['post'];
        $pageIndex = intval($httpPost['pageIndex']);
        $pageCount = intval($httpPost['pageCount']);
        $uid = $this->getUid();

        // get jobs from mysql deps on uid, pageIndex, pageCount
        $obj = new Service_Page_FullTask();
        $ret = $obj->getJobs($uid, $pageIndex, $pageCount);
        $this->jsonResponse($ret);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
