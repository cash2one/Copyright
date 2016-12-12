<?php
/**
 * @name sampleScript
 * @desc 示例脚本
 * @author iknow@baidu.com
 */

//这个CT任务大致三两分钟启动一次
Bd_Init::init();

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
    private $status;
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
        $this->status = 4;
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
        $this->status = 5;
        //获取要处理的列表
        $tasks = $this->getTasks();
        if(!empty($tasks) && count($tasks) > 0)
        {
            foreach($tasks as $index=>$item)
            {
                $ext = json_decode($item['ext'],true);
                if(empty($ext) || count($ext['schedule_history']) >= 3)
                {
                    continue;
                }
                $jobid = $item['jobid'];
                $uid = $item['uid'];
                $salt = $item['salt'];
                $fileName = $item['fileName'];
                $mode = $item['mode'];
                $type = $item['type'];
                $scope = $item['scope'];
                $startTime = $item['custom_start_time'];
                $endTime = $item['custom_end_time'];

                $spf = new Service_Page_FullTask();
                $spf->schedule($jobid,$uid,$salt,$fileName,$mode,$type,$scope,$startTime,$endTime,$ext);
                sleep(1);
            }
        }
    }

    /**
     * @param void
     * @return mixed|void
     */
    private function getTasks()
    {
        $status = $this->status;
        //要查询的字段
        $fields = array('jobid','uid','salt','fileName','mode','type','scope','custom_start_time','custom_end_time','status','job_process','ext');
        switch($status)
        {
            case 4:
                //找到那些只是调度成功的
                $sdf = new Service_Data_FullTask();
                $conditions = array('status='=>$status);
                $tasks = $sdf->select($fields,$conditions);
                break;
            case 5:
                //找到调度失败的，目的是再次进行调度
                $sdf = new Service_Data_FullTask();
                $conditions = array('status='=>$status);

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
        $currentProcess = $job['job_process'];
        //请求线下，获取job对应的状态
        $waiterUrl = Bd_Conf::getAppConf("fulltask/waiter_url").'jobid='.$jobid;
        $responseRet = Service_Copyright_Curl::send($waiterUrl,null,1);
        if($responseRet === false)
        {
            Bd_Log::warning('Oh, No! the waiter is no response !');
        }
        else
        {
            $ret = json_decode($responseRet);
            if($ret['errno'] == 0 && !empty($ret['result']))
            {
                $spf = new Service_Page_FullTask();
                $job_process = $ret['result']['job_process'];
                if($job_process == 100 && !empty($ret['result']['job_result_file']) && !empty($ret['result']['job_stat']))
                {
                    $status = 3;
                    $job_result_file = $ret['result']['job_result_file'];
                    $job_stat = $ret['result']['job_stat'];
                    $row = array('status'=>$status,'job_process'=>$job_process,'job_result_file'=>$job_result_file,'job_stat'=>$job_stat);
                    $spf->updateTable($jobid,$row);
                }
                else if($job_process >0 &&$job_process < 100 && $currentProcess != $job_process)
                {
                    $status = 1;
                    $row = array('status'=>$status,'job_process'=>$job_process);
                    $spf->updateTable($jobid,$row);
                }
            }
        }

    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
