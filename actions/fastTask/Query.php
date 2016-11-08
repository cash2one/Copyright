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
class Action_Query extends Ap_Action_Abstract
{
     /*
     *  @param :
     *  @return :
     * */

    public function execute()
    {
        $httpGet = $_GET;
        $jobId = $httpGet['jobId'];
        $ret['errno'] = 0;
        $ret['message'] = '';
        $ret['jobId'] = $jobId;
        $ret['mode'] = 0;
        $ret['type'] = 0;
        $ret['scope'] = 0;
        $ret['query'] = '';
        $ret['result'] = array();

        $fields[] = "info";
        for($i = 0; $i < 100; $i++)
        {   
            $fields[] = $i; 
        }   
        $hashCache = new Service_Data_HashCache();
        $retCache = $hashCache->read($jobId, $fields);
        // redis访问失败
        if ($retCache === false || $retCache['err_no'] != 0)
        {
            $ret['errno'] = 1;
            $ret['message'] = "visit cache fail!";
            echo json_encode($ret);
        }
        // 访问的jobid不存在
        else if (empty($retCache['ret']["$jobId"]))
        {
            $ret['errno'] = 2;
            $ret['message'] = "jobid = $jobId doesn't exist!";
            echo json_encode($ret);
        }
        else if (!isset($retCache['ret']["$jobId"]['info']) ||
                 empty($retCache['ret']["$jobId"]['info']))
        {
            $ret['errno'] = 3;
            $ret['message'] = "job info miss!";
            echo json_encode($ret);
        }
        else
        {
            $info = json_decode($retCache['ret']["$jobId"]['info'], true);
            $mode = $info['mode'];
            $scope = $info['scope'];

            $caseNum = 10;
            if ($mode == 0 && $scope == 0)
            {       
                $caseNum = 10;
            }       
            else if ($mode == 0 && $scope == 1)
            {       
                $caseNum = 100;
            }       
            else if ($mode == 1 && $scope == 0)
            {     
                $caseNum = 10;
            } 

            // 某个下标不存在或者为空个数不够返回false
            $miss = false;
            for ($i = 0; $i < $caseNum; ++$i)
            {
                if (!isset($retCache['ret']["$jobId"][$i]) || 
                    empty($retCache['ret']["$jobId"][$i]))
                {
                    $ret['errno'] = 4;
                    $ret['message'] = "cache index = $i miss";
                    $miss = true;
                    break;
                }
                $ret['result'][] = json_decode($retCache['ret']["$jobId"][$i], true);
            }
            echo json_encode($ret);
        }
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
