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
    protected $jobId;
    protected $query;
    protected $contentOrLink;
    protected $type;
    protected $scope;
    
    protected $searchResult;
    protected $normResult;
    protected $detectResult;

    /**
     * @param jobId, query, type, scope, contentOrLink
     * @return _
     */ 
    function __construct($_jobId, $_query, $_type, $_scope, $_contentOrLink = null) {
        $this->jobId = $_jobId;
        $this->query = $_query;
        $this->type = $_type;
        $this->scope = $_scope;
        $this->contentOrLink = $_contentOrLink;
    }
    /**
     * @param
     * @return
     */
    abstract function Search($pn, $start, $end, $casePerPage = 10, $ext = array());

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

    }

    /**
     * @param
     * @return
     */
    function readCache($key,$fields = array())
    {

    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
