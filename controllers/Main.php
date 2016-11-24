<?php
/**
 * @name Main_Controller
 * @desc 主控制器,也是默认控制器
 * @author iknow@baidu.com
 */
class Controller_Main extends Ap_Controller_Abstract {
	public $actions = array(
		'index' => 'actions/Index.php',
		'statistic'=>'actions/Statistic.php',
		'test' => 'actions/Test.php',
	);
}
