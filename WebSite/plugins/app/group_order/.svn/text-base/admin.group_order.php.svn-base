<?php
/**
 * @author chenping
 * @version $Id: admin.group_activity_order.php 2010-3-25 11:48:02 $
 * @package group_activity_order
 * @uses ctl_order
 * //================================
 * 	团购活动预订单 后台
 * //================================
 *
 */
if (!class_exists('ctl_order')) {
	require_once(CORE_DIR.'/admin/controller/order/ctl.order.php');

}
class admin_group_order extends ctl_order  {
	var $object     = 'plugins/group_order';
	var $finder_default_cols = '_cmd,_tag_,order_id,createtime,total_amount,ship_name,pay_status,shipping,payment,member_id,is_normal_order';
	function __construct(){
		parent::ctl_order();
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->finder_action_tpl = dirname(__FILE__).'/view/admin/group_order/finder_action.html';
		$this->detail_title = dirname(__FILE__).'/view/admin/order/detail_title.html';
	}
	function page($view){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::page($view);
	}
	function display($file){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::display($file);
	}
	function _views(){
		return array(
		__('全部')=>"",
		__('未处理')=>array('pay_status'=>array('0'),'ship_status'=>array('0'),'status'=>'active'),
		__('已确认参加团购')=>array('pay_status'=>array('1','2','3'),'status'=>'active'),
		__('已作废')=>array('status'=>'dead'),
		__('已生成正式订单')=>array('extension_code'=>'succ'),
		);
	}

	function _detail(){
		return array(
		'detail_info'=>array('label'=>__('基本信息'),'tpl'=>dirname(__FILE__).'/view/admin/order/order_detail.html'),
		'detail_items'=>array('label'=>__('商品'),'tpl'=>'order/od_items.html'),
		'detail_bills'=>array('label'=>__('收退款记录'),'tpl'=>'order/od_bill.html'),
		'detail_pmt'=>array('label'=>__('优惠方案'),'tpl'=>'order/od_pmts.html'),
		'detail_mark'=>array('label'=>__('订单备注'),'tpl'=>'order/od_mark.html'),
		'detail_logs'=>array('label'=>__('订单日志'),'tpl'=>'order/od_logs.html'),
		'detail_msg'=>array('label'=>__('顾客留言'),'tpl'=>'order/od_msg.html'),
		);
	}
	function detail($object_id,$func=null){
		$groupModel = $this->system->loadModel('plugins/group_activity');
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->pagedata['actbarHtml']=dirname(__FILE__).'/view/admin/order/actbar.html';
		$this->pagedata['group_order'] = $groupModel->getTurntoNormalOrder($object_id);
		parent::detail($object_id,$func);
	}
	function singlepage($view){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::singlepage($view);
	}
	function showEdit($orderid){
		$this->path[] = array('text'=>__('订单编辑'));
		$objOrder = &$this->system->loadModel('trading/order');
		$aOrder = $objOrder->getFieldById($orderid);
		$aOrder['discount'] = 0 - $aOrder['discount'];

		$oCur = &$this->system->loadModel('system/cur');
		$aCur = $oCur->getSysCur();
		$aOrder['cur_name'] = $aCur[$aOrder['currency']];

		$aOrder['items'] = $objOrder->getItemList($orderid);
		$aOrder['pmt'] = $objOrder->getPmtList($orderid);

		if($aOrder['member_id'] > 0){
			$objMember = &$this->system->loadModel('member/member');
			$aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
			$aOrder['ship_email'] = $aOrder['member']['email'];
		}else{
			$aOrder['member'] = array();
		}

		$objDelivery = &$this->system->loadModel('trading/delivery');
		$aArea = $objDelivery->getDlAreaList();
		foreach ($aArea as $v){
			$aTmp[$v['name']] = $v['name'];
		}
		$aOrder['deliveryArea'] = $aTmp;

		$aRet = $objDelivery->getDlTypeList();
		foreach ($aRet as $v){
			$aShipping[$v['dt_id']] = $v['dt_name'];
		}
		$aOrder['selectDelivery'] = $aShipping;

		$objPayment = &$this->system->loadModel('trading/payment');
		$aRet = $objPayment->getMethods();
		$aPayment[-1] = '货到付款';
		foreach ($aRet as $v){
			$aPayment[$v['id']] = $v['custom_name'];
		}
		$aOrder['extendCon'] = $objPayment->getExtendCon($aOrder['extend'],$aOrder['payment']);
		$aOrder['selectPayment'] = $aPayment;

		$objCurrency = &$this->system->loadModel('system/cur');
		$aRet = $objCurrency->curAll();
		foreach ($aRet as $v){
			$aCurrency[$v['cur_code']] = $v['cur_name'];
		}
		$aOrder['curList'] = $aCurrency;
		$this->pagedata['order'] = $aOrder;

		$this->pagedata['order_edit'] = dirname(__FILE__).'/view/admin/order/order_edit.html';
		$this->pagedata['edit_items'] = dirname(__FILE__).'/view/admin/order/edit_items.html';
		$this->singlepage(dirname(__FILE__).'/view/admin/order/page.html');
	}
	function delete() {
		$actModel= $this->system->loadModel('plugins/group_activity');
		$actModel->delete_order_act($_POST,'group_order');
		if (empty($_POST['order_id'])) {
			echo __('选定记录已删除成功!');
			exit;
		}
		if($this->model->delete($_POST)){
			echo __('选定记录已删除成功!');
		}else{
			echo __('选定记录无法删除!');
		}
	}

