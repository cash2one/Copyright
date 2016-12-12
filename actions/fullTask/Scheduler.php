<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Scheduler.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/28 15:56
 * @brief
 *
 **/
class Action_Scheduler extends Service_Action_Abstract
{

    /**
     * @param
     * @return
     */
    public function invoke()
    {
        $httpPost = $_POST;
        $jobId = $httpPost['jobid'];
        $mode = $httpPost['mode'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $salt = $httpPost['salt'];
        $fileName = $httpPost['fileName'];
        $response = array('errno'=>0,'result'=>'schedule successfully!');
        $this->jsonResponse($response);

        //make log
        Bd_Log::notice(sprintf('[jobid]%s,[mode]%s,[type]%s,[scope]%s,[salt]%s,[fileName]%s, schedule successfully!',$jobId,$mode,$type,$scope,$salt,$fileName));

        //根据salt 和fileName 拼凑输入的文件地址
        $queryPath = Service_Copyright_File::getFullTaskPath() . '/' . $salt . '/' . $fileName;
        if ($mode == 0 && $scope == 0) {
            $obj = new Service_FullTask_TitlePs($jobId, $type, $scope, $queryPath);
            $obj->run();
        }
        if ($mode == 0 && $scope == 1) {
            $obj = new Service_FullTask_TitleIknow($jobId, $type, $scope, $queryPath);
            $obj->run();
        }
        if ($mode == 1 && $scope == 0) {
            $obj = new Service_FullTask_ContentPs($jobId, $type, $scope, $queryPath);
            $obj->run();
        }
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
