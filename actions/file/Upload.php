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
class Action_Upload extends Service_Action_Abstract
{
    /**
    * @param :
    * @return :
    * */
    public function invoke()
    {
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

        $content = get_file_contents($arrFileInfo['temp']); //文件内容
        $scf = new Service_Copyright_File();
        if ($scf->Check($content))
        {
            $fileId = $this->genFileId($content);
            if($scf->save2Local($fileId,$content))
            {
                $ret = array('errno'=>0,'fileId'=>$fileId);
            }
            else
            {
                //文件存储失败
                $ret = array('errno'=>-1,'fileId'=>$fileId,'message'=>sprintf('save file %s failed',$fileId));
            }
        }
        $this->jsonResponse($ret);
    }

    /**
    * @param:
    * @return :
    * */
    public function genFileId($content)
    {
        return md5($content);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