	function showAdd(){
		$view = dirname(__FILE__).'/view/admin/order/page.html';
		$this->pagedata['order_new'] = dirname(__FILE__).'/view/admin/order/order_new.html';
		$this->singlepage($view);
	}
	function create(){
		if(!empty($_POST['username'])){
			$objMember = &$this->system->loadModel('member/member');
			$aUser = $objMember->getList('member_id,member_lv_id',array('member_id'=>$_POST['username']),0,1);
			$aUser = $aUser[0];
			if(empty($aUser['member_id'])){
				echo __('<script>alert("不存在的会员名称!")</script>');
				exit;
			}
		}else{
			$aUser = array('member_id' => 0, 'member_lv_id' => 0);
		}
		$_SESSION['tmp_admin_create_order'] = array();
		$_SESSION['tmp_admin_create_order']['member'] = $aUser;

		if($_POST['goods']){
			$aTmp['product_id'][] = $_POST['goods'];
			$objPdt = &$this->system->loadModel('goods/finderPdt');
			$aPdt = $objPdt->getList('goods_id, product_id', $aTmp, 0, count($_POST['goods']));//获取goods对应的商品
			unset($aTmp);
			foreach($aPdt as $key => $row){
				$num = ceil($_POST['goodsnum']);
				if($num > 0){
					$_SESSION['tmp_admin_create_order']['cart']['g']['cart'][$row['goods_id'].'-'.$aPdt[$key]['product_id'].'-na'] = $num;

					$oPromotion = &$this->system->loadModel('trading/promotion');//获取goods对应的商品
					if($pmtid = $oPromotion->getGoodsPromotionId($row['goods_id'], $aUser['member_lv_id'])){
						$_SESSION['tmp_admin_create_order']['cart']['g']['pmt'][$row['goods_id']] = $pmtid;
					}
				}
			}
		}
		if($_POST['package']){
			$aTmp['goods_id'] = $_POST['package'];
			$oPackage = &$this->system->loadModel('trading/package');
			$aPkg = $oPackage->getList('goods_id', $aTmp, 0, count($_POST['package']));//获取package对应的绑定商品
			unset($aTmp);
			foreach($aPkg as $key => $row){
				$num = ceil($_POST['pkgnum'][$aPkg[$key]['goods_id']]);
				if($num > 0){
					$_SESSION['tmp_admin_create_order']['cart']['p'][$row['goods_id']]['num'] = $num;
				}
			}
		}

		if(!$_SESSION['tmp_admin_create_order']['cart']){
			echo __('<script>MessageBox.error("没有购买商品或者购买数量为0!");</script>');
			exit;
		}

		//$objCart = &$this->system->loadModel('trading/cart');
		$objCart = $this->system->loadModel('plugins/group_activity/group_activity_cart');

		$aOut = $objCart->getCheckout($_SESSION['tmp_admin_create_order']['cart'], $aUser, '');
		$aOut['trading']['admindo'] = 1;
		$this->pagedata['has_physical'] = $aOut['has_physical'];
		$this->pagedata['minfo'] = $aOut['minfo'];
		$this->pagedata['areas'] = $aOut['areas'];
		$this->pagedata['currencys'] = $aOut['currencys'];
		$this->pagedata['currency'] = $aOut['currency'];
		$this->pagedata['payments'] = $aOut['payments'];
		$payment = $this->system->loadModel('trading/payment');
		$payment->showPayExtendCon($aOut['payments']);
		$this->pagedata['payments'] = $aOut['payments'];
		$this->pagedata['trading'] = $aOut['trading'];
		if ($this->pagedata['payments']){
			foreach($this->pagedata['payments'] as $key => $val){
				$this->pagedata['payments'][$key]['config']=unserialize($val['config']);
			}
		}
		if($aUser['member_id']){
			$member = &$this->system->loadModel('member/member');
			$addrlist = $member->getMemberAddr($aUser['member_id']);
			foreach($addrlist as $rows){
				if(empty($rows['tel'])){
					$str_tel = __('手机：').$rows['mobile'];
				}else{
					$str_tel = __('电话：').$rows['tel'];
				}
				$addr[] = array('addr_id'=> $rows['addr_id'],'def_addr'=>$rows['def_addr'],'addr_region'=> $rows['area'],
				'addr_label'=> $rows['addr'].__(' (收货人：').$rows['name'].' '.$str_tel.__(' 邮编：').$rows['zip'].')');
			}
			$this->pagedata['trading']['receiver']['addrlist'] = $addr;
			$this->pagedata['is_allow'] = (count($addr)<5 ? 1 : 0);
		}
		$view = dirname(__FILE__).'/view/admin/order/order_create.html';
		$this->display($view);
	}

