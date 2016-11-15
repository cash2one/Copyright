<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Submit.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/9 15:04
 * @brief
 *
 **/
class Action_Submit extends Service_Action_Abstract
{
    /*
    *
    *  mode字典类型: 0=标题类, 1=内容类
    * type字典类型: 0=小说/出版物, 1=影视剧，2=小说内容，3=短文内容
    * scope字典类型: 0=百度搜索结果, 1=百度知道站内资源，2=百度贴吧
    * query表示标题内容
    * text表示文本内容(仅当mode类型为内容类时使用)
    *
    * */

    public function invoke()
    {
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $mode = $httpPost['mode'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $fileId = $httpPost['fileId'];
        //默认用户自定的时间，起始时间和终止时间都是0
        $custom_start_time = 0;
        $custom_end_time = 0;
        if (empty($httpPost['fullTime'])) {
            $custom_start_time = isset($httpPost['startTime']) ? $httpPost['startTime'] : 0;
            $custom_end_time = isset($httpPost['endTime']) ? $httpPost['endTime'] : 0;
        }

        //这是寅生写的生成jobid的方法
        $jobId = $this->genJobId(
            $mode,
            $type,
            $scope,
            $fileId,
            intval($httpPost['fullTime']),
            $custom_start_time,
            $custom_end_time,
            $this->uid);

        // sumbit a new job here
        $obj = new Service_Page_FullTask();
        $ret = $obj->createJob(
            $jobId,
            $this->uid,
            $fileId,
            $mode,
            $type,
            $scope,
            $custom_start_time,
            $custom_end_time
        );
        $this->jsonResponse($ret);
    }

    /**
     * @param :
     * @return :
     * */
    public function genJobId(
        $mode,
        $type,
        $scope,
        $fileId,
        $fullTime,
        $startTime,
        $endTime,
        $uid)
    {
        $str = "uid:$uid ";
        $str .= "mode:$mode ";
        $str .= "type:$type ";
        $str .= "scope:$scope ";
        $str .= "fileId:$fileId ";
        $str .= "fullTime:$fullTime ";
        $str .= "startTime:$startTime ";
        $str .= "endTime:$endTime";
        $jobId = md5($str);
        return $jobId;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
