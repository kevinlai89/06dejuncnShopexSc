<?php
require_once(CORE_DIR.'/shop/controller/ctl.paycenter.php');
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}

class scarebuying_shop_cct_paycenter extends ctl_paycenter {

    function scarebuying_shop_cct_paycenter(&$system){
        parent::ctl_paycenter($system);
    }
    function order(){
        $this->check_order();
        parent::order();
    }
    function check_order(){
        $order_id = $_POST['order_id'];
        $scareModel=new mdl_scare();
        $ordermodel = &$this->system->loadModel('trading/order');
        $order = $ordermodel->getList('*',array('order_id'=>$order_id));
        $goodstr = $order[0]['tostr'];
        $goodlist = explode(',',$goodstr);
        foreach($goodlist as $k=>$good){
            $pstr = strpos($good,'(限时抢购商品)');
            if($pstr){
                $goodsinfo = explode('@|',$good);
                $good = $goodsinfo[0].'@|'.$goodsinfo[1].'@|';
                $goods_id = $goodsinfo[1];
                $scareinfo = $scareModel->getScareByGoodsId($goods_id);
                $count = $scareModel->getScareOrder($good,$scareinfo);
                $num =  explode('(',$goodsinfo[2]);
                $nums = explode(')',$num[1]);
                $allcount = $count+$nums[0]; 
                if($allcount>$scareinfo['count']){
                     $this->splash('failed',$_SERVER["HTTP_REFERER"],__('订单中限时抢购商品已售完，无法付款'));
                    
                }
            }
        }
    
    }
}