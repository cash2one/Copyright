<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Mysql.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:43
 * @brief
 *
 **/
class Service_Dao_Mysql
{
    const DEFAULT_DBCONF = 'copyright'; //默认的数据库配置就叫copyright.conf

    private $_db = null;
    private $_sqlAssember = null;
    private $_dbCfg = null; //db config

    /**
     * @param string $dbCfg
     */
    public function __construct($dbCfg = self::DEFAULT_DBCONF)
    {
        $this->_dbCfg = $dbCfg;
    }

    /**
     *
     */
    private function _initDb()
    {
        if (null == $this->_db) {
            $this->_db = Bd_Db_ConnMgr::getConn($this->_dbCfg);
            if ($this->_db === false) {
                $db_errno = Bd_Db_ConnMgr::getErrno();
                $db_error = Bd_Db_ConnMgr::getError();
                $log = sprintf("connect databsae error, errno=%s,error=%s", $db_errno, $db_error);
                Bd_Log::fatal($log);
            }
        }
    }

    /**
     * @param $table
     * @param $row
     * @return bool
     */
    public function insert($table,$row,$options=null,$onDup = null)
    {
        $this->_initDb();
        $ret = $this->_db->insert($table,$row,$options,$onDup);
        if($ret === false)
        {
            $executeSql = $this->_db->getLastSQL();
            $detailErr = stripslashes($this->_db->error());
            $log = "[sql] $executeSql [error] $detailErr";
            Bd_Log::warning($log);
        }
        return $ret;
    }


    /**
     * 可以处理一条或多条数据
     * @param $table
     * @param $rows
     * @param string $dbConfPath
     * @return bool
     * @throws Exception
     */
    public function smartInsert($table, $rows, $dbConfPath = self::DEFAULT_DBCONF)
    {
        $this->_initDb();
        if (count($rows) == 1) {
            $item = $rows[0];
            $ret = $this->_db->insert($table, $item);
            if ($ret === false) {
                $executeSql = $this->_db->getLastSQL();
                $detailErr = stripslashes($this->_db->error());
                $message = "[sql] $executeSql [error] $detailErr";
                Bd_Log::warning($message);
            }
        } else {
            //进行事务操作
            $ret = false;
            $this->_db->startTransaction();
            foreach ($rows as $index => $item) {
                $ret = $this->_db->insert($table, $item);
                if (false === $ret) {
                    $this->_db->rollback();
                    break;
                }
            }
            if (false != $ret) {
                $ret = $this->_db->commit();
            }
            if (false === $ret) {
                $executeSql = $this->_db->getLastSQL();
                $detailErr = stripslashes($this->_db->error());
                $message = "[sql] $executeSql [error] $detailErr";
                Bd_Log::warning($message);
                $exp = new Exception("db transaction error" . $message);
                throw $exp;
            }
        }

        return $ret;
    }

    /**
     * 获取最后插入记录的ID
     * @param
     * @return mixed
     */
    public function getInsertId()
    {
        $this->_initDb();
        return $this->_db->getInsertID();
    }

