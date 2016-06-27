<?php
/**
 * @author shopex
 * @time: 2010-3-8 15:53
 * //===========================
 * mdl.group_activity.php
 * 团购活动模型层
 * //===========================
 *
 */
 if (!class_exists('shopObject')){
 define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
 require(CORE_INCLUDE_DIR.'/shopObject.php');
 }
class mdl_group_activity extends shopObject  {
	var $adminCtl	= 'plugins/group_activity';
	var $tableName	= 'sdb_group_activity_purchase';
	var $idColumn	= 'act_id';
	var $name		= '团购';
	var $defaultCols= 'act_id,gid,state,_cmd,deposit,limitnum,goodsnum,ordernum,current_price';
	var $appendCols	= 'end_time';
	var $defaultOrder=array('end_time',' desc');
	var $has_tag	= true;
	var $textColumn	= 'act_id';
	var $typeName	= 'group';

	function mdl_group_activity(){
       
		parent::shopObject();
	}

	/**
	 * 保存团购活动
	 *
	 * @param unknown_type $ActParam
	 */
	function save($ActParam){
       
		if (isset($ActParam['act_id'])) {
			$rs=$this->db->query('SELECT * FROM '.$this->tableName.' WHERE act_id=\''.$ActParam['act_id'].'\'');
			 $sql=$this->db->getUpdateSQL($rs,$ActParam);
          
			if (!$sql || $this->db->exec($sql)) {
				return $ActParam['act_id'];
			} else {
				return false;
			}
		}else {
			$rs=$this->db->query('SELECT * FROM '.$this->tableName.' WHERE 0=1 ');
			$sql=$this->db->getInsertSQL($rs,$ActParam);
			if (!$sql || $this->db->exec($sql)) {
				return $this->db->lastInsertId();
			}else {
				return false;
			}
		}
	}

	function getColumns(){
		$col=parent::getColumns();
		$col['_cmd'] = array('label'=>__('操作'),'width'=>'150','html'=>dirname(__FILE__).'/view/admin/group_activity/finder_command.html');
		$col['goodsnum'] = array('label'=>__('已预定商品数'),'type'=>'number','width'=>75,'sql'=>'1');
		$col['ordernum'] = array('label'=>__('订单数'),'type'=>'number','width'=>75,'sql'=>'1');
		$col['current_price'] = array('label'=>__('当前价格'),'type'=>'money','width'=>75,'sql'=>'1');
		return $col;
	}

