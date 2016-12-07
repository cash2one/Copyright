<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Curl.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/12/7 13:48
 * @brief
 *
 **/
class Service_Copyright_Curl
{
    /**
     * 基础方法， 用curl发起http post请求
     * @param $url
     * @param $post
     * @param int $timeout
     * @return mixed
     */
    public static function send($url,$post,$timeout = 1)
    {
        $timeout = ($timeout<1)?1:$timeout;

        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_POST,1); //发起post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 	//返回的内容作为变量存储，而不是直接输出
        $ret = curl_exec($curl);
        curl_close($curl);

        if(false === $ret)
        {
            Bd_Log::warning(sprintf('[url]%s,[post]%s,[timeout]%s, return false',$url,json_encode($post),$timeout));
        }
        else
        {
            //作为底层方法，要记录服务端返回的原始数据
            if(is_string($ret))
            {
                $retData = mc_pack_pack2array($ret);
                if(false === $retData)
                {
                    $retData = $ret;
                }
                $serializeRetData = json_encode($retData);
            }
            else
            {
                $serializeRetData = json_encode($ret);
            }
            Bd_Log::notice(sprintf('[url]%s,[post]%s,[return]%s . curl return successfully!',$url,json_encode($post),$serializeRetData));
        }
        return $ret;
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
