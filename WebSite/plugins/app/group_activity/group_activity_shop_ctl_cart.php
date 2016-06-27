<?php
/**
 * @author chenping
 * @version $Id: group_activity_shop_ctl_cart.php 2010-3-25 14:35:01 $
 * @package group_activity
 * @uses ctl_cart
 * //=========================
 * 	接管购物车的方法
 * //==========================
 *
 */
if (!class_exists('ctl_cart')) {
	require_once(CORE_DIR.'/shop/controller/ctl.cart.php');
}
class group_activity_shop_ctl_cart extends ctl_cart{

	/**
	 *
	 * @param unknown_type $system
	 */
	function group_activity_shop_ctl_cart(&$system){
		parent::shopPage($system);
		$this->_verifyMember(false);
		if(!$this->system->getConf('system.use_cart',true)){
			$system->responseCode(404);
			echo '<h1>cart has been disabled</h1>';
			exit();
		}
		/*******************************************
		*			调用 app/group_activity下的模型 chenping 2010-3-26 13:39
		*******************************************/
		//$this->objCart = &$this->system->loadModel('trading/cart');
		$this->objCart	=	$this->system->loadModel('plugins/group_activity/group_activity_cart');
		$this->objCart->checkMember($this->member);

		if($_POST['isfastbuy']){
			if($_POST['goods']){
				$aParams = $this->objCart->getParams($_POST['goods']);
				$this->cart = $this->objCart->setFastBuy('g', $aParams);
				setcookie('S[Cart_Fastbuy]', $this->objCart->_save($this->cart));
			}else{
				$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
			}
		}else if ($_POST['isgroupbuy']) {			//进行团购 chenping 2010-3-25 14:40
			if ($_POST['goods']) {
				$aParams = $this->objCart->getParams($_POST['goods']);
				$this->cart = $this->objCart->setGroupBuy('g',$aParams);
				setcookie('S[Cart_Groupbuy]',$this->objCart->_save($this->cart));
			}else {
				$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Groupbuy']);
			}

		}else{
			$this->cart = $this->objCart->getCart('all');
		}

		$this->products = $this->cart['g'];
		$this->pkggoods = $this->cart['p'];
		$this->gifts = $this->cart['f'];
	}

