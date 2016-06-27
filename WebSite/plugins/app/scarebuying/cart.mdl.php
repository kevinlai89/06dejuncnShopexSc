<?php
define('CORE_MODEL_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/model_v5':'/model'));
require_once(CORE_MODEL_DIR.'/trading/mdl.cart.php');
if (!class_exists('sale_mdl')) {
	require_once('sale.mdl.php');
}
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
class cart_mdl extends mdl_cart{

	/**
     * 加入购物车
     * @param string $objType 购物车中哪个组：g/商品；p/捆绑商品；f/赠品；c/优惠券
     * @param array $aParams 购物车参数项数组
     * @param number $quantity 加入购物车数组项数量
     * @return bool
     */
	function addToCart($objType='g', &$aParams, $quantity=1)
	{
       
		switch($objType){
			case 'g':
				if($aParams['gid'] > 0 && $aParams['pid'] > 0 && $quantity > 0){
					$cartKey = $aParams['gid'].'-'.$aParams['pid'].'-'.$aParams['adj'];
					$aCart = $this->getCart('g');


					//限时抢购
					$scareModel=new mdl_scare();
					$scareInfo=$scareModel->getFieldByGoodsId($aParams['gid']);
					if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')) {

						$goods_num_count=isset($aCart['cart'][$cartKey]) ? ($aCart['cart'][$cartKey] +$quantity) : $quantity;
                      
                        if($scareInfo['scare_count']){
						if (intval($scareInfo['scare_count'])<intval($goods_num_count)) {
							$this->setError(10001);
							trigger_error(__('超出该商品的限购数量！'),E_USER_NOTICE);
							return false;
						}
						unset($goods_num_count);
					}elseif($scareInfo['scare_count']=='0'){
                        $this->setError(10001);
							trigger_error(__('超出该商品的限购数量！'),E_USER_NOTICE);
							return false;
                    }
                    }
					//end





					if (isset($aCart['cart'][$cartKey])){
						$aCart['cart'][$cartKey] += $quantity;
						$buyStatus = 1;
					}else{
						$aCart['cart'][$cartKey] = $quantity;
						$buyStatus = 0;
					}


					if($aParams['pmtid'] > 0) $aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];

					$objGoods = $this->system->loadModel('trading/goods');
					$aGoods = $objGoods->getMarketableById($aParams['gid']);
					if($aGoods['marketable'] == "false"){
						$this->setError(10001);
						trigger_error(__('该货品已经下架'),E_USER_NOTICE);
						return false;
					}
                     
					if(!$this->_checkStore($aParams['pid'], $aCart['cart'][$cartKey])){
                      
						if($buyStatus == 0){
                              
							$this->setError(10001);
							trigger_error(__('库存不足'),E_USER_NOTICE);
							return false;
						}else{
							return 'notify';
						}
						exit;
					}

					if($stradj != 'na'){
						$aAdj = explode('|', $aParams['adj']);
						foreach($aAdj as $val){
							$adjItem = explode('_', $val);
							if($adjItem[0]>0 && $adjItem[2]>0){
								if(!$this->_checkStore($adjItem[0], $adjItem[2]*$aCart['cart'][$cartKey])){
									$this->setError(10001);
									trigger_error(__('配件库存不足'),E_USER_NOTICE);
									return false;
								}
							}
						}
					}
					return $this->save('g', $aCart);
				}else{
					$this->setError(10001);
					trigger_error(__('参数错误!'),E_USER_NOTICE);
					return false;
				}
				break;
			case 'p':
				if($aParams['pkgid']){
					$aCart = $this->getCart('p');
					$aCart[$aParams['pkgid']]['num'] += $quantity;
					if (!$this->_checkGoodsStore($aParams['pkgid'], $aCart[$aParams['pkgid']]['num'])) {
						$this->setError('10000');
						trigger_error(__('捆绑商品数量不足'),E_USER_ERROR);
						return false;
					}
					return $this->save('p', $aCart);
				}else{
					$this->setError(10001);
					trigger_error(__('参数错误!'),E_USER_NOTICE);
					return false;
				}
				break;
			case 'f':
				if ($aParams['gift_id']) {
					$aCart = $this->getCart('f');
					$aCart[$aParams['gift_id']]['num'] += $quantity;
					return $this->save('f', $aCart);
				}else{
					$this->setError(10001);
					trigger_error(__('参数错误!'),E_USER_NOTICE);
					return false;
				}
				break;
			case 'c':
				//todo判断coupon 的有效性
				//暂时强行规定一个购物车，有且只使用一张优惠券
				if (is_array($aParams)&&count($aParams==1)) {
					foreach ($aParams as $k=>$c) {
						$cart_c[$k] = array('type' => $c['type'],
						'pmt_id' => $c['pmt_id']);
					}
					return $this->save('c', $cart_c);
				}else{
					$this->setError(10001);
					trigger_error(__('参数错误!'),E_USER_NOTICE);
					return false;
				}
				break;
		}
	}

	/**
     * 更新购物车中商品
     * @param string $objType 购物车中哪个组：g/商品；p/捆绑商品；f/赠品；c/优惠券
     * @param string $cartKey 购物车数组项
     * @param number $quantity 购物车数组项更新成指定数量
     * @param array $aMsg 处理过程中的消息回馈
     * @return bool
     */
	function updateCart($objType='g', $cartKey, $quantity, &$aMsg)
	{
		$quantity = intval($quantity);
		if($quantity < 1){
			$aMsg[] = __('输入更新数量不合法');
			//            trigger_error(__('输入更新数量不合法'),E_USER_NOTICE);
			return false;
		}

		switch($objType){
			case 'g':
				list($goodsid,$productid,$stradj) = explode('-', $cartKey);
				$o_goods=$this->system->loadModel('goods/finderPdt');
				$goods_info=$o_goods->instance($productid);
				if($goodsid > 0 && $productid > 0 && $quantity > 0){
					$aCart = $this->getCart($objType);
					$aCart['cart'][$cartKey] = $quantity;
					if(!$this->_checkStore($productid, $aCart['cart'][$cartKey])){
						$aMsg[] = __($goods_info['name'].'<br>商品库存不足');
						return false;
					}


					//限时抢购
					$scareModel=new mdl_scare();
					$scareInfo=$scareModel->getFieldByGoodsId($goodsid);
					if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')) {
						if ($scareInfo['buycountlimit']!=''&&$scareInfo['buycountlimit']<$quantity) {
							$aMsg[]='每人限购'.$scareInfo['buycountlimit'].'件！';
							return false;
						}
						if ($scareInfo['scare_count']<$quantity) {
							$aMsg[]='超出限购数量！';
							return false;
						}
					}
					//end


					if($stradj != 'na'){
						$aAdj = explode('|', $stradj);
						foreach($aAdj as $val){
							$adjItem = explode('_', $val);
							if($adjItem[0]>0 && $adjItem[2]>0){
								if(!$this->_checkStore($adjItem[0], $adjItem[2]*$aCart['cart'][$cartKey])){
									$aMsg[] = __($goods_info['name'].'<br>配件库存不足');
									return false;
								}
							}
						}
					}
					return $this->save($objType, $aCart);
				}else{
					$aMsg[] = __('参数错误');
					return false;
				}
				break;
			case 'p':
				if($quantity > 0){
					$aCart = $this->getCart('p');
					$aCart[$cartKey]['num'] = $quantity;
					if (!$this->_checkGoodsStore($cartKey, $aCart[$cartKey]['num'])) {
						$aMsg[] = __('捆绑商品库存不足');
						return false;
					}
					return $this->save('p', $aCart);
				}else{
					$aMsg[] = __('参数错误');
					return false;
				}
				break;
			case 'f':
				$oGift = &$this->system->loadModel('trading/gift');
				$aGiftInfo = $oGift->getGiftById($cartKey);

				if (intval($cartKey)>0) {
					if ($oGift->isOnSale($aGiftInfo, $this->memInfo['member_lv_id'], $quantity)) {
						if ($this->memInfo['point']>=$aGiftInfo['point']*$quantity){//判断积分是否足够
							$aCart = $this->getCart($objType);
							$aCart[$cartKey]['num'] = $quantity;
							return $this->save($objType, $aCart);
						}else{
							$aMsg[] = __('用户积分不足');
							//                            trigger_error(__('用户积分不足'),E_USER_ERROR);
							return false;
						}
					}else{
						$aMsg[] = __('库存不足/购买数量超过限定数量/过期/超过最大购买限额');
						//                        trigger_error(__('库存不足/购买数量超过限定数量/过期/超过最大购买限额'),E_USER_ERROR);
						return false;
					}
				}
				break;
		}
	}


	/**
     * 将购物车中几种/几个商品数量写入 $_COOKIE['CART_COUNT'] $_COOKIE['CART_NUMBER']
     * @global string document the fact that this function uses $_myvar
     * @staticvar integer $staticvar this is actually what is returned
     * @param string $param1 name to declare
     * @param array $aCart 购物车数组
     * @return bool
     */
	function setCartNum(&$aCart)
	{
		//$sale = &$this->system->loadModel('trading/sale');
		$sale=&new sale_mdl();
		$trading = $sale->getCartObject($aCart,$GLOBALS['runtime']['member_lv'],true);
		$count = count($trading['products'])+count($trading['gift_e'])+count($trading['package']);
		if($trading['products'])
		foreach($trading['products'] as $rows){
			$number += $rows['nums'];
		}
		if($trading['gift_e'])
		foreach($trading['gift_e'] as $rows){
			$number += $rows['nums'];
		}
		if($trading['package'])
		foreach($trading['package'] as $rows){
			$number += $rows['nums'];
		}
		if($count!=$_COOKIE['CART_COUNT']){
			$this->system->setCookie('CART_COUNT',$count);
		}
		if($number!=$_COOKIE['CART_NUMBER']){
			$this->system->setCookie('CART_NUMBER',$number);
		}
	}



	function checkoutInfo(&$aCart, &$aMember, $aParam=null) {
		//$sale = &$this->system->loadModel('trading/sale');
		$sale=  &new sale_mdl();
		$trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true);
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

	//判断购物车中是否存在限时抢购商品
	function limitGoodsInCart($cart,&$limitGoodsCart){
		$scareModel=new mdl_scare();
		foreach ($cart as $k=>$c) {
			$tmp = explode('-',$k);
			$scareInfo = $scareModel->getFieldByGoodsId($tmp[0]);
			if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['scare_count']>0) {
				$limitGoodsCart[$k]=$c;
			}
		}
		return $limitGoodsCart ? true : false;
	}
}
?>