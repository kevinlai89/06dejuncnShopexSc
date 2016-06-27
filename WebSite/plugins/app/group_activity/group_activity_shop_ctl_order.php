<?php
/**
 * @author chenping
 * @version $Id: group_activity_shop_ctl_order.php 2010-2-26 11:33:55 $
 * @package group_activity
 * @uses ctl_order
 *
 */
if (!class_exists('ctl_order')) {
	require_once(CORE_DIR.'/shop/controller/ctl.order.php');
}
class group_activity_shop_ctl_order extends ctl_order{

	function __construct(){
		parent::ctl_order();
	}
	function create(){
       
		$this->begin($this->system->mkUrl('cart', 'checkout'));
		$this->_verifyMember(false);

		/*****************************************
		*	$order,$oCart 调用app/group_activity下的模型
		*****************************************/
		//$order = &$this->system->loadModel('trading/order');
		//$oCart = &$this->system->loadModel('trading/cart');
		$order = $this->system->loadModel('plugins/group_activity/group_activity_order');
		$oCart = $this->system->loadModel('plugins/group_activity/group_activity_cart');

		$oCart->checkMember($this->member);
		if($_POST['isfastbuy']){
			$cart = $oCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
		}elseif ($_POST['isgroupbuy']==2){
			$cart = $oCart->getCart('all',$_COOKIE['Cart_Groupbuy']);
		}else{
			$cart = $oCart->getCart('all');
		}
		if($_POST['delivery']['ship_addr_area']!=''){
			$_POST['delivery']['ship_addr'] = $_POST['delivery']['ship_addr_area'].' '.$_POST['delivery']['ship_addr'];
		}
		$orderid = $order->create($cart, $this->member,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);
		if($orderid){
			if($_POST['fromCart'] && !$_POST['isfastbuy']){
				$oCart->removeCart();
			}
			/*             $this->redirect('index','order',array($orderid)); */
			//如果达到限购活动结束

			if ($_POST['isgroupbuy']==2) {
				$groupModel=$this->system->loadModel('plugins/group_activity');
				$grouporder=$groupModel->isgrouporder($orderid);
				$goodsnum=$groupModel->getGoodsNum($grouporder['act_id']);
				$groupInfo=$groupModel->getFieldById($grouporder['act_id'],array('limitnum'));
                
				if ($groupInfo['limitnum']==$goodsnum && $groupInfo['limitnum']) {
					$actInfo['act_id']=$grouporder['act_id'];
					$actInfo['state']=4;
					$groupModel->save($actInfo);
				}
                
			}
            if($_COOKIE['IS_POSTAGE']){
                 $this->system->setcookie('IS_POSTAGE','',time()-10000);
            }


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
}