	function getList($cols,$filter='',$start=0,$limit=20,$orderType=null){
		$this->chgState();
		$rows=parent::getList($cols,$filter,$start,$limit,$orderType);
		foreach ($rows as $key=>$row) {
			$rows[$key]['goodsnum'] = $this->getGoodsNum($row['act_id']);
			$rows[$key]['ordernum'] = $this->getOrderNum($row['act_id']);
			$rows[$key]['ordernum'].="<a href='index.php?ctl=plugins/group_order&act=index&p[act_id]=$row[act_id]' target='_self' style='padding-left:20px;color:blue;'>查看</a>";
			$rows[$key]['current_price'] = $this->getCurrentPrice($row['act_id'],$rows[$key]['goodsnum']);
			/*
			if ($row['state']==2&&($row['end_time']<time())) {
			$sql = 'UPDATE '.$this->tableName.' SET state=4 WHERE act_id=\''.$row['act_id'].'\'';
			$this->db->exec($sql);
			}
			*/
		}
		return $rows;
	}
    function get_group_byid($goods_id){
        $sql='select  *  from '.$this->tableName.' where gid='.$goods_id.' and state=2';
        return $this->db->selectrow($sql);
    }
     function get_group_byidtrue($goods_id,$limit){
          if($goods_id){
        $str_goods_id=implode(',',$goods_id);
         $sql='select * from '.$this->tableName.' WHERE  gid  in ('.$str_goods_id.')   limit 0,'.$limit;
        }else{
         $sql='select * from '.$this->tableName.' where disabled="false"  limit 0,'.$limit;
        }
        return   $this->db->select($sql);
    }
     function get_group_list(){
        $sql='select  gid  from '.$this->tableName.' where  state  in (2,1)  and disabled="false"';
        return $this->db->select($sql);
    }
    function get_group_nums_list($goods_id){
      
        $sql='select  *  from sdb_products where  goods_id='.$goods_id;
        return $this->db->select($sql);
    }
	/**
	 * 改变活动状态
	 */
	function chgState(){
		$sql = "SELECT act_id,limitnum,end_time FROM ".$this->tableName." WHERE state=2 ";
		$rows = $this->db->select($sql);
		foreach ($rows as $row) {
			if ($row['end_time']<time()|| (intval($row['limitnum'])==intval($this->getGoodsNum($row['act_id']))&&$row['limitnum']!=0)) {
				$sql = 'UPDATE '.$this->tableName.' SET state=4 WHERE act_id=\''.$row['act_id'].'\'';
				$this->db->exec($sql);
			}
		}
	}
	function chgStateByGid($gid){
		$sql = "SELECT act_id,limitnum,end_time FROM ".$this->tableName." WHERE state=2 and gid='$gid'";
		$row = $this->db->selectrow($sql);
		if ($row) {
			if ($row['end_time']<time()|| (intval($row['limitnum'])==intval($this->getGoodsNum($row['act_id']))&&$row['limitnum']!=0)) {
				$sql = 'UPDATE '.$this->tableName.' SET state=4 WHERE act_id=\''.$row['act_id'].'\' and gid=\''.$gid.'\'';
				$this->db->exec($sql);
			}
		}
	}
	/**
	 * 如果预团购数达到限购数量，改变活动状态
	 */
	function chgStateByLimit(){

	}
	/**
	 * 得到已预定商品数
	 *
	 * @param unknown_type $act_id
	 */
	function getGoodsNum($act_id){
		$actInfo = $this->getFieldById($act_id);
		//$orderModel = $this->system->loadModel('trading/order');
        
		 $sql ="SELECT * FROM sdb_group_activity_order_act WHERE act_id='$act_id' ";
		if ($actInfo['state']==4 || $actInfo['state']==2) {
			$sql.=" and extension_code='group_buy'";
		}
		$orderAct = $this->db->select($sql);
		$num=0;
		foreach ($orderAct as $key=>$value) {
			//$orderNum = $orderModel->getFieldById($value['order_id'],array('itemnum'));
			$orderNum=$this->db->selectrow("select itemnum,payed from sdb_orders where order_id='$value[order_id]' and disabled='false' and status!='dead' and pay_status not in('4','5') ");
             $num=$num+$orderNum['itemnum'];
			if (floatval($value['group_total_amount'])!=floatval($orderNum['payed'])) {
				continue;
			}
        
			

		}
      
		return $num;
		/*
		$sql ="SELECT * FROM sdb_group_activity_order_act WHERE act_id='$act_id'";
		$orderAct = $this->db->select($sql);
		if ($orderAct) {
		foreach ($orderAct as $key=>$value) {
		if ($value['order_id']) {
		$order_id[]=$value['order_id'];
		}
		}
		$sql = "SELECT sum(nums) as goodsnum  FROM  sdb_order_items WHERE order_id in(".implode(',',$order_id).")";
		$goodsnum = $this->db->selectrow($sql);
		return $goodsnum['goodsnum'];
		}else {
		return 0;
		}
		*/
	}
	/**
	 * 得到订单数
	 *
	 * @param unknown_type $act_id
	 */
	function getOrderNum($act_id){
		$sql = "SELECT COUNT(*)  as ordernum FROM	sdb_group_activity_order_act AS a LEFT JOIN sdb_orders AS o ON(a.order_id=o.order_id) WHERE a.act_id='$act_id'  AND o.disabled='false'";
		$ordernum = $this->db->selectrow($sql);
		$onum = $ordernum ? $ordernum['ordernum'] : 0;
		return $onum;
	}
	/**
	 * 得到当前的价格
	 *
	 * @param unknown_type $act_id
	 */
	function getCurrentPrice($act_id,$goodsnum=0){
		$actInfo = $this->getFieldById($act_id);
		$ext_info = unserialize($actInfo['ext_info']);
		foreach ($ext_info as $k=>$v) {
			$price_step[$v['num']] = $v['price'];
		}
		krsort($price_step);
		if (!$goodsnum) {
			/*
			$sql = "SELECT SUM(nums) as goodsnum FROM  	sdb_group_activity_schedule_order_items WHERE act_id='$row[act_id]'";
			$count = $this->db->selectrow($sql);
			$goodsnum=$count['goodsnum'];
			*/
			$goodsnum=$this->getGoodsNum($act_id);
		}
		foreach ($price_step as $n=>$p) {
			if ($goodsnum>=$n) {
				$current_price = $p;
				break;
			}
		}
		$key_num=array_keys($price_step);
		sort($key_num);
		$priceByMinNum=$price_step[$key_num[0]];
		//得到商品的销售价
		$goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo = $goodsModel->getFieldById($actInfo['gid'],array('price'));

		$current_price = $current_price ? $current_price : $goodsInfo['price'];
		return $current_price;
	}
	function _columns(){
		$schema = &$this->system->loadModel('utility/schemas');
		$table = 'purchase';
		if(file_exists(PLUGIN_DIR.'/app/group_activity/dbschema/'.$table.'.php')){
			$define = require(PLUGIN_DIR.'/app/group_activity/dbschema/'.$table.'.php');
			$this->__table_define = &$db[$table]['columns'];
		}
		return $this->__table_define;
	}

