<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file TitlePs.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/4 11:18
 * @brief
 *
 **/

class Service_Copyright_TitlePs extends Service_Copyright_Base
{
    /**
     * @param 
     * @return 
     */
    public static function get_score($type, $title) {

        $input['pid'] = $type;
        $input['line'] = $title;
        $postdata = json_encode($input);
        $header = array( "Content-Type: application/json");
        $httpproxy = Orp_FetchUrl::getInstance(array(
            'timeout' => 10000,
            'conn_timeout' => 2000,
            'max_response_size'=> 1024000,
        ));
        $url = Bd_Conf::getAppConf("search/svm_infer_url"); 
        $res = $httpproxy->post($url, $postdata, $header);
        $var = json_decode($res, true);
        return $var['score'];
    }

    /**
     * @param
     * @return
     */
    public static function get_tags($type, $title) {
        $input['pid'] =  'qtag';
        if (($type == 'film') && ($key = Service_Copyright_TitlePs::contains_not_video($title)) != '') {
           return "非视频_$key";
        }
        
        $input['doc'] = $title;
        $doc_len = mb_strlen($input['doc'],'utf8');
        $postdata = json_encode($input);
        $header = array(
            "Content-Type: application/json",
        );
        $httpproxy = Orp_FetchUrl::getInstance(array(
            'timeout' => 10000,
            'conn_timeout' =>5000,
            'max_response_size' => 1024000,
        ));
        $url = Bd_Conf::getAppConf("search/dnn_infer_url"); 
        $res = $httpproxy->post($url, $postdata, $header);
        $var = json_decode($res, true);
        $tags = implode("_",$var['label']);
        return $tags;
    }

    /**
     * @param
     * @return
     */
    public static function contains_not_video($title) {
        $arr_not_video = array("小说", "txt", "TXT", "全文", "全本", "插曲", "歌曲", "铃声", "音乐", "配乐", "mp3","MP3");
        foreach($arr_not_video as $key) {
            if (mb_strpos($title, $key, 0, 'UTF-8') !== false) {
                return $key;
            }
        }
        return '';
    }

    /**
     * @param
     * @return
     */
    function Search($pn, $start, $end, $casePerPage = 10, $ext = array()) {
        $base_url = Bd_Conf::getAppConf("search/base_url");
        $query_url = $base_url . urlencode($this->query);
        $url = $query_url;
        if ($pn > 0){
            $url .= '&pn=' . ($pn * 10);
        }
        $response = Service_Copyright_HtmlHelper::get_list_from_ps($url);
        $content = $response['content'];
        $html = Service_Copyright_HtmlHelper::str_get_html($content); 
        $results=$html->find("#content_left [id*=^\d+$]");
    
        foreach ($results as $k => $result) {
//            $k --;
            if(empty($result)){
                continue;
            }
            if ($k >= $end || $k < $start) 
            {
                continue;
            }
            $this->searchResult[$pn * $casePerPage + $k] = $result;
        }
        BD_Log::notice("[search] ".count($this->searchResult));
    }

    /**
     * @param
     * @return
     */
    function Norm() {
        foreach ($this->searchResult as $index => $result) {
            $t = $result->find('.t', 0);
            if(empty($t)){
                continue;
            }
            $a = $t->find('a', 0);
            $href = $a->href;
            $a_txt = $a->innertext;

            $abstract = $result->find('.c-abstract', 0);
            if (empty($abstract)) {
                $abstract = (string)$result->find('div', 0);
            }
            else {
                $abstract = strip_tags($abstract->innertext);
            }

            $domain = $result->find('.f13', 0);
            if (empty($domain)) {
                $domain = null;
            }
            else {
                $domain = $domain->find('a', 0);
                if (!empty($domain)) {
                    $domain = explode("/", strip_tags($domain->innertext));
                    $domain = $domain[0];
                }
            }

            $a_txt_arr = array();
            $list_title = '';
            if(strstr($a_txt,"_")){
                $a_txt_arr = explode("_",$a_txt);
                //$list_title = strip_tags($a_txt_arr[0]);
            }elseif(strstr($a_txt,"-")){
                $a_txt_arr = explode("-",$a_txt);
                //$list_title = strip_tags($a_txt_arr[0]);
            }elseif(strstr($a_txt,"|")){
                $a_txt_arr = explode("|",$a_txt);
                //$list_title = strip_tags($a_txt_arr[0]);
            }else{
                $list_title = strip_tags($a_txt);
            }
            
            $list_title = strip_tags($a_txt);
            if ($this->type == 1) {
                $ret_arr['score'] = Service_Copyright_TitlePs::get_score("film", $list_title);
                $ret_arr['tags'] = Service_Copyright_TitlePs::get_tags("film", $list_title);
            }
            else {
                $ret_arr['score'] = Service_Copyright_TitlePs::get_score("fiction", $list_title);
                $ret_arr['tags'] = Service_Copyright_TitlePs::get_tags("fiction", $list_title);
            }
            $ret_arr["title"] = $list_title;
            $ret_arr["url"] = $href;
            $ret_arr["domain"] = $domain;
            $ret_arr["abstract"] = $abstract;
            $this->normResult[$index] = $ret_arr;            
        }
        BD_Log::notice("Norm ".count($this->normResult));
    }

    /**
     * @param
     * @return
     */
    function Detect() {
        foreach ($this->normResult as $index => $cur) {
            if (empty($cur)) {
                continue;
            }
            $url = $cur["url"];
            $txt = $cur["title"];
            $domain = $cur["domain"];
            $score = $cur["score"];
            $tags = $cur["tags"];
            $copyright = 0;
            if ($this->type == 1) {
                if ($score > 0.55 || preg_match("/非视频/", $tags) != 0) {
                    $copyright = 0;
                }
                else if (preg_match("/电影|影视|视频|电视剧/", $tags) != 0
                    || preg_match("/电影|影视|视频|电视剧|剧集/", $txt) != 0) {
                    $copyright = 1;
                }
                else if (preg_match("/游戏|动漫|音乐|文学|小说|非视频/", $tags) == 0) {
                    $copyright = 1;
                }
                else if ($score < 0.000000664) {
                    $copyright = 1;
                }
                else {
                    $copyright = 0;
                }
            }
            else if ($this->type == 0){
                if ($score < 0.587638
                    && preg_match("/音乐|动漫|影视|漫画|游戏|视频|电影|电视剧|暗黑破坏神|穿越火线|mp3/i", $tags) == 0) {
                        $copyright = 1;
                    }
            }
            $this->detectResult[$index] = $cur;
            if ($copyright == 1) { $copyright = 2; }
            $this->detectResult[$index]['risk'] = $copyright;
            if ($this->hitWhiteList($domain)) {
                $this->detectResult[$index]['risk'] = 0;
            }
        }   
        BD_Log::notice("Detect ".json_encode($this->detectResult));
    }

    /**
     * @param 
     * @return 
     */ 
    public function hitWhiteList($domain) {
        if (!$domain) {
            return false;
        }
        $lists = Bd_Conf::getAppConf("whitelist/titleps");
        $tokens = explode(';', $lists);
        foreach ($tokens as $whitename) {
            if (strpos($whitename, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

}

//$obj = new Service_Copyright_TitlePs('English', 0, 0);
//$obj->computeFastTaskStatistic(0, 0, 5);

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
