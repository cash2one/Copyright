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
    const FILE = 'file';
    const MAX_SIZE = 100000; //限制文件上传字节数

    protected $allowType = array('txt');
    protected $errmsg;

    /**
    * @param :
    * @return :
    * */
    function check()
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
        $temp = explode(".",$_FILES[self::FILE]['name']);
        $extension = end($temp);
        $extension = empty($extension)?'':strtolower($extension);
        if(in_array($extension,$this->allowType))
        {
            return true;
        }
        $this->errmsg = sprintf('[file extension]%s, this is not support,suggest file extension be %s !',$extension,implode(',',$this->allowType));
        return false;
    }

    /**
     * @param
     * @return bool
     */
    private function checkFileSize()
    {
        //file size
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
    public function save2Local($parentFolder)
    {
        $tmpFile = $_FILES[self::FILE]["tmp_name"];
        $newFile = $parentFolder.'/'.$_FILES[self::FILE]["name"];
        if(!empty($tmpFile) &&  !empty($newFile) )
        {
            $this->beddingDir($parentFolder);
            if(@move_uploaded_file($tmpFile,$newFile))
            {
                return true;
            }
            Bd_Log::warning(sprintf('move file from %s to %s failed!',$tmpFile,$newFile));
            return false;
        }
        Bd_Log::wanring(sprintf('[tmpFile]%s or [newFile] is empty!',$tmpFile,$newFile));
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
