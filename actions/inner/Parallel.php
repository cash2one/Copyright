<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Parallel.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/4 10:56
 * @brief  单个进程接到任务运行调度的类
 *  
 **/
 
class Action_Parallel extends Service_Action_Abstract
{
    public function invoke()
    {   
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $jobId = $httpPost['jobid'];
        $pn = $httpPost['pn'];
        $start = $httpPost['start'];
        $end = $httpPost['end'];
        $casePerPage = $httpPost['casePerPage'];
        $mode = $httpPost['mode'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $query = $this->iconvutf8($httpPost['query']);
        $chapter = $this->iconvutf8($httpPost['chapter']);
        $text = $this->iconvutf8($httpPost['text']);
        Bd_log::notice(sprintf('[query]%s,[chapter]%s,[text]%s',$query,$chapter,$text));
        
        $unitJob = new Service_Data_Unit();
        $ret = $unitJob->schedule(
            $jobId,
            $pn, 
            $start, 
            $end, 
            $casePerPage, 
            $mode, 
            $type, 
            $scope, 
            $query, 
            $chapter, 
            $text);
    }   
}
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
