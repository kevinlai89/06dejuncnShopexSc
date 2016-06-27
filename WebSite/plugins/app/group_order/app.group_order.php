<?php
/**
 * @author chenping
 * @version $Id: app.group_activity_order.php 2010-3-25 11:43:23 $
 * @package group_activity_order
 * @uses app
 * //====================
 *	团购活动预订单 app主文件
 * //====================
 */
class app_group_order extends app {
	var $author = 'shopex';
	var $ver	= 1.0;
	var $help	= '';
	var $name	= '团购活动预订单';

	function install(){
		//$this->system->setConf('system.ctlmap','');
		return parent::install();
	}
	function uninstall(){
		//将无效的团购订单放入回收站
		$groupModel = $this->system->loadModel('plugins/group_activity');
		$groupOrderModel=$this->system->loadModel('plugins/group_order');
		if ($orderIds=$groupModel->getInvalidOrder()) {
			$groupOrderModel->recycle($orderIds);
		}
		return parent::uninstall();
	}

	function getMenu(&$menu){
		$menu['order']['items'][0]['items'][]=array(
		'type' =>'menu',
		'label'=>__('团购订单列表'),
		'link' =>'index.php?ctl=plugins/group_order&act=index',
		);
	}
	
	function listener(){
		return array(
		//当付款时 触发发邮件操作
		'trading/order:payed' =>'event_handler:toSendMail'
		);
	}

}
?>
