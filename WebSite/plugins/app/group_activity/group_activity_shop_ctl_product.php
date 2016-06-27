<?php
/**
 * @author chenping
 * @version $Id: group_activity_shop_ctl_product.php 2010-3-24 15:06 $
 * @uses ctl_product
 * //================
 * 接管ctl_product中的方法
 * //================
 *
 */
if (!class_exists('ctl_product')) {
	require_once(CORE_DIR.'/shop/controller/ctl.product.php');
}
class group_activity_shop_ctl_product extends ctl_product{

	function group_activity_shop_ctl_product(){
		parent::ctl_product();
	}

	function index($gid,$specImg='',$spec_id=''){
		/*******************************************
		* 			得到团购信息
		*******************************************/
		$group_activity_model = $this->system->loadModel('plugins/group_activity/group_activity');
		$group_activity_model->chgStateByGid($gid);
		$goodsActivityInfo = $group_activity_model->getOpenActivityByGid($gid);
		if ($goodsActivityInfo) {
			$goodsActivityInfo['state'] = $group_activity_model->getstate($goodsActivityInfo['state']);
			$goodsActivityInfo['ext_info'] = unserialize($goodsActivityInfo['ext_info']);
			$goodsActivityInfo['goodsnum'] = $group_activity_model->getGoodsNum($goodsActivityInfo['act_id']);
			$goodsActivityInfo['current_price'] = $group_activity_model->getCurrentPrice($goodsActivityInfo['act_id']);
			$goodsActivityInfo['postage'] = unserialize($goodsActivityInfo['postage']);
			$this->pagedata['goodsActivityInfo'] = $goodsActivityInfo;
		}
       
     
		unset($goodsActivityInfo);
		$this->pagedata['_MAIN_'] = dirname(__FILE__).'/view/shop/product/index.html';
		parent::index($gid,$specImg,$spec_id);
	}

	function output(){
		$this->template_dir = CORE_DIR.'/shop/view/';
		parent::output();
	}
}
?>