	function getFieldById($act_id,$aField=array('*')){
		return $this->db->selectrow('SELECT '.implode(',',$aField).' FROM '.$this->tableName.' WHERE act_id=\''.$act_id.'\' and disabled=\'false\'');
	}

	function getProduct($product,&$message){
		if ($rows=$this->db->selectrow('SELECT gid FROM '.$this->tableName.' WHERE gid=\''.$product['products'].'\'')) {
			$message='该商品已参加团购活动';
			return true;
		}else {	//在存在其他团购活动中
			return false;
		}
	}
	/**
	 * 根据传入的条件判断活动状态
	 * 
	 * 
	 * 
	 *
	 *
	 * @param unknown $condition
	 * @param unknown $param
	 * @return unknown
	 */
	function setState($param){
		if ($param['act_open']=='false') {
			return 1;
		}
        
		$minNum=$this->getActMinNum($param['act_id']);
		if (time()<$param['start_time']) {
			$state = 1;
		}elseif (time()>=$param['start_time']&&time()<$param['end_time']){
			$state = 2;
		}elseif (($param['end_time']<time() || $param['goodsnum']==$param['limitnum'])&&($param['goodsnum']>=$minNum)){
			$state = 3;
		}elseif (($param['end_time']<time() || $param['goodsnum']==$param['limitnum'])&&($param['goodsnum']<$minNum)){
			$state = 4;
			//}elseif (){
			//	$state = 5;
			//}else {
			//	$state = 1;
		}
		return $state;
	}

    function adminsetState($param){
		if ($param['act_open']=='false') {
			return 1;
		}
        
		$minNum=$this->getActMinNum($param['act_id']);
		if (time()<$param['start_time']) {
			$state = 1;
		}elseif (time()>=$param['start_time']&&time()<$param['end_time']){
			$state = 2;
		}elseif ($param['end_time']<time()){
			$state = 4;
			//}elseif (){
			//	$state = 5;
			//}else {
			//	$state = 1;
		}
		return $state;
	}
     


	/**
	 * 得到活动价格阶梯中最小金额数量
	 *
	 * @return unknown
	 */
	function getActMinNum($act_id){
		$actInfo = $this->getFieldById($act_id,array('ext_info'));
		$price_step = unserialize($actInfo['ext_info']);
		foreach ($price_step as $key=>$value) {
			$tmp[$value['num']] = $value['price'];
		}
		$key_nums=array_keys($tmp);
		sort($key_nums);
		return $key_nums[0];
	}
	/**
	 * 得到有效的团购信息 根据活动id
	 *
	 * @param unknown_type $actId
	 * @return unknown
	 */
	function getValidGoodsActivityByActId($actId){
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE act_id=\''.$act_id.'\' AND disabled=\'false\' AND act_open=\'true\' AND \''.time().'\' BETWEEN start_time AND end_time AND state=\'2\'';
		return $this->db->selectrow($sql);
	}

	/**
	 * 得到有效的团购信息 根据商品id
	 *
	 * @param unknown_type $gid
	 * @return unknown
	 */
	function getValidGoodsActivityByGid($gid){
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE gid=\''.$gid.'\' AND disabled=\'false\' AND act_open=\'true\'  AND state=\'2\' AND \''.time().'\' BETWEEN start_time AND end_time';
		return $this->db->selectrow($sql);
	}

	/**
	 * 得到开启的活动
	 *
	 * @param unknown_type $gid
	 */
	function getOpenActivityByGid($gid){
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE gid=\''.$gid.'\' AND disabled=\'false\' AND  act_open=\'true\'';
		return  $this->db->selectrow($sql);
	}

