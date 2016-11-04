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
class Service_Copyright_Base extends Service_Copyright_Abstract
{

    function run()
    {
        $this->search();
        $this->norm();
        $this->detect();
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
