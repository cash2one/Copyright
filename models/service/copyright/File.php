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
    const FULLTASK_FOLDER = 'FullTask';
    const FILE = 'file';

    protected $allowType = array('txt');
    protected $errmsg;
    public $fileName;
    private $max_size = 100000; //文件上传最大字节数


    public function __construct()
    {
        $this->fileName = $_FILES[self::FILE]['name'];
        $this->max_size = Bd_Conf::getAppConf("fulltask/max_file_size");
    }

    /**
     * @param
     * @return string
     */
    public static function getFullTaskPath()
    {
        return DATA_PATH.'/app/'.Bd_AppEnv::getCurrApp().'/'.self::FULLTASK_FOLDER;
    }

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
        if($size>0 && $size <= $this->max_size)
        {
            return true;
        }
        $this->errmsg = sprintf('[file size] %s is beyond of max size!',$size);
        return false;
    }


    /**
     * 存储到本机 ， 当线上是分布式的，并且用了NFS的话， 是可行的
     * @param $fileId
     * @param $content
     * @return bool
     */
    public function save2Local($fromFileName,$toFileFullPath)
    {
        if(!empty($fromFileName) &&  !empty($toFileFullPath) )
        {
            $this->beddingDir($toFileFullPath);
            if(@move_uploaded_file($fromFileName,$toFileFullPath))
            {
                return true;
            }
            Bd_Log::warning(sprintf('move file from %s to %s failed!',$fromFileName,$toFileFullPath));
            return false;
        }
        Bd_Log::wanring(sprintf('[tmpFile]%s or [newFile] is empty!',$fromFileName,$toFileFullPath));
        return false;
    }

    /**
     * @param $salt
     * @param $fileName
     * @return string
     */
    public function getFileFtpAddr($salt,$fileName)
    {
        $ftp_config = Bd_Conf::getAppConf("fulltask/ftp_config");
        $port = $ftp_config['port'];
        if( 21 == $port)
        {
            $url = sprintf('ftp://%s:%s@%s/%s',$ftp_config['username'],$ftp_config['password'],$ftp_config['hostname'],$ftp_config['pathinfo']);
        }
        else
        {
            $url = sprintf('ftp://%s:%s@%s:%s/%s',$ftp_config['username'],$ftp_config['password'],$ftp_config['hostname'],$ftp_config['port'],$ftp_config['pathinfo']);
        }
        $url .= $salt.'/'.$fileName;
        return $url;
    }

    /**
     * @param $salt
     * @param $fileName
     * @return string
     */
    public function getFileHttpAddr($salt,$fileName)
    {
        $http_config = Bd_Conf::getAppConf("fulltask/http_config");
        $port = $http_config['port'];
        if(80 == $port)
        {
            $url = sprintf('http://%s/%s',$http_config['hostname'],$http_config['pathinfo']);
        }
        else
        {
            $url = sprintf('http://%s:%s/%s',$http_config['hostname'],$http_config['port'],$http_config['pathinfo']);
        }
        $url .= $salt.'/'.$fileName;
        return $url;
    }

    /**
     * nginx静态服务的文件是否存在
     * @param $httpUrl
     * @return bool
     */
    public function isFileExists($httpUrl)
    {
        $ch = curl_init($httpUrl);
        curl_setopt($ch,CURLOPT_NOBODY,true);
        curl_setopt($ch,CURLOPT_CUSTOMEQUEST,'GET');
        $response = curl_exec($ch);
        $found = false;
        if($response !== false)
        {
            $statusCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            if(200 == $statusCode)
            {
                $found = true;
            }
        }
        curl_close($ch);
        return $found;

    }

    /**
     * @param $fromFile
     * @param $salt
     * @param $newFileName
     * @return bool
     */
    public function save2ftp($fromFile,$salt,$newFileName)
    {
        //文件的ftp路径
        $url = $this->getFileFtpAddr($salt,$newFileName);

        $ch = curl_init($url);
        $fp = fopen($fromFile,'r');
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1); //如果没有那个ftp的路径，就创建路径
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fromFile));
        curl_exec($ch);
        $errno = curl_errno($ch);
        if($errno == 0)
        {
            Bd_Log::notice(sprintf('save2ftp successfully! [fromFile]%s,[url]%s',$fromFile,$url));
            return true;
        }
        else
        {
            Bd_Log::fatal(sprintf('save2ftp failed! [fromFile]%s,[url]%s',$fromFile,$url));
            return false;
        }
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
