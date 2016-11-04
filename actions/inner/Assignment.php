<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Assignment.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/4 10:56
 * @brief  分发任务接口类
 *  
 **/
 
 class Action_Assignment extends Ap_Action_Abstract
 {
     /*
     * 
     * mode字典类型: 0=标题类, 1=内容类
     * type字典类型: 0=小说/出版物, 1=影视剧，2=小说内容，3=短文内容
     * scope字典类型: 0=百度搜索结果, 1=百度知道站内资源，2=百度贴吧
     * query表示标题内容
     * text表示文本内容(仅当mode类型为内容类时使用)
     * 
     * */

    public function execute()
    {
        $httpGet = $_GET;
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $mode = $httpPost['mode'];
        $query = $httpPost['query'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $chapter = isset($httpPost['chapter'])?$httpPost['chapter']:"";
        $text = isset($httpPost['text'])?$httpPost['text']:"";
     
        $assignJob = new Service_Copyright_Assign();
        $ret = $assignJob->assignParallel($mode, $type, $scope, $query, $chapter, $text, 100);
        echo json_encode($ret);
    }
 }
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
