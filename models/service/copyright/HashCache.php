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

    const VOLUME_SIZE = 10; //分片
    const PAGE_SIZE = 10; //分页

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
                else if($ret['err_no'] != 0 && $ret['err_msg'] == "error in some keys")
                {
                    Bd_Log::warning(sprintf('[redis ret]%s',json_encode($ret)));
                    return $this->readRetry($key,$fields);
                }
                return $ret;
            }
        }

        return false;
    }

    /**
     * @param
     * @parma fields 是key value形式的
     * @return
     */
    public function readRetry($key, array $fields)
    {
        $result = array();
        if (!empty($fields)) {
            $volume_size = self::VOLUME_SIZE;
            $volume_start = 0;
            for($i=0;$i<ceil(count($fields)/$volume_size);$i++)
            {
                //分片
                $volume_fields = array_slice($fields,$volume_start,$volume_size);

                $reqs = array();
                //20161130改成批量的方式
                $page_size = self::PAGE_SIZE;
                $page_start = 0;
                for($j=0;$j<ceil(count($volume_fields)/self::PAGE_SIZE);$j++)
                {
                    //分页，切割数组
                    $temp = array_slice($volume_fields,$page_start,$page_size);

                    $sub_input = array('key'=>$key);
                    foreach($temp as $index=>$field)
                    {
                        $sub_input['field'][] = $field;
                    }
                    $reqs[] = $sub_input;

                    $page_start += $page_size;
                }

                $input = array('reqs'=>$reqs);
                Bd_Log::notice(sprintf('[input]%s',json_encode($input)));
                $ret = $this->redis->HMGET($input);
                if($ret['err_no'] == 0 && count($ret['ret'][$key]) == count($volume_fields))
                {
                    $result['err_no'] = $ret['err_no'];
                    $result['err_msg'] = $ret['err_msg'];
                    $tally = 0;
                    foreach($volume_fields as $index=>$item)
                    {
                        $result['ret'][$key][$item] = $ret['ret'][$key][$tally];
                        $tally++;
                    }
                }
                else
                {
                    Bd_Log::warning(sprintf('[ret]%s',json_encode($ret)));
                    return $ret;
                }

                $volume_start += $volume_size;
            }
            return $result;
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
