<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Submit.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/8 9:44
 * @brief 
 *  
 **/
//class Action_Submit extends Ap_Action_Abstract
class Action_Submit extends Service_Action_Abstract
{
    /*
    *
    * mode字典类型: 0=标题类, 1=内容类
    * type字典类型: 0=小说/出版物, 1=影视剧，2=小说内容，3=短文内容
    * scope字典类型: 0=百度搜索结果, 1=百度知道站内资源，2=百度贴吧
    * query表示标题内容
    * text表示文本内容(仅当mode类型为内容类时使用)
    *
    * */
    public function invoke()
    {
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $mode = $httpPost['mode'];
        $query = $httpPost['query'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $chapter = isset($httpPost['chapter'])?$httpPost['chapter']:"";
        $text = isset($httpPost['text'])?$httpPost['text']:"";
        $caseNum = 10;
        $casePerParallelProcess = 10;
        if ($mode == 0 && $scope == 0)
        {       
            $caseNum = 50;
            $casePerParallelProcess = 5;
        }       
        else if ($mode == 0 && $scope == 1)
        {       
            $caseNum = 100;
            $casePerParallelProcess = 10;
        }
        else if ($mode == 1 && $scope == 0)
        {
            $caseNum = 10;
            $casePerParallelProcess = 1;
        }

        $jobId = $this->genJobId($mode, $type, $scope, $query, $chapter, $text);
        $ret['errno'] = 0;
        $ret['message'] = '';
        $ret['jobid'] = $jobId; 

        // check whether cache in !
        //$cacheData = array();
        if ($this->inCache($jobId, $caseNum))
        {
            $this->jsonResponse($ret);
        }
        // if no data cache in,submit the new task
        else
        {
            $assignJob = new Service_Copyright_Assign();
            $parallelRet = $assignJob->assignParallel(
                $jobId,
                $mode,
                $type,
                $scope,
                $query,
                $chapter,
                $text,
                $caseNum,
                $casePerParallelProcess);

            if ($parallelRet['errno'] == 0)
            {
                // 发起异步请求 生成分析报告
                $this->callStatistic(
                    $jobId,
                    $mode, 
                    $type, 
                    $scope, 
                    $query, 
                    $chapter, 
                    $text, 
                    $parallelRet['result']);

                $this->jsonResponse($ret);
            }
            else
            {
                $ret['errno'] = 1;
                $ret['message'] = "parallel failed!";
                $this->jsonResponse($ret);
            }
        }
    }

    /**
    * @param :
    * @return :
    * */
    public function genJobId(
        $mode, 
        $type, 
        $scope, 
        $query, 
        $chapter, 
        $text)
    {
        $str = "mode:$mode ";
        $str .= "type:$type ";
        $str .= "scope:$scope ";
        $str .= "query:$query ";
        $str .= "chapter:$chapter ";
        $str .= "text:$text";
        $jobId = md5($str);
        return $jobId;
    }

    /**
     * @param :
     * @return :
     * @desc : 执行提交操作前先检查本次提交是否存在缓存中，如果存在直接返回
     * */
    public function inCache($jobId, $caseNum)
    {
        $fields[] = "info";
        for($i = 0; $i < $caseNum; $i++)
        {   
            $fields[] = $i; 
        }   
        $hashCache = new Service_Copyright_HashCache();
        $retCache = $hashCache->read($jobId, $fields);
        //redis访问失败
        if ($retCache === false || $retCache['err_no'] != 0)
        {
            return false;
        }
        // 访问的jobid不存在
        else if (empty($retCache['ret']["$jobId"]))
        {
            return false;
        }
        else
        {
            // 某个下标不存在或者为空个数不够返回false
            foreach ($retCache['ret']["$jobId"] as $index => $value)
            {
                if (!isset($value) || empty($value))
                {
                    return false;
                }
            }
            return true;
        }
    }

    /**
    * @param :
    * @return :
    * @desc : 调用数据分析接口，这里采用异步方式调用以提高速度
     * */
    public function callStatistic(
        $jobId,
        $mode, 
        $type, 
        $scope, 
        $query, 
        $chapter, 
        $text, 
        $data)
    {
        return;
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
