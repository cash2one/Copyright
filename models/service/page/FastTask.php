<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file FastTask.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/16 19:31
 * @brief
 *
 **/
class Service_Page_FastTask
{
    protected $sdf;

    /**
     * @param
     * @return
     */
    public function __construct()
    {
        if(empty($this->sdf))
        {
            $this->sdf = new Service_Data_FastTask();
        }
    }

    /**
     * @param $jobid
     * @param $uid
     * @param $query
     * @param $mode
     * @param $type
     * @param $scope
     * @param $createTime
     * @param null $chapter
     * @param null $text
     * @return mixed
     */
    public function createJob($jobid,$uid,$query,$mode,$type,$scope,$createTime,$jobStatistic,$status,$jobResult=null,$chapter=null,$text=null)
    {
        //构造row数据
        $row = array('jobid'=>$jobid,'uid'=>$uid,'query'=>$query,'mode'=>$mode,'type'=>$type,'scope'=>$scope,'create_time'=>$createTime);
        $row['job_stat'] = $jobStatistic;
        $row['status'] = $status;
        if(!empty($jobResult))
        {
            $row['job_result'] = $jobResult;
        }
        if(!empty($chapter))
        {
            $row['chapter'] = $chapter;
        }
        if(!empty($text))
        {
            $row['text'] = $text;
        }
        $ret = $this->sdf->insertTable($row);
        return $ret;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