	/**
	 * $isfastbuy 1、快速购买 2、立即团购
	 *
	 * @param unknown_type $isfastbuy
	 */
	function checkout($isfastbuy=0){
		//快速购买
		if($isfastbuy==1){
			$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
			$this->products = $this->cart['g'];
			$_POST['isfastbuy'] = 1;
		}
		//立即团购
        
		if ($isfastbuy==2) {
			$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Groupbuy']);
			$this->products = $this->cart['g'];
			$_POST['isgroupbuy'] = 2;
		}
		//判断团购活动是否有效
		if ($_POST['isgroupbuy']==2) {
			if (!$this->member['member_id']) {
				$this->splash('failed','back',__('登陆后，方可进行团购'));
			}
			$goodsActModel = $this->system->loadModel('plugins/group_activity');
			$actInfo = $goodsActModel->getValidGoodsActivityByGid($_POST['goods']['goods_id']);

            $goodsActivityInfo = $goodsActModel->getOpenActivityByGid($_POST['goods']['goods_id']);
            $this->pagedata['goodsActivityInfo_score']=$goodsActivityInfo;
            $statues=$goodsActModel->getstate($goodsActivityInfo['state']);
           
			if (!$actInfo) {
				$this->splash('failed','back',__('团购'.$statues));
			}
			$goodsnum=$goodsActModel->getGoodsNum($actInfo['act_id']);
           $is_postage_f=unserialize($actInfo['postage']);
         
            if($is_postage_f['is_postage']==1){
                if($_COOKIE['IS_POSTAGE']){//销毁上次订单页的cookie
                     $this->system->setcookie('IS_POSTAGE','',time()-10000);
                }
                if($_POST['goods']['num']>=$is_postage_f['buycount']){
                    $this->system->setcookie('IS_POSTAGE',1,null);
                }elseif($goodsnum>=$is_postage_f['buycount']){
                    if($is_postage_f['postage_favorable']=='2'){//修改单笔订单购买数量未达到要求数量但是却仍然免运费
                        $this->system->setcookie('IS_POSTAGE',1,null);
                        
                    }
                }

            }elseif($is_postage_f['is_postage']==2 && $_COOKIE['IS_POSTAGE']){
                $this->system->setcookie('IS_POSTAGE','',time()-10000);

            }
          
			if (($goodsnum+$_POST['goods']['num'])>$actInfo['limitnum'] && $actInfo['limitnum']) {
				$leftnum=$actInfo['limitnum']-$goodsnum;
				$this->splash('failed','back',"你顶多还可以购买".$leftnum."件团购商品");
			}
		}
		//重新定义跳转页
		$this->template_dir = CORE_DIR.'/shop/view/';
		$this->pagedata['_MAIN_'] = dirname(__FILE__).'/view/shop/cart/checkout.html';
		$this->pagedata['checkout_total_url'] = dirname(__FILE__).'/view/shop/cart/checkout_total.html';

		$this->title = __('填写购物信息');
		if(count($this->products['cart'])+count($this->pkggoods)+count($this->gifts) == 0){
			$this->redirect('cart');
			exit;
		}

		if(!$this->member['member_id'] && !$_COOKIE['ST_ShopEx-Anonymity-Buy']){
			$this->redirect('cart','loginBuy',array($_POST['isfastbuy']));
			exit;
		}

		$aOut = $this->objCart->getCheckout($this->cart, $this->member, $this->system->request['cur']);
		$this->pagedata['has_physical'] = $aOut['has_physical'];
		$this->pagedata['minfo'] = $aOut['minfo'];
		$this->pagedata['areas'] = $aOut['areas'];
		$this->pagedata['dlytime'] = date('Y-m-d', time()+floatval($this->system->getConf('site.delivery_time'))*3600*24);
		$this->pagedata['currencys'] = $aOut['currencys'];
		$this->pagedata['currency'] = $aOut['currency'];
		$payment = $this->system->loadModel('trading/payment');
		$payment->showPayExtendCon($aOut['payments']);
		$this->pagedata['payments'] = $aOut['payments'];
		if ($aOut['payments']){
			foreach($aOut['payments'] as $key => $val){
				if(!$this->member['member_id'] && $val['pay_type'] == 'deposit'){
					unset($this->pagedata['payments'][$key]);
					continue;
				}
				$this->pagedata['payments'][$key]['config']=unserialize($val['config']);
			}
		}
		$this->pagedata['config'] = unserialize($aOut['payments']['config']);
        $this->pagedata['group_price'] = $_POST['current_price'];//显示团购价格
  
		$this->pagedata['trading'] = $aOut['trading'];
      
     
      
		if($this->member['member_id']){
			$member = &$this->system->loadModel('member/member');
			$addrlist = $member->getMemberAddr($this->member['member_id']);
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
		}else{
			setcookie('S[ST_ShopEx-Anonymity-Buy]','',time()-1000);
		}
       
		$this->output();
	}
	function output(){
		$this->template_dir = CORE_DIR.'/shop/view/';
		parent::output();
	}
	function total(){
		$tarea = explode(':', $_POST['area'] );
		$_POST['area'] = $tarea[count($tarea)-1];
		$trading=$this->objCart->checkoutInfo($this->cart, $this->member, $_POST);
	 
       if($_COOKIE['IS_POSTAGE']){
           $trading['total_amount']=$trading['total_amount']-$trading['cost_protect']-$trading['cost_freight'];
           $trading['final_amount']=$trading['final_amount']-$trading['cost_protect']-$trading['cost_freight'];
           $trading['cost_freight']=0;
           $trading['cost_protect']=0;

       }
       $this->pagedata['trading']=$trading;
          
		$this->__tmpl=dirname(__FILE__).'/view/shop/cart/checkout_total.html';
       
		$this->output();
	}
	function shipping(){	
		$sale = $this->system->loadModel('plugins/group_activity/group_activity_sale');
		
		$trading = $sale->getCartObject($this->cart,$GLOBALS['runtime']['member_lv'],true);
		$shipping = &$this->system->loadModel('trading/delivery');
		$aShippings = $shipping->getDlTypeByArea($_POST['area']);
		foreach($aShippings as $k=>$s){
              
			$aShippings[$k]['price'] = cal_fee($s['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$s['price']);
			$s['pad']==0?$aShippings[$k]['has_cod'] = 0:$aShippings[$k]['has_cod'] = 1;
			if($s['protect']==1){
				$aShippings[$k]['protect'] = true;
			}else{
				$aShippings[$k]['protect'] = false;
			}
           
            if($_POST['isgroupbuy']==2){
                 $array = explode('货到付款',$s['dt_name']);
                if( count($array)>1  || $s['dt_name']=='货到付款'){
                    unset($aShippings[$k]);
                }
                $array1 = explode('上门自取',$s['dt_name']);
                if( count($array1)>1  || $s['dt_name']=='上门自取'){
                    unset($aShippings[$k]);
                }
            } 
           
		}
       
		
		$this->pagedata['shippings'] = $aShippings;
		$this->display('cart/checkout_shipping.html');
	}
	
	function display($view){
		$this->template_dir = CORE_DIR.'/shop/view/';
		parent::display($view);
	}
}
?>