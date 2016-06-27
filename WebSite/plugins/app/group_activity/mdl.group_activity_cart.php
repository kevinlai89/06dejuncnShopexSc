<?php
/**
 * @author shopex
 * @version $Id: mdl.group_activity_cart.php 2010-3-25 16:06:21 $
 * @package group_activity
 * @uses mdl_cart
 * 
 *
 */

if (!class_exists('mdl_cart')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/trading/mdl.cart.php');
}
class mdl_group_activity_cart extends mdl_cart{

	function mdl_group_activity_cart(){
		parent::mdl_cart();
	}

	/**
	 * 得到cookie中的团购商品
	 *
	 * @param unknown_type $objType
	 * @param unknown_type $aParams
	 */
	function setGroupBuy($objType='g', $aParams){
		//todo 需要再考虑捆绑商品
		if(!$this->_checkStore($aParams['pid'], 1)){
			$this->setError(10001);
			trigger_error(__('库存不足'),E_USER_NOTICE);
			return false;
		}

		$aAdj = explode('|', $aParams['adj']);
		foreach($aAdj as $val){
			$adjItem = explode('_', $val);
			if($adjItem[0]>0 && $adjItem[2]>0){
				if(!$this->_checkStore($adjItem[0], $adjItem[2])){
					$this->setError(10001);
					trigger_error(__('配件库存不足'),E_USER_NOTICE);
					return false;
				}
			}
		}

		if($objType == 'g'){
			$cartKey = $aParams['gid'].'-'.$aParams['pid'].'-'.$aParams['adj'];
			$aCart['g']['cart'][$cartKey] = $aParams['num'];
			if($aParams['pmtid'] > 0) $aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];
		}
		return $aCart;
	}

	/**
	 * $sale 调用app/group_activity下的模型
	 *
	 * @param unknown_type $aCart
	 * @param unknown_type $aMember
	 * @param unknown_type $aParam
	 * @return unknown
	 */
	function checkoutInfo(&$aCart, &$aMember, $aParam=null) {
		//$sale = &$this->system->loadModel('trading/sale');
		$sale = $this->system->loadModel('plugins/group_activity/group_activity_sale');
		
		$trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true);
        //print_r($trading);exit;
		$trading['total_amount'] = $trading['totalPrice'];
		if($aParam['shipping_id']){
			$shipping = &$this->system->loadModel('trading/delivery');
			$aShip = $shipping->getDlTypeByArea($aParam['area'], 0, $aParam['shipping_id']);
			if($trading['exemptFreight'] == 1){
				$trading['cost_freight'] = 0;
			}else{
				$trading['cost_freight'] = cal_fee($aShip[0]['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$aShip[0]['price']);
			}
			$trading['shipping_id'] = $aParam['shipping_id'];
			if($aParam['is_protect'] == 'true' && $aShip[0]['protect']){
				$trading['is_protect'] = 1;
				$trading['cost_protect'] = max($aShip[0]['protect_rate']*$trading['totalPrice'], $aShip[0]['minprice']);
			}
			$trading['total_amount'] += $trading['cost_freight']+$trading['cost_protect'];
		}
		if($this->system->getConf('site.trigger_tax')){
			$trading['is_tax'] = 1;
			if(isset($aParam['is_tax']) && $aParam['is_tax'] == 'true'){
				$trading['tax_checked'] = 'checked';
				$trading['cost_tax'] = $trading['totalPrice'] * $this->system->getConf('site.tax_ratio');
				$trading['total_amount'] += $trading['cost_tax'];
			}
			$trading['tax_rate'] = $this->system->getConf('site.tax_ratio');
		}

		if($aParam['payment']){
			$payment = &$this->system->loadModel('trading/payment');
			$aPay = $payment->getPaymentById($aParam['payment']);
			$config=unserialize($aPay['config']);
			if ($config['method']<>2)
			$trading['cost_payment'] = $aPay['fee'] * $trading['total_amount'];
			else
			$trading['cost_payment'] = $config['fee'];
			$trading['total_amount'] += $trading['cost_payment'];
		}

		$trading['score_g'] = $trading['pmt_b']['totalGainScore'];
		$trading['pmt_amount'] = $trading['pmt_b']['totalPrice'] - $trading['totalPrice'];
		$trading['member_id'] = $aMember['member_id'];

		$order = &$this->system->loadModel('trading/order');
		$newNum = $order->getOrderDecimal($trading['total_amount']);
		$trading['discount'] = $trading['total_amount'] - $newNum;
		$trading['total_amount'] = $newNum;
		$oCur = &$this->system->loadModel('system/cur');
		$currency = $oCur->getcur($aParam['cur']);
		if($currency['cur_code']){
			$trading['cur_rate'] = $currency['cur_rate'];
		}else{
			$trading['cur_rate'] = 1;
		}
		$trading['final_amount'] = $newNum * $trading['cur_rate'];
		$trading['cur_sign'] = $currency['cur_sign'];
		$trading['cur_display'] = $this->system->request['cur'];
		$trading['cur_code'] = $currency['cur_code'];
		return $trading;
	}
    function get_group_product($gid){
        $sql = 'SELECT * FROM sdb_group_activity_purchase WHERE gid="'.$gid.'"';
		return $rows = $this->db->selectrow($sql);

    }
    
}
?>