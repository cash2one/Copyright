<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file FileContentCheck.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/15 12:19
 * @brief
 *
 **/
class Service_Copyright_File
{
    const UPLOAD_FOLDER = 'upload';
    const MAX_SIZE = 100000; //限制文件上传字节数

    protected $allowType = array('txt','jpg','php');
    protected $errmsg;

    /**
    * @param :
    * @return :
    * */
    function Check()
    {
        if($this->checkFileType() && $this->checkFileSize())
        {
           return true;
        }
        return $this->errmsg;
    }

    /**
     * @param
     * @return bool
     */
    private function checkFileType()
    {
        //file extension
        $extension = pathinfo($_FILES["file"]["size"]['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        if(in_array($extension,$this->allowType))
        {
            return true;
        }
        $this->errmsg = sprintf('[file extension]%s is not support!',$extension);
        return false;
    }

    /**
     * @param
     * @return bool
     */
    private function checkFileSize()
    {
        //file sieze
        $size = $_FILES["file"]["size"];
        if($size <= self::MAX_SIZE)
        {
            return true;
        }
        $this->errmsg = sprintf('[file size] %s is beyond of max size!',$size);
        return false;
    }


    /**
     * @param $fileId
     * @param $content
     * @return bool
     */
    public function save2Local($newFileName)
    {
        $tmpFile = $_FILES["file"]["tmp_name"];
        if(!empty($tmpFile) &&  !empty($newFileName) )
        {
            $parentFolderPath= DATA_PATH.'/app/'.Bd_AppEnv::getCurrApp().'/'.self::UPLOAD_FOLDER;
            $this->beddingDir($parentFolderPath);
            $localFilePath = $parentFolderPath.'/'.$newFileName;
            if(@move_uploaded_file($tmpFile,$localFilePath))
            {
                return true;
            }
            Bd_Log::warning(sprintf('move file %s failed',$tmpFile));
            return false;
        }
        Bd_Log::wanring(sprintf('[newFileName]%s or [tmpFile] is empty!',$newFileName));
        return false;
    }

    /**
     * 创建对应的路径
     * @param $dir
     * @param int $mode
     * @return bool
     */
    public function beddingDir($dir,$mode=0777)
    {
        return is_dir($dir) || (self::beddingDir(dirname($dir)) && mkdir($dir,$mode));
    }


}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