	/**
	 * 团购状态描述
	 *
	 * @param unknown_type $state
	 * @return unknown
	 */
	function getstate($state){
		switch ($state){
			case 1:
				$descstate='未开始';
				break;
			case 2:
				$descstate='进行中';
				break;
			case 3:
				$descstate='已结束（成功）';
				break;
			case 4:
				$descstate='已结束，待处理';
				break;
			case 5:
				$descstate='已结束（失败）';
				break;
			default:
				$descstate='未开始';
				break;

		}
		return $descstate;
	}


	/**
	 * 关闭活动
	 *
	 * @param unknown_type $act_id
	 */
	function doClose($act_id){
		$sql = "update ".$this->tableName." SET act_open='false',state=1  WHERE act_id='$act_id'";
		//$sql = "update ".$this->tableName." SET act_open='false' WHERE act_id='$act_id'";
		$this->db->exec($sql);
	}
	/**
	 * 开启活动
	 *
	 * @param unknown_type $act_id
	 */
	function doOpen($act_id,$state=0){
		if ($state) {
			$sql = "UPDATE ".$this->tableName." SET act_open='true' ,state=$state WHERE act_id='$act_id'";
		}else {
			$sql = "UPDATE ".$this->tableName." SET act_open='true'  WHERE act_id='$act_id'";
		}
		$this->db->exec($sql);
	}

