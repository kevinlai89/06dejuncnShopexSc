<?php
/**
 * @author zhangxuehui 
 * @package scarebuying
 * @uses 关联系统预设的库存预展时间 当支付的时候从限时抢购的表中减去库存 
 * @version Id scarebuying_admin_cct_order.php 2011-7-7 12:00:00
 */
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
class scarebuying_event_handler extends app{
    //监听支付
    function payed($event_type,$order_data){
        $order_id = $order_data['order_id'];
        $store_time_type = $this->system->getConf('system.store.time');
        if($store_time_type=='2'){
            $oScare = new mdl_scare();
            if($oScare->getOrderStastus($order_id,'pay_status')){
                $scare_goods = $oScare->getOrderGoodsById($order_id);
                if(isset($scare_goods)){
                    foreach($scare_goods as $k=>$v){
                        $oScare->reduceCount($v['goods_id'],$v['nums']);
                    }
                }
            }



        }


    }
}
