<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file ContentPs.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/14 14:25:29
 * @brief 
 *  
 **/

//require('Abstract.php');

class Service_FullTask_ContentPs extends Service_FullTask_Abstract {

    /**
     * @param
     * @return 0, 1, -1
     */
    public static function cmp_obj($a, $b) {
        if ($a['riskCount'] > $b['riskCount']) { return -1; }
        if ($a['riskCount'] < $b['riskCount']) { return 1; }
        return 0;
    }

    /*
     * @param
     * @return
     */ 
    public function run() {
        BD_Log::notice("service content ps is running...\n");
        $this->update_status(0);
        $result_path = dirname($this->queryPath) . '/' . $this->jobId . '/';
        if (!file_exists($resultPath)) {
            mkdir($resultPath);
        }
        $output1 = $result_path . 'fetch_daily' . '.csv';

        $this->process($this->type, $this->queryPath, $output1);
        $this->update_status(90, $output1); 

        BD_Log::notice("content ps is running statistics...\n");
        $statJson = $this->compute_statistic($output1);
        $this->update_status(100, null, $statJson); 
        BD_Log::notice("content ps is done.\n");
    }

    /**
     * @param
     * @return
     */
    private function replace_from_template($src_file, $dst_file, $rules) {
        $src_content = file_get_contents($src_file);
        $dst_content = $src_content;
        foreach ($rules as $key => $value) {
            $dst_content = str_replace($key, $value, $dst_content);
        }
        file_put_contents($dst_file, $dst_content);
    }

    /**
     * @param
     * @return
     */ 
    private function process($type, $file, $output) {
        $fn = fopen($file, "r");
        $fd = fopen($output, 'w');
        fwrite($fd, chr(0xEF).chr(0xBB).chr(0xBF));
        while ($line = fgets($fn)) {
            $line = trim($line);
            $tokens = explode("#####", $line);
            fputcsv($fd, array("资源关键词：", $tokens[0]));
            fputcsv($fd, array('序号', '外网标题', '外网链接', '外网域名', '公共字符数', '公共字符占比', '标题相似度', '内容相似度', '判断结果'));
            $obj = new Service_Copyright_ContentPs($this->jobId, $tokens[0], $type, 0, $tokens[1]);
            for ($pn = 0; $pn < 5; $pn ++) {
                $ret = $obj->simpleRun($pn, 0, 10, 10);
                foreach ($ret as $index => $item) {
                    fputcsv($fd, array($index + $pn * 10, $item['title'], $item['url'],$item['domain'], $item['sim_len'], $item['sim_other_content'], $item['sim_title'], $item['sim_content'], $item['risk']));
                }
            }
        }
    }

    /**
     * @param
     * @return
     */ 
    public function compute_statistic($resultPath) {
        $fd = fopen($resultPath, "r");
        $riskCount = 0;
        $totalScan = 0;
        $priacyAttachCount = 0;
        $priacyUrlCount = 0;
        while ($line = fgetcsv($fd)) {
            if (strpos($line[0], "资源关键词：") !== false) {
                $query = $line[1];
            }
            else {
                if (strpos($line[0], "序号") !== false) { continue; }
                $risk = $line[count($line) - 1];
                $domain = $line[3];
                if ($queryTotalScan[$query]) {
                    $queryTotalScan[$query] ++;
                }
                else {
                    $queryTotalScan[$query] = 1;
                }
                if ($risk != 0) {
                    $riskCount ++;
                    if ($queryRiskCount[$query]) { $queryRiskCount[$query] ++; }
                    else {
                        $queryRiskCount[$query] = 1;
                    }
                    if ($risk == 2) {
                        $highRiskCount ++;
                        if ($queryHighRiskCount[$query]) { $queryHighRiskCount[$query] ++; }
                        else { $queryHighRiskCount[$query] = 1; }
                    }
                    else {
                        $lowRiskCount ++;
                        if ($queryLowRiskCount[$query]) { $queryLowRiskCount[$query] ++; }
                        else { $queryLowRiskCount[$query] = 1; }
                    }
                }
            }
        }
        $overview = array(
            'totalScan' => $totalScan,
            'hitResourceCount' => $totalScan,
            'riskCount' => $riskCount,
            'highRiskCount' => $highRiskCount,
            'priacyAttachCount' => $priacyAttachCount,
            'priacyUrlCount' => $priacyUrlCount,
        );
       // $content = '';
        foreach ($queryRiskCount as $key => $value) {
            $interval = 3;
            if ($queryRiskCount[$key] / $queryTotalScan[$key] > 0.2) {
                $interval = 0;
            }
            else if ($queryRiskCount[$key] / $queryTotalScan[$key] > 0.5) {
                $interval = 1;
            }
            else if ($queryRiskCount[$key] / $queryTotalScan[$key] > 0.01) {
                $interval = 2;
            }
         //   $content .= $query;
            $riskEstimate[$key] = array(
                'interval' => $interval,
                'totalScan' => $queryTotalScan[$key],
                'riskCount' => $queryRiskCount[$key],
                'noRiskCount' => $queryTotalScan[$key] - $queryRiskCount[$key],
                'riskRate' => sprintf("%.2lf", $queryRiskCount[$key] / $queryTotalScan[$key]),
                'highRiskCount' => intval($queryHighRiskCount[$key]),
                'lowRiskCount' => intval($queryLowRiskCount[$key]),
                'priacyAttachCount' => intval($queryAttachCount[$key]),
                'priacyUrlCount' => intval($queryUrlCount[$key]),
            );
          //  foreach ($riskEstimate[$key] as $k => $v) {
          //     $content .= "\t" . $v;
          //  }
          //  $content .= "\n";
        }
        //file_put_contents($statPath, $content);

        foreach ($riskEstimate as $key => $row) {
            $volume[$key] = $row['riskCount'];
        }
        array_multisort($volume, SORT_DESC, $riskEstimate);
        $riskEstimate = array_slice($riskEstimate, 0, 10);

        foreach ($domainRiskCount as $key => $value) {
            $piracySource[] = array(
                'from' => 'http://' . $key,
                'fromType' => 0,
                'count' => $value,
            );
        }
        foreach ($piracySource as $key => $value) {
            $volume[$key] = $value['count'];
        }
        array_multisort($volume, SORT_DESC, $piracySource);
        $piracySource = array_slice($piracySource, 0, 10);
        $result = array(
            'overview' => $overview,
            'riskEstimate' => $riskEstimate,
            'priacySource' => $piracySource,
        );
        return $result;
    }
}

/*
$obj = new Service_FullTask_ContentPs('1', 0, 0, '/home/users/pancheng/pancheng-src/offline/upload/words.txt');
$ret = $obj->compute_statistic('/home/users/pancheng/pancheng-src/offline/results/fetch_ps_1479370491.txt',
                            '/home/users/pancheng/pancheng-src/offline/results/stat_result_1479370491.txt');
print_r($ret);
 */
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
