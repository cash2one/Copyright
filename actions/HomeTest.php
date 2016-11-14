<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file HomeTest.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/8 18:15
 * @brief
 *
 **/
class Action_HomeTest extends Service_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function invoke()
    {
        //var_dump(Bd_Passport::checkUserLogin());
        //var_dump(Saf_SmartMain::getUserInfo());
        $userInfo = Bd_Passport::checkUserLogin();
        $userInfo['isLogin'] = 0;
        if(!empty($userInfo) && isset($userInfo['uid']) && isset($userInfo['uname']))
        {
            $userInfo['isLogin'] = 1;
        }

        $tpl = $this->smartyInstance();
        $tpl->assign('userInfo',$userInfo);
        $tpl->display('copyright/page/index.tpl');

        //$tpl->display('copyright/HomeTest.tpl');
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