	/**
	 *重新计算价格
	 *
	 * @param unknown_type $trading
	 */
	function recalculate_price(&$trading){
		$actInfo = $this->getValidGoodsActivityByGid($trading['products'][0]['goods_id']);
		if ($actInfo) {
			$trading['products'][0]['groupInfo'] = array(
			'current_price' =>$this->getCurrentPrice($actInfo['act_id']),
			'score' => $actInfo['score'],
			'total' =>$this->getCurrentPrice($actInfo['act_id'])*$trading['products'][0]['nums'],
			);
			//团购保证金
			$trading['groupTotal'] = $actInfo['deposit'];

		}

	}
	/**
	 * 得到该商品冻结的库存
	 *
	 * @param unknown_type $gid
	 */
	function getFreezStore($gid){
		$sql = "SELECT 	sum(freez) as freez_store FROM sdb_products WHERE goods_id='$gid'";
		$freezStore=$this->db->selectrow($sql);
		return empty($freezStore) ? 0 : $freezStore['freez_store'];
	}
	/**
	 * 验证是否存在未处理的团购预订单
	 *
	 * @param unknown_type $actId
	 */
	function validateGroupOrder($actId){
       
		$sql = "SELECT * FROM sdb_group_activity_order_act WHERE 1 ";
		if (is_string($actId)) {
			$sql.=" AND act_id='$actId'";
		}else if (is_array($actId)) {
			$sql.=" AND act_id in(".implode(',',$actId['act_id']).")";
		}
		if ($row=$this->db->select($sql)) {
			return true;
		}else {
			return false;
		}
	}
	/**
	 * 得到该活动下的所有有效团购订单
	 */
	function getOrderByActId($act_id){
		$sql =  "SELECT a.* FROM sdb_group_activity_order_act as a left join sdb_orders as o on(a.order_id=o.order_id) WHERE a.act_id='$act_id' and a.extension_code='group_buy' and o.disabled='false'";
		return $this->db->select($sql);
	}
	/**
	 * 宣布成功
	 * $data['final_amount'] = $data['total_amount'] * $data['cur_rate'];
	 *  $aData['total_amount'] = $itemsFund + $aData['cost_freight'] + $aData['cost_protect'] + $aData['cost_payment'] + $aData['cost_tax'] - $aData['discount'] - $aData['pmt_amount'];
	 */
	function doDeclareSuccess($param){
		$orderModel = $this->system->loadModel('trading/order');
		$oCur = $this->system->loadModel('system/cur');
		$oAdvance = $this->system->loadModel("member/advance");
		$oRefund = $this->system->loadModel('trading/refund');
		$memModel= $this->system->loadModel('member/member');

		if ($payment=$this->getPaymentId('deposit',array('id'))) {
			$payment_id=$payment[0]['id'];
		}else {
			trigger_error(__('预付款支付方式必须开启！'),E_USER_ERROR);
			return false;
			exit;
		}
		$actInfo  = $this->getFieldById($param['act_id']);
		$postage = unserialize($actInfo['postage']);
		$goodsnum = $this->getGoodsNum($param['act_id']);

		foreach ($param['order'] as $key=>$order) {
			//得到该订单信息
			$orderInfo= $orderModel->getFieldById($order['order_id']);
			//得到该订单明细
			$orderItems = $orderModel->getItemList($order['order_id']);

			if ($orderInfo['status']=='active'&&($orderInfo['pay_status']==1 || $orderInfo['pay_status']==2)) {//如果已经支付保证金
				/*****************************************
				*			修改商品明细
				*****************************************/
              
				$aItem['price'] = $oCur->formatNumber($param['current_price'],false);
				$aItem['amount'] = intval($orderItems[0]['nums'])*$param['current_price'];
				$aItem['amount'] = $oCur->formatNumber($aItem['amount'],false);
				$aItem['order_id']=$order['order_id'];
				$aItem['product_id']=$orderItems[0]['product_id'];
				if($orderModel->existItem($aItem['order_id'], $aItem['product_id'])){
					$orderModel->editItem($aItem);
				}
				/*****************************************
				*			修改定单金额
				*****************************************/
				//if($orderInfo['is_protect'] != 'true') $orderInfo['cost_protect'] = 0;
				//if($orderInfo['is_tax'] != 'true') $orderInfo['cost_tax'] = 0;
				$aData=$orderInfo;
				//是否免邮
				if ($postage['is_postage']==1) {
					if ($postage['postage_favorable']==1 &&  $orderItems[0]['nums']>=$postage['buycount']) {//单笔订单 购买数达到某值 免邮
						$aData['cost_freight'] = $oCur->formatNumber(0);
						$orderInfo['cost_freight'] = $oCur->formatNumber(0);
					}elseif ($postage['postage_favorable']==2 && $goodsnum>=$postage['buycount']){//商品总订购数达到某值 免邮
						$aData['cost_freight'] = $oCur->formatNumber(0);
						$orderInfo['cost_freight'] = $oCur->formatNumber(0);
					}
				}
               
				 $aData['score_g'] = $actInfo['score'] *  $orderItems[0]['nums'];
				$aData['cost_item'] = $aItem['amount'];
				$aData['order_id']=$order['order_id'];
				//重新计算总价
				$aData['total_amount'] = $aItem['amount'] + $orderInfo['cost_freight'] + $orderInfo['cost_protect'] + $orderInfo['cost_payment'] + $orderInfo['cost_tax'] - $orderInfo['discount'] - $orderInfo['pmt_amount'] ; //pmt_amount促销金额
				$aData['total_amount'] = $oCur->formatNumber($aData['total_amount']);
				//判断是否保证金的金额大于
				if ($orderInfo['total_amount']>$aData['total_amount']) {
					//保证金大，则退多余金额  订单状态：已支付
					$aData['pay_status']=$orderInfo['pay_status'];
					$aData['payed']=$aData['total_amount'];//保证金及其他费用
					//退款到预付款操作
					$payMoney=$orderInfo['total_amount']-$aData['total_amount'];
				}elseif ($orderInfo['total_amount']==$aData['total_amount']){
					//金额不作改变 订单状态：已支付
					$aData['pay_status']=$orderInfo['pay_status'];
					$aData['payed']=$orderInfo['total_amount'];//保证金及其他费用
				}else {
					//保证金额不足 订单状态：部分付款
					$aData['pay_status']=3;
					$aData['payed']=$orderInfo['total_amount'];//保证金及其他费用
				}


				$aData['final_amount'] = $aData['total_amount'] * $orderInfo['cur_rate'];
				$aData['final_amount'] = $oCur->formatNumber($aData['final_amount']);
				$aData['acttime'] = time();
				if($orderModel->toEdit($order['order_id'], $aData )){
					$orderModel->addLog(__('订单编辑'), $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , __('编辑') );
					//如有要求需要退款
					if ($payMoney) {
						//给积分
                     
						$orderModel->toPoint($aData);
                      
						$message= __('预存款退款：#O(团购订单){').$order['order_id'].'}#';
						if(!$oAdvance->add($orderInfo['member_id'], $payMoney, $message, $message, '', $orderInfo['order_id'] ,'' ,__('预存款退款'))){
							trigger_error(__('预存款退款失败'),E_ERROR);
							return false;
							exit;
						}
						//生成退款单据
						$aRefund['money'] = $payMoney;
						$aRefund['order_id'] = $order['order_id'];
						$aRefund['send_op_id'] = intval($this->system->op_id);
						$aRefund['pay_type'] = 'deposit';
						$aRefund['member_id'] = $orderInfo['member_id'];
						$aRefund['account'] = '';
						$memInfo=$memModel->getFieldById($orderInfo['member_id'],array('uname'));
						$aRefund['pay_account'] = $memInfo['uname'];
						$aRefund['bank'] = '';
						$aRefund['title'] = 'title';
						$aRefund['currency'] = $orderInfo['currency'];
						$aRefund['payment'] = $payment_id;
						$aRefund['paymethod'] = $payment_id;
						$aRefund['status'] = 'sent';
						$aRefund['memo'] = ($aData['memo'] ? $aData['memo'].'#' : '').__('管理员后台退款产生');
						$refund_id = $oRefund->create($aRefund);
						if(!$refund_id){
							$this->setError(10001);
							trigger_error(__('退款单不能正常生成'),E_USER_ERROR);
							return false;
						}
						$orderModel->addLog(__('订单退款').$payMoney, $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , __('退款'));
					}
					/*****************************************
					*			转成正式订单
					*****************************************/
					$sql = "UPDATE sdb_group_activity_order_act SET extension_code='succ',last_change_time=".time()." WHERE  order_id='$order[order_id]'";
					$this->db->exec($sql);
				}else {
					$this->setError(10001);
					trigger_error(__('生成订单失败!'),E_ERROR);
					return false;
					exit;
				}
			}elseif ($orderInfo['status']=='active'&&$orderInfo['pay_status']==3) {//未完全支付则要求
				//退款
				if ($orderInfo['pay_status']!=0&&$orderInfo['pay_status']!=5) {
					$refund['order_id']=$order['order_id'];
					$refund['money']=$orderInfo['payed']-$orderInfo['cost_payment'];
					$refund['payment']=$payment_id;
					$refund['pay_type']='deposit';
					$memInfo=$memModel->getFieldById($orderInfo['member_id'],array('uname'));
					$refund['pay_account']=$memInfo['uname'];
					$refund['opid'] =$this->system->op_id;
					$refund['opname']=$this->system->op_name;
					if(!$orderModel->refund($refund)){
						$this->setError(10001);
						trigger_error(__('退款失败'),E_ERROR);
						return false;
						exit;
					}
					/*****************************************
					*			转成正式订单失败
					*****************************************/
					$sql = "UPDATE sdb_group_activity_order_act SET extension_code='fail',last_change_time=".time()." WHERE  order_id='$order[order_id]'";
					$this->db->exec($sql);
				}
			}elseif ($orderInfo['status']=='active' && $orderInfo['pay_status']==0) {
				//如果是活单 未付款 订单作废
				$cancel=$orderModel->toCancel($order['order_id']);
				if (!$cancel) {
					$this->setError(10001);
					trigger_error(__('订单作废失败'),E_USER_ERROR);
					return false;
				}
				/*****************************************
				*			转成正式订单失败
				*****************************************/
				$sql = "UPDATE sdb_group_activity_order_act SET extension_code='fail' ,last_change_time=".time()." WHERE  order_id='$order[order_id]'";
				$this->db->exec($sql);
			}else {
				$sql = "UPDATE sdb_group_activity_order_act SET extension_code='fail' ,last_change_time=".time()." WHERE  order_id='$order[order_id]'";
				$this->db->exec($sql);
			}
			//宣布成功触发事件
			$this->fireEvent('activitySuccess',$aData,$aData['member_id']);
		}
		return true;
	}
	/**
	 * 宣布失败
	 *
	 */
	function doDeclareFail($param){
		$orderModel = $this->system->loadModel('trading/order');
		$memModel = $this->system->loadModel('member/member');
		if ($payment=$this->getPaymentId('deposit',array('id'))) {
			$payment_id=$payment[0]['id'];
		}else {
			trigger_error(__('预付款支付方式必须开启！'),E_USER_ERROR);
			return false;
			exit;
		}
		foreach ($param['order'] as $key=>$order) {
			//得到订单信息
			$orderInfo= $orderModel->getFieldById($order['order_id']);
			if ($orderInfo['status']=='active'&&$orderInfo['pay_status']==0) {//如果是活单 但未付款
				//订单作废
				$cancel=$orderModel->toCancel($order['order_id']);
				if (!$cancel) {
					$this->setError(10001);
					trigger_error(__('订单作废失败'),E_USER_ERROR);
					return false;
				}
			}elseif ($orderInfo['status']=='active'&&$orderInfo['pay_status']!=0&&$orderInfo['pay_status']!=5) {//如果是活单，已付款
				//如果已付款则退款
				$orderInfo= $orderModel->getFieldById($order['order_id']);
				$refund['order_id']=$order['order_id'];
				$refund['money']=$orderInfo['payed']-$orderInfo['cost_payment'];
				$refund['payment']=$payment_id;
				$refund['pay_type']='deposit';
				$memInfo=$memModel->getFieldById($orderInfo['member_id'],array('uname'));
				$refund['pay_account']=$memInfo['uname'];
				$refund['opid'] =$this->system->op_id;
				$refund['opname']=$this->system->op_name;
				if (!$orderModel->refund($refund)) {
					$this->setError(10001);
					trigger_error(__('订单退款失败'),E_USER_ERROR);
					return false;
				}
			}
			/*****************************************
			*			转成正式订单失败
			*****************************************/
			$sql = "UPDATE sdb_group_activity_order_act SET extension_code='fail' ,last_change_time=".time()." WHERE  order_id='$order[order_id]'";
			$this->db->exec($sql);
			//触发宣布失败事件
			$this->fireEvent('activityFailure',$orderInfo,$orderInfo['member_id']);
		}
		return true;
	}
	/**
	 * 得到支付id
	 */
	function getPaymentId($pay_type='deposit',$aField=array('*')){
		$sql ="SELECT ".implode(',',$aField)." FROM sdb_payment_cfg  WHERE pay_type='".$pay_type."' and disabled='false'";
		return $this->db->select($sql);
	}

