<?php
require_once('pageFactory.php');
class group_order_event_handler extends pageFactory {
	function group_order_event_handler(){
		parent::pageFactory();
	}
	function toSendMail($event_type,$order_data){
		$group_activity_model = $this->system->loadModel('plugins/group_activity/group_activity');
		$group_activity_info = $group_activity_model->isgrouporder($order_data['order_id']);
		switch ($event_type){
			case 'payed':
				if ($group_activity_info && $group_activity_info['extension_code']=='group_buy') {
					$orderModel = $this->system->loadModel('plugins/group_activity/group_activity_order');
					$orderModel->fireEvent('schedulePayed', $order_data,$order_data['member_id']);
				}
				break;
			default:
				break;
		}

	}
}