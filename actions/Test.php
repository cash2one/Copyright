<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Test.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/8 18:15
 * @brief
 *
 **/
class Action_Test extends Service_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function invoke()
    {
        //登录成功之后要跳转去的url
        $currentUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        //passport的登陆地址
        $loginUrl = Bd_Conf::getAppConf("global/passport/login_url");
        //拼凑目标地址
        $destUrl = $loginUrl.'$u='.$currentUrl;

        $result = array('currentUrl'=>$currentUrl,'loginUrl'=>$loginUrl,'destUrl'=>$destUrl);
        $this->jsonResponse($result);

    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
