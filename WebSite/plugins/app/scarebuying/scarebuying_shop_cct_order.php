<?php
require_once(CORE_DIR.'/shop/controller/ctl.order.php');
if (!class_exists('sale_mdl')) {
	require_once('sale.mdl.php');

}
if (!class_exists('cart_mdl')) {
	require_once('cart.mdl.php');
}
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
if (!class_exists('order_mdl')) {
	require_once('order.mdl.php');
}

class scarebuying_shop_cct_order extends ctl_order {

	function scarebuying_shop_cct_order(){
		parent::ctl_order();
		$this->template_dir=CORE_DIR.'/shop/view/';
	}

	function create(){
        if($_POST['isgroupbuy']==2){
          require_once(PLUGIN_DIR.'/app/group_activity/group_activity_shop_ctl_order.php');
         $group_activity_ctl_order=new group_activity_shop_ctl_order();
         $group_activity_ctl_order->create();
        }
		$this->begin($this->system->mkUrl('cart', 'checkout'));
		$this->_verifyMember(false);

		//调用app/scareBuying下的order类
		$order=&new order_mdl();
		//$order = &$this->system->loadModel('trading/order');
		//调用app/scareBuying下的cart类
		$oCart=&new cart_mdl();
		//$oCart = &$this->system->loadModel('trading/cart');
		
		$oCart->checkMember($this->member);
		if($_POST['isfastbuy']){
			$cart = $oCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
		}else{
			$cart = $oCart->getCart('all');
		}


		if($_POST['delivery']['ship_addr_area']!=''){

			$_POST['delivery']['ship_addr'] = $_POST['delivery']['ship_addr_area'].' '.$_POST['delivery']['ship_addr'];

		}
		//判断是否超出限购数量
		if ($this->member['member_id']&&$oCart->limitGoodsInCart($cart['g']['cart'],$limitGoodsCart)) {
			$scareModel = &new mdl_scare(); 
			$scarebuying_log = $this->system->loadModel('plugins/scarebuying/scarebuying_log');
			$beyondNum = false;
			foreach ( $limitGoodsCart as $k=>$v) {
				$tmp = explode('-',$k);
				$scareInfo  = $scareModel->getFieldByGoodsId($tmp[0]);
				$number = $scarebuying_log->getGoodsNumByTime(array('member_id'=>$this->member['member_id'],'goods_id'=>$tmp[0],'start_time'=>$scareInfo['s_time'],'end_time'=>$scareInfo['e_time']));
				if (!is_null($scareInfo['buycountlimit'])&&$scareInfo['buycountlimit']!=''&&($number['number']+$v)>$scareInfo['buycountlimit']) {
					$beyondNum = true;
					break;
				}
			}
			if ($beyondNum) {
				trigger_error(__('您购买的某些商品超过了限购数量！'),E_USER_ERROR);
			}
		}
		//end


		$orderid = $order->create($cart, $this->member,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);
		if($orderid){
			//记录限时抢购商品
			if ($this->member['member_id']&&$oCart->limitGoodsInCart($cart['g']['cart'],$limitGoodsCart)) {
				
				$slog['member_id'] = $this->member['member_id'];
				$slog['name'] = $this->member['uname'];
				$slog['createtime']  = time();
				$slog['order_id'] = $orderid;
				foreach ($limitGoodsCart as $k=>$v) {
					$tmp = explode('-',$k);
					$slog['goods_id'] = $tmp[0];
					$slog['product_id'] = $tmp[1];
					$slog['number'] = $v;
					$scarebuying_log->save($slog);
				}
			}
			//end
			
			if($_POST['fromCart'] && !$_POST['isfastbuy']){
				$oCart->removeCart();
			}
			/*             $this->redirect('index','order',array($orderid)); */
		}else{
			trigger_error(__('对不起，订单创建过程中发生问题，请重新提交或稍后提交'),E_USER_ERROR);
		}
		$this->system->setcookie('ST_ShopEx-Order-Buy', md5($this->system->getConf('certificate.token').$orderid));
		$account=$this->system->loadModel('member/account');
		$account->fireEvent('createorder',$this->member,$this->member['member_id']);
		$this->end_only(true, __('订单建立成功'), $this->system->mkUrl('order', 'index', array($orderid)));

		$GLOBALS['pageinfo']['order_id'] = $orderid;
		$this->redirect('order','index',array($orderid));
	}

	
	function index($order_id, $selecttype=false){
		$this->pagedata['_MAIN_']=CORE_DIR.'/shop/view/order/index.html';
		parent::index($order_id,$selecttype);
	}

	function detail($order_id, $selecttype=false){
		$this->pagedata['_MAIN_']=CORE_DIR.'/shop/view/order/detail.html';
		parent::detail($order_id,$selecttype);
	}
	
}
?>