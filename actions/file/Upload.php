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
    */
    public function invoke()
    {
        $scf = new Service_Copyright_File();
        $checkRet = $scf->check();
        if($checkRet === true)
        {
            $salt = $this->generateSalt();
            $parentFolder = Service_Copyright_File::getFullTaskPath().'/'.$salt;
            if($scf->save2Local($parentFolder))
            {
                $ret = array('errno'=>0,'salt'=>$salt);
            }
            else
            {
                $ret = array('errno'=>0,'salt'=>$salt,'message'=>'save file failed!');
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
    public function generateSalt()
    {
        //从临时文件中获取文件1k字节的内容
        $fileName = $_FILES[Service_Copyright_File::FILE]['name'];
        $content = file_get_contents($_FILES[Service_Copyright_File::FILE]["tmp_name"],0,null,0,1024);
        $uid = $this->getUid();
        $temp = 'fileName'.$fileName;
        $temp .= 'content'.$content;
        $temp .= 'uid'.$uid;
        $temp .= 'time'.time();
        return md5($temp);
    }
} 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
