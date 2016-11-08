<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Abstract.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:16
 * @brief
 *
 **/
abstract class Service_Copyright_Abstract
{
    protected $searchResult;
    protected $normResult;
    protected $detectResult;

    /**
     * @param
     * @return
     */
    abstract function Search($pn,$start,$end,$ext = array());

    /**
     * @param
     * @return
     */
    abstract function Norm();

    /**
     * @param
     * @return
     */
    abstract function Detect();

    /**
     * @param
     * @return
     */
    function writeCache($key,array $field_value)
    {
        $sch = new Service_Copyright_HashCache();
        $ret = $sch->write($key,$field_value);
        return $ret;
    }

    /**
     * @param
     * @return
     */
    function readCache($key,array $fields)
    {
        $sch = new Service_Copyright_HashCache();
        $ret = $sch->read($key,$fields);
        return $ret;
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
