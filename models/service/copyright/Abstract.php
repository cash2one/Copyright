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
    /*
     *
     */
    abstract function Search($pn,$start,$end,$ext = array());
    abstract function Norm();
    abstract function Detect();

    function writeCache($key,array $field_value)
    {

    }

    function readCache($key,$fields = array())
    {

    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
