<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file FullTask.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/14 15:04
 * @brief
 *
 **/
class Service_Page_FullTask
{
    protected $sdf;

    public function __construct()
    {
        if(empty($this->sdf))
        {
            $this->sdf = new Service_Data_FullTask();
        }
    }

    /**
     * @param $jobid
     * @param $uid
     * @param $file
     * @param $mode
     * @param $type
     * @param $scope
     * @param int $custom_start_time 默认0 表示没有起始时间
     * @param int $custom_end_time 默认0表示没自定义的时间是当前时间
     */
    public function insertTable($jobid,$uid,$file,$mode,$type,$scope,$custom_start_time=0,$custom_end_time=0)
    {
        //构造row数据
        $row = array('jobid'=>$jobid,'uid'=>$uid,'file'=>$file,'mode'=>$mode,'type'=>$type,'scope'=>$scope);

        $ret = $this->sdf->insertTable($row);
        return $ret;
    }


    /**
     * @param $uid
     * @param $pageIndex
     * @param $pageCount
     */
    public function getJobs($uid,$pageIndex,$pageCount)
    {
        //先要拉取个count ， 这个用户曾经提交了多少个job
        $count = $this->sdf->getUidTaskCount($uid);
        if($count ==0)
        {
            return 0;
        }
        else if($count>0)
        {
            $fileds = array();
            $index = $pageCount*($pageIndex-1);
            $limit = $pageCount;
            return $this->sdf->select($fileds,$index,$limit);
        }
        else if($count === false)
        {
            Bd_Log::warning('log here');
            return 0;
        }
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