	function delete_order_act(&$data,$type='group_order'){

		$sql = "DELETE FROM sdb_group_activity_order_act WHERE 1";
		if ($data['order_id'][0]!='_ALL_') {
			$sql.=" and order_id in(".implode(',',$data['order_id']).")";
		}elseif ($data['order_id'][0]=='_ALL_'){
			switch ($type){
				case 'order':
					$sql.=' and order_id in( select order_id from sdb_orders where disabled=\'true\')';
					break;
				case 'group_order':
					$sql.=' and disabled=\'true\' or order_id in( select order_id from sdb_orders where disabled=\'true\')';
					break;
				default:
					break;
			}
		}

		$sSql = "SELECT order_id FROM sdb_group_activity_order_act WHERE extension_code='succ' AND disabled='true'";
		if ($data['order_id'][0]!='_ALL_') {
			$sSql.=" and order_id in(".implode(',',$data['order_id']).")";
		}
		$groupOrder = $this->db->select($sSql);
		foreach ($groupOrder as $key=>$row) {
			$orderIds[]=$row['order_id'];
		}
		if ($orderIds) {
			foreach ($data['order_id'] as $k=>$v) {
				if (in_array($v,$orderIds)) {
					unset($data['order_id'][$k]);
				}
			}
		}
		sort($data['order_id']);
		$this->db->exec($sql);
	}


