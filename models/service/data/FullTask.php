<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file FullTask.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/14 16:11
 * @brief
 *
 **/
class Service_Data_FullTask
{
    const TABLE_NAME = 'full_task'; //表名固定住

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

        $ret = $this->sdm->insert($this->table,$row);
        return $ret;
    }

    /**
     * @param $uid
     * @return mixed
     * @throws Exception
     */
    public function getUidTaskCount($uid,$status=null)
    {
        //查询条件
        $condition = array('uid='=>$uid);
        if(isset($status))
        {
            $condition['status='] = $status;
        }
        $ret = $this->sdm->selectCount($this->table,$condition);
        return $ret;
    }

    /**
     * @param $fields
     * @param $uid
     * @param $startIndex
     * @param $number
     * @param null $status
     * @return mixed
     * @throws Exception
     */
    public function select($fields,$uid,$startIndex,$number,$status = null)
    {
        //初始化查询条件
        $condition = array('uid='=>$uid);
        if(isset($status))
        {
            $condition['status='] = $status;
        }
        //order & limit
        $order = "order by create_time DESC";
        $limit = "limit $startIndex,$number";
        $appends = array($order,$limit);

        $ret = $this->sdm->select($this->table,$fields,$condition,null,$appends);
        return $ret;
    }

    /**
     * @param $jobid
     * @param $row
     * @return mixed
     * @throws Exception
     */
    public function updateTable($jobid,$row)
    {
        $condition = array('jobid='=>$jobid);
        $ret = $this->sdm->update($this->table,$row,$condition);
        return $ret;
    }


}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
