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
class Action_Submit extends Ap_Action_Abstract
{
     /*
     *
     * mode�ֵ�����: 0=������, 1=������
     * type�ֵ�����: 0=С˵/������, 1=Ӱ�Ӿ磬2=С˵���ݣ�3=��������
     * scope�ֵ�����: 0=�ٶ��������, 1=�ٶ�֪��վ����Դ��2=�ٶ�����
     * query��ʾ��������
     * text��ʾ�ı�����(����mode����Ϊ������ʱʹ��)
     *
     * */

    public function execute()
    {
        $httpGet = $_GET;
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
            $caseNum = 10;
            $casePerParallelProcess = 1;
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

        // ��黺�����Ƿ����
        $cacheData = array();
        if ($this->inCache($jobId, $caseNum))
        {
            echo json_encode($ret);
        }
        // �����в����� �ύ������
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
                // �����첽���� ���ɷ�������
                $this->callStatistic(
                    $jobId,
                    $mode, 
                    $type, 
                    $scope, 
                    $query, 
                    $chapter, 
                    $text, 
                    $parallelRet['result']);

                echo json_encode($ret);
            }
            else
            {
                $ret['errno'] = 1;
                $ret['message'] = "parallel failed!";
                echo json_encode($ret);
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
    * @desc : ִ���ύ����ǰ�ȼ�鱾���ύ�Ƿ���ڻ����У��������ֱ�ӷ���
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
        // redis����ʧ��
        if ($retCache === false || $retCache['err_no'] != 0)
        {
            return false;
        }
        // ���ʵ�jobid������
        else if (empty($retCache['ret']["$jobId"]))
        {
            return false;
        }
        else
        {
            // ĳ���±겻���ڻ���Ϊ�ո�����������false
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
    * @desc : �������ݷ����ӿڣ���������첽��ʽ����������ٶ�
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
