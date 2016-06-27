<?php
/**
 * @author chenping
 * @version $Id: mdl.group_activity_order.php 2010-3-26 18:17
 * @package group activity_order
 * @uses shopObject
 *
 */
if (!class_exists('mdl_order')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/trading/mdl.order.php');
}
class mdl_group_order extends mdl_order {
	var $adminCtl = 'plugins/group_order';
	var $defaultCols = 'order_id,order_tips,createtime,total_amount,ship_name,pay_status,shipping,payment,member_id';
	var $hasTag = true;
	var $name='团购预订单';

	function __construct(){
		parent::mdl_order();
	}
	function modifier_payment(&$rows){
		$status = array(0=>__('线下支付'),
		-1=>__('货到付款') );
		foreach($rows as $k => $v){
			if($v < 1) $rows[$k] = $status[$v];
		}
		foreach($this->db->select('SELECT id,custom_name FROM sdb_payment_cfg WHERE id IN ('.implode(',', array_keys($rows)).')') as $r){
			$rows[$r['id']] = $r['custom_name'];
		}
	}

	function modifier_is_normal_order(&$rows){
		$groupModel = $this->system->loadModel('plugins/group_activity');
		foreach ($rows as $key=>$value) {
			if ($groupModel->getTurntoNormalOrder($value)) {
				$rows[$key] = '是';
			}else {
				$rows[$key] = '否';
			}
		}
	}
	function getColumns(){
		$col=parent::getColumns();
		$col['_cmd'] = array('label'=>__('操作'),'width'=>'75','html'=>dirname(__FILE__).'/view/admin/group_order/finder_command.html');
		$col['print_status']['hidden']=true;
		$col['ship_status']['hidden']=true;
		$col['is_normal_order'] = array('label'=>__('是否已生成正式订单'),'width'=>'75','sql'=>'order_id','hidden'=>false,'locked'=>1);
		return $col;
	}
	function _filter($filter){
		$order_id = array();
		$where = array(1);
		$sql = 'SELECT * FROM sdb_group_activity_order_act WHERE 1 AND disabled=\'false\'';

		if ($_GET['p']['act_id']&&is_string($_GET['p']['act_id'])) {
			$sql.=' and act_id='.$_GET['p']['act_id'];
			if (isset($_GET['p']['grouponstate'])) {
				switch ($_GET['p']['grouponstate']){
					case 3:
						$sql.=" and extension_code='succ' ";
						break;
					case 5:
						$sql.=" and extension_code='fail' ";
						break;
					default:
						$sql.=" and extension_code='group_buy' ";
						break;
				}
			}
		}
		if ($filter['extension_code']=='succ') {
			$sql.=' and extension_code=\'succ\'';
		}
		$rows = $this->db->select($sql);
		if ($rows) {
			foreach ($rows as $row) {
				if ($row['order_id']) {
					$order_id[] = $row['order_id'];
				}
			}
			if ($order_id) {
				$where[] = 'order_id  in('.implode(',',$order_id).')';
			}
		}elseif($this->disabledMark=='normal') {
			$where[] = 'order_id=-1';
		}
		$where_order_id=array(1);
		if ($this->use_recycle&&$this->disabledMark=='recycle') {
			foreach ($this->getDeleteGroupOrder() as $key=>$row) {
				$w[]=$row['order_id'];
			}
			if ($w) {
				$where_order_id[] = 'order_id  in('.implode(',',$w).')';
			}
		}
		return  parent::_filter($filter).' and '.implode(' and ',$where).' and '.implode(' OR ',$where_order_id);
	}
	/**
	 * 取打印显示
	 *
	 * @param unknown_type $rows
	 */
	function modifier_print_status(&$rows){}
	/**
	 * 更新团购预订单状态 预订单转为正式订单
	 */
	function toNormalOrder($orderid){
		if ($row=$this->db->selectrow("select * from sdb_group_activity_order_act where extension_code='group_buy' and order_id='$orderid'")) {
			$sql = "UPDATE sdb_group_activity_order_act SET extension_code='normal' WHERE order_id='$orderid'";
			$this->db->exec($sql);
		}
	}
	function getList($cols,$filter='',$start=0,$limit=20,$orderType=null){
		$rows=parent::getList($cols,$filter,$start,$limit,$orderType);
		$groupModel = $this->system->loadModel('plugins/group_activity');
		foreach ($rows as $key=>$row) {
			$group_order = $groupModel->getTurntoNormalOrder($row['order_id']);
			if ($group_order) {
				$rows[$key]['total_amount']=$group_order['group_total_amount'];
				$rows[$key]['pay_status'] = 1;
			}
		}
		return $rows;
	}

