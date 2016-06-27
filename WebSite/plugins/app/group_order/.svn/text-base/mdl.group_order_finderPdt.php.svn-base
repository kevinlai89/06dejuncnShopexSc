<?php
/**
 * @author chenping
 * @version mdl.group_order_finderPdt.php 2010-4-9 11:04:11 $
 * @package group_order
 * @uses mdl_finderPdt
 */
if (!class_exists('mdl_finderPdt')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/goods/mdl.finderPdt.php');
}
class mdl_group_order_finderPdt extends mdl_finderPdt{

	function __construct(){
		parent::mdl_finderPdt();
	}


	function _filter($filter){
		//得到团购活动的商品
		$where=array(1);
		$groupModel=$this->system->loadModel('plugins/group_activity');
		$lists = $groupModel->getGroupGoodsList();
		foreach ($lists as $row) {
			$goods[]=$row['gid'];
		}
		unset($lists);
		if ($goods) {
			$where[]=" goods_id in(".implode(',',$goods).")";
		}else {
			$where[]="goods_id=0";
		}
		return parent::_filter($filter).' and '.implode(' and ',$where);
	}

}
?>
