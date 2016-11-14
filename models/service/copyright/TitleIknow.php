<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file TitleIknow.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/4 11:18
 * @brief
 *
 **/

class Service_Copyright_TitleIknow extends Service_Copyright_Base {


    /**
     * @param
     * @return
     */    
    function Search($pn, $start, $end, $casePerPage = 10, $ext = array()) {
        $ret['errno'] = 0;
        $ret['result'] = array();
        $rn = 100;
        $query = "ie=utf8"."&word=$this->query"."&pn=$pn"."&rn=$rn";
        ral_set_pathinfo('search/api/e9d3ce7b038101cd0b78fdcdced58queryinner');
        ral_set_querystring($query);

        $arrRet = ral('iknowSearch', 'post', array(), rand());
        if(empty($arrRet)) {   
            $ret['errno'] = -1; 
            return $ret;
        }    
        $arrRet = json_decode($arrRet,true);
        $index = 0;
        $forredis = array();
        foreach ($arrRet['arrAutoask']['search_list'] as $index => $arrInfo) {
            if ($index < $start) {
                continue;
            }
            else if ($index >= $end) {
                $index--;
                break;
            }
            $delta = 0;
            if ($pn != 0) {
                $delta = 10;
            }
            $this->searchResult[$pn * $casePerPage + $index + $delta] = $arrInfo;
            $ret['result']["$index"] = $arrInfo;
        }
    }

    /**
     * @param
     * @return
     */
    function Norm() {
        foreach ($this->searchResult as $id => $arrInfo) {
            $qid = $arrInfo['qid'];
            $url = "http://zhidao.baidu.com/question/$qid.html";
            $arrRet = Service_Data_Da::isResource($type, $arrInfo['title']);
            $arrRet['errno'] =0;
            $arrRet['result']['risk']=1;
            if ($arrRet['errno'] != 0)
            {
                //errno
                $ret['errno'] = -1;
            }
            $title = $arrInfo['title'];
            //用于处理检索不精准的问题
            $retCon = preg_match("/$this->query/", $arrInfo['title'],$arrMat);
            if ($retCon == 0)
            {
            //    continue;
            }
            $this->normResult[$id]['url'] = $url;
            $this->normResult[$id]['title'] = $arrInfo['title'];
            $this->normResult[$id]['risk'] = $arrRet['result']['risk'];

            $arrCnt  = array();
            if ($arrRet['result']['risk']==1)
            {
                $qids[] = array('qid' => $qid);
                $arrUrlToId[$url] = $id;
            }
        }
        $arrAns = Service_Data_QtaService::getAnsList($qids);   
        foreach($arrAns['result'] as $id=>$v)
        {
            unset($arrCnt);
            $qid = $arrAns['result'][$id]['qid'];
            foreach ($arrAns['result'][$id]['normal_replys'] as $k => $list)
            {
                $strCon = iconv('gbk', 'utf-8', $list['content'].$list['content_rich']);
                $arrPirate = Service_Data_Pirate::pirate($strCon);
                if ($arrPirate['result']['label']==1)
                {
                    $arrCnt[$list['deleted']]++;
                }
            }
            foreach ($arrAns['result'][$id]['special_replys'] as $k => $list)
            {
                $strCon = iconv('gbk', 'utf-8', $list['content'].$list['content_rich']);
                $arrPirate = Service_Data_Pirate::pirate($strCon);
                if ($arrPirate['result']['label']==1)
                {
                    $arrCnt[$list['deleted']]++;
                }
            }
            $url = "http://zhidao.baidu.com/question/$qid.html";
            $this->normResult[$arrUrlToId[$url]]['delcnt'] = empty($arrCnt[1]) ? 0:$arrCnt[1];
            $this->normResult[$arrUrlToId[$url]]['onlinecnt'] = empty($arrCnt[0]) ? 0:$arrCnt[0];
        }
        //    BD_LOG::notice('Norm ' . count($this->normResult));
    }

    /**
     * @param
     * @return
     */
    function Detect() {
        $this->detectResult = $this->normResult;
        //BD_LOG::notice('Detect ' . count($this->detectResult));
    }
}
//$obj = new Service_Copyright_TitleIknow('1', '岛上书店', 0, 1);
//$obj->run(0, 0, 5);

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>