	function refund($aData){
		$aOrder = $this->load($aData['order_id']);
		if(!$aOrder){
			$this->system->error(501);
			return false;
			exit;
		}
		if(!$this->checkOrderStatus('refund', $aOrder)){
			$this->setError(10001);
			trigger_error(__('退款失败: 订单状态锁定'),E_USER_ERROR);
			return false;
			exit;
		}

		$payMoney = $aOrder['amount']['payed'] - $aOrder['amount']['cost_payment'];
		$aUpdate['pay_status']= '5';    //预设订单状态
		$aUpdate['payed'] = $aOrder['amount']['cost_payment'];    //预设订单支付金额
		if(isset($aData['money'])){    //从退款单提交进入
			if($aData['money'] > $payMoney || $aData['money'] <= 0){
				$this->setError(10001);
				trigger_error(__('退款金额不在订单已支付金额范围'),E_USER_ERROR);
				return false;
			}

			if($payMoney > $aData['money']){
				$aUpdate['pay_status'] = '4';
				$aUpdate['payed'] = $aOrder['amount']['payed'] - $aData['money'];
			}
			$paymentId = $aData['payment'];
			$payMethod = $aData['payment'];
			$payMoney = $aData['money'];
		}else{    //未从退款单提交进入
			$paymentId = $aOrder['payment'];
			$payMethod = __("手工");
			switch($aOrder['paytype']){
				case 'DEPOSIT':
					$aData['pay_type'] = 'deposit';
					break;
				case 'OFFLINE':
					$aData['pay_type'] = 'offline';
					break;
				default:
					$aData['pay_type'] = 'online';
					break;
			}
		}

		if($aData['pay_type'] == 'deposit'){
			$oAdvance = &$this->system->loadModel("member/advance");
			if(!$oAdvance->checkAccount($aOrder['member_id'], 0, $message)){
				trigger_error(__('支付失败：').$message,E_USER_ERROR);
				return false;
				exit;
			}
		}

		$aRefund['money'] = $payMoney;
		$aRefund['order_id'] = $aData['order_id'];
		$aRefund['send_op_id'] = intval($aData['opid']);
		$aRefund['pay_type'] = $aData['pay_type'];
		$aRefund['member_id'] = $aOrder['member_id'];
		$aRefund['account'] = $aData['account'];
		$aRefund['pay_account'] = $aData['pay_account'];
		$aRefund['bank'] = $aData['bank'];
		$aRefund['title'] = 'title';
		$aRefund['currency'] = $aOrder['currency'];
		$aRefund['payment'] = $paymentId;
		$aRefund['paymethod'] = $payMethod;
		$aRefund['status'] = 'sent';
		$aRefund['memo'] = ($aData['memo'] ? $aData['memo'].'#' : '').__('管理员后台退款产生');

		$oRefund = &$this->system->loadModel('trading/refund');
		$refund_id = $oRefund->create($aRefund);
		if(!$refund_id){
			$this->setError(10001);
			trigger_error(__('退款单不能正常生成'),E_USER_ERROR);
			return false;
		}
		$this->addLog(__('订单退款').$payMoney, $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , __('退款'));

		$aUpdate['acttime'] = time();
		/**
        *    @function    toEdit():编辑订单
        */
		if(!$this->toEdit($aData['order_id'], $aUpdate)){
			$this->setError(10001);
			trigger_error(__('更新订单状态失败'),E_USER_ERROR);
			return false;
		}
		$freez_status = $this->freez_time();
		if($freez_status == 'pay' || $freez_status == 'order'){
			if($aUpdate['pay_status']=='5' && $aOrder['ship_status'] == '0'){
				$this->toUnfreez($aData['order_id']);
				$objGift = &$this->system->loadModel('trading/gift');
				$rsG= $this->db->select('SELECT gift_id,nums  FROM sdb_gift_items   WHERE order_id = '.$aData['order_id'].' ');
				foreach($rsG as $key=>$val){
					$objGift->unFreezStock($val['gift_id'],$val['nums']);
				}
			}
		}

		if($aData['pay_type'] =='deposit'){    //预存款付款
			$message .= __('预存款退款：(团购订单)#O{').$aData['order_id'].'}#';
			if(!$oAdvance->add($aOrder['member_id'], $payMoney, $message, $message, '', $aData['order_id'] ,'' ,__('预存款退款'))){
				return false;
			}
		}

		$aPara['pay_status'] = $aUpdate['pay_status'];
		$aPara['order_id'] = $aData['order_id'];
		$aPara['return_score'] = $aData['return_score'];
		$aPara['money'] = $aData['money'];
		$this->toReturnPoint($aPara);

		$eventData['order_id'] = $aData['order_id'];
		$eventData['total_amount'] = $aOrder['amount']['total'];
		$eventData['is_tax'] = $aOrder['is_tax'];
		$eventData['member_id'] = $aOrder['member_id'];
		//modify chenping 2010-4-9 17:31
		$this->fireEvent('scheduleRefund', $eventData,$aOrder['member_id']);

		return $aPara['pay_status'];
	}

