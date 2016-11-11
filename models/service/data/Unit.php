<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Unit.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/4 11:22
 * @brief
 *
 **/
//单进程调起


class Service_Data_Unit
{

    const PREFIX = 'Service_Copyright_';

    /**
    * @param : str, num, num, num, num, num, num, num, str, str, str
    * @return : array
    * */
    function schedule(
            $jobId,
            $pn,    
            $start, 
            $end,   
            $casePerPage, 
            $mode,  
            $type,  
            $scope, 
            $query, 
            $chapter, 
            $text)
    {
        if ($mode == 0 && $scope == 0)
        {       
            $obj = new Service_Copyright_TitlePs($jobId, $query, $type, $scope);
            $obj->run($pn, $start, $end, $casePerPage);
        }
        else if ($mode == 0 && $scope == 1)
        {
            $obj = new Service_Copyright_TitleIknow($jobId, $query, $type, $scope);
            $obj->run($pn, $start, $end, $casePerPage);
        }
        else if ($mode == 1 && $scope == 0)
        {       
            $obj = new Service_Copyright_ContentPs($jobId, $query, $type, $scope, $text); 
            $obj->run($pn, $start, $end, $casePerPage);
        }   
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
