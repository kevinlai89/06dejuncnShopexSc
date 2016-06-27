<?php
define('CORE_MODEL_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/model_v5':'/model'));
require_once(CORE_MODEL_DIR.'/trading/mdl.order.php');
if (!class_exists('sale_mdl')) {
	require_once('sale.mdl.php');
}
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
class order_mdl extends mdl_order{


	function create(&$aCart,&$aMember,&$aDelivery,&$aPayment,&$minfo,&$postInfo ){
		//$oSale = &$this->system->loadModel('trading/sale');
        
		$oSale=&new sale_mdl();
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
			$trading['cost_freight'] = $oCur->formatNumber(cal_fee($rows[0]['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$rows[0]['price']), false);
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

		//限时抢购 减少库存
		$scareModel=new mdl_scare();
		foreach ($orderInfo['products'] as $k=>$p) {
			$scareInfo=$scareModel->getFieldByGoodsId($p['goods_id']);
			if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['scare_count']>0) {   
                //限时抢购于系统关联预展库存 2011-7-7 by zhangxuehui
                $store_time_type = $this->system->getConf('system.store.time');
                $p['nums'] = ($store_time_type =='1'||$store_time_type =='0')?$p['nums']:'0';
                //限时抢购于系统关联预展库存 2011-7-7 by zhangxuehui
				$scareModel->reduceCount($p['goods_id'],$p['nums']);
			}
		}
		//end
		return $this->save($orderInfo, true,$postInfo);
	}




}
?>