    /**
     * 返回删除成功的行数
     * @param $tbl
     * @param $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     * @throws Exception
     */
    public function delete($tbl, $conds, $options = null, $appends = null)
    {
        $this->_initDb();
        $ret = $this->_db->delete($tbl, $conds, $options, $appends);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("db delete error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 更新记录成功的行数
     * @param $tbl
     * @param $row
     * @param $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     * @throws Exception
     */
    public function update($tbl, $row, $conds, $options = null, $appends = null)
    {
        $this->_initDb();
        $ret = $this->_db->update($tbl, $row, $conds, $options, $appends);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("db update error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * @param $tbl
     * @param $fields
     * @param null $conds
     * @param null $options
     * @param null $appends
     * @param $fetchType
     * @param bool $bolUseResult
     * @return mixed
     * @throws Exception
     */
    public function select($tbl, $fields, $conds = null, $options = null, $appends = null,
                           $fetchType = Bd_DB::FETCH_ASSOC, $bolUseResult = false)
    {
        $this->_initDb();
        $ret = $this->_db->select($tbl, $fields, $conds, $options, $appends, $fetchType, $bolUseResult);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("db select error" . $msg);
            Bd_Log::warning($msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 查询总数
     * @param $tbl
     * @param null $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     * @throws Exception
     */
    public function selectCount($tbl, $conds = null, $options = null, $appends = null)
    {
        $this->_initDb();
        $ret = $this->_db->selectCount($tbl, $conds, $options, $appends);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            Bd_Log::warning($msg);
            $exp = new Exception("db select count error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 查询方法 , 接收的是一条sql
     * @param $sql
     * @param $fetchType
     * @param bool $bolUseResult
     * @return mixed
     * @throws Exception
     */
    public function query($sql, $fetchType = Bd_DB::FETCH_ASSOC, $bolUseResult = false)
    {
        $this->_initDb();
        $ret = $this->_db->query($sql, $fetchType, $bolUseResult);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("execute error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 执行sql【new】
     * @param $sql
     * @return mixed
     * @throws Exception
     */
    public function execute($sql)
    {
        $this->_initDb();
        $params = array();
        $count = func_num_args();
        if ($count > 1) {
            for ($i = 1; $i < $count; $i++) {
                $params[] = mysql_escape_string(func_get_arg($i));
            }
            $querySql = vsprintf($sql, $params);
        } else {
            $querySql = $sql;
        }
        //var_dump($querySql);
        $ret = $this->_db->query($querySql, Bd_DB::FETCH_ASSOC, false);
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("db execute error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 执行事务
     * @param $arrSql
     * @return bool|mixed
     * @throws Exception
     */
    public function transaction($arrSql)
    {
        $this->_initDb();
        $ret = false;
        $this->startTransaction();
        foreach ($arrSql as $sql) {
            $ret = $this->query($sql);
            if (false === $ret) {
                $this->rollback();
                break;
            }
        }
        if (false !== $ret) {
            $ret = $this->commit();
        }
        if ($ret === false) {
            $msg = "[sql] " . $this->_db->getLastSQL() . "[error] " . serialize($this->_db->error());
            $exp = new Exception("db transaction error" . $msg);
            throw $exp;
        }
        return $ret;
    }

    /**
     * 基于当前连接的字符集escape字符串
     * @param $string
     * @return mixed
     */
    public function escapeString($string)
    {
        $this->_initDb();
        return $this->_db->escapeString($string);
    }

    /**
     * 获取select sql $tables 表名 | $fields 字段名 | $conds 条件 | $options 选项 | $appends 结尾操作
     * @param $tables
     * @param $fields
     * @param null $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     */
    public function getSelect($tables, $fields, $conds = null, $options = null, $appends = null)
    {
        $this->_initDb();
        $this->_getSQLAssember();
        return $this->_sqlAssember->getSelect($tables, $fields, $conds, $options, $appends);
    }

    /**
     * 获取insert sql
     * @param $table
     * @param $row
     * @param null $options
     * @param null $onDup
     * @return mixed
     */
    public function getInsert($table, $row, $options = null, $onDup = null)
    {
        $this->_initDb();
        $this->_getSQLAssember();
        return $this->_sqlAssember->getInsert($table, $row, $options, $onDup);
    }

    /**
     * update的语句
     * @param $table
     * @param $row
     * @param null $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     */
    public function getUpdate($table, $row, $conds = null, $options = null, $appends = null)
    {
        $this->_initDb();
        $this->_getSQLAssember();
        return $this->_sqlAssember->getUpdate($table, $row, $conds, $options, $appends);
    }

    /**
     * delete 语句
     * @param $table
     * @param null $conds
     * @param null $options
     * @param null $appends
     * @return mixed
     */
    public function getDelete($table, $conds = null, $options = null, $appends = null)
    {
        $this->_initDb();
        $this->_getSQLAssember();
        return $this->_sqlAssember->getDelete($table, $conds, $options, $appends);
    }

    /**
     * 返回assember 对象
     * @param
     * @return Bd_Db_SQLAssember|null
     */
    private function _getSQLAssember()
    {
        $this->_initDb();
        if ($this->_sqlAssember == null) {
            $this->_sqlAssember = new Bd_Db_SQLAssember($this->_db);
        }
        return $this->_sqlAssember;
    }

    /**
     * 事务开始
     * @param
     * @return mixed
     */
    public function startTransaction()
    {
        $this->_initDb();
        return $this->_db->startTransaction();
    }

    /**
     * 提交事务
     * @param
     * @return mixed
     */
    public function commit()
    {
        $this->_initDb();
        return $this->_db->commit();
    }

    /**
     * 回滚事务
     * @param
     * @return mixed
     */
    public function rollback()
    {
        $this->_initDb();
        return $this->_db->rollback();
    }

    /**
     * 错误
     * @param
     * @return mixed
     */
    public function error()
    {
        $this->_initDb();
        return $this->_db->error();
    }

    /**
     * 刚刚执行的T-sql语句
     * @param
     * @return mixed
     */
    public function getLastSQL()
    {
        $this->_initDb();
        return $this->_db->getLastSQL();
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
