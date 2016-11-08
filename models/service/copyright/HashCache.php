<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file HashCache.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/2 14:27
 * @brief
 *
 **/
class Service_Copyright_HashCache
{
    const TIMEOUT = 100; //默认是缓存100s

    const AK_REDIS_CLASS = 'Ak_Service_Redis';

    //暂时用spamftp的线下的配置
    public static $redisConfig=array(
        'pid'=>"iknow",
        'tk'=>"iknow",
        'app' => "antispam",
        'instance' => "spamftp",
    );

    private $redis;

    /**
    * @param :
    * @return :
    * */
    public function __construct()
    {
        if(empty($this->redis))
        {
            $this->redis = self::getInstance();
        }
    }

    /**
    * @param :
    * @return :
    * */
    private static function getInstance()
    {
        return Bd_RalRpc::create(self::AK_REDIS_CLASS,self::$redisConfig);
    }

    /**
     * 写缓存的入口
     * @param : key redis的key
     * @field_value 因为是hash结构的，所以要纯如field value的数据
     * @timeout 过期时间
     * @return :
     */
    public function write($key,array $field_value,$timeout = self::TIMEOUT)
    {
        if(!empty($field_value))
        {
            //Bd_Log::notice(json_encode($field_value));
            $input = array();
            foreach($field_value as $field => $value)
            {
                $input['fields'][] = array('field'=>$field,'value'=>$value);
            }
            if(!empty($input['fields']))
            {
                $input['key'] = $key;
                $ret = $this->redis->HMSET($input);
                //Bd_Log::fatal($key);
                return $ret;
                if(!empty($ret) && $ret['err_no'] == 0)
                {
                    //设置过期时间
                    $this->setExpire($key,$timeout);
                    return true;
                }
            }
        }

        return false;

    }

    /**
    * @param :
    * @return :
    * */
    public function read($key,array $fields)
    {
        if(!empty($fields))
        {
            $input = array();
            foreach($fields as  $index=>$field)
            {
                $input['field'][] =$field;
            }
            if(!empty($input['field']))
            {
                $input['key'] = $key;
                $ret = $this->redis->HMGET($input);
                return $ret;
            }
        }

        return false;
    }

    /**
    * @param :
    * @return :
    * */
    private function setExpire($key,$timeout = self::TIMEOUT)
    {
        $input = array('key'=>$key,'seconds'=>$timeout);
        $this->redis->expire($input);
    }

    /**
    * @param :
    * @return :
    * */
    public function write_single($key,$field,$value,$timeout = self::TIMEOUT)
    {
        $input = array('key'=>$key,'field'=>$field,'value'=>$value);
        $ret = $this->redis->hset($input);
        if(is_array($ret) && $ret['err_no'] == 0)
        {
            $this->setExpire($key,$timeout);
            return true;
        }
        else
        {
            return false;
        }

    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
