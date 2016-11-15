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
    const FOLDER = 'FullTask';
    /**
    * @param :
    * @return :
    * */
    public function invoke()
    {
        $scf = new Service_Copyright_File();
        $checkRet = $scf->check();
        if($checkRet === true)
        {
            $fileId = $this->genFileId();
            $parentFolder = Service_Copyright_File::getFullTaskPath().'/'.$fileId;
            if($scf->save2Local($parentFolder))
            {
                $ret = array('errno'=>0,'fileId'=>$fileId);
            }
            else
            {
                $ret = array('errno'=>0,'fileId'=>$fileId,'message'=>'save file failed!');
            }
        }
        else
        {
            $ret = array('errno'=>-1,'message'=>$checkRet);
        }
        $this->jsonResponse($ret);

    }

    /**
    * @param:
    * @return :
    * */
    public function genFileId()
    {
        //从临时文件中获取文件1k字节的内容
        $content = file_get_contents($_FILES[Service_Copyright_File::FILE]["tmp_name"],0,null,0,1024);
        $temp = 'content'.$content;
        $temp .= 'time'.time();
        return md5($temp);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
