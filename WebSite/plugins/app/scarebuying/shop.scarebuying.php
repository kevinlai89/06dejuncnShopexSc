<?php
/**
 
 * @uses shopPage
 * //===========================
 * shop.group_activity.php
 * 限时抢购前台
 * //===========================
 *
 */
if (!class_exists('shopPage')) {
	require_once('shopPage.php');
}
class shop_scarebuying extends shopPage  {
	var $noCache = true;
	function shop_scarebuying(){
		parent::shopPage();
	}
    function index(){
        echo time();
    }

}