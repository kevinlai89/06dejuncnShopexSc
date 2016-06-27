<?php
if(!class_exists('pageFactory')){
    require(CORE_DIR.'/include/pageFactory.php');
}

class countdown_listener extends pageFactory{

    function countdown_listener(){
        $this->system = &$GLOBALS['system'];
    }

	//记录抢购订单日志
    function countdownorderlog($event_type, $order_data){
        if ($order_data){
			$oCountdown = &$this->system->loadModel('plugins/countdown');

			$oCountdown->checkOrderInfo($order_data);
			
			//print_r($order_data);
			//exit();
			/*
             $url = $this->system->base_url();
             $info_v = array('order_id'=>$order_data['order_id'],'shipment'=>$shipment,'total'=>$order_data['total_amount'],'member_id'=>$order_data['member_id'],'member'=>$order_data['uname'],'num'=>$order_data['itemnum'],'goods_id'=>$order_data['products']['0']['goods_id'],'price'=>$order_data['totalPrice'],'goods_url'=>$url.'?product-'.$order_data['products']['0']['goods_id'],'thumbnail_pic'=>$order_data['products']['0']['thumbnail_pic'],'goods_name'=>$order_data['tostr']);
             if (!$_COOKIE["SHOPEX_LOGIN_NAME"]){
             $info_v['from'] = 'font';
             $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", serialize($info_v),0,"/");
             }
             else {
                 $info_v['from'] = 'admin';
				 $status =  &$this->system->loadModel("system/status");
                 $status->set('site.orderinfo',serialize($info_v));
             }*/
       }
    }

  }

?>