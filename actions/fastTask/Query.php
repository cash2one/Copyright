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
        $jobId = $httpGet['jobid'];
        $ret['errno'] = 0;
        $ret['message'] = '';
        $ret['jobid'] = $httpGet['jobid'];
        $ret['mode'] = $httpGet['mode'];
        $ret['type'] = $httpGet['type'];
        $ret['scope'] = $httpGet['scope'];
        $jobId = $httpGet['jobid'];
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
        // redisè®¿é—®å¤±è´¥
        if ($retCache === false || $retCache['err_no'] != 0)
        {
            $ret['errno'] = 1;
            $ret['message'] = "visit cache fail!";
            echo json_encode($ret);
        }
        // è®¿é—®çš„jobidä¸å­˜åœ¨
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
<<<<<<< HEAD
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

            // æŸä¸ªä¸‹æ ‡ä¸å­˜åœ¨æˆ–è€…ä¸ºç©ºä¸ªæ•°ä¸å¤Ÿè¿”å›false
=======
            // Ä³¸öÏÂ±ê²»´æÔÚ»òÕßÎª¿Õ¸öÊı²»¹»·µ»Øfalse
>>>>>>> 3124ac44b20565f3d2449e1df5b8eff9ee47bbbc
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
