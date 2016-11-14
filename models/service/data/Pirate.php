<?php

class Service_Data_Pirate
{
    /**
     * @param $strCon
     * @return mixed
     */
    public static function pirate($strCon)
    {
        $ret['errno'] = 0;

        $intRet = preg_match('/http[s]?\:\/\/[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+|www\.[a-zA-Z\d\$\-_\@\&\+\=\;\/\#\?\:\%\~\|\.]+|\<file fsid/i', $strCon, $arrMat);
        if ($intRet > 0) {
            $ret['result']['label'] = 1;
        }
        return $ret;
    }
}
/*
$ret = Service_Data_Pirate::pirate('http://pan.baidu.com/share/link?

http://www.duokan.com/book/90317
有九十几页，不知道全不全。');
print_r($ret);
*/
