<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file Abstract.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/14 15:21:49
 * @brief 
 *  
 **/


abstract class Service_FullTask_Abstract {

    public static $PHP_PATH;
    
    protected $jobId;
    protected $type;
    protected $scope;
    protected $queryPath;

    /**
     * @param 
     * @return 
     */ 
    public function __construct($_jobId, $_type, $_scope, $_queryPath) {
        $this->jobId = $_jobId;
        $this->type = $_type;
        $this->scope = $_scope;
        $this->queryPath = $_queryPath;
        self::$PHP_PATH = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/php/bin/php';
    }

    /**
     * @param
     * @return
     */
    public function update_status($process, $resultPath = null, $statJson = null) {
        $dir = Service_Copyright_File::getFullTaskPath(); 
        $jobStatus = file_get_contents($dir . "/job_status.txt");
        if ($jobStatus) {
            $arrJobs = json_decode($jobStatus, true);
        }
        else {
            $arrJobs = array();
        }
        $jobItem = $arrJobs[$this->jobId];
        if ($jobItem == null) { $jobItem = array(); }
        $jobItem['process'] = $process;
        if ($resultPath) {
            $jobItem['job_result_file'] = $resultPath;
        }
        if ($statJson) {
            $jobItem['job_stat'] = $statJson;
        }
        $arrJobs[$this->jobId] = $jobItem;
        $jobStatus = json_encode($arrJobs);
        file_put_contents($dir . '/job_status.txt', $jobStatus, LOCK_EX);
    }

    /**
     * @param
     * @return
     */   
    abstract public function run();
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