	function toDisabled($data){
		$sql = "UPDATE sdb_group_activity_order_act SET disabled='true' WHERE order_id in(".implode(',',$data['order_id']).")";
		$this->db->exec($sql);
	}
	//判断是否为团购订单
	function isgrouporder($orderid){
		$sql = " SELECT * FROM	sdb_group_activity_order_act WHERE order_id='$orderid' ";
		return  $this->db->selectrow($sql);
	}

	function getGroupGoodsList(){
		$sql = "SELECT * FROM sdb_group_activity_purchase WHERE  disabled='false' AND act_open='true'  AND state='2' AND '".time()."' BETWEEN start_time AND end_time";
		$lists=$this->db->select($sql);
		return $lists;
	}
	/**
	 * 得到生成正式订单的预订单
	 *
	 * @param unknown_type $orderid
	 */
	function getTurntoNormalOrder($orderId){
		$sql = "SELECT * FROM sdb_group_activity_order_act WHERE order_id='$orderId' AND extension_code='succ'";
		return $this->db->selectrow($sql);
	}

	function getInvalidOrder(){
		$orderId=array();
		$sql = "SELECT * FROM sdb_group_activity_order_act WHERE extension_code!='succ'";
		$rows=$this->db->select($sql);
		foreach ($rows as $row) {
			$orderId['order_id'][]=$row['order_id'];
		}
		return $orderId;
	}

