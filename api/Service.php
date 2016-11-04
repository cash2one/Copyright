<?php
/**
 * @name Service
 * @desc sample api Service
 * @author iknow@baidu.com
 */
class Iknow_Api_Newapp_Service extends Saf_Api_Service implements Iknow_Api_Newapp_Interface{
	public function __construct(){
		parent::__construct('newapp');
		$this->oe = "utf-8";
	}
    public function getSample(Iknow_Api_Newapp_Entity_ReqgetSample $req,
    						  Iknow_Api_Newapp_Entity_ResgetSample $res){
		$arrInput = $req->toArray();
		/*  
		 *           此处添加arrParms的keys到PageServeice的参数的隐射
		 *           默认不做隐射
		 *           arrInput = array('versionId' => 111);
		 *           eg: $arrParam['Id'] = $arrInput['versionId'];
		 **/
		$arrInput['method']=__FUNCTION__;

		$strUrl = "newapp/api/sample?fromapi=1";
		$strPageService = "Service_Page_SampleApi";
		$arrOutput = null;

		$arrRes = $this->execute($arrInput, $arrOutput, $strPageService, $strUrl, 'get');
		if($arrRes !== false)
		{   
			$res->loadFromArray($arrRes);
			if($res !== false){
				return $res;
			}else{
				return null;
			}	
		}   
		return false;
	}
}

