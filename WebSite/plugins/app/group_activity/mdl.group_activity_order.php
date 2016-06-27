<?php
/**
 * @author chenping
 * @version $Id: mdl.group_activity_order.php 2010-3-26 13:10:22 $
 * @package group_activity
 * @uses mdl_order
 *
 */
if (!class_exists('mdl_order')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/trading/mdl.order.php');
}
class mdl_group_activity_order extends mdl_order{
	
	function __construct(){
		parent::mdl_order();
	}
	function _filter($filter){
		$order_id = array();
		$where = array(1);
		$sql = 'SELECT * FROM sdb_group_activity_order_act WHERE extension_code in(\'group_buy\',\'fail\')';
		$rows = $this->db->select($sql);
		foreach ($rows as $row) {
			if  ($row['order_id']) {
				$order_id[] = $row['order_id'];
			}
		}
		if ($order_id) {
			$where[] = 'order_id not in('.implode(',',$order_id).')';
		}
		if ($_GET['order_id_from_group']) {
			$where[] = 'order_id='.$_GET['order_id_from_group'];
		}
		return  parent::_filter($filter).' and '.implode(' and ',$where);
	}
	/**
	 * $oSale 调用app/group_activity下的模型
	 *
	 * @param unknown_type $aCart
	 * @param unknown_type $aMember
	 * @param unknown_type $aDelivery
	 * @param unknown_type $aPayment
	 * @param unknown_type $minfo
	 * @param unknown_type $postInfo
	 * @return unknown
	 */
	function create(&$aCart,&$aMember,&$aDelivery,&$aPayment,&$minfo,&$postInfo ){
		//$oSale = &$this->system->loadModel('trading/sale');
		$oSale = $this->system->loadModel('plugins/group_activity/group_activity_sale');


		$trading = $oSale->getCartObject($aCart,$aMember['member_lv_id'],true,true);

		//保存收货人地址
		$this->_saveAddr($aMember['member_id'], $aDelivery);
		$iProduct = 0;
		if (is_array($trading['products']) && count($trading['products'])){
			$objGoods = &$this->system->loadModel('trading/goods');    //生成订单前检查库存
			$objCart = &$this->system->loadModel('trading/cart');
			$arr = array();
			$aLinkId = array();
			foreach($trading['products'] as $k => $p){
				$aStore = $objGoods->getFieldById($p['goods_id'], array('marketable','disabled'));
				if($aStore['marketable'] == 'false' || $aStore['disabled'] == 'true'){
					/**
                     * trigger Smarty error
                     *
                     * @param string $error_msg
                     * @param integer $error_type
                     */
					trigger_error($p['name'].__('商品未发布不能下单。'),E_USER_ERROR);
					return false;
					exit;
				}

				if($this->freez_time()=='order'){
					if(!$objCart->_checkStore($p['product_id'], $p['nums'])){
						trigger_error("商品“".$p['name']."”库存不足",E_USER_ERROR);
						return false;
						exit;
					}
				}

				//判断配件库存to检查变量
				if(count($p['adjList'])){
					foreach($p['adjList'] as $pid => $num){
						if(!$objCart->_checkStore($pid, $num*$p['nums'])){
							trigger_error("商品配件库存不足",E_USER_ERROR);
							return false;
							exit;
						}
					}
				}
				$arr[] = $p['name'].'('.$p['nums'].')';
				$this->itemnum+=$p['nums'];
				$aLinkId[] = $p['goods_id'];
				$trading['products'][$k]['addon']['minfo'] = $minfo[$p['product_id']];    //将商品用户信息存入addon
				$trading['products'][$k]['minfo'] = $minfo[$p['product_id']];    //将商品用户信息存入addon

				if($p['goods_id']) $aP[] = $p['goods_id'];
				$iProduct++;
			}
		}
		if($trading['package'] || $trading['gift_e']) $otherPhysical = true;
		else $otherPhysical = false;
		if(count($aP) || $otherPhysical){
			$return = $this->checkOrderDelivery($aP, $aDelivery, $otherPhysical, $aMember['member_id']);    //检测实体商品配送信息的合法性
			if($return){
				$aDelivery['is_delivery'] = $return;
				if($return == 'Y' && empty($aDelivery['shipping_id'])){
					trigger_error(__("提交不成功，请选择配送方式"),E_USER_ERROR);
					return false;
					exit;
				}
			}else{
				trigger_error(__("对不起，请完整填写配送信息"),E_USER_ERROR);
				return false;
				exit;
			}
		}

		$iPackage = 0;
		if (is_array($trading['package']) && count($trading['package'])){
			$objCart = &$this->system->loadModel('trading/cart');
			foreach ($trading['package'] as $v) {
				if (!$objCart->_checkStore($v['goods_id'], $v['nums'])) {
					trigger_error(__("捆绑商品库存不足"),E_USER_ERROR);
					return false;
					exit;
				}
				$iPackage++;
				$arr[] = $v['name'].'('.$v['nums'].')';
			}
		}
		if(is_array($trading['gift_e']) && count($trading['gift_e'])){
			foreach ($trading['gift_e'] as $v){
				$arr[] = $v['name'].'('.$v['nums'].')';
			}
		}
		if($iProduct + $iPackage + count($trading['gift_p']) + count($trading['gift_e']) == 0){
			trigger_error(__("购物车中无有效商品!"),E_USER_ERROR);
			return false;
		}

		//        $objProduct->updateRate($aLinkId);    //更新商品推荐度
		$oCur = &$this->system->loadModel('system/cur');
		$tdelivery = explode( ':' , $aDelivery['ship_area'] );
		$area_id = $tdelivery[count($tdelivery)-1];
		$oDelivery = &$this->system->loadModel('trading/delivery');
		$rows = $oDelivery->getDlTypeByArea($area_id,$trading['weight'],$aDelivery['shipping_id']);
        
		if($trading['exemptFreight'] == 1){    //[exemptFreight] => 1免运费
			$aDelivery['cost_freight']=0;
		}else{
            if($_COOKIE['IS_POSTAGE']=='1'){//免运费bug处理
                $aDelivery['cost_freight']=0;
            }else{
			    $trading['cost_freight'] = $oCur->formatNumber(cal_fee($rows[0]['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$rows[0]['price']), false);
            }
		}
		$trading['cost_freight'] = is_null($trading['cost_freight'])?0:$trading['cost_freight'];
		if($aDelivery['is_protect'][$aDelivery['shipping_id']] && $rows[0]['protect']==1){
			$aDelivery['cost_protect'] = $oCur->formatNumber(max($trading['totalPrice']*$rows[0]['protect_rate'],$rows[0]['minprice']), false);
			$aDelivery['is_protect'] = 'true';
		}else{
			$aDelivery['cost_protect']=0;
			$aDelivery['is_protect'] = 'false';
		}
		if($aPayment['payment'] > 0 || $aPayment['payment'] == -1){
			$oPayment = &$this->system->loadModel('trading/payment');
			$aPay = $oPayment->getPaymentById($aPayment['payment']);
			if($aPay['pay_type'] == 'DEPOSIT' && $aMember['member_id'] == ""){
				trigger_error(__("未登录客户不能选择预存款支付!"),E_USER_ERROR);
				return false;
			}
			$config=unserialize($aPay['config']);
			$aPayment['fee'] = $aPay['fee'];
			if ($config['method']==2){
				$aPayment['fee'] = $config['fee'];
				$aPayment['method'] = $config['method'];
			}
		}else{
			trigger_error(__("提交不成功，未选择支付方式!"),E_USER_ERROR);
			return false;
		}
		$currency = $oCur->getcur($aPayment['currency'], true);
		$aPayment['currency'] = $currency['cur_code'];

		if(!$this->checkPoint($aMember['member_id'], $trading)){
			return false;
		}
		if(!$this->checkGift($trading['gift_p'])){
			unset($trading['gift_p']);  //直接不给
		}
		$orderInfo = $trading;
		$orderInfo['order_id'] = $this->gen_id();
		$orderInfo['cur_rate'] = ($currency['cur_rate']>0 ? $currency['cur_rate']:1);
		$orderInfo['tostr'] = implode(',',$arr);
		$orderInfo['itemnum'] = $this->itemnum;
		getRefer($orderInfo);    //推荐下单
		$aDelivery['ship_time'] = ($aDelivery['day']=='specal' ? $aDelivery['specal_day'] : $aDelivery['day']).' '.$aDelivery['time'];
		$orderInfo = array_merge($orderInfo,$aDelivery, $aPayment);
		if( $aMember ){
			$orderInfo = array_merge($orderInfo,$aMember);
		}

		return $this->save($orderInfo, true,$postInfo);
	}
	function save(&$trading, $doCreate=false,&$postInfo ){
		$data = $trading;
		$objDelivery = &$this->system->loadModel('trading/reship');
		$oCur = &$this->system->loadModel('system/cur');
		$aShipping = $objDelivery->getDlTypeById($trading['shipping_id']);
		//        $aArea = $objDelivery->getDlAreaById($trading['area']);
		$data['shipping'] = $aShipping['dt_name'];
		//        $data['shipping_area'] = $aArea['name']; 废弃字段
		$data['acttime'] = time();
		$data['createtime'] = time();
		$data['ip'] = remote_addr();
		$trading['totalPrice'] = $oCur->formatNumber($trading['totalPrice'], false);
		$trading['pmt_b']['totalPrice'] = $oCur->formatNumber($trading['pmt_b']['totalPrice'], false);
		$data['cost_item'] = $trading['totalPrice'];
		$data['total_amount'] = $trading['totalPrice']+$trading['cost_freight']+$trading['cost_protect'];
		$data['pmt_amount'] = $trading['pmt_b']['totalPrice'] - $trading['totalPrice'];
		if($trading['is_tax'] && $this->system->getConf('site.trigger_tax')){
			$data['is_tax'] = 'true';
			$data['cost_tax'] = $trading['totalPrice'] * $this->system->getConf('site.tax_ratio');
			$data['cost_tax'] = $oCur->formatNumber($data['cost_tax'], false);
			$data['total_amount'] += $data['cost_tax'];
		}
		if($trading['payment'] > 0){
			if ($data['method'])
			$data['cost_payment'] = $data['fee'];
			else
			$data['cost_payment'] = $data['fee'] * $data['total_amount'];
			$data['cost_payment'] = $oCur->formatNumber($data['cost_payment'], false);
			$data['total_amount'] += $data['cost_payment'];
		}

		$newNum = $this->getOrderDecimal($data['total_amount']);

		$data['discount'] = floatval($data['total_amount'] - $newNum);
		$data['total_amount'] = $newNum;
		$data['final_amount'] = $data['total_amount'] * $data['cur_rate'];
		$data['final_amount'] = $oCur->formatNumber($data['final_amount'], false);
		$data['score_g'] = intval($data['totalGainScore']);
		$data['score_u'] = intval($data['totalConsumeScore']);
		$data['score_e'] = intval($newNum);
		if ($trading['payment']!="-1"){
			//----检测该支付方式是否还有子选项，如快钱选择银行
			$payment=$this->system->loadModel('trading/payment');
			$payment->recgextend($data,$postInfo,$extendInfo);
			$data['extend']=serialize($extendInfo);
			//------------------------------------------------
		}
		//+判断是否有远端商品
		if(true || $this->system->getConf('certificate.distribute')){ //检测付款前的订单状态,如果是刚付款立即发货
			if (!empty($trading['products']) && is_array($trading['products'])) {
				foreach($trading['products'] as $product){
					$_where_bns[] = sprintf('\'%s\'',addslashes($product['bn']));
				}
				$_sql = sprintf('select local_bn,supplier_id
                                 from sdb_supplier_pdtbn
                                 where local_bn in(%s) and `default`=\'true\'', implode(',', $_where_bns));
				$_remote_product = $this->db->select($_sql);
				$_remote_product = array_change_key($_remote_product, 'local_bn');
				if($_remote_product){
					$data['is_has_remote_pdts'] = 'true';
				}
			}
		}

		//----------------
		$rs = $this->db->exec('SELECT * FROM sdb_orders WHERE order_id='.$data['order_id']);
		$sql = $this->db->getUpdateSql($rs,$data,$doCreate);
		$this->_info['order_id'] = $data['order_id'];        //会员id
		if(!$this->db->exec($sql)){
			return false;
		}elseif($doCreate){
			$this->addLog(__('订单创建'), $this->op_id?$this->op_id:null, $this->op_name?$this->op_name:null , __('添加') );
		}
		$status = &$this->system->loadModel('system/status');
		$status->add('ORDER_NEW');
		$status->count_order_to_pay();
		$status->count_order_new();


		//+商品------------------------------------------------------------
		if (!empty($trading['products']) && is_array($trading['products'])) {
			$objGoods = &$this->system->loadModel('trading/goods');
			foreach ($trading['products'] as $product) {
				$product['order_id'] = $data['order_id'];
				$product['bn'] = $product['bn'];
				$product['name'] = $product['name'];
				$product['addon'] = serialize($product['addon']);
				$product['minfo'] = serialize($product['minfo']);
				$product['supplier_id'] = $_remote_product[$product['bn']]['supplier_id'];

				$rs = $this->db->query('SELECT * FROM sdb_order_items WHERE 0=1');
				$sqlString = $this->db->GetInsertSQL($rs, $product);
				if($sqlString) $this->db->exec($sqlString);
				$objGoods->updateRank($product['goods_id'], 'buy_count', $product['nums']);    //购买次数统计
				//冻结库存
				if($this->freez_time()=='order'){
					$this->db->exec("UPDATE sdb_products SET freez = freez + ".intval($product['nums'])." WHERE product_id = ".intval($product['product_id']));
					$this->db->exec("UPDATE sdb_products SET freez = ".intval($product['nums'])." WHERE product_id = ".intval($product['product_id'])." AND freez IS NULL");
				}
			}

			//判断是否为团购预订单
			if ($_POST['isgroupbuy']==2) {
				$groupActivityModel = $this->system->loadModel('plugins/group_activity');
				$groupInfo = $groupActivityModel->getValidGoodsActivityByGid($trading['products'][0]['goods_id']);

				$group['order_id'] = $data['order_id'];
				$group['act_id'] = $groupInfo['act_id'];
				$group['extension_code'] = 'group_buy';
				$group['group_total_amount'] = $data['total_amount'];
				$groupRs = $this->db->query('SELECT * FROM sdb_group_activity_order_act WHERE 0=1');
				$groupSql = $this->db->GetInsertSQL($groupRs,$group);
				if ($groupSql) {
					$this->db->exec($groupSql);
				}

				//给订单加团购标签
				$tagModel = $this->system->loadModel('system/tag');
				$tag_id = $tagModel->getTagByName('order','团购');
				if ($tag_id) {
					$tagModel->addTag($tag_id,$data['order_id']);
				}


			}
		}

		//+捆绑商品------------------------------------------------------------
		if (is_array($trading['package']) && count($trading['package'])) {
			foreach ($trading['package'] as $pkgData) {
				$pkgData['order_id'] = $data['order_id'];
				$pkgData['product_id'] = $pkgData['goods_id'];
				$pkg[] = $pkgData['goods_id'];
				$pkgData['is_type'] = 'pkg';
				$pkgData['addon'] = serialize($pkgData['addon']);
				$rs = $this->db->query('SELECT * FROM sdb_order_items WHERE order_id='.$pkgData['order_id'].' AND is_type = \'pkg\' AND product_id='.intval($pkgData['goods_id']));
				$sqlString = $this->db->GetUpdateSQL($rs, $pkgData,true);
				$this->db->exec($sqlString);
			}
			$this->db->exec('DELETE FROM sdb_order_items WHERE order_id='.$pkgData['order_id'].' AND is_type = \'pkg\' AND product_id NOT IN('.implode(',',$pkg).')');
		}

		//+促销信息------------------------------------------------------------
		if ($trading['pmt_o']['pmt_ids']) {//促销
			$sSql = 'INSERT INTO sdb_order_pmt (pmt_id,pmt_describe,order_id) select pmt_id,pmt_describe,\''
			.$data['order_id'].'\' FROM sdb_promotion WHERE pmt_id in('
			.implode(',',$trading['pmt_o']['pmt_ids']).')';
			$this->db->exec($sSql);
			foreach($trading['pmt_o']['pmt_ids'] as $k=>$pmtId) {
				$sSql = 'UPDATE sdb_order_pmt SET pmt_amount='.floatval($trading['pmt_o']['pmt_money'][$k])
				.' WHERE pmt_id='.intval($pmtId).' AND order_id='.$this->db->quote($data['order_id']);
				$this->db->exec($sSql);
			}
		}
		if ($trading['products']) {
			$pre_pmtOrder = array();
			foreach ($trading['products'] as $v) {
				if ($v['pmt_id']){
					$pre_pmtOrder[$v['pmt_id']] += $v['price'] - $v['_pmt']['price'];
				}
			}
			$aPmtIds = array_keys($pre_pmtOrder);
			if(!empty($aPmtIds)){
				$sSql = 'SELECT pmt_id,pmt_describe FROM sdb_promotion WHERE pmt_id IN('.implode(',', $aPmtIds).')';
				$aPmtOrder = $this->db->select($sSql);
				foreach($aPmtOrder as $k=>$v) {
					$v['pmt_amount'] = $pre_pmtOrder[$v['pmt_id']];
					$v['order_id'] = $data['order_id'];

					$rs = $this->db->query('select * from sdb_order_pmt where 0=1');
					$sqlString = $this->db->GetInsertSQL($rs, $v);
					$this->db->exec($sqlString);
				}
			}
		}

		//+积分处理------------------------------------------------------------
		$oMemberPoint = &$this->system->loadModel('trading/memberPoint');
		$oGift = &$this->system->loadModel('trading/gift');
		$aGiftData = array();
		if ($data['score_u']>=0) {
			if (!$oMemberPoint->payAllConsumePoint($data['member_id'],$data['order_id'])) {
				;
			}else{
				//+赠品处理------------------------------------------------------------
				if (is_array($trading['gift_e']) && count($trading['gift_e'])) {
					foreach($trading['gift_e'] as $giftId => $v) {
						$giftId = $v['gift_id'];
						$aGiftData[$giftId] = array(
						'gift_id' => $giftId,
						'name' => $v['name'],
						'nums' => $v['nums'],
						'point' => $v['point']);
						if($this->freez_time()=='order'){
							if (!$oGift->freezStock($v['gift_id'], $v['nums'])) {//兑换赠品缺货
								;}
						}
					}
				}
			}
		}
		if (is_array($trading['gift_p']) && count($trading['gift_p'])){
			foreach($trading['gift_p'] as $v) {
				$giftId = $v['gift_id'];
				if (isset($aGiftData[$giftId])) {
					$aGiftData[$giftId]['nums'] += $v['nums'];
				}else {
					$aGiftData[$giftId] = array(
					'gift_id' => $giftId,
					'name' => $v['name'],
					'nums' => $v['nums'],
					'point' => $v['point']);
				}
			}
		}
		if($aGiftData) {
			foreach($aGiftData as $item) {
				$oGift = &$this->system->loadModel('trading/gift');
				$item['order_id'] = $data['order_id'];
				$rs = $this->db->query('select * from sdb_gift_items where 0=1');
				$sqlString = $this->db->GetInsertSQL($rs, $item);
				$this->db->exec($sqlString);
			}
		}

		//+优惠券------------------------------------------------------------
		if (is_array($trading['coupon_u']) && !empty($trading['coupon_u'])) {
			$oCoupon = &$this->system->loadModel('trading/coupon');
			foreach ($trading['coupon_u'] as $code => $v) {
				$aTmp = $this->db->selectRow('select cpns_name from sdb_coupons where cpns_id='.intval($v['cpns_id']));
				$aData = array(
				'order_id' => $data['order_id'],
				'cpns_id' => $v['cpns_id'],
				'memc_code' => $code,
				'cpns_name' => $aTmp['cpns_name'],
				'cpns_type' => $v['cpns_type']);
				$rs = $this->db->query('select * from sdb_coupons_u_items where 0=1');

				$sqlString = $this->db->GetInsertSQL($rs, $aData);
				$this->db->exec($sqlString);
				$oCoupon->applyMemberCoupon($v['cpns_id'], $code, $data['order_id'], $data['member_id']);
			}
		}

		if (is_array($trading['coupon_p']) && !empty($trading['coupon_p'])) {
			foreach ($trading['coupon_p'] as $code => $v) {
				$aData = array(
				'order_id' => $data['order_id'],
				'cpns_id' => $v['cpns_id'],
				'cpns_name' => $v['cpns_name'],
				'nums' => $v['nums']);
				$rs = $this->db->query('select * from sdb_coupons_p_items where 0=1');
				$sqlString = $this->db->GetInsertSQL($rs, $aData);
				$this->db->exec($sqlString);
			}
		}

		$data['is_tax'] = ($data['is_tax'] ? true : false);
		//订单生成成功事件
		//如果是团购订单 发送团购邮件
		if ($_POST['isgroupbuy']==2) {
			$this->fireEvent('scheduleCreate',$data,$data['member_id']);
		}else {
			$this->fireEvent('create',$data,$data['member_id']);
		}
		if($data['total_amount'] == 0){
			$pdata['order_id'] = $data['order_id'];
			$pdata['member_id'] = $data['member_id'];
			$pdata['money'] = 0;
			$this->payed($pdata);
		}
		return $data['order_id'];
	}

	/**
	 * 调用$trigger 调用 app/group_activity下的模型
	 *
	 * @param unknown_type $action
	 * @param unknown_type $data
	 * @param unknown_type $memberid
	 * @return unknown
	 */
	function fireEvent($action,$data,$memberid){
		if (!$data['email'])
		$data['email'] = $data['ship_email'];

		//parent::fireEvent($action,$data,$memberid);
		//$trigger = &$this->system->loadModel('system/trigger');
		$trigger = $this->system->loadModel('plugins/group_activity/group_activity_trigger');
		return $trigger->object_fire_event($action,$data, $memberid,$this);
	}
	
	function toReturnPoint($PARA){
		//如果是由团购预订单生成 ， 但正式订单中进行退款 则不扣积分
		$sql = "SELECT * FROM sdb_group_activity_order_act WHERE order_id=".$PARA['order_id']." AND extension_code='succ'";
		if ($groupOrder=$this->db->selectrow($sql)) {
			$PARA['return_score']=0;
		}
		unset($sql);
		return parent::toReturnPoint($PARA);
	}

}
?>
