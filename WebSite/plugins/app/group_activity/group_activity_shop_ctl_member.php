<?php
/**
 * @author chenping
 * @version $Id group_activity_shop_ctl_member.php 2010-4-7 16:13:13 $
 * @package group_activity
 * @uses ctl_member
 * 
 *
 */
if (!class_exists('ctl_member')) {
	require_once(CORE_DIR.'/shop/controller/ctl.member.php');
}
class group_activity_shop_ctl_member extends ctl_member{

	function __construct(){
		parent::ctl_member();
        $this->customer_template_type = 'member';
	}
	function orders($nPage=1){
		$this->template_dir = CORE_DIR.'/shop/view/';

		$order = &$this->system->loadModel('trading/order');
		$groupModel = $this->system->loadModel('plugins/group_activity');
		$aData = $order->fetchByMember($this->member['member_id'],$nPage-1);
		//判断是否为团购订单
		foreach ($aData['data'] as $key=>$order) {
			$grouporder = $groupModel->isgrouporder($order['order_id']);
			if ($grouporder) {
				$aData['data'][$key]['isgrouporder'] = 1;
				$aData['data'][$key]['extension_code'] = $grouporder['extension_code'];
			}
		}
		$this->pagedata['html_type']='orders';
		//
		$this->pagedata['orders'] = $aData['data'];
		$this->pagination($nPage,$aData['page'],'orders');
		$this->_output();
	}
	function _output(){
		if($GLOBALS['runtime']['member_lv']){
			$oLevel = &$this->system->loadModel('member/level');
			$aLevel = $oLevel->getFieldById($GLOBALS['runtime']['member_lv']);
			if($aLevel['disabled']=='false'){
				$this->member['levelname'] = $aLevel['name'];
			}
		}

		$oSex = &$this->system->loadModel('member/member');
		$aSex = $oSex->getFieldById($this->member['member_id']);

		$this->pagedata['member'] = $this->member;
		$this->pagedata['sex'] = $aSex['sex'];
		$this->pagedata['cpmenu'] = $this->map;
		$this->pagedata['current'] = $this->_action;
		$this->pagedata['_PAGE_']=$this->pagedata['_PAGE_']?'member/'.$this->pagedata['_PAGE_']:'member/'.$this->_tmpl;
		$this->pagedata['_MAIN_'] = 'member/main.html';

		if ($this->pagedata['html_type']=='orders') {
			$this->pagedata['_PAGE_'] = dirname(__FILE__).'/view/shop/member/orders.html';
		}elseif ($this->pagedata['html_type']=='orderdetail') {
			$this->pagedata['_PAGE_'] = dirname(__FILE__).'/view/shop/member/orderdetail.html';
		}elseif ($this->pagedata['html_type']=='index'){
			$this->pagedata['_PAGE_'] = dirname(__FILE__).'/view/shop/member/index.html';
		}

		parent::output();
	}
	function orderdetail($order_id){
		$objOrder = &$this->system->loadModel('trading/order');

		$aOrder = $objOrder->load($order_id);
		$this->_verifyMember($aOrder['member_id']);
		$logs = $objOrder->getLogs($order_id);

		$this->pagedata['orderlogs'] = $objOrder->alterOrderLog($logs);

		if(!$aOrder||$this->member['member_id']!=$aOrder['member_id']){
			$this->system->error(404);
			exit;
		}
		if($aOrder['member_id']){
			$member = &$this->system->loadModel('member/member');
			$aMember = $member->getFieldById($aOrder['member_id'], array('email'));
			$aOrder['receiver']['email'] = $aMember['email'];
		}
		if ($aOrder['pay_extend']){
			$payment=$this->system->loadModel('trading/payment');
			$aOrder['extendCon'] = $payment->getExtendCon($aOrder['pay_extend'],$aOrder['payment']);
		}
		//判断是否为团购订单
		$groupModel = $this->system->loadModel('plugins/group_activity/group_activity');
		$groupOrder = $groupModel->isgrouporder($aOrder['order_id']);
		if ($groupOrder) {
			$aOrder['isgrouporder']=1;
			$aOrder['extension_code']=$groupOrder['extension_code'];
			$aOrder['group_last_change_time']=$groupOrder['last_change_time'];
		}
		$this->pagedata['html_type']='orderdetail';
		$this->pagedata['orderinfo_url']=dirname(__FILE__).'/view/shop/common/orderinfo.html';
		//end

		$this->pagedata['order'] = $aOrder;

		$gItems = $objOrder->getItemList($order_id);
		foreach($gItems as $key => $item){
			$gItems[$key]['addon'] = unserialize($item['addon']);
			if($item['minfo'] && unserialize($item['minfo'])){
				$gItems[$key]['minfo'] = unserialize($item['minfo']);
			}else{
				$gItems[$key]['minfo'] = array();
			}
		}
		$this->pagedata['order']['items'] = $gItems;
		$this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);


		$oMsg = &$this->system->loadModel('resources/message');
		$orderMsg = $oMsg->getOrderMessage($order_id);
		$this->pagedata['ordermsg'] = $orderMsg;
      
		$this->_output();
	}

	//welcome
	function index() {
		$oMem = &$this->system->loadModel('member/member');
		$groupModel = $this->system->loadModel('plugins/group_activity/group_activity');

		$aInfo = $oMem->getMemberInfo($this->member['member_id']);
		$this->pagedata['mem'] = $aInfo;

		$wInfo = $oMem->getWelcomeInfo($this->member['member_id']);
		$this->pagedata['wel'] = $wInfo;

		$order = &$this->system->loadModel('trading/order');
		$aData = $order->fetchByMember($this->member['member_id']);
		//判断是否为团购订单
		foreach ($aData['data'] as $key=>$order) {
			$grouporder = $groupModel->isgrouporder($order['order_id']);
			if ($grouporder) {
				$aData['data'][$key]['isgrouporder'] = 1;
				$aData['data'][$key]['extension_code'] = $grouporder['extension_code'];
			}
		}
		$this->pagedata['html_type']='index';
		//
		$this->pagedata['orders'] = $aData['data'];

		$oMem = &$this->system->loadModel('member/member');
		$aData = $oMem->getFavorite($this->member['member_id']);
		$this->pagedata['favorite'] = $aData['data'];

		$this->_output();
	}
	function orderpay($order_id, $selecttype=false){
		$groupModel=$this->system->loadModel('plugins/group_activity/group_activity');
		$grouporder=$groupModel->isgrouporder($order_id);
		if ($grouporder) {
			$goodsnum=$groupModel->getGoodsNum($grouporder['act_id']);
			$groupInfo=$groupModel->getFieldById($grouporder['act_id'],array('limitnum','end_time','state'));
			if ($groupInfo['limitnum'] && $groupInfo['limitnum']==$goodsnum&&$groupInfo['state']!=3&&$groupInfo['state']!=5) {
				if ($groupInfo['state']==2) {
					$actInfo['act_id']=$grouporder['act_id'];
					$actInfo['state']=4;
					$groupModel->save($actInfo);
				}
				$this->splash('failed','back',__('对不起，可预购商品数达到限购数量，您无法进行付款'));
			}
			
			if (($groupInfo['end_time']<time() || $groupInfo['state']==4)&&$groupInfo['state']!=3&&$groupInfo['state']!=5) {
				$this->splash('failed','back',__('对不起，团购活动已结束，您无法进行付款'));
			}
			
			$orderModel = $this->system->loadModel('trading/order');
			$orderInfo = $orderModel->getFieldById($order_id,array('itemnum'));
			if ($groupInfo['limitnum'] && ($goodsnum+$orderInfo['itemnum'])==$groupInfo['limitnum']) {
				if ($groupInfo['state']==2) {
					$actInfo['act_id']=$grouporder['act_id'];
					$actInfo['state']=4;
					$groupModel->save($actInfo);
				}
			}
			
		}
		parent::orderpay($order_id,$selecttype);
	}
}
?>
