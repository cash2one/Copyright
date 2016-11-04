可以按照以下步骤来部署和运行程序:
1.请确保机器路径iknow@cp01-ocean-169.epc.baidu.com:/home/work已经安装了odp标准环境，请参考:http://odp.bae.baidu.com
2.在当前目录执行shell命令make dev_pc;
3.在webserver加入rewrite rule,将http://yourhost/newapp为前缀的所有请求，路由到/home/work/webroot/newapp/index.php文件;
4.重启webserver;
5.访问http://yourhost/newapp/sample?id=1,出现GoodBye World!, 表示运行成功,否则请查看php错误日志;
