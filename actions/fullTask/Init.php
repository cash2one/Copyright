<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Init.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/15 10:01
 * @brief
 *
 **/
class Action_Init extends Service_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function invoke()
    {
        $tpl = $this->smartyInstance();
        $tpl->display('copyright/page/full-search.tpl');
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
