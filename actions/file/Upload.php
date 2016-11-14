<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file Upload.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/14 16:16
 * @brief 
 *  
 **/
class Action_Upload extends Ap_Action_Abstract
{
    public function execute()
    {
        $httpGet = $_GET;
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];

        $ret['errno'] = 0;
        $ret['message'] = '';
        $ret['fileId'] = '';

        $arrFileInfo=array();
        //上传文件的名称、类型、大小、临时文件名称、上传过程错误号
        $arrFileInfo['name'] = $_FILES["file"]["name"];
        $arrFileInfo['type'] = $_FILES["file"]["type"];
        $arrFileInfo['size'] = round( ($_FILES["file"]["size"] / 1024),3);//0.001kb
        $arrFileInfo['temp'] = $_FILES["file"]["tmp_name"];
        $arrFileInfo['errno'] = $_FILES["file"]["error"] ;
        $arrInput['fileInfo'] = $arrFileInfo;

        $content = get_file_contents($arrFileInfo['temp']);
        $allRight = new Service_Copyright_FileContentCheck();
        if ($allRight->Check($content))
        {
            $ret['fileId'] = $this->genFileId($content);
        }
        return $ret;
    }

    public function genFileId($content)
    {
        return md5($content);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
