<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Download.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/15 23:45
 * @brief
 *
 **/
class Action_Download extends Service_Action_Abstract
{

    /**
     * @param
     * @return
     */
    public function invoke()
    {
        $httpGet = $_GET;
        $salt = $httpGet['salt'];
        $fileName = $httpGet['file'];
        $fileName = $this->iconvutf8($fileName);

        //如果连fileName都没有， 那只能return
        if(empty($fileName))
        {
            $ret = array('errno'=>-1,'message'=>'please input the file');
            $this->jsonResponse($ret);
            return;
        }

        //如果没有salt ， 就说明下载的是样式文件
        if(empty($salt))
        {
            return $this->downloadSampleFile($fileName);
        }
        else
        {

        }

    }

    /**
     * 样式文件的下载
     * @param $fileName
     * @return
     */
    protected function downloadSampleFile($fileName)
    {
        $filePath = Service_Copyright_File::getFullTaskPath().'/sample/'.$fileName;
        $this->downloadFileResponse($filePath);
    }

    /**
     * @param $salt
     * @param $fileName
     * @return
     */
    protected function downloadFile($salt,$fileName)
    {
        //拼凑文件地址
        $scf = new Service_Copyright_File();
        $httpFileAddr = $scf->getFileHttpAddr($salt,$fileName);
        //校验文件是否存在
        if($scf->isFileExists($httpFileAddr))
        {
            //文件如果存在，就做302跳转
            header('Location:'.$httpFileAddr);
            exit();
        }
        else
        {
            $result = array('errno'=>-1,'message'=>sprintf('[file]%s , not exists!',$httpFileAddr));
            $this->jsonResponse($result);
        }
    }

    /*
     *  注释掉这个方法， 因为文件方案并不是nfs
     *
    public function invoke()
    {
        $httpGet = $_GET;
        $salt = $httpGet['salt'];
        $fileName = $httpGet['file'];
        $fileName = $this->iconvutf8($fileName);

        if(empty($salt))
        {
            $salt = 'sample'; //如果没有指定salt ， 说明是全量检索的样板文件下载， 地址是放在sample里的
        }

        //不下载空文件
        if(empty($fileName))
        {
            $ret = array('errno'=>-1,'message'=>'please input the file');
            $this->jsonResponse($ret);
            return;
        }

        $filePath = Service_Copyright_File::getFullTaskPath().'/'.$salt.'/'.$fileName;
        $this->downloadFileResponse($filePath);

    }
    */
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
