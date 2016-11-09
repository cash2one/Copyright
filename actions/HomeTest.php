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
        var_dump(Bd_Passport::checkUserLogin());
        var_dump(Saf_SmartMain::getUserInfo());
        $userInfo = Bd_Passport::checkUserLogin();
        $uid = 0;
        $uname = '';
        if(!empty($userInfo) && isset($userInfo['uid']) && isset($userInfo['uname']))
        {
            $uid = $userInfo['uid'];
            $uname = $userInfo['uname'];
        }
        $tpl = Bd_TplFactory::getInstance();
        $tpl->assign('uid',$uid);
        $tpl->assign('uname',$uname);
        $tpl->display('copyright/HomeTest.tpl');

    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
