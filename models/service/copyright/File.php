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
    const FOLDER = 'file';
    /**
    * @param :
    * @return :
    * */
    function Check($content)
    {
        return true;
    }

    /**
     * @param $fileId
     * @param $content
     * @return bool
     */
    public function save2Local($fileId,$content)
    {
        if(!empty($fileId) && !empty($content))
        {
            $parentFolderPath= DATA_PATH.'/app/'.Bd_AppEnv::getCurrApp().'/'.self::FOLDER;
            $this->beddingDir($parentFolderPath);
            $localFilePath = $parentFolderPath.'/'.$fileId;
            file_put_contents($localFilePath,$content);
            return true;
        }
        Bd_Log::wanring(sprintf('[fileId]%s or [content] is empty!',$fileId));
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