	function fireEvent($action , &$object, $member_id=0){
		//$trigger = &$this->system->loadModel('system/trigger');
		if (!$object['email'])
		$object['email'] = $object['ship_email'];
		$trigger = $this->system->loadModel('plugins/group_activity/group_activity_trigger');
		return $trigger->object_fire_event($action,$object, $member_id,$this);
	}
	/**
	 * 根据活动状态得到活动
	 *
	 * @param unknown_type $state
	 */
	function getGroupActivityByState($state=4,$aField=array('*'),$filter=null){
		$sql = "SELECT ".implode(',',$aField)." FROM ".$this->tableName." WHERE state='".$state."' AND disabled='false' AND act_open='true'";
		$where=array(1);
		if ($filter&&is_array($filter)) {
			if ($filter['end_time']) {
				$where[]=' end_time <'.time();
			}
		}
		$sql.=' and '.implode(' and ',$where);
		return $this->db->select($sql);
	}
    function autochangestatues(){
    $sql = "SELECT * FROM ".$this->tableName." WHERE state=1 and disabled='false' AND act_open='true'";
    foreach($row=$this->db->select($sql) as $k=>$v){

        if( time() >=$v['start_time']&&time() <=$v['end_time']){
          
            $sqlupdate="update  ".$this->tableName."  set state=2 WHERE disabled='false' AND act_open='true' AND act_id =".$v['act_id'];   
            $this->db->exec($sqlupdate);
        }
    }
    }


	/**
	 * 满足条件自动宣布成功
	 */
	function autoDeclareSuccess(){
		$groupActivity = $this->getGroupActivityByState(4,array('*'),array('end_time'=>true));
		foreach ($groupActivity as $key=>$value) {
			$goodsnum=$this->getGoodsNum($value['act_id']);
			$minNum = $this->getActMinNum($value['act_id']);
			if ($goodsnum>=$minNum) {
				$actInfo=array(
				'state'=>3,
				'act_id'=>$value['act_id']
				);
				if ($this->getOrderNum($value['act_id'])) {
					//得到当前价格
					$param['current_price']=$this->getCurrentPrice($value['act_id']);
					$param['act_id'] = $value['act_id'];
					//得到该活动下的所有订单
					$param['order']=$this->getOrderByActId($value['act_id']);
					$result=$this->doDeclareSuccess($param);
					if ($result) {
						$this->save($actInfo);
					}else {
						$result=false;
						break;
					}
				}else {
					$result=false;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * 得到已保证金的预定商品数
	 *
	 * @param unknown_type $act_id
	 */
	function getGoodsNumByPayDeposit($act_id){
		$actInfo = $this->getFieldById($act_id);

		$sql ="SELECT * FROM sdb_group_activity_order_act WHERE act_id='$act_id' ";
		if ($actInfo['state']==4 || $actInfo['state']==2) {
			$sql.=" and extension_code='group_buy'";
		}
		$orderAct = $this->db->select($sql);
		$num=0;
		foreach ($orderAct as $key=>$value) {

			$orderNum=$this->db->selectrow("select itemnum,payed from sdb_orders where order_id='$value[order_id]' and disabled='false' and status!='dead' and pay_status not in('4','5') ");
			if (floatval($value['group_total_amount'])!=floatval($orderNum['payed'])) {
				continue;
			}
			$num+=$orderNum['itemnum'];

		}
		return $num;
	}

	function modifier_act_open(&$rows){
		$state=array(
		'true'=>__('已开启'),
		'false'=>__('未开启'),
		);
		foreach($rows as $k => $v){
			if($v=='true'){
				$rows[$k] = $state[$v];
			}elseif ($v=='false'){
				$rows[$k] = $state[$v];
			}
		}
	}

	function DeclareFailToSendMail($param){
		$orderModel = $this->system->loadModel('trading/order');
		$memModel = $this->system->loadModel('member/member');
		foreach ($param['order'] as $key=>$order) {
			$orderInfo= $orderModel->getFieldById($order['order_id']);
			/*****************************************
			*			转成正式订单失败
			*****************************************/
			$sql = "UPDATE sdb_group_activity_order_act SET extension_code='fail',last_change_time=".time()." WHERE  order_id='$order[order_id]'";
			$this->db->exec($sql);
			//触发宣布失败事件
			$this->fireEvent('activityFailure',$orderInfo,$orderInfo['member_id']);
		}
	}
}