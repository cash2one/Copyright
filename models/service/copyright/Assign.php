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
    private $header = array("pathinfo" => "copyright/Parallel");
    
    private function allocateTitleIknow(
        $processNum, 
        $casePerParallelProcess, 
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text)
    {
        $parallel_server = array();
        $processNumF = intval(21/$casePerParallelProcess);
        $casePerPage = 20;
        for ($i = 0; $i < $processNumF; ++$i)
        {
            $pn = intval(($casePerParallelProcess * $i)/$casePerPage);
            $start = $casePerParallelProcess * $i - $casePerPage * $pn;
            $input = array(
                "pn" => $pn,
                "start" => $start,
                "end" => $start+$casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text);
            $request = array($this->service, $this->method, $this->input, $this->extra, $this->header);
            $parallelServer["$i"] = $request;
        }
        $casePerPage = 10;
        for ($i = $processNumF; $i < $processNum; ++$i)
        {
            $pn = intval(($casePerParallelProcess * $i)/$casePerPage);
            $start = $casePerParallelProcess * $i - $casePerPage * $pn;
            $input = array(
                "pn" => $pn-1,
                "start" => $start,
                "end" => $start+$casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text);
            $request = array($this->service, $this->method, $this->input, $this->extra, $this->header);
            $parallelServer["$i"] = $request;
        }
        return $parallelServer;
    }

    private function allocateTiltePs(
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
                "pn" => $pn,
                "start" => $start,
                "num" => $casePerParallelProcess,
                "casePerPage" => $casePerPage,
                "mode" => $mode,
                "type" => $type,
                "scope" => $scope,
                "query" => $query,
                "chapter" => $chapter,
                "text" => $text);
            $request = array($service, $method, $input, $extra, $header);
            $parallelServer["$i"] = $request;
        }
        return $parallelServer;
    }

    private function allocateContentTieba(
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

    private function allocateContentPs(
        $processNum, 
        $casePerParallelProcess, 
        $mode,
        $type, 
        $scope, 
        $query,
        $chapter,
        $text))
    {
        return $this->allocateTiltePs(
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
    public function assignParallel(
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
                $processNum, 
                $casePerParallelProcess,
                $mode,
                $type, 
                $scope,
                $query,
                $chapter,
                $text);
        }
        
        //BD_Log::notice(json_encode($parallel_server));
        
        if (count($parallelServer) != $processNum)
        {
            $ret['errno'] = 2;
            $ret['message'] = "parallel num : $parallel_server not equal process num : $processNum !";
        }

        ral_multi($parallel_server);
        return $ret;
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
