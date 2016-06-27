<?php
/**
 * @author chenping
 * @time: 2010-3-24 15:00
 * @uses shopPage
 * //===========================
 * shop.group_activity.php
 * 团购活动前台
 * //===========================
 *
 */
if (!class_exists('shopPage')) {
	require_once('shopPage.php');
}
class shop_group_activity extends shopPage  {
	var $noCache = true;
	function shop_group_activity(){
		parent::shopPage();
	}
    function index(){
        echo time();
    }

}