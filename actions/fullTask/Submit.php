<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Submit.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/9 15:04
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
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $fileId = $httpPost['fileId'];
        $fullTime = isset($httpPost['fullTime'])?$httpPost['fullTime']:0;
        $startTime = isset($httpPost['startTime'])?$httpPost['startTime']:0;
        $endTime = isset($httpPost['endTime'])?$httpPost['endTime']:0;
        $uid = "xxx";

        // sumbit a new job here
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
