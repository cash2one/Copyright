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

        if(empty($salt))
        {
            $salt = 'sample'; //如果没有指定salt ， 说明是全量检索的样板文件下载， 地址是放在sample里的
        }

        $filePath = Service_Copyright_File::getFullTaskPath().'/'.$salt.'/'.$fileName;
        $this->downloadFileResponse($filePath);

    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
