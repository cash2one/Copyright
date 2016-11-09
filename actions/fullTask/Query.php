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
class Action_Query extends Ap_Action_Abstract
{
     /*
     *  @param :
     *  @return :
     * */

    public function execute()
    {
        $httpGet = $_GET;
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $pageIndex = $httpPost['pageIndex'];
        $pageCount = $httpPost['pageCount'];
        $uid = "xxx";

        // get jobs from mysql deps on uid, pageIndex, pageCount
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
