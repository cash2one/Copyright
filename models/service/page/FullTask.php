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
     * @param $salt
     * @param $fileName
     * @param $mode
     * @param $type
     * @param $scope
     * @param int $custom_start_time 默认0 表示没有起始时间
     * @param int $custom_end_time 默认0表示没自定义的时间是当前时间
     * @return bool
     */
    public function createJob($jobid,$uid,$salt,$fileName,$mode,$type,$scope,$custom_start_time=0,$custom_end_time=0)
    {
        //构造row数据
        $row = array('jobid'=>$jobid,'uid'=>$uid,'salt'=>$salt,'file_name'=>$fileName,'mode'=>$mode,'type'=>$type,'scope'=>$scope);
        if($custom_start_time != 0)
        {
            $row['custom_start_time'] = $custom_start_time;
        }
        if($custom_end_time != 0)
        {
            $row['custom_end_time'] = $custom_end_time;
        }
        $ret = $this->sdf->insertTable($row);
        return $ret;
    }

    /**
     * @param $total
     * @param $currentPageIndex
     * @param $pageCount
     * @return int
     */
    private function getCurrentPageMaxIndex($total,$currentPageIndex,$pageCount)
    {
        if($currentPageIndex == 1)
        {
            return $total;
        }
        else
        {
            $totalPage = ceil($total/$pageCount);
            return intval(($totalPage-$currentPageIndex+1)*$pageCount);
        }
    }

    /**
     * @param $uid
     * @param $pageIndex
     * @param $pageCount
     * @return
     */
    public function getJobs($uid,$pageIndex,$pageCount,$status=null)
    {
        //先要拉取个count ， 这个用户曾经提交了多少个job
        $count = $this->sdf->getUidTaskCount($uid,$status);

        if($count === false)
        {
            return array('errno'=>-1,'result'=>array(),'message'=>'query jobs failed!');
        }

        $result = array();

        if($count > 0)
        {
            $fields = array('jobid','salt','file_name','create_time','mode','type','scope','status','job_process','job_result_file','custom_start_time','custom_end_time');
            $index = $pageCount*($pageIndex-1);
            $limit = $pageCount;
            $ret = $this->sdf->select($fields,$uid,$index,$limit,$status);
            $result = array();
            //格式化数据,从数据库到对象

            $serial_number = $this->getCurrentPageMaxIndex($count,$pageIndex,$pageCount);
            foreach($ret as $index=>$value) {
                $item = array('jobid' => $value['jobid']);
                //任务序号
                $item['index'] = $serial_number;
                $serial_number--;

                //全量任务对应的文件名字
                $item['sourceFile'] = $value['file_name'];
                $item['salt'] = $value['salt'];
                //全量任务对应的文件服务器的相对路径
                //$item['sourceFileServerPath'] = $value['salt'].'/'.$value['file_name'];
                $item['sourceFileServerPath'] = $value['file_name'];
                $item['createTime'] = intval($value['create_time']);
                $item['mode'] = intval($value['mode']);
                //当mode=0，即标题类的时候， 才有范围的说法
                if($item['mode'] == 0)
                {
                    $item['scope'] = intval($value['scope']);
                }
                $item['type'] = intval($value['type']);
                $item['status'] = intval($value['status']);
                $item['process'] = intval($value['job_process']);
                if(!empty($value['job_result_file']))
                {
                    $item['downloadAddr'] = sprintf('/copyright/file/download?salt=%s&file=%s',$value['salt'],$value['job_result_file']);
                }

                $custom_start_time = intval($value['custom_start_time']);
                $custom_end_time = intval($value['custom_end_time']);
                //有用户自定义时间的那种
                if ($custom_start_time > 0 && $custom_end_time > 0) {
                    $item['startDate'] = $custom_start_time;
                    $item['endDate'] = $custom_end_time;
                } else {
                    $item['fullTime'] = 1;  //全量任务的那种
                }
                $result[] = $item;
            }
        }
        return array('errno'=>0,'count'=>$count,'result'=>$result);

    }

    /**
     * @param $jobid
     * @param $row
     * @return mixed
     */
    public function updateTable($jobid,$row)
    {
        $ret = $this->sdf->updateTable($jobid,$row);
        return $ret;
    }

    /**
     * @param
     * @return
     */
    public function schedule()
    {
        //uid
        //jobid
        //file content
        //mode
        //type
        //scope
        //start optional
        //end optional
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
