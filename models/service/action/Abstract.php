<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Abstract.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/9 16:28
 * @brief
 *
 **/
abstract class Service_Action_Abstract extends Ap_Action_Abstract
{
    protected $smartySelfDefine = true;

    /**
     * @param void
     * @return mixed
     */
    public function execute()
    {
        return $this->invoke();
    }

    /**
     * 抽象方法声明
     * @param
     * @return mixed
     */
    abstract public function invoke();

    /**
     * 因为前端要求smarty对象的配置地址和插件地址可以进行自定义。 所以这个方法很重要
     * @param
     * @return mixed
     */
    public function smartyInstance()
    {
        $tpl = Bd_TplFactory::getInstance();
        $tpl->setConfigDir(ROOT_PATH . '/template/config');
        $tpl->setPluginsDir(ROOT_PATH . '/template/plugins');

        return $tpl;
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
