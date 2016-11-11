<?php

class Service_Data_Da
{
    /**
     * @param $type
     * @param $line
     * @return mixed
     */
    public static function isResource($type, $line)
    {
        $ret['errno'] = 0;
        switch ($type) {
            case 'film':
                $ret = Service_Data_Da::isFilm($line);
                break;
            case 'book':
            case 'fiction':
                $ret = Service_Data_Da::isFiction($line);
                break;
            default:
                $ret['errno'] = -1;
                break;
        }
        return $ret;
    }

    /**
     * @param $line
     * @return mixed
     */
    public static function isFiction($line)
    {
        $ret['errno'] = 0;
        $ret['result']['label'] = 0;

        if (preg_match("/txt|全文|全本|pdf|epub|mobi/i", $line, $arrMat) > 0) {
            $ret['result']['label'] = 1;
            return $ret;
        }

        $arrSvm = Service_Data_Da::getDnnRet('fiction', $line);
        $ret['result']['score'] = $arrSvm['result']['score'];

        if ($ret['result']['score'] < 0.05) {
            $ret['result']['label'] = 1;
        }
        return $ret;

    }

    /**
     * @param $line
     * @return mixed
     */
    public static function isFilm($line)
    {
        $ret['errno'] = 0;
        $ret['result']['label'] = 0;

        if (preg_match("/小说|txt|全文|全本|插曲|歌曲|铃声|音乐|配乐|mp3/i", $line, $arrMat) > 0) {
            return $ret;
        }

        $arrTag = Service_Data_Da::getTag($line);
        $ret['result']['tags'] = $arrTag['result']['tags'];
        //$NF ~ /电影|影视|动漫|视频|电视剧|资源共享/ && $NF !~ /游戏|音乐|文学|小说|非视频/
        if (preg_match("/游戏|音乐|文学|小说/i", $arrTag['result']['tags'], $arrMat) > 0) {
            return $ret;
        }
        $arrSvm = Service_Data_Da::getSvmRet('film', $line);
        $ret['result']['score'] = $arrSvm['result']['score'];

        if ($ret['result']['score'] < 0.01) {
            $ret['result']['label'] = 1;
        }
        return $ret;
    }

    /**
     * @param $type
     * @param $line
     * @return mixed
     */
    public static function getDnnRet($type, $line)
    {
        $ret['errno'] = 0;

        $input['pid'] = $type;
        $input['line'] = $line;
        $postdata = json_encode(array($input));
        Bd_log::notice("score[$postdata]");
        ral_set_pathinfo("DnnService/DnnInf");
        $arrRet = ral('Dnn', 'post', $postdata, rand());
        if (empty($arrRet)) {
            //log
            $ret['errno'] = -1;
            return $ret;
        }
        $ret['result']['score'] = $arrRet['single_response'][0]['score'];
        Bd_log::notice("score[{$ret['result']['score']}]");
        return $ret;
    }

    /**
     * @param $type
     * @param $line
     * @return mixed
     */
    public static function getSvmRet($type, $line)
    {
        $ret['errno'] = 0;

        $input['pid'] = $type;
        $input['line'] = $line;
        $postdata = json_encode($input);
        ral_set_pathinfo("SVMService/svm_infer");
        /*$ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, 'http://10.100.18.62:2010/SVMService/svm_infer');
        $header = array(
            "Content-Type: application/json",
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $curlresult = curl_exec($ch);
        $arrRet = json_decode($curlresult, true); */
        $arrRet = ral('Svm', 'post', $postdata, rand());
        if (empty($arrRet)) {
            //log
            $ret['errno'] = -1;
            return $ret;
        }
        $ret['result']['score'] = $arrRet['score'];
        return $ret;
    }

    /**
     * @param $line
     * @return mixed
     */
    public static function getTag($line)
    {
        $ret['errno'] = 0;

        $input['pid'] = 'qtag';
        $input['doc'] = $line;
        $postdata = json_encode($input);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://10.100.18.62:2011/DnnService/DnnInf');
        $header = array(
            "Content-Type: application/json",
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $curlresult = curl_exec($ch);
        $arrRet = json_decode($curlresult, true);
        if (empty($arrRet) || $arrRet['err_no'] != 0) {
            //log
            $ret['errno'] = -1;
            return $ret;
        }
        $ret['result']['tags'] = implode("_", $arrRet['label']);
        return $ret;
    }

}
