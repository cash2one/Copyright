<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/

/**
 * @file testHashCache.php
 * @author chenzhenyu01(com@baidu.com)
 * @date 2016/11/8 15:18
 * @brief 
 *  
 **/


Bd_Init::init('copyright');

testWrite();

//构造数据

/**
 * @param
 * @return mixed
 */
function testWrite()
{
 $field_value = array();
 for($i = 40;$i<49;$i++)
 {
  $field_value[$i] = getRandStr();
 }
 $obj = new Service_Copyright_HashCache();

 $key = getRandStr();
 var_dump('[key]'.$key);

 $ret = $obj->write($key,$field_value);
 return $ret;
}

function getRandStr($length=null)
{
 if(empty($length))
 {
  $length = rand(10,20);
 }

 $str = '';
 $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
 $max = strlen($strPol)-1;
 for($i=0;$i<$length;$i++)
 {
  $str .= $strPol[rand(0,$max)];
 }
 return $str;
}
 
 
 
 /* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
 ?>
