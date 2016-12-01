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
class Action_Test extends Ap_Action_Abstract
{
    /**
     * @param
     * @return
     */
    public function execute()
    {
        //测试FetchUrl
        //测试FetchUrl
        //测试外网
        $url = 'https://www.douban.com';
        var_dump(sprintf('----------fetchUrl [url]%s ---------',$url));
        $httpproxy = Orp_FetchUrl::getInstance(array('timeout' =>30000,'conn_timeout' =>10000,'max_response_size'=> 1024000));
        $res = $httpproxy->get($url);
        var_dump($res);

        //测试内网
        $url = 'http://10.40.45.67:8090/rts/test';
        var_dump(sprintf('----------fetchUrl [url]%s ---------',$url));
        $httpproxy = Orp_FetchUrl::getInstance(array('timeout' =>30000,'conn_timeout' =>10000,'max_response_size'=> 1024000));
        $res = $httpproxy->get($url);
        var_dump($res);

        //测试内网
        $url = 'http://10.65.211.21/s?wd=hello';
        var_dump(sprintf('----------fetchUrl [url]%s ---------',$url));
        $httpproxy = Orp_FetchUrl::getInstance(array('timeout' =>30000,'conn_timeout' =>10000,'max_response_size'=> 1024000));
        $res = $httpproxy->get($url);
        var_dump($res);


        /*
        //登录成功之后要跳转去的url
        $currentUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        //passport的登陆地址
        $loginUrl = Bd_Conf::getAppConf("passport/login_url");
        //拼凑目标地址
        $destUrl = $loginUrl.'$u='.$currentUrl;

        $result = array('currentUrl'=>$currentUrl,'loginUrl'=>$loginUrl,'destUrl'=>$destUrl);
        */
        /*
         * 测试userinfo
        $userInfo = Bd_Passport::checkUserLogin();
        var_dump($userInfo);
        */

    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
