<?php
/**
 * @name index
 * @desc 入口文件
 * @author iknow@baidu.com
 */
$objApplication = Bd_Init::init();
$objResponse = $objApplication->bootstrap()->run();