	function shipping(){
		$_POST['isgroupbuy']=2;
		$aCart = $_SESSION['tmp_admin_create_order']['cart'];
		$aMember = $_SESSION['tmp_admin_create_order']['member'];

		//$sale = &$this->system->loadModel('trading/sale');
		$sale = $this->system->loadModel('plugins/group_activity/group_activity_sale');

		$trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true);

		$shipping = &$this->system->loadModel('trading/delivery');
		$aShippings = $shipping->getDlTypeByArea($_POST['area']);
		foreach($aShippings as $k=>$s){
			$aShippings[$k]['price'] = cal_fee($s['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$s['price']);
			$s['pad']==0?$aShippings[$k]['has_cod'] = 0:$aShippings[$k]['has_cod'] = 1;
			if($s['protect']==1){
				$aShippings[$k]['protect'] = max($trading['totalPrice']*$s['protect_rate'],$s['minprice']);
			}else{
				$aShippings[$k]['protect'] = false;
			}
		}

		$this->pagedata['shippings'] = $aShippings;
		$this->display('shop:cart/checkout_shipping.html');
	}
	function total(){
		$_POST['isgroupbuy']=2;
		$aCart = $_SESSION['tmp_admin_create_order']['cart'];
		$aMember = $_SESSION['tmp_admin_create_order']['member'];
		$tarea = explode(':', $_POST['area'] );
		$_POST['area'] = $tarea[count($tarea)-1];
		//$objCart = &$this->system->loadModel('trading/cart');
		$objCart = $this->system->loadModel('plugins/group_activity/group_activity_cart');
		$this->pagedata['trading'] = $objCart->checkoutInfo($aCart, $aMember, $_POST);
		$view=PLUGIN_DIR.'/app/group_activity/view/shop/cart/checkout_total.html';
		$this->display($view);
	}
	function doCreate(){
		$this->begin('index.php?ctl=plugins/group_order&act=index');
		$_POST['isgroupbuy']='2';
		$aCart = $_POST['aCart'];
		$aCart = $_SESSION['tmp_admin_create_order']['cart'];
		$aMember = $_POST['aMember'];
		$aMember = $_SESSION['tmp_admin_create_order']['member'];
		unset($_SESSION['tmp_admin_create_order']);

		//$order = &$this->system->loadModel('trading/order');
		$order = $this->system->loadModel('plugins/group_activity/group_activity_order');
		$order->op_id = $this->system->op_id;
		$order->op_name = $this->system->op_name;

		$orderid = $order->create($aCart,$aMember,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);
		$this->end($orderid,__('订单: ').$orderid.__(' 生成成功'));
	}

	function toRefund($orderid){
		if(!$orderid) $orderid = $_POST['order_id'];
		else $_POST['order_id'] = $orderid;

		$_POST['opid'] = $this->system->op_id;
		$_POST['opname'] = $this->system->op_name;
		$this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);

		//$objOrder = &$this->system->loadModel('trading/order');
		$objOrder = $this->system->loadModel('plugins/group_order');

		$objOrder->op_id = $this->system->op_id;
		$objOrder->op_name = $this->system->op_name;
		if($objOrder->refund($_POST)){    //处理订单收款
			$this->end(true, __('退款成功'));
		}else{
			$this->end(false, __('退款失败'));
		}
	}
	/**
     * toPayed
     * 后台手动到款
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
	function toPayed($orderid){
		if(!$orderid) $orderid = $_POST['order_id'];
		else $_POST['order_id'] = $orderid;

		$_POST['opid'] = $this->system->op_id;
		$_POST['opname'] = $this->system->op_name;
		$this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);
		//$objOrder = &$this->system->loadModel('trading/order');
		$objOrder = $this->system->loadModel('plugins/group_order');

		$objOrder->op_id = $this->system->op_id;
		$objOrder->op_name = $this->system->op_name;
		if($objOrder->toPayed($_POST, true)){    //处理订单收款
			$this->end(true, __('支付成功'));
		}else{
			$this->end(false, __('支付失败'));
		}
	}
	/**
     * cancel
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
	function cancel($orderid){
		//$objOrder = &$this->system->loadModel('trading/order');
		$objOrder = $this->system->loadModel('plugins/group_order');
		if($orderid){
			$objOrder->op_id = $this->system->op_id;
			$objOrder->op_name = $this->system->op_name;
			$order = $objOrder->toCancel($orderid);
			echo __('订单已作废');
			exit;
		}
		echo __('<span failedSplash="true">订单作废操作失败</span>');
	}

	function recycle() {
		if (empty($_POST['order_id'])) {
			echo '选定记录删入回车站';
			exit;
		}
		$this->model->deleteGroupOrder($_POST);
		parent::recycle();
	}
	function active() {
		if (empty($_POST['order_id'])) {
			echo '选定记录已从回车站恢复';
		}
		$this->model->activeGroupOrder($_POST);
		parent::active();
	}
	/*
	function recycleIndex($options=null){
	//parent::recycleIndex($options);
	$filter=array('disabled'=>'true');
	foreach ($this->model->getDeleteGroupOrder() as $key=>$row) {
	$filter['order_id'][]=$row['order_id'];
	}

	$o = &$this->model;
	$o->disabledMark='recycle';
	$this->index(array('disabled'=>'true'));
	}
	*/
	function showRefund($orderid){/*{{{*/
		if(!$orderid){
			echo __('订单号传递出错');
			return false;
		}
		$this->pagedata['orderid'] = $orderid;
		$objOrder = &$this->system->loadModel('trading/order');
		$aORet = $objOrder->getFieldById($orderid);

		$objPayment = &$this->system->loadModel('trading/payment');
		$aPayment = $objPayment->getMethods();
		$this->pagedata['payment'] = $aPayment;
		$aPayid = $objPayment->getPaymentById($aORet['payment']);
		$this->pagedata['payment_id'] = $aORet['payment'];
		$this->pagedata['op_name'] = 'admin';
		$this->pagedata['typeList'] = array('online'=>__("在线支付"), 'offline'=>__("线下支付"), 'deposit'=>__("预存款支付"));
		$this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');

		if($aORet['member_id'] > 0){
			$objMember = &$this->system->loadModel('member/member');
			$aRet = $objMember->getMemberInfo($aORet['member_id']);
			$this->pagedata['member'] = $aRet;
		}else{
			$this->pagedata['member'] = array();
		}
		$this->pagedata['order'] = $aORet;

		$aRet = $objPayment->getAccount();
		$aAccount = array(__('--使用已存在帐户--'));
		foreach ($aRet as $v){
			$aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
		}
		$this->pagedata['pay_account'] = $aAccount;
		$oPointHistory = &$this->system->loadModel('trading/pointHistory');
		$this->pagedata['score_g'] = $this->pagedata['score_g'] - $oPointHistory->getOrderHistoryGetPoint($orderid);

		$view = dirname(__FILE__).'/view/admin/order/orderrefund.html';
		$this->display($view);
	}
	function showPayed($orderid){
		if(!$orderid){
			echo __('订单号传递出错');
			return false;
		}
		$this->pagedata['orderid'] = $orderid;
		$objOrder = &$this->system->loadModel('trading/order');
		$aORet = $objOrder->getFieldById($orderid);

		$oCur = &$this->system->loadModel('system/cur');
		$aCur = $oCur->getSysCur();
		$aORet['cur_name'] = $aCur[$aORet['currency']];

		$objPayment = &$this->system->loadModel('trading/payment');
		$aPayment = $objPayment->getMethods();
		$this->pagedata['payment'] = $aPayment;
		$aPayid = $objPayment->getPaymentById($aORet['payment']);
		$this->pagedata['payment_id'] = $aORet['payment'];
		$this->pagedata['op_name'] = 'admin';
		$this->pagedata['typeList'] = array('online'=>__("在线支付"), 'offline'=>__("线下支付"), 'deposit'=>__("预存款支付"));
		$this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');

		if($aORet['member_id'] > 0){
			$objMember = &$this->system->loadModel('member/member');
			$aRet = $objMember->getMemberInfo($aORet['member_id']);
			$this->pagedata['member'] = $aRet;
		}else{
			$this->pagedata['member'] = array();
		}
		$this->pagedata['order'] = $aORet;

		$aRet = $objPayment->getAccount();
		$aAccount = array(__('--使用已存在帐户--'));
		foreach ($aRet as $v){
			$aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
		}
		$this->pagedata['pay_account'] = $aAccount;

		$view = dirname(__FILE__).'/view/admin/order/orderpayed.html';
		$this->display($view);
	}

}
?>
