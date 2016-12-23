<?php
/**
 * @name sampleScript
 * @desc 示例脚本
 * @author iknow@baidu.com
 */

//这个CT任务大致三两分钟启动一次
Bd_Init::init('copyright');

//主体功能逻辑写在这里

//A
//1. 从数据库中拉取 status==4的， 表示调度成功的
//2. 遍历列表，每个元素都是一个job， 发送请求到线下Waiter，拉取线下每个job的进度 返回job_process，更新数据库 job_process 和 update_time
//3. 当job_process == 100的时候， 线下也返回job_result_file 和job_stat， 然后更新 status=3，更新 job_result_file 和job_stat

//B事情
//1. 从数据库中拉取 status==5的， （表示调度失败的） 并且create_time不超过24小时的， 根据ext获取调度次数小于3次的
//2. 遍历列表，每个元素都是一个job， 发送请求到线下Scheduler
$fullTaskWaiter = new FullTaskWaiter();
$fullTaskWaiter->work();

//如果利用noah ct任务系统运行脚本，需要显示退出，设置退出码为0，否则监控系统会报警
exit(0);



class FullTaskWaiter
{
    private static $modeCondition = array(
        'urge'=>array(1,4),  //1表示正在执行，4表示调度成功  要催促正在执行和调度成功的进度
        'reschedule'=>array(0,5), //0表示job创建成功，5表示调度失败， 要对创建成功和调度失败的重新进行调度
    );

    private $mode;
    public function work()
    {
        //更新job的process状态
        $this->urge();
        //重新唤醒，重新schedule没有调度成功的job
        $this->reschedule();
    }


    /**
     * 催促，更新状态
     * 拉取列表，逐个job请求线下，获取当前状态
     * @param
     * @return
     */
    public function urge()
    {
        $this->mode = __FUNCTION__;
        //获取要处理的列表
        $tasks = $this->getTasks();
        if(!empty($tasks) && count($tasks) > 0)
        {
            foreach($tasks as $index=>$item)
            {
                $this->updateJob($item);
                sleep(1);
            }
        }
    }

    /**
     * @param
     * @return
     */
    public function reschedule()
    {
        $this->mode = __FUNCTION__;
        //获取要处理的列表
        $tasks = $this->getTasks();
        if(!empty($tasks) && count($tasks) > 0)
        {
            foreach($tasks as $index=>$item)
            {
                $ext = json_decode($item['ext'],true);
                $jobid = $item['jobid'];
                if(empty($ext) || count($ext['schedule_history']) >= 3)
                {
                    var_dump(sprintf('[jobid]%s,[schedule_history]%s,no need to reschedule again!',$jobid,implode(',',$ext['schedule_history'])));
                    continue;
                }

                $uid = $item['uid'];
                $salt = $item['salt'];
                $fileName = $item['file_name'];
                $mode = $item['mode'];
                $type = $item['type'];
                $scope = $item['scope'];
                $startTime = $item['custom_start_time'];
                $endTime = $item['custom_end_time'];

                $spf = new Service_Page_FullTask();
                $spf->schedule($jobid,$uid,$salt,$fileName,$mode,$type,$scope,$startTime,$endTime,$ext);

                var_dump(sprintf('[jobid]%s,[schedule_history]%s, schedule!',$jobid,implode(',',$ext['schedule_history'])));
                sleep(1);
            }
        }
    }

    /**
     * @param $field
     * @param array $arr
     * @return string
     */
    private function orJoint($field,array $arr)
    {
        $conditionStr = "";
        if(!empty($arr))
        {
            foreach($arr as $item)
            {
                if(!empty($conditionStr))
                {
                    $conditionStr .= " OR ";
                }

                if(is_int($item))
                {
                    $conditionStr .= "`$field`=$item";
                }
                else
                {
                    $conditionStr .= "`$field`='$item'";
                }
            }
        }
        return $conditionStr;
    }

    /**
     * @param void
     * @return mixed|void
     */
    private function getTasks()
    {
        //要查询的字段
        $fields = array('jobid','uid','salt','file_name','mode','type','scope','custom_start_time','custom_end_time','status','job_process','ext');
        switch($this->mode)
        {
            case 'urge':
                $sdf = new Service_Data_FullTask();
                //status 要用或操作
                $statusCondition = $this->orJoint('status',self::$modeCondition[$this->mode]);
                $conditions = array($statusCondition);

                $tasks = $sdf->select($fields,$conditions);
                break;
            case 'reschedule':
                //找到调度失败的，目的是再次进行调度
                $sdf = new Service_Data_FullTask();
                //status 要用或操作
                $statusCondition = $this->orJoint('status',self::$modeCondition[$this->mode]);
                $conditions = array($statusCondition);

                //控制时间要不超过24小时的
                $currentTime = time();
                $conditions['create_time>='] = $currentTime-86400;
                $conditions['create_time<='] = $currentTime;

                $tasks = $sdf->select($fields,$conditions);
                break;
            default:
                return;
        }
        return $tasks;
    }

    /**
     * @param $jobid
     * @return
     */
    protected function updateJob($job)
    {
        $jobid = $job['jobid'];
        $salt = $job['salt'];
        $currentProcess = $job['job_process'];
        //请求线下，获取job对应的状态, 要把salt 和jobid传过去
        $waiterUrl = Bd_Conf::getAppConf("fulltask/waiter_url").'salt='.$salt.'&jobid='.$jobid;
        $responseRet = Service_Copyright_Curl::send($waiterUrl,null,1);
        var_dump(sprintf('[waiter url]%s,[return]%s',$waiterUrl,$responseRet));
        if($responseRet === false)
        {
            Bd_Log::warning(sprintf('Oh, No! the waiter is no response ![waiterUrl]%s',$waiterUrl));
        }
        else
        {
            $ret = json_decode($responseRet,true);
            if($ret['errno'] == 0 && !empty($ret['result']))
            {
                $spf = new Service_Page_FullTask();
                $job_process = $ret['result']['job_process'];
                if($job_process == 100 && !empty($ret['result']['job_result_file']) && !empty($ret['result']['job_stat']))
                {
                    $status = 3; //status = 3 表示已经完成
                    $job_result_file = $ret['result']['job_result_file'];
                    $job_stat = $ret['result']['job_stat'];
                    $row = array('status'=>$status,'job_process'=>$job_process,'job_result_file'=>$job_result_file,'job_stat'=>$job_stat);
                    $spf->updateTable($jobid,$row);
                }
                else if($job_process >0 &&$job_process < 100 && $currentProcess != $job_process)
                {
                    $status = 1; //status = 1 表示正在运行中
                    $row = array('status'=>$status,'job_process'=>$job_process);
                    $spf->updateTable($jobid,$row);
                }
            }
        }

    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
