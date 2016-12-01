<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file ContentPs.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/4 11:18
 * @brief
 *
 **/

class Service_Copyright_ContentPs extends Service_Copyright_Base
{

    /**
     * @param
     * @return
     */
    function GetContentFromLink($link) {
        $content_tmp = Service_Copyright_HtmlHelper::dailyPostUrl($link);
        $content = $content_tmp['content_ret'];
        $list_charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i",$content,$temp)? strtolower($temp[1]):"utf-8";
        $get_txt_retry = 5;
        for($i = 0 ; $i < $get_txt_retry ; $i++){    
            $Readability = new Service_Copyright_Readability($content, $list_charset);
            $content = $Readability->getContent();
            if($content['content'] == false) {
                continue;
            }
            else{
                $content = str_replace(array("\r\n", "\r", "\n"), '', strip_tags($content['content'])); 
                break;
            }
        }
        return $content;
    }

    /**
     * @param
     * @return
     */
    function Search($pn, $start, $end, $casePerPage = 10, $ext = array()) {
        $base_url = Bd_Conf::getAppConf("search/base_url");
        //$base_url = 'http://10.65.211.21:80/s?wd=';
        $query_url = $base_url . urlencode($this->query);
        $hasUrl =  preg_match('/http[s]?\:\/\/[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+|www\.[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+/i',$this->contentOrLink);
        if ($hasUrl) {
            $this->contentOrLink = $this->GetContentFromLink($this->contentOrLink);
        }
        $url = $query_url;
        if ($pn > 0){
            $url .= '&pn=' . ($pn * 10);
        }
        $response = Service_Copyright_HtmlHelper::get_list_from_ps($url);
        $content = $response['content'];
        $html = Service_Copyright_HtmlHelper::str_get_html($content); 
        $results=$html->find("#content_left [id*=^\d+$]");

        foreach ($results as $k => $result) {
            //$k --;
            if(empty($result)){
                continue;
            }
            if ($k >= $end || $k < $start) {
                continue;
            }
            $this->searchResult[$pn * $casePerPage + $k] = $result;
        }
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
            $a_txt_arr = array();
            $list_title = '';
            
            $abstract = $result->find('.c-abstract', 0);
            if (empty($abstract)) {
                $abstract = null;
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
                $domain = explode("/", $domain->innertext);
                $domain = $domain[0];
            }

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
            
            $lcs = new Service_Copyright_LCS();
            if(!empty($a_txt_arr)) {
                $ps_title_simnum = array();
                foreach($a_txt_arr as $k => $v){
                    $v = strip_tags($v);
                    $ps_title_sim = $lcs->getSimilar($this->query, $v);
                    $ps_title_sim = sprintf("%.2f", $ps_title_sim) * 100;
                    $ps_title_simnum[$k] = intval($ps_title_sim);
                }
                arsort($ps_title_simnum);
                $hit_key = key($ps_title_simnum);
                $list_title = strip_tags($a_txt_arr[$hit_key]);
            }

            $sim_title = $lcs->getSimilar($this->query, $list_title);
            $sim_title = sprintf("%.2f", $sim_title)*100; 
            $sim_content = -1;
            $list_html_txt = $this->GetContentFromLink($href);//$list_html['content_ret'];
            //$list_html_url = Service_Copyright_HtmlHelper::dailyPostUrl($href);
            //$list_html_url = $list_html_url['url_ret'];
            $sim_content_ret = similar_text($this->contentOrLink, $list_html_txt, $sim_content);
            $sim_content = sprintf("%.2f", $sim_content);
            //$parts_html_url = parse_url($list_html_url);
            //$domain = $parts_html_url['host'];
            $ret_arr["daily_title"] = $this->query;
            $ret_arr["daily_txt"] = $this->contentOrLink;
            $ret_arr["title"] = strip_tags($a_txt);
            $ret_arr["url"] = $list_html_url;
            $ret_arr["domain"] = $domain;
            $ret_arr["sim_title"] = $sim_title;
            $ret_arr["sim_content"] = $sim_content;
            $ret_arr["sim_len"] = $sim_content_ret;//(int)((double)$sim_content / 100 * mb_strlen($daily_txt, "utf-8"));
            if ($sim_content == -1) {
                $ret_arr["sim_len"] = 0;
            }
            if (strlen($list_html_txt) != 0) {
                $ret_arr["sim_other_content"] = (double)$ret_arr["sim_len"] / strlen($list_html_txt);
            }
            else {
                $ret_arr["sim_other_content"] = 0;
            }
            $this->normResult[$index] = $ret_arr;            

        }
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
            $txt = $cur['title'];
            $domain = $cur["domain"];
            $sim_title = $cur["sim_title"];
            $sim_content = $cur["sim_content"];
            $sim_len = $cur["sim_len"];
            $sim_other_content = $cur["sim_other_content"] * 100;
            $sim_other_content = sprintf("%.2f", $sim_other_content);
            if ($sim_content > 60 || ($sim_other_content > 80 && $sim_len > 500)) { $hign_cnt ++; $level = 2; }
            else {
                if ($sim_title > 80 && $sim_content == -1) { $mid_cnt ++; $level = 1; }
                else { $level = 0; }
            }
            $this->detectResult[$index] = $cur;
            $this->detectResult[$index]['risk'] = $level;
            unset($this->detectResult[$index]['daily_txt']);
        }  
        BD_Log::notice("Detect ".count($this->detectResult)); 
    }

}

//$obj = new Service_Copyright_ContentPs('1', '人为什么要睡觉', 1, 0, 'https://zhidao.baidu.com/daily/view?id=10299');
//$obj->computeFastTaskStatistic(0, 0, 5);

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
