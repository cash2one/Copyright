<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Base.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:24
 * @brief
 *
 **/
abstract class Service_Copyright_Base extends Service_Copyright_Abstract
{

    /**
     * @param
     * @return
     */    
    function run($pn, $start, $end, $casePerPage = 10)
    {
        $this->Search($pn, $start, $end, $casePerPage);
        $this->Norm();
        $this->Detect();
        $this->writeRedis();
    }

    /**
     * @param
     * @return
     */
    function simpleRun($pn, $start, $end, $casePerPage = 10)
    {
        $this->Search($pn, $start, $end, $casePerPage);
        $this->Norm();
        $this->Detect();
        return $this->detectResult;
    }

    /**
     * @param
     * @return
     */
    function writeRedis() {
        $forredis = array();
        foreach ($this->detectResult as $key => $value) {
            BD_LOG::notice("forredis $key, " . json_encode($value));
            $forredis[$key] = json_encode($value);
        }
        Bd_Log::notice('[to redis field_value]'.json_encode($forredis));
        $obj = new Service_Copyright_HashCache();
        $ret = $obj->write($this->jobId, $forredis);
        BD_LOG::notice("write redis ret: $ret");
    }
}



/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
