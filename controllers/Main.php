<?php
/**
 * @name Main_Controller
 * @desc 主控制器,也是默认控制器
 * @author iknow@baidu.com
 */
class Controller_Main extends Ap_Controller_Abstract {
	public $actions = array(
        'assignment' => 'actions/inner/Assignment.php',
        'parallel' => 'actions/inner/Parallel.php',
	);
}
