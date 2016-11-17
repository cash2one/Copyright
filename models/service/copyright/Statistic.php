<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Statistic.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:11
 * @brief  用于任务统计分析的类
 *
 **/
class Service_Copyright_Statistic
{
    /**
     * @param
     * @return
     */
    public function computeFastTaskStatistic()
    {
        //目前是瞎构造的
        $overview = array('totalScan'=>9900,'hitResourceCount'=>500,'riskCount'=>465,'highRiskCount'=>199,'priacyAttachCount'=>344,'priacyUrlCount'=>278);
        $riskEstimate = array('totalScan'=>9900,'riskCount'=>465,'highRiskCount'=>199,'lowRiskCount'=>266,'priacyAttachCount'=>344,'priacyUrlCount'=>278);
        $priacySource = array(array('from'=>'www.daoban.com','fromType'=>0,'count'=>89),array('from'=>'盗版发帖人','fromType'=>1,'count'=>304));
        $interval = 1;
        $result = array('overview'=>$overview,'riskEstimate'=>$riskEstimate,'priacySource'=>$priacySource,'intval'=>$interval);
        return $result;
    }

    /**
     * @param $jobid
     * @return
     */
    public function fastTaskAnalysis($jobid)
    {
        //从数据库表里面拉取数据
        //1. 首先确定是否有这jobid
        $sdf = new Service_Data_FastTask();
        $count = $sdf->getJobIdCount($jobid);
        if($count == 0)
        {
            Bd_Log::warning(sprintf('[jobid] %s , not found this job',$jobid));
            return false;
        }
        //2. 拼凑要拉取的字段， 然后从数据库中拉取
        $fields = array('jobid','uid','query','mode','type','scope','chapter','text','create_time','job_stat','status');
        $ret = $sdf->getStatistic($fields,$jobid);
        //3. 对结果进行格式化
        $result = array();
        if(!empty($ret[0]))
        {
            $result = array('jobid'=>$jobid,'uid'=>$ret[0]['uid'],'query'=>$ret[0]['query']);
            $result['mode'] = intval($ret[0]['mode']);
            $result['type'] = intval($ret[0]['type']);
            $result['scope'] = intval($ret[0]['scope']);
            if(!empty($ret[0]['chapter']))
            {
                $result['chapter'] = $ret[0]['chapter'];
            }
            if(!empty($ret[0]['text']))
            {
                $result['text'] = $ret[0]['text'];
            }
            $result['createDate'] = intval($ret[0]['create_time']);
            $result['jobStatistic'] = json_decode($ret[0]['job_stat'],true);
            $result['status'] = intval($ret[0]['status']);
        }
        return $result;
    }

    /**
     * @param $jobid
     * @return
     */
    public function fullTaskAnalysis($jobid)
    {
        //从数据库表里面拉取数据
        //1. 首先确定是否有这jobid
        $sdf = new Service_Data_FullTask();
        $count = $sdf->getJobIdCount($jobid);
        if($count == 0)
        {
            Bd_Log::warning(sprintf('[jobid] %s , not found this job',$jobid));
            return false;
        }
        //2. 拼凑要拉取的字段， 然后从数据库中拉取
        $fields = array('jobid','uid','salt','file_name','mode','type','scope','create_time','custom_start_time','custom_end_time','job_result_file','job_stat','status');
        $ret = $sdf->getStatistic($fields,$jobid);
        //3. 对结果进行格式化
        $result = array();
        if(!empty($ret[0]))
        {
            $result = array('jobid'=>$jobid,'uid'=>$ret[0]['uid'],'sourceFile'=>$ret[0]['file_name']);
            $result['mode'] = intval($ret[0]['mode']);
            $result['type'] = intval($ret[0]['type']);
            $result['scope'] = intval($ret[0]['scope']);
            $result['salt'] = $ret[0]['salt'];
            $result['sourceFile'] = $ret[0]['file_name'];
            $result['resultFile'] = $ret[0]['job_result_file'];

            $result['createDate'] = intval($ret[0]['create_time']);
            if(empty($ret[0]['custom_start_time']) && empty($ret[0]['custom_end_time']))
            {
                $result['fullTime'] = 1;
            }
            $result['startDate'] = intval($ret[0]['custom_start_time']);
            $result['endDate'] = intval($ret[0]['custom_end_time']);

            $result['jobStatistic'] = json_decode($ret[0]['job_stat'],true);
            $result['status'] = intval($ret[0]['status']);
        }
        return $result;
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
