<?php
/***************************************************************************
 * 
 * Copyright (c) 2016 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file TitleIknow.php
 * @author pancheng(com@baidu.com)
 * @date 2016/11/14 14:25:40
 * @brief 
 *  
 **/

//require('Abstract.php');

class Service_FullTask_TitleIknow extends Service_FullTask_Abstract {


    /**
     * @param
     * @return 0, 1, -1
     */
    public static function cmp_obj($a, $b) {
        if ($a['riskCount'] > $b['riskCount']) { return -1; }
        if ($a['riskCount'] < $b['riskCount']) { return 1; }
        return 0;
    }

    /**
     * @param
     * @return
     */
    public function generate_scripts() {
        $ret = array();
        $template_path = dirname(Service_Copyright_File::getFullTaskPath()) . '/script_template/';
        $generate_path = dirname($this->queryPath) . '/script_generate/';
        $result_path = dirname($this->queryPath) . '/' . $this->jobId . '/';

        if (!file_exists($generate_path)) {
            mkdir($generate_path);
        }
        if (!file_exists($result_path)) {
            mkdir($result_path);
        }
        $timestamp = $this->jobId; //time();
        $type = 'fiction';
        if ($this->type == 1) { $type = 'film'; }
        $script0 = $generate_path . $this->jobId . '_fetch_hive' . '.php';
        $script1 = $generate_path . $this->jobId . '_fetch_hive' . '.sql';
        $script2 = $template_path . 'filter_content.php';
        $script3 = $generate_path . $this->jobId . '_svm' . '.php';
        $script4 = $generate_path . $this->jobId . '_qtags' . '.php';
        $script5 = $template_path . 'judge_' . $type . '.sh';
        $script6 = $template_path . 'format_csv.php';

        $output1 = $result_path . $this->jobId . '_fetch_hive' . '.txt';
        $output2 = $result_path . $this->jobId . '_filter_content' . '.txt';
        $output3 = $result_path . $this->jobId . '_svm' . '.txt';
        $output4 = $result_path . $this->jobId . '_qtags' . '.txt';
        $output5 = $result_path . $this->jobId . '_judge_' . $type . '.txt';
        $output6 = $result_path . 'result.csv';

        $tokens = explode("/", $script0);
        $replace1 = array(
            "{words.txt}" => $this->queryPath,
            "{date}" => date("Ymd", strtotime("yesterday")),
            "{TF_info.php}" => $script0,
            "{TF_info1.php}" => $tokens[count($tokens) - 1],
        );

        $tokens = explode("/", $this->queryPath);
        $replace2 = array(
            "{words.txt}" => $tokens[count($tokens) - 1],
        );

        $replace3 = array(
            "{type}" => $type,
        );

        $this->replace_from_template($template_path . 'fetch_hive.sql', $script1, $replace1);
        $this->replace_from_template($template_path . 'fetch_hive.php', $script0, $replace2);
        $this->replace_from_template($template_path . 'svm.php', $script3, $replace3);
        $this->replace_from_template($template_path . 'qtags.php', $script4, $replace3);
        
        $ret[0] = array(
            'executable' => "hive -f $script1 > $output1",
            'progress' => 40,
            'resultPath' => null,
        );
        $ret[1] = array(
            'executable' => self::$PHP_PATH . " $script2 < $output1 > $output2",
            'progress' => 50,
            'resultPath' => null,
        );
        $ret[2] = array(
            'executable' => self::$PHP_PATH . " $script3 $output2 > $output3",
            'progress' => 60,
            'resultPath' => null,
        );
        $ret[3] = array(
            'executable' => self::$PHP_PATH . "  $script4 $output3 > $output4",
            'progress' => 70,
            'resultPath' => null,
        );
        $ret[4] = array(
            'executable' => "sh $script5 $output4 > $output5",
            'progress' => 80,
            'resultPath' => null,
        );
        $ret[5] = array(
            'executable' => self::$PHP_PATH . " $script6 $output5 $output6",
            'progress' => 90,
            'resultPath' => $output6,
        );
        return $ret;
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
    public function run() {
        BD_Log::notice("service title iknow is running...\n");
        $this->update_status(0);
        $arrScripts = $this->generate_scripts();
        foreach ($arrScripts as $script) {
            BD_LOG::notice("current executing: ". $script['executable']);
            exec($script['executable']);
            $this->update_status($script['progress'], $script['resultPath']);
        }
        BD_Log::notice("title iknow is running statistics...\n");
        $statJson = $this->compute_statistic($script['resultPath']);
        $this->update_status(100, null, $statJson);
        BD_Log::notice("title iknow is done.\n");
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
            if (strpos($line[0], "序号") !== false) {
                continue;
            }
            $totalScan ++;
            $query = $line[1];
            $risk = $line[count($line) - 1];
            $priacy = $line[4];
            $userName = $line[3];
            if ($risk == 1) { $risk = 2; }
            if ($risk != 0) {
                $userRiskCount[$userName] ++;
            }
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
            if (strpos($priacy, "<file ") !== false) {
                $priacyAttachCount ++;
                if ($queryAttachCount[$query]) { $queryAttachCount[$query] ++; }
                else {
                    $queryAttachCount[$query] = 1;
                }
            }
            else {
                $priacyUrlCount ++;
                if ($queryUrlCount[$query]) { $queryUrlCount[$query] ++; }
                else {
                    $queryUrlCount[$query] = 1;
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
        }
        foreach ($riskEstimate as $key => $row) {
            $volume[$key] = $row['riskCount'];
        }
        array_multisort($volume, SORT_DESC, $riskEstimate);
        $riskEstimate = array_slice($riskEstimate, 0, 10);
        
        foreach ($userRiskCount as $key => $value) {
            $piracySource[] = array(
                'from' => $key,
                'fromType' => 1,
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
$obj = new Service_FullTask_TitleIknow('1', 0, 0, '/home/users/pancheng/pancheng-src/offline/upload/words.txt');
$ret = $obj->compute_statistic('/home/users/pancheng/pancheng-src/offline/results/judge_fiction_1479367797.txt', 
                        '/home/users/pancheng/pancheng-src/offline/results/stat_result_1479367797.txt');
print_r($ret);
 */
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
