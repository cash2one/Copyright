<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file HashCache.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 10:58
 * @brief
 *
 **/
//数据缓存和拉取
class Service_Copyright_HashCache
{
    const TIMEOUT = 600; //默认缓存十分钟

    private $redis;

    /**
     * @param
     * @return
     */
    public function __construct()
    {
        if (empty($this->redis)) {
            $this->redis = Service_Dao_Redis::getInstance();
        }
    }

    /**
     * @param
     * @return
     */
    public function write($key, $field_value, $timeout = self::TIMEOUT)
    {
        if (!empty($field_value)) {
            $input = array();
            foreach ($field_value as $field => $value) {
                $input['fields'][] = array('field' => $field, 'value' => $value);
            }
            if (!empty($input['fields'])) {
                $input['key'] = $key;
                $ret = $this->redis->HMSET($input);
                if (!empty($ret) && $ret['err_no'] == 0) {
                    //设置过期时间
                    $this->setExpire($key, $timeout);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param
     * @parma fields 是key value形式的
     * @return
     */
    public function read($key, array $fields)
    {
        file_put_contents('/home/iknow/odp/log/zhenyu1.log',var_export(func_get_args(),ture));
        if (!empty($fields)) {
            $input = array();
            foreach ($fields as $index => $field) {
                $input['field'][] = $field;
            }
            if (!empty($input['field'])) {
                $input['key'] = $key;
                $ret = $this->redis->HMGET($input);

                //把数组按照key value的形式会对回去
                $count = count($fields);
                $new_ret = array();
                if($ret['err_no'] == 0 && count($ret['ret'][$key]) == $count)
                {
                    $i = 0;
                    foreach($fields as $index=>$item)
                    {
                        $new_ret[$item] = $ret['ret'][$key][$i];
                        $i++;
                    }
                    $ret['ret'][$key] = $new_ret;
                }
                file_put_contents('/home/iknow/odp/log/zhenyu.log',var_export($ret,true));
                return $ret;
            }
        }

        return false;
    }

    /**
     * @param
     * @return
     */
    private function setExpire($key, $timeout = self::TIMEOUT)
    {
        $input = array('key' => $key, 'seconds' => $timeout);
        $this->redis->expire($input);
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
