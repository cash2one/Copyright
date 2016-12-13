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
        $jobId = $_GET['jobid'];
        $salt = $_GET['salt'];
        $parentFolder = Service_Copyright_File::getFullTaskPath() . '/' . $salt . '/' . $jobId;
        if (!file_exists($parentFolder . '/job_status.txt')) {
            $ret = array('errno' => -1, 'message' => 'do not exist this job_status.txt');
            $this->jsonResponse($ret);
            return;
        }
        $jobStatus = file_get_contents($parentFolder . '/job_status.txt');
        $arrJobs = json_decode($jobStatus, true);

        if (empty($arrJobs[$jobId])) {
            $ret = array('errno' => -1, 'message' => 'do not exist this jobid');
            $this->jsonResponse($ret);
        }
        else {
            $ret = array(
                'errno' => 0,
                'result' => array(
                    'job_process' => $arrJobs[$jobId]['process'],
                    'job_result_file' => $arrJobs[$jobId]['job_result_file'],
                    'job_stat' => json_encode($arrJobs[$jobId]['job_stat']),
                ),
            );
            $this->jsonResponse($ret);
        }
        /*
         * 现在每个salt文件下存放一个job_status，不用每次更新
         */
       /* 
        fastcgi_finish_request();
        foreach ($arrJobs as $index => $value) {
            if ($value['job_process'] === 100) {
                unset($arrJobs[$index]);
            }
        }
        $jobStatus = json_encode($arrJobs);
        file_put_contents($parentFolder . '/job_status.txt', $jobStatus, LOCK_EX);
         */
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
