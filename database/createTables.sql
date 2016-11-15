DROP TABLE IF EXISTS fast_task;
CREATE TABLE `fast_task` (
  `id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `jobid` CHAR(32) NOT NULL COMMENT 'jobid, 因为是MD5，所以定长',
  `uid` BIGINT(10) UNSIGNED NOT NULL COMMENT '创建job的uid',
  `query` VARCHAR(512) NOT NULL COMMENT '检索的关键词',
  `mode` TINYINT(1) UNSIGNED NOT NULL COMMENT 'mode字典类型:  0=标题类 , 1=内容类',
  `type` TINYINT(1) UNSIGNED NOT NULL COMMENT 'type字典类型:0=小说/出版物,1=影视剧',
  `scope` TINYINT(1) UNSIGNED NOT NULL COMMENT 'scope字典类型:0=百度搜索结果 ,1=百度知道站内资源',
  `chapter` VARCHAR(256) DEFAULT NULL COMMENT '可选字段，当进行内容检索的时候，检索的章节',
  `text` VARCHAR(512) DEFAULT NULL COMMENT 'text可选字段，内容类检索时可输入网页链接或文本内容',
  `create_time` INT(10) UNSIGNED NOT NULL COMMENT 'job创建的时间',
  `update_time` INT(10) UNSIGNED NOT NULL default 0 COMMENT 'job最近一次更新的时间',
  -- `job_result` text COMMENT 'job的结果',
  `job_stat` VARCHAR(512) DEFAULT NULL COMMENT 'job的聚合统计结果',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'job当前状态 0=job成功创建',
  `ext` varchar(1024) default null comment '扩展字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `jobid` (`jobid`),
  KEY `uid` (`uid`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS full_task;
CREATE TABLE `full_task` (
  `id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `jobid` CHAR(32) NOT NULL COMMENT 'jobid, 因为是MD5，所以定长',
  `uid` BIGINT(10) UNSIGNED NOT NULL COMMENT '创建job的uid',
  `custom_start_time` INT(10) UNSIGNED NOT NULL default 0 COMMENT '用户自定义的检索开始时间',
  `custom_end_time` INT(10) UNSIGNED NOT NULL default 0 COMMENT '用户自定义的检索末尾时间',
  `file_id` char(32) NOT NULL COMMENT '全量任务对应的文件Id',
  `file_name` VARCHAR(128) NOT NULL default '' COMMENT '全量任务对应的文件名称',
  `mode` TINYINT(1) UNSIGNED NOT NULL COMMENT 'mode字典类型:  0=标题类 , 1=内容类',
  `type` TINYINT(1) UNSIGNED NOT NULL COMMENT 'type字典类型:0=小说/出版物,1=影视剧',
  `scope` TINYINT(1) UNSIGNED NOT NULL COMMENT 'scope字典类型:0=百度搜索结果 ,1=百度知道站内资源',
  -- `chapter` VARCHAR(256) DEFAULT NULL COMMENT '可选字段，当进行内容检索的时候，检索的章节',
  -- `text` VARCHAR(512) DEFAULT NULL COMMENT 'text可选字段，内容类检索时可输入网页链接或文本内容',
  `create_time` INT(10) UNSIGNED NOT NULL COMMENT 'job创建的时间',
  `update_time` INT(10) UNSIGNED NOT NULL default 0 COMMENT 'job最近一次更新的时间',
  `job_result_file` VARCHAR(256) COMMENT 'job的结果文件路径',
  `job_analysis_file` VARCHAR(256) COMMENT 'job的分析文件路径',
  `job_stat` VARCHAR(512) DEFAULT NULL COMMENT 'job的聚合统计结果',
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'job当前状态 0=job成功创建',
  `job_process` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'job当前的进度 0~100',
  `ext` varchar(1024) default null comment '扩展字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `jobid` (`jobid`),
  KEY `uid` (`uid`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
