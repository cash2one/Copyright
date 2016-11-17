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
     * @param null $jobResult
     * @param null $chapter
     * @param null $text
     * @return bool
     */
    public function saveJob($jobid,$uid,$query,$mode,$type,$scope,$createTime,$jobResult=null,$chapter=null,$text=null)
    {
        //是否有必要进行存储
        $count = $this->sdf->getJobIdCount($jobid);
        if($count == 0)
        {
            $scs = new Service_Copyright_Statistic();
            $jobStat = $scs->computeFastTaskStatistic($jobResult); //统计结果

            //构造row数据
            $row = array('jobid'=>$jobid,'uid'=>$uid,'query'=>$query,'mode'=>$mode,'type'=>$type,'scope'=>$scope,'create_time'=>$createTime);
            $row['status'] = 3; //3表示job执行成功
            if(!empty($jobResult))
            {
                $row['job_result'] = json_encode($jobResult);
            }
            if(!empty($jobStat))
            {
                $row['job_stat'] = $jobStat;
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
        return true; //说明已经有jobid存储了， 那么就直接返回true;

    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
