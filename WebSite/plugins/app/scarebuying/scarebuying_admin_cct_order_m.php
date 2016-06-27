<?php
/**
 * @author zhangxuehui 
 * @package scarebuying
 * @uses 关联系统预设的库存预展时间 当发货的时候从限时抢购的表中减去库存 
 * @version Id scarebuying_admin_cct_order.php 2011-7-7 12:00:00
 */
if (!class_exists('ctl_order')) {
    require_once(CORE_DIR.'/admin/controller/order/ctl.order.php');
}
if (!class_exists('order_mdl')) {
    require_once('order.mdl.php');
}
if (!class_exists('mdl_scare')) {
    require_once('mdl.scare.php');
}
class scarebuying_admin_cct_order_m extends ctl_order{

    function toDelivery(){
        $order_id = $_POST['order_id'];
        $_POST['opid'] = $this->system->op_id;
        $_POST['opname'] = $this->system->op_name;
        $objOrder = &$this->system->loadModel('trading/order');
        $aItems = $objOrder->getItemList($_POST['order_id'],'',true);
        foreach($_POST['send'] as $k=>$v){
           if($aItems[0]['store']<$v){
               $store_statues=1;
           }
        }
        if(!$store_staues){
            $store_time_type = $this->system->getConf('system.store.time'); 
            if($store_time_type=='3'){
                $oScare = new mdl_scare();
                if($oScare->getOrderStastus($order_id,'ship_status')){
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
}
