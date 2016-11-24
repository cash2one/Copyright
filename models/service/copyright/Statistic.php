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
     * @return 0, 1, -1
     */ 
    public static function cmp_obj($a, $b) {
        if ($a['count'] > $b['count']) return -1;
        if ($a['count'] < $b['count']) return 1;
        return 0;
    }

    /**
     * @param $jobResult
     * @return array
     */
    public function computeFastTaskStatistic($jobResult)
    {
        //目前是瞎构造的
        //$overview 对应总体概况，$riskEstimate对应风险评估，$priacySource对应盗版来源， 是个数组
        /*
        $overview = array('totalScan'=>9900,'hitResourceCount'=>500,'riskCount'=>465,'highRiskCount'=>199,'priacyAttachCount'=>344,'priacyUrlCount'=>278);
        $riskEstimate = array('interval'=>1,'totalScan'=>9900,'riskCount'=>465,'noRiskCount'=>777,'riskRate'=>432/789,'highRiskCount'=>199,'lowRiskCount'=>266,'priacyAttachCount'=>789,'priacyUrlCount'=>278);
        $priacySource = array(array('from'=>'www.daoban.com','fromType'=>0,'count'=>89),array('from'=>'盗版发帖人','fromType'=>1,'count'=>304));
        $result = array('overview'=>$overview,'riskEstimate'=>$riskEstimate,'priacySource'=>$priacySource);
        return $result;
        */

        $totalScan = count($jobResult);
        $riskCount = 0;
        $highRiskCount = 0;
        $priacyAttachCount = 0;
        $priacyUrlCount = 0;
        $domainCount = array();
        $userCount = array();
        for ($jobResult as $key => $value) {
            $url = $value['url'];
            $title = $value['title'];
            $domain = $value['domain'];
            $user = $value['user'];
            $risk = $value['risk'];
            if ($risk != 0) { $riskCount ++; }
            if ($risk == 2) { $highRiskCount ++; }
            if ($domain != null) {
                if ($domainCount[$domain]) { $domainCount[$domain] ++; }
                else { $domainCount[$domain] = 1; }
            }
            if ($user != null) {    
                if ($userCount[$user]) { $userCount[$user] ++; }
                else { $userCount[$user] = 1; }
            }
        }
        $overview = array(
            'totalScan' => $totalScan,
            'hitResourceCount' => $totalScan,
            'riskCount' => $riskCount,
            'highRiskCount' => $highRiskCount,
            'priacyAttachCount' => $priacyAttachCount,
            'priacyUrlCount' => $priacyUrlCount,
        );
        $interval = 3;
        if ($riskCount / $totalScan > 0.2) $interval = 0;
        else if ($riskCount / $totalScan > 0.05) $interval = 1;
        else if ($riskCount / $totalScan > 0.01) $interval = 2;
        $riskEstimate = array(
            'interval' => $interval,
            'totalScan' => $totalScan,
            'riskCount' => $riskCount,
            'noRiskCount' => $noRiskCount,
            'riskRate' => $riskCount / $totalScan,
            'highRiskCount' => $highRiskCount,
            'lowRiskCount' => $lowRiskCount,
            'priacyAttachCount' => $priacyAttachCount,
            'priacyUrlCount' => $priacyUrlCount, 
        );
        $priacySource = array();
        //arsort($domainCount);
        //arsort($userCount);
        foreach ($domainCount as $key => $value) {
            $priacySource[] = array(
                'from' => $key,
                'fromType' => 0,
                'count' => $value,
            );
        }
        foreach ($userCount as $key => $value) {
            $priacySource[] = array(
                'from' => $key,
                'fromType' => 1,
                'count' => $value,
            );
        }
        usort($priacySource, array('Service_Copyright_Statistic', 'cmp_obj'));
        $priacySource = array_slice($priacySource, 0, 10);
        $result = array('overview'=>$overview,'riskEstimate'=>$riskEstimate,'priacySource'=>$priacySource);
    }

    /**
     * @param
     * @return
     */
    public function computeFullTaskStatistic()
    {

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
