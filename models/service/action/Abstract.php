<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Abstract.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/9 16:28
 * @brief
 *
 **/
abstract class Service_Action_Abstract extends Ap_Action_Abstract
{
    public static $connect_cut = false;
    protected $smartySelfDefine = true;

    protected $uid;
    protected $userInfo;

    /**
     * @param void
     * @return mixed
     */
    public function execute()
    {
        //get the class name by the instance
        $class = get_class($this);
        $request = Saf_SmartMain::getCgi();
        //log the original request in here
        Bd_Log::notice(sprintf('[class]%s,[request]%s', $class, json_encode($request)));
        return $this->invoke();
    }

    /**
     * 抽象方法声明
     * @param
     * @return mixed
     */
    abstract public function invoke();

    /**
     * 因为前端要求smarty对象的配置地址和插件地址可以进行自定义。 所以这个方法很重要
     * @param
     * @return mixed
     */
    public function smartyInstance()
    {
        //检查用于是否登陆
        $userInfo = Bd_Passport::checkUserLogin();
        $userInfo['isLogin'] = 0;
        if (!empty($userInfo) && isset($userInfo['uid']) && isset($userInfo['uname'])) {
            $userInfo['isLogin'] = 1;
        }
        $this->userInfo = $userInfo;
        if($this->userInfo['isLogin'] == 0)
        {
            $currentUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $destUrl = 'https://passport.rdtest.baidu.com/?login&u='.$currentUrl;
            header('Location:'.$destUrl);
            exit();
        }

        //实例smarty对象
        $tpl = Bd_TplFactory::getInstance();

        //赋值tpl里面的信息
        $tpl->assign('userInfo', $userInfo);

        if ($this->smartySelfDefine) {
            $tpl->setConfigDir(ROOT_PATH . '/template/config');
            $tpl->setPluginsDir(ROOT_PATH . '/template/plugins');
        }

        return $tpl;
    }

    /**
     * @param
     * @return int
     */
    public function getUid()
    {
        if (empty($this->uid)) {
            $userInfo = Bd_Passport::checkUserLogin();
            if (!empty($userInfo) && isset($userInfo['uid']))
            {
                $this->uid = intval($userInfo['uid']);;
            }
        }
        return $this->uid;
    }

    /**
     * @param $result
     * @return
     */
    public function jsonResponse($result)
    {
        if (self::$connect_cut) {
            return;
        }

        header('Content-type:text/json');

        echo json_encode($result);

        fastcgi_finish_request();
        self::$connect_cut = true;
    }

    /**
     * @param $filePath
     * @return
     */
    public function downloadFileResponse($filePath)
    {
        if(!file_exists($filePath))
        {
            $ret = array('errno'=>-1,'message'=>'not found this file!');
            $this->jsonResponse($ret);
            return;
        }
        $temp = pathinfo($filePath);
        $fileName = $temp['basename'];
        $fileSize=filesize($filePath);
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$fileSize);
        Header("Content-Disposition: attachment; filename=".$fileName);
        $buffer = 1024;
        $fileCount=0;
        //向浏览器返回数据
        $fp = fopen($filePath,"r");
        while(!feof($fp) && $fileCount<$fileSize){
            $stream = fread($fp,$buffer);
            $fileCount += $buffer;
            echo $stream;
        }
        fclose($fp);
    }

    /**
     * @param $sourceStr
     * @return string
     */
    public function iconvutf8($sourceStr)
    {
        if (empty($sourceStr)) {
            return $sourceStr;
        }

        //encode的顺序很重要
        if (preg_match("/[\x7f-\xff]/", $sourceStr)) {
            $encodeList = array('GB2312', 'GBK', 'UTF-8');
            $encode = mb_detect_encoding($sourceStr, $encodeList, true);
            if ($encode === 'UTF-8') {

                return $sourceStr;
            } else {
                return iconv($encode, 'UTF-8//IGNORE', $sourceStr);
            }
            //$encodeList = array('GBK','UTF-8','GB2312');
        } else {
            $encodeList = array('UTF-8', 'GBK', 'GB2312');
        }

        foreach ($encodeList as $index => $code) {
            $convStr = iconv($code, 'UTF-8', $sourceStr);
            $lastStr = iconv('UTF-8', $code, $convStr);
            if ($lastStr == $sourceStr) {
                return $convStr;
            }
        }
        return $sourceStr;
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
