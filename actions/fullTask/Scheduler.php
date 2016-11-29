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
class Action_Scheduler extends Ap_Action_Abstract
{

    /**
     * @param
     * @return
     */
    public function execute() {
        $httpPost = $_POST; //$request['post'];
        $jobId = $httpPost['jobId'];
        $mode = $httpPost['mode'];
        //$queryPath = $httpPost['queryPath'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        echo "submit successfully\n";
        fastcgi_finish_request();
        $salt = time();
        $parentFolder = Service_Copyright_File::getFullTaskPath() . '/' . $salt;
        mkdir($parentFolder);
        $queryPath = $parentFolder . '/' . $_FILES['query']['name'];
        BD_LOG::notice("query_path: ". $queryPath);
        move_uploaded_file($_FILES['query']['tmp_name'], $queryPath);
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
