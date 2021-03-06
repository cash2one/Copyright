<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Submit.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/9 15:04
 * @brief
 *
 **/
class Action_Submit extends Service_Action_Abstract
{
    /**
     *  mode字典类型: 0=标题类, 1=内容类
     * type字典类型: 0=小说/出版物, 1=影视剧，2=小说内容，3=短文内容
     * scope字典类型: 0=百度搜索结果, 1=百度知道站内资源，2=百度贴吧
     * query表示标题内容
     * text表示文本内容(仅当mode类型为内容类时使用)
     */
    public function invoke()
    {
        $request = Saf_SmartMain::getCgi();
        $httpPost = $request['post'];
        $mode = $httpPost['mode'];
        $type = $httpPost['type'];
        $scope = $httpPost['scope'];
        $salt = $httpPost['salt'];
        //$fileName = $this->iconvutf8($httpPost['fileName']); //刚刚上传的文件名
        $fileName = urldecode($httpPost['fileName']); //刚刚上传的文件名

        //简单写， 粗暴了点， 但是时间紧迫
        if (empty($salt)) {
            $ret = array('errno' => -1, 'message' => 'please provide the salt!');
            $this->jsonResponse($ret);
            return;
        }
        if(empty($fileName))
        {
            $ret = array('errno' => -1, 'message' => 'please provide the fileName!');
            $this->jsonResponse($ret);
            return;
        }

        //因为orp的实例机器会文件上传到ftp服务器， 所以在submit的时候要判断一下文件是否存在
        $scf = new Service_Copyright_File();
        $fileHttpUrl = $scf->getFileHttpAddr($salt,$fileName);

        //如果ftp文件不存在， 那么就报异常退出
        if($scf->isFileExists($fileHttpUrl) == false)
        {
            $ret = array('errno'=>-1,'message'=>sprintf('[file]%s, not exists!',$fileHttpUrl));
            $this->jsonResponse($ret);
            return;
        }

        /*
        //如果用NFS ， 需要把这段代码注释打开
        //根据salt， 获取对应路径下的文件名， 这个文件是全量任务要用到的用户上传的文件
        $parentFolder = Service_Copyright_File::getFullTaskPath() . '/' . $salt;
        if (is_dir($parentFolder)) {
            $files = glob($parentFolder . '/*.*');
            if (count($files) == 1) {
                //如果在当前路径下只有一个文件， 说明没有问题，获取文件名
                $temp = pathinfo($files[0]);
                $fileName = $temp['basename']; //在这里获取到文件名字
            } else {
                $ret = array('errno' => -1, 'message' => sprintf('[path]%s contain more than one file!', $parentFolder));
                $this->jsonResponse($ret);
                return;
            }
        } else {
            $ret = array('errno' => -1, 'message' => sprintf('no found the path of %s', $parentFolder));
            $this->jsonResponse($ret);
            return;
        }
        */

        //默认用户自定的时间，起始时间和终止时间都是0
        $custom_start_time = 0;
        $custom_end_time = 0;
        if (empty($httpPost['fullTime'])) {
            $custom_start_time = isset($httpPost['startDate']) ? $httpPost['startDate'] : 0;
            $custom_end_time = isset($httpPost['endDate']) ? $httpPost['endDate'] : 0;
        }

        //这是寅生写的生成jobid的方法
        $jobId = $this->genJobId(
            $this->getUid(),
            $salt,
            $fileName,
            $mode,
            $type,
            $scope,
            $custom_start_time,
            $custom_end_time
        );

        // submit a new job here
        $obj = new Service_Page_FullTask();
        $createJobCount = $obj->createJob(
            $jobId,
            $this->getUid(),
            $salt,
            $fileName,
            $mode,
            $type,
            $scope,
            $custom_start_time,
            $custom_end_time
        );
        $needSchedule = false;
        if ($createJobCount == 1) {
            $ret = array('errno' => 0, 'jobId' => $jobId);
            $needSchedule = true; //任务创建成功 ， 就需要进行调度
        } else {
            //说明jobid 已经和已有的重复了，导致数据库插入不成功!
            $ret = array('errno' => -1, 'message' => 'Please do not repeat create jobs!');
        }
        $this->jsonResponse($ret);

        //调度任务启动
        if($needSchedule)
        {
            $obj->schedule($jobId,$this->getUid(),$salt,$fileName,$mode,$type,$scope,$custom_start_time,$custom_end_time);
        }
    }

    /**
     * @param :
     * @return :
     * */
    public function genJobId(
        $uid,
        $salt,
        $sourceFile,
        $mode,
        $type,
        $scope,
        $startTime,
        $endTime)
    {
        $str = "uid:$uid ";
        $str .= "salt:$salt "; //一个salt只能创建一个job
        $str .= "sourceFile:$sourceFile";
        $str .= "mode:$mode ";
        $str .= "type:$type ";
        $str .= "scope:$scope ";
        $str .= "startTime:$startTime ";
        $str .= "endTime:$endTime";
        $jobId = md5($str);
        return $jobId;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
