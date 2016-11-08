<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Assign.php
 * @author cuiyinsheng(com@baidu.com)
 * @date 2016/11/4 14:42
 * @brief
 *
 **/
 
class Service_Copyright_Assign
{
    private $service = "Copyright";
    private $method = "post"; 
    private $extra = array();
    private $header = array("pathinfo" => "copyright/inner/parallel");
    
    /**
    * @param : str, num, num, num, num, num, str, str, str
    * @return : array
    * */
    private function allocateTitleIknow(
        $jobId,
        $processNum, 
        $casePerParallelProcess, 
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text)
    {
        $parallelServer = array();
        $processNumF = intval(21/$casePerParallelProcess);
        $casePerPage = 20;
        for ($i = 0; $i < $processNumF; ++$i)
        {
            $pn = intval(($casePerParallelProcess * $i)/$casePerPage);
            $start = $casePerParallelProcess * $i - $casePerPage * $pn;
            $input = array(
                "jobid" => $jobId,
                "pn" => $pn,
                "start" => $start,
                "end" => $start+$casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text,
            );
            $request = array($this->service, $this->method, $input, $this->extra, $this->header);
            $parallelServer["$i"] = $request;
        }
        $casePerPage = 10;
        for ($i = $processNumF; $i < $processNum; ++$i)
        {
            $pn = intval(($casePerParallelProcess * $i)/$casePerPage);
            $start = $casePerParallelProcess * $i - $casePerPage * $pn;
            $input = array(
                "jobid" => $jobId,
                "pn" => $pn-1,
                "start" => $start,
                "end" => $start+$casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text,
            );
            $request = array($this->service, $this->method, $input, $this->extra, $this->header);
            $parallelServer["$i"] = $request;
        }
        return $parallelServer;
    }

    /**
    * @param : str, num, num, num, num, num, str, str, str
    * @return : array
    * */
    private function allocateTiltePs(
        $jobId,
        $processNum, 
        $casePerParallelProcess,
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text)
    {
        $parallelServer = array();
        $casePerPage = 10;
        for ($i = 0; $i < $processNum; ++$i)
        {
            $pn = intval(($casePerParallelProcess * $i)/$casePerPage);
            $start = $casePerParallelProcess * $i - $casePerPage * $pn;
            $input = array(
                "jobid" => $jobId,
                "pn" => $pn,
                "start" => $start,
                "end" => $start+$casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text,
            );
            $request = array($this->service, $this->method, $input, $this->extra, $this->header);
            $parallelServer["$i"] = $request;
        }
        return $parallelServer;
    }

    /**
    * @param : str, num, num, num, num, num, str, str, str
    * @return : array
    * */
    private function allocateContentTieba(
        $jobId,
        $processNum, 
        $casePerParallelProcess, 
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text)
    {
        $parallelServer = array();
        return $parallelServer;

    }

    /**
    * @param : str, num, num, num, num, num, str, str, str
    * @return : array
    * */
    private function allocateContentPs(
        $jobId,
        $processNum, 
        $casePerParallelProcess, 
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text)
    {
        return $this->allocateTiltePs(
            $jobId,
            $processNum, 
            $casePerParallelProcess, 
            $mode,
            $type,
            $scope,
            $query,
            $chapter,
            $text);
    }

    // casePerParallelProcess 必须可以被10整除且小于10,即(1,2,5,10)
    /**
    * @param : str, num, num, num, num, num, str, str, str
    * @return : array
    * */
    public function assignParallel(
        $jobId,
        $mode, 
        $type, 
        $scope,
        $query,
        $chapter,
        $text, 
        $caseNum, 
        $casePerParallelProcess = 10)
    {
        $ret['errno'] = 0;
        $ret['message'] = '';
        if ($casePerParallelProcess != 1 &&
            $casePerParallelProcess != 2 &&
            $casePerParallelProcess != 5 &&
            $casePerParallelProcess != 10)
        {
            $casePerParallelProcess = 10;
        }

        if ($caseNum <= 0)
        {
            return $ret;
        }

        $processNum = intval(($caseNum+1)/$casePerParallelProcess);
        if ($processNum > 32)
        {
            $ret['errno'] = 1;
            $ret['message'] = "processNum=$processNum can't larger than 32 !";
            return $ret;
        }

        if ($mode == 0 && $scope == 0)
        {
            $parallelServer = $this->allocateTiltePs(
                $jobId,
                $processNum, 
                $casePerParallelProcess, 
                $mode,
                $type, 
                $scope,
                $query,
                $chapter,
                $text);
        }
        else  if ($mode == 0 && $scope == 1)
        {
            $parallelServer = $this->allocateTilteIknow(
                $jobId,
                $processNum, 
                $casePerParallelProcess,
                $mode,
                $type, 
                $scope,
                $query,
                $chapter,
                $text);
        }
        else if ($mode == 1 && $scope == 0)
        {
            $parallelServer = $this->allocateContentPs(
                $jobId,
                $processNum, 
                $casePerParallelProcess,
                $mode,  
                $type,  
                $scope, 
                $query, 
                $chapter,
                $text);   
        }
        
        //BD_Log::notice(json_encode($parallelServer));
        
        if (count($parallelServer) != $processNum)
        {
            $ret['errno'] = 2;
            $ret['message'] = "parallel num : $parallelServer not equal process num : $processNum !";
        }

        ral_multi($parallelServer);
        return $ret;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
