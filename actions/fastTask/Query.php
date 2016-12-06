<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Query.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/8 11:18
 * @brief 
 *  
 **/
class Action_Query extends Service_Action_Abstract
{
    public $query;
    public $createTime;
    public $chapter;
    public $text;
     /*
     *  @param :
     *  @return :
     * */

    public function invoke()
    {
        $httpGet = $_GET;
        $jobId = $httpGet['jobid'];
        $ret['errno'] = 0;
        $ret['message'] = '';
        if(empty($httpGet['jobid']))
        {
            $ret = array('errno'=>-1,'message'=>'jobid is empty!');
            $this->jsonResponse($ret);
            return;
        }
        $ret['jobid'] = $httpGet['jobid'];
        //这个地方应该是寅生加的， 根据jobid 其实可以从redis取出来的， 不过传过来也行
        $ret['mode'] = $httpGet['mode'];
        $ret['type'] = $httpGet['type'];
        $ret['scope'] = $httpGet['scope'];
        $mode = $ret['mode'];
        $type = $ret['type'];
        $scope = $ret['scope'];
        $ret['result'] = array();

        $caseNum = 100;
        if ($mode == 0 && $scope == 0)
        {
            $caseNum = 50;
        }
        else if ($mode == 0 && $scope == 1)
        {
            $caseNum = 100;
        }
        else if ($mode == 1 && $scope == 0)
        {
            $caseNum = 10;
        }
        $fields[] = "info";
        for($i = 0; $i < $caseNum; $i++)
        {   
            $fields[] = $i; 
        }   
        $hashCache = new Service_Copyright_HashCache();
        $retCache = $hashCache->read($jobId, $fields);
        // redis访问失败
        if ($retCache === false || $retCache['err_no'] != 0)
        {
            $ret['errno'] = 1;
            $ret['message'] = "visit cache fail!";
            $this->jsonResponse($ret);
        }
        // 没查询到jobid
        else if (empty($retCache['ret']["$jobId"]))
        {
            $ret['errno'] = 2;
            $ret['message'] = "jobid = $jobId doesn't exist!";
            $this->jsonResponse($ret);
        }
        else if (!isset($retCache['ret']["$jobId"]['info']) ||
                 empty($retCache['ret']["$jobId"]['info']))
        {
            $ret['errno'] = 3;
            $ret['message'] = "job info miss!";
            $this->jsonResponse($ret);
        }
        else
        {
            // 将redis中缓存的数据打到返回结果里面
            $missCount = 0;
            for ($i = 0; $i < $caseNum; ++$i)
            {
                if (!isset($retCache['ret']["$jobId"][$i]) || 
                    empty($retCache['ret']["$jobId"][$i]))
                {
                    //只统计缺失多少个，不退出
                    $missCount ++;
                    //$ret['errno'] = 4;
                    //$ret['message'] = "cache index = $i miss";
                    //break;
                }
                else 
                { 
                    $ret['result'][] = json_decode($retCache['ret']["$jobId"][$i], true);
                }
            }
            // 只要缺失的数量小于10%，都可以接受
            if ($missCount < $caseNum * 0.1) {
                $ret['errno'] = 4;
                $ret['message'] = "cache miss too much";
            }

            $this->processInfo($retCache['ret']["$jobId"]['info']);
            $ret['query'] = $this->query;
            $this->jsonResponse($ret); //给前端返回， 并断开连接

            //就是这里， 要添加数据分析结果，要入库mysql

            $spf = new Service_Page_FastTask();
            $spf->saveJob($jobId,$this->getUid(),$this->query,$mode,$type,$scope,$this->createTime,
                $ret['result'],
                $this->chapter,$this->text);

        }
    }

    /**
     * @param $infoJsonString
     * @return
     */
    private function processInfo($infoJsonString)
    {
        $infoArr = json_decode($infoJsonString,true);
        $this->query = $infoArr['query'];
        $this->createTime = $infoArr['createTime'];
        if(!empty($infoArr['chapter']))
        {
            $this->chapter = $infoArr['chapter'];
        }
        if(!empty($infoArr['text']))
        {
            $this->text = $infoArr['text'];
        }
        return;
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
