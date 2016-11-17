<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file FastTask.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/16 19:30
 * @brief
 *
 **/
class Service_Data_FastTask
{
    const TABLE_NAME = 'fast_task'; //表名固定住
    protected $sdm;
    protected $table = self::TABLE_NAME;

    public function __construct()
    {
        if(empty($this->sdm))
        {
            $this->sdm = new Service_Dao_Mysql();
        }

    }

    /**
     * @param $jobid
     * @return mixed
     * @throws Exception
     */
    public function getJobIdCount($jobid)
    {
        //查询条件
        $condition = array('jobid='=>$jobid);
        $ret = $this->sdm->selectCount($this->table,$condition);
        return $ret;
    }

    /**
     * @param $row
     * @return bool
     */
    public function insertTable($row)
    {
        //设置创建时间是当前的时间戳
        if(!isset($row['create_time']))
        {
            $row['create_time'] = time();
        }
        $options = 'IGNORE';
        $ret = $this->sdm->insert($this->table,$row,$options);
        return $ret;
    }

    /**
     * @param $fields
     * @param $jobid
     * @return mixed
     * @throws Exception
     */
    public function getStatistic($fields,$jobid)
    {
        //初始化查询条件
        $condition = array('jobid='=>$jobid);
        $ret = $this->sdm->select($this->table,$fields,$condition);
        return $ret;
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
