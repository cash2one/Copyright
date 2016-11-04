<?php
/**
 * @name Iknow_Api_Newapp_Interface
 * @desc sample api interface
 * @author iknow@baidu.com
 */
interface Iknow_Api_Newapp_Interface{
    public function getSample(Iknow_Api_Newapp_Entity_ReqgetSample $req,
    						  Iknow_Api_Newapp_Entity_ResgetSample $res);
}

class Iknow_Api_Newapp_Entity_ReqgetSample extends Saf_Api_Entity{
	public $id = 0;
    public function __construct(){
    }
}
class Iknow_Api_Newapp_Entity_ResgetSample extends Saf_Api_Entity{
    public $errno = 0 ;
    public $data = null ;
    public function __construct(){
    }
}

