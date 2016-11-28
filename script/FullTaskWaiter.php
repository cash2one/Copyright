<?php
/**
 * @name sampleScript
 * @desc 示例脚本
 * @author iknow@baidu.com
 */
Bd_Init::init();

//主体功能逻辑写在这里

//A
//1. 从数据库中拉取 status==4的， 表示调度成功的
//2. 遍历列表，每个元素都是一个job， 发送请求到线下Waiter，拉取线下每个job的进度 返回job_process，更新数据库 job_process 和 update_time
//3. 当job_process == 100的时候， 线下也返回job_result_file 和job_stat， 然后更新 status=3，更新 job_result_file 和job_stat

//B事情
//1. 从数据库中拉取 status==5的， （表示调度失败的） 并且create_time不超过24小时的， 根据ext获取调度次数小于3次的
//2. 遍历列表，每个元素都是一个job， 发送请求到线下Scheduler
echo 'Hello, sample script running...';

//如果利用noah ct任务系统运行脚本，需要显示退出，设置退出码为0，否则监控系统会报警
exit(0);
