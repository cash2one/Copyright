<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT',    3);
define('HDOM_TYPE_ENDTAG',  4);
define('HDOM_TYPE_ROOT',    5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO',     3);
define('HDOM_INFO_BEGIN',   0);
define('HDOM_INFO_END',     1);
define('HDOM_INFO_QUOTE',   2);
define('HDOM_INFO_SPACE',   3);
define('HDOM_INFO_TEXT',    4);
define('HDOM_INFO_INNER',   5);
define('HDOM_INFO_OUTER',   6);
define('HDOM_INFO_ENDSPACE',7);
define('DEFAULT_TARGET_CHARSET', 'UTF-8');
define('DEFAULT_BR_TEXT', "\r\n");
define('DEFAULT_SPAN_TEXT', " ");
define('MAX_FILE_SIZE', 6000000);
 
/**
 * @file HtmlHelper.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/04 16:45:45
 * @brief 
 *  
 **/

class Service_Copyright_HtmlHelper {

    // get html dom from file
    // $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
    /**
     * @param
     * @return
     */
    public static function file_get_html($url, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT) {

        // We DO force the tags to be terminated.
        $dom = new Service_Copyright_HtmlDom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        //For time out!!
        $opts = array(
            'http' => array(
                'timeout'=>60,
            ),
        );

        $context=stream_context_create($opts);      
        $contents = file_get_contents($url, $use_include_path, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        //$contents = retrieve_url_contents($url);
        if (empty($contents) || strlen($contents) > MAX_FILE_SIZE)
        {
            return false;
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);
        return $dom;
    }

    // get html dom from string
    /**
     * @param
     * @return
     */
    public static function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)
    {
        $dom = new Service_Copyright_HtmlDom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE)
        {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }

    /**
     * @param
     * @return
     */
    public static function dailyPostUrl($url,$data = false,$header = array(),$ispost = false ){
        $useragent_list = array(
            'Opera/9.27 (Windows NT 5.2; U; zh-cn)',
            'Opera/8.0 (Macintosh; PPC Mac OS X; U; en)',
            'Mozilla/5.0 (Macintosh; PPC Mac OS X; U; en) Opera 8.0',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13',
            'Mozilla/5.0 (iPhone; U; CPU like Mac OS X) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080219 Firefox/2.0.0.12 Navigator/9.0.0.6',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1', 
            'Mozilla/5.0 (Windows; U; Windows NT 5.1) Gecko/20070309 Firefox/2.0.0.3',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1) Gecko/20070803 Firefox/1.5.0.12',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Win64; x64; Trident/4.0)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
        );
        $useragent_num = count($useragent_list);
        $select_num = rand(0,$useragent_num);
        $select_ua = $useragent_list[$select_num];
        
        $httpproxy = Orp_FetchUrl::getInstance(array(
            'user_agent' => $select_ua, 
            'timeout' => 1200, 
            'conn_timeout' => 5000, 
            'max_response_size'=> 1024000,
        ));
        $content_ret = $httpproxy->get($url);
        $curl_info = $httpproxy->curl_info();
        return $ret = array(
            'content_ret' => $content_ret,
            'url_ret' => $curl_info['url'],
        );
    }

    /**
     * @param
     * @return
     */
    public static function get_list_from_ps($url,$param = array(),$retry=5){
        $req = $url;
        $timeout = 1;
        if (isset($param['header']) && $param['header'] == 1){
            $has_header = 1;
        }else{  
            $has_header = 0;
        }

        if (isset($param['body']) && $param['body'] == 0){
            $has_body = 0;
        }else{  
            $has_body = 1;
        }

        if (isset($param['may_not_200']) && $param['may_not_200'] == 1){
            $may_not_200 = 1;
        }else{  
            $may_not_200 = 0;
        }

        for ($i = 0 ; $i < $retry ; $i++){

            $httpproxy = Orp_FetchUrl::getInstance(array(
                'user_agent' => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; InfoPath.1; CIBA)",
                'timeout' => 1000, 
                'conn_timeout' => 1000,
                'encoding' => 'gzip',
                'referer' => 'http://www.baidu.com',
                'max_response_size' => 1024000,
            ));
            $res['content'] = $httpproxy->get($url);
            //打log
            if($res['content'] === false || empty($res['content']))
            {
                Bd_Log::warning(sprintf('fetchUrl[httpproxy]%s,[response]%s',json_encode($httpproxy),$res['content']));
            }


            $res['status'] = $httpproxy->http_code();
            if ($res['content'] == false || ($res['status'] != '200' && $may_not_200 == 0)){
                //var_dump("oh no");
                sleep(2);
                continue;
            }else{
                break;
            }
        }

        return $res;
    }    
}

//$ret = Service_Copyright_HtmlHelper::dailyPostUrl("http://wiki.jikexueyuan.com/project/react/why-react.html");
//print_r($ret);

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
