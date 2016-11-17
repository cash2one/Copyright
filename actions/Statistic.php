<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Statistic.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 10:51
 * @brief
 *
 **/
class Action_Statistic extends Service_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function invoke()
    {
        $httpGet = $_GET;
        $jobid = $httpGet['jobid'];
        $channel = isset($httpGet['channel'])?intval($httpGet['channel']):0;

        $scs = new Service_Copyright_Statistic();
        $tpl = $this->smartyInstance();
        if($channel == 0)
        {
            //拉取快速的分析结果
            $ret = $scs->fastTaskAnalysis($jobid);
            $statisticResult = $ret['jobStatistic'];
            //赋值tpl里面的信息
            $tpl->assign('query', $ret['query']);
            $tpl->assign('mode', $ret['mode']);
            $tpl->assign('type', $ret['type']);
            $tpl->assign('result', $statisticResult);

            //快速检索
            $tpl->display('copyright/page/full-result-analyze.tpl');
        }
        else
        {
            //拉取全量的分析结果
            $ret = $scs->fullTaskAnalysis($jobid);
            $statisticResult = $ret['jobStatistic'];
            //赋值tpl里面的信息
            $tpl->assign('sourceFile', $ret['sourceFile']);
            $tpl->assign('mode', $ret['mode']);
            $tpl->assign('type', $ret['type']);
            $tpl->assign('result', $statisticResult);
            //全量检索
            $tpl->display('copyright/page/quick-result-analyze.tpl');
        }
        //zhenyu 调试
        $this->jsonResponse($statisticResult);

    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