	function toCancel($orderid){
		$aOrder = $this->load($orderid);
		if(!$aOrder){
			$this->system->error(501);
			return false;
			exit;
		}
		if(!$this->checkOrderStatus('cancel', $aOrder)){
			$this->setError(10001);
			trigger_error(__('订单状态锁定'),E_USER_ERROR);
			return false;
			exit;
		}

		$sqlString = "UPDATE sdb_orders SET status = 'dead',last_change_time='".time()."' WHERE order_id=".$this->db->quote($orderid);
		$this->db->query($sqlString);

		if($this->freez_time() == 'order'){
			$this->toUnfreez($orderid);    //冻结库存解冻
		}
		$this->_info['order_id'] = $orderid;
		$this->addLog(__('订单作废'), $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , __('作废'));

		$aPara['order_id'] = $orderid;
		$aPara['total_amount'] = $aOrder['amount']['total'];
		$this->toCancelPoint($aPara);
		$aPara['is_tax'] = $aOrder['is_tax'];
		$aPara['member_id'] = $aOrder['member_id'];

		//modify by chenping 2010-4-9 17:33
		$this->fireEvent('scheduleCancel', $aPara,$aOrder['member_id']);
		return true;
	}

	/**
      *    @params:
      *    values:$data=array(
      *                     order_id
      *                     payment_id  //支付单号
      *                     pay_type    //支付单类型：（deposit预存款）
      *                     money       //支付金额(已折算本位币)
      *                     currency    //支付货币
      *                     member_id   //支付会员
      *                     paymethod   //支付方式名称
      *                     status      //支付前的支付单状态
      *                     pay_assure  //是否担保交易 true/false
      *                     pay_account //发邮件时的付款人
      *                         )
      */
	function payed($data, &$message){
		if(empty($data['order_id'])){
			$message .= '支付单：订单号{'.$info['payment_id'].'}没有对应订单号';
			return false;
		}
		$aOrder = $this->getFieldById($data['order_id'], array('total_amount','payed','pay_status','ship_status','status','member_id','is_tax'));
		$aOrder['order_id'] = $data['order_id'];
		if($aOrder['pay_status'] == '0' || $aOrder['pay_status'] == '3' || ($aOrder['pay_status'] == '2' && !$data['pay_assure'])){    //如何是未支付或者部分支付或者支付中
			if($data['pay_type'] =='deposit' && ($aOrder['pay_status'] == '0' || $aOrder['pay_status'] == '3')){  //预存款付款
				$message .= '预存款支付：订单号{'.$data['order_id'].'}';
				$oAdvance = $this->system->loadModel("member/advance");
				if(!$oAdvance->deduct($data['member_id'], $data['money'], $message, $message, '', $data['order_id'] ,'' , '预存款支付')){
					return false;
				}
			}
		}

		if($aOrder['total_amount'] - $aOrder['payed'] <= $data['money']){
			/**
            *    @branch:全额付款
            */
			$aOrder['pay_status']= ($data['pay_assure'] ? '2':'1');    //如果是担保交易则2，否则已支付1
			$aOrder['payed'] = $aOrder['total_amount'];
		}else{  //部分付款
			$aOrder['pay_status'] = '3';
			$aOrder['payed'] = $aOrder['payed'] + $data['money'];

			/*            if($aData['pay_status'] == 1){
			$lastMoney = $nonPay - $payMoney;
			$this->addLog(__('更改订单金额:减少').$lastMoney);
			$aUpdate['pay_status'] = 1;
			$aUpdate['discount'] += $lastMoney;
			}else{
			$aUpdate['pay_status'] = 3;
			}*/
		}
		$aOrder['acttime'] = time();
		$aOrder['last_change_time'] = time();
		if(!$this->toEdit($data['order_id'], $aOrder)){
			$message .= __('更新订单失败');
			return false;
		}

		$this->addLog('订单'.$aOrder['order_id'].'付款'.($data['pay_assure'] ? '（担保交易）':'').$data['money'], $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , '付款');
		if($aOrder['status'] != 'active'){  //死单被支付的情况
			return true;
		}

		if($aOrder['pay_status']=='1' || $aOrder['pay_status']=='2'){
			if($this->freez_time()=='pay'){
				$missProduct=array();
				$objCart = &$this->system->loadModel('trading/cart');
				$objGift = &$this->system->loadModel('trading/gift');
				$rs = $this->db->select('SELECT product_id,nums,name  FROM sdb_order_items  WHERE order_id = '.$aOrder['order_id'].' ');
				$rsG= $this->db->select('SELECT gift_id,nums  FROM sdb_gift_items   WHERE order_id = '.$aOrder['order_id'].' ');

				foreach($rs as $k=>$p){
					if($p['nums']>=0){
						$this->db->exec("UPDATE sdb_products SET freez = freez + ".intval($p['nums'])." WHERE product_id = ".intval($p['product_id']));
					}
					$this->db->exec("UPDATE sdb_products SET freez = ".intval($p['nums'])." WHERE product_id = ".intval($p['product_id'])." AND freez IS NULL");
				}
				foreach($rsG as $key=>$val){
					$objGift->freezStock($val['gift_id'],$val['nums']);
				}
			}
			if($this->system->getConf('system.auto_delivery')){ //检测付款前的订单状态,如果是刚付款立即发货
				$this->delivery($aOrder, false);
			}
		}

		if ($aOrder['pay_status'] == '1'){
			$aPara = $aOrder;
			$aOrder['money'] = $data['money'];
			$aOrder['pay_account'] = $data['pay_account'];
			$this->toCoupon($aOrder);  //给优惠券
			$this->toPoint($aOrder);  //给积分
			$this->toExperience($aOrder);

			$status = &$this->system->loadModel('system/status');
			if($data['order_id'] && ($aOrder['pay_status'] == '1' || $aOrder['pay_status'] == '2')){
				if($aOrder['ship_status'] == '1'){
					$status->add('ORDER_SUCC');
					$status->add('REVENUE', $aOrder['total_amount']);
				}else{
					$status->count_order_to_dly();
				}
			}
			$status->count_order_to_pay();
			//$s = $this->fireEvent('payed', $aOrder,$aOrder['member_id']);
			//modify chenping 2010-04-13 11:04
			$s = $this->fireEvent('schedulePayed',$aOrder,$aOrder['member-id']);
		}

		return $aOrder['pay_status'];
	}

