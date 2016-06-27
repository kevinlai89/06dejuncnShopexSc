<?php
/**
 * @author chenping
 * @version  $Id group_activity_admin_ctl_order.php 2010-3-31 18:31:22 $
 * @package group_activity
 * @uses ctl_order
 *
 */
if (!class_exists('ctl_order')) {
	require_once(CORE_DIR.'/admin/controller/order/ctl.order.php');
}
class group_activity_admin_ctl_order extends ctl_order{
	var $object = 'plugins/group_activity/group_activity_order';
	var $controller  = 'order/order';
	function __construct(){
		parent::ctl_order();
		$this->template_dir = CORE_DIR.'/admin/view/';
	}
	function index($operate){
		parent::index($operate);
	}

	function delete() {
		$actModel= $this->system->loadModel('plugins/group_activity');
		$actModel->delete_order_act($_POST,'order');
		if($this->model->delete($_POST)){
			echo __('选定记录已删除成功!');
		}else{
			echo __('选定记录无法删除!');
		}
	}
	/*
	function toRefund($orderid){
	if(!$orderid) $orderid = $_POST['order_id'];
	else $_POST['order_id'] = $orderid;

	$_POST['opid'] = $this->system->op_id;
	$_POST['opname'] = $this->system->op_name;
	$this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);
	//$objOrder = &$this->system->loadModel('trading/order');
	$objOrder  = $this->system->loadModel('plugins/group_activity/group_activity_order');

	$objOrder->op_id = $this->system->op_id;
	$objOrder->op_name = $this->system->op_name;
	if($objOrder->refund($_POST)){    //处理订单收款
	$this->end(true, __('退款成功'));
	}else{
	$this->end(false, __('退款失败'));
	}
	}
	*/
}
