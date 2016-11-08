<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Redis.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:44
 * @brief
 *
 **/
class Service_Dao_Redis
{
    const AK_REDIS_CLASS = 'Ak_Service_Redis';

    public static $redisConfig = array(
        'pid' => "iknow_spam",
        'tk' => "iknow",
        'uname' => "iknow",
    );

    //redis实例
    /**
     * @param
     * @return
     */
    public static function getInstance()
    {
        return Bd_RalRpc::create(self::AK_REDIS_CLASS,self::$redisConfig);
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