	/**
	 * 过滤掉已生成正式订单的预订单
	 *
	 * @param unknown_type $orderIds
	 */
	function deleteGroupOrder(&$orderIds){
       
		if ($orderIds['order_id'][0]=='_ALL_') {
           
			unset($orderIds['order_id']);
			foreach ($this->db->select("SELECT * FROM sdb_group_activity_order_act ") as $k=>$v) {
				if ($v['extension_code']=='succ') {
					$this->db->exec("UPDATE sdb_group_activity_order_act SET disabled='true' WHERE order_id=$v[order_id]");
				}else {
					$orderIds['order_id'][]=$v['order_id'];
				}
			}
			return $orderIds;
		}
        
		foreach ($orderIds['order_id'] as $key=>$order_id) {
			 $sql = "SELECT * FROM sdb_group_activity_order_act WHERE order_id=".$order_id." AND extension_code='succ' ";
			if ($this->db->selectrow($sql)) {
                $order_statues=1;
                
				//$this->db->exec("UPDATE sdb_group_activity_order_act SET disabled='true' WHERE order_id=$order_id");
				//unset($orderIds['order_id'][$key]);
			}
		}
        if($order_statues){
           echo __('删除生成正式订单的团购订单，也将删除正式订单');
        }
	}
	function activeGroupOrder(&$orderIds){
		if ($orderIds['order_id'][0]=='_ALL_') {
			unset($orderIds['order_id']);
			foreach ($this->db->select("SELECT * FROM sdb_group_activity_order_act ") as $k=>$v) {
				if ($v['extension_code']=='succ') {
					$this->db->exec("UPDATE sdb_group_activity_order_act SET disabled='false' WHERE order_id=$v[order_id]");
				}else {
					$orderIds['order_id'][]=$v['order_id'];
				}
			}
			return $orderIds;
		}
		foreach ($orderIds['order_id'] as $key=>$order_id) {
			$sql = "SELECT * FROM sdb_group_activity_order_act WHERE order_id=".$order_id." AND extension_code='succ' AND disabled='true' ";
			if ($this->db->selectrow($sql)) {
				$this->db->exec("UPDATE sdb_group_activity_order_act SET disabled='false' WHERE order_id=$order_id");
			}
		}
	}
	function getDeleteGroupOrder(){
		$sql = "SELECT order_id FROM sdb_group_activity_order_act WHERE extension_code='succ' AND disabled='true' ";
		return $this->db->select($sql);
	}

	function fireEvent($action,$data,$memberid){
		if (!$data['email'])
		$data['email'] = $data['ship_email'];

		//parent::fireEvent($action,$data,$memberid);
		//$trigger = &$this->system->loadModel('system/trigger');
		$trigger = $this->system->loadModel('plugins/group_activity/group_activity_trigger');
		return $trigger->object_fire_event($action,$data, $memberid,$this);
	}


}
?>
