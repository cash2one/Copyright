<?php
/**
 * @name Inner_Controller
 * @desc 主控制器,也是默认控制器
 * @author iknow@baidu.com
 */
class Controller_FastTask extends Ap_Controller_Abstract {
	public $actions = array(
        'upload' => 'actions/file/Upload.php',
	);
}
