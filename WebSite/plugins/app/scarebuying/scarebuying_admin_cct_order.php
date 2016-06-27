<?php
/**
 * @author chenping
 * @package scarebuying
 * @uses ctl_order
 * @version Id scarebuying_admin_cct_order.php 2010-5-17 11:27:01
 */
if (!class_exists('ctl_order')) {
	require_once(CORE_DIR.'/admin/controller/order/ctl.order.php');
}
class scarebuying_admin_cct_order extends ctl_order {
   
	function cancel($orderid) {
		$scarebuying_log = $this->system->loadModel('plugins/scarebuying/scarebuying_log');
		$scarebuying_log->deleteByOrderId($orderid);
		parent::cancel($orderid);
	}
	
	 function recycle() {
	 	$scarebuying_log = $this->system->loadModel('plugins/scarebuying/scarebuying_log');
	 	foreach ($_POST['order_id'] as $orderid) {
	 		$scarebuying_log->deleteByOrderId($orderid);
	 	}
	 	parent::recycle();
	 }
	
}
