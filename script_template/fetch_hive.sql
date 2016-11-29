set mapred.job.priority=VERY_HIGH;
set mapred.job.name=TF_iknowspam;
set HDFS_PHP=home/iknow/odp/php/bin/php -c home/iknow/odp/php/etc/php.ini;
set mapred.cache.archives=hdfs://szwg-ecomon-hdfs.dmop.baidu.com:54310/app/ns/iknow/odp_php.tgz#home;
set mapred.job.map.capacity=500;

add file {TF_info.php};
add file {words.txt};

--insert overwrite local directory 'hdfs://szwg-ecomon-hdfs.dmop.baidu.com:54310/app/ns/iknow/spam/pancheng/hive_result'
select transform(Q.qid, Q.title, R.rid, R.uid, R.uname, R.content) using '${hiveconf:HDFS_PHP} ./{TF_info1.php}' as (match, qid, title, rid, uid, uname, content)
from  
	(SELECT  qid, rid, uid, uname, content FROM qb_tblReply 
      WHERE deleted='n' and dt='20161107') R
	join
	(SELECT  qid, title FROM qb_tblQuestion WHERE
      deleted='n' and dt='20161107') Q 
	ON R.qid = Q.qid

