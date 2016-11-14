<?php
/***************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Component_Base_QtaService.php
 * @author qinpiqi(qinpiqi@baidu.com)
 * @date 2014/11/27 14:37:32
 * @brief
 *
 **/
class Service_Data_QtaService
{
    /**
     * @param $qid
     * @return array
     */
    public static function getOriginalAsk($qid)
    {
        $ret = array(
            "errno" => 0,
            "result" => array(),
        );
        if ($qid < 0) {
            $ret['errno'] = -1;
            return $ret;
        }
        $qtaObj = new Service_Data_Qta();
        $req[] = array("qid" => intval($qid));
        $qttr = array(
            'title',
            'content',
            'content_len',
            'uid',
            'uname',
            'uip',
            'cid',
            'score',
            'status',
            'anonymous',
            'wap_flag',
            'deleted',
            'create_time',
            'bit_pack',
        );

        $retry = 0;
        while (empty($qInfo) && $retry < 2) //repeat 1 times
        {
            $qInfo = $qtaObj->getQBAttr($req, $qttr);
            $retry++;
        }
        if ($qInfo === false) {
            $ret['errno'] = -1;
        } else if (!empty($qInfo[0])) {
            $qInfo[0]['title'] = iconv('gbk', 'utf-8', $qInfo[0]['title']);
            $qInfo[0]['content'] = iconv('gbk', 'utf-8', $qInfo[0]['content']);
            $qInfo[0]['uname'] = iconv('gbk', 'utf-8', $qInfo[0]['uname']);
            $ret['result'] = $qInfo[0];
        }

        return $ret;
    }

    /**
     * @param $arrQid
     * @return array
     */
    public static function getAnsList($arrQid)
    {
        $ret = array(
            "errno" => 0,
            "result" => array(),
        );
        if (empty($arrQid)) {
            $ret['errno'] = -1;
            return $ret;
        }
        $qtaObj = new Service_Data_Qta();
        //$req[] = $arrQid;
        $req = $arrQid;
        $qttr = array(
            'qid',
            'title',
            'content',
            'deleted',
        );
        $rttr = array(
            'rid',
            'uid',
            'deleted',
            'content',
            'content_rich',
            'bit_pack',
        );

        $retry = 0;
        while (empty($qInfo) && $retry < 2) //repeat 1 times
        {
            $qInfo = $qtaObj->getQBAttr($req, $qttr, $rttr);
            $retry++;
        }
        if ($qInfo === false) {
            $ret['errno'] = -1;
        } else if (!empty($qInfo[0])) {
            foreach ($qInfo as $id => $info) {
                $qInfo[$id]['title'] = iconv('gbk', 'utf-8', $qInfo[$id]['title']);
                $qInfo[$id]['content'] = iconv('gbk', 'utf-8', $qInfo[$id]['content']);
                $qInfo[$id]['uname'] = iconv('gbk', 'utf-8', $qInfo[$id]['uname']);
            }
            $ret['result'] = $qInfo;
        }
        return $ret;
    }
}

?>
