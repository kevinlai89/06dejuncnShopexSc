<?php
require_once(CORE_DIR.'/shop/controller/ctl.cart.php');
if (!class_exists('sale_mdl')) {
	require_once('sale.mdl.php');

}
if (!class_exists('cart_mdl')) {
	require_once('cart.mdl.php');
}
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}

class scarebuying_shop_cct_cart extends ctl_cart{

	function scarebuying_shop_cct_cart(&$system){
		//parent::ctl_cart($system);
		//$this->objCart=& new cart_mdl();
		parent::shopPage($system);
		$this->_verifyMember(false);
		if(!$this->system->getConf('system.use_cart',true)){
			$system->responseCode(404);
			echo '<h1>cart has been disabled</h1>';
			exit();
		}
		//$this->objCart = &$this->system->loadModel('trading/cart');
		$this->objCart=& new cart_mdl();
		$this->objCart->checkMember($this->member);

		if($_POST['isfastbuy']){
			if($_POST['goods']){
				$aParams = $this->objCart->getParams($_POST['goods']);
				$this->cart = $this->objCart->setFastBuy('g', $aParams);
				setcookie('S[Cart_Fastbuy]', $this->objCart->_save($this->cart));
			}else{
				$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
			}
		}else{
			$this->cart = $this->objCart->getCart('all');
		}

		$this->products = $this->cart['g'];
		$this->pkggoods = $this->cart['p'];
		$this->gifts = $this->cart['f'];
		
		$this->action = $this->system->request['action'];
	}
	function cartTotal(){
		$this->ctl_cart();
		// $sale = &$this->system->loadModel('trading/sale');
		$sale=&new sale_mdl();
		$trading = $sale->getCartObject($this->cart,$GLOBALS['runtime']['member_lv'],true);
		$this->pagedata['trading'] = &$trading;
		$this->__tmpl = 'cart/cart_total.html';
		$this->output();
	}

	function index(){
		$this->title = __('查看购物车');
		//$sale = &$this->system->loadModel('trading/sale');
		$sale=&new sale_mdl();
		//print_r($this->cart);
		$trading = $sale->getCartObject($this->cart,$GLOBALS['runtime']['member_lv'],true);

		$number=count($trading['products'])+count($trading['gift_e'])+count($trading['package']);
		if($number!=$_COOKIE['CART_COUNT']){
			$this->system->setCookie('CART_COUNT',$number);
		}
		$this->pagedata['trading'] = &$trading;
		$cur = &$this->system->loadModel('system/cur');
		$aCur = $cur->getFormat($this->system->request['cur']);
		$this->pagedata['currency'] = json_encode($aCur);

		header("Expires: -1");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store");
		$this->pagedata['_MAIN_']=CORE_DIR.'/shop/view/cart/index.html';
		$this->output();
	}

	function view($mini=0){
		// $sale = &$this->system->loadModel('trading/sale');
		$sale=&new sale_mdl();
		$this->cart = $this->objCart->getCart('all');
		$this->pagedata['trading'] = $sale->getCartObject($this->cart,$GLOBALS['runtime']['member_lv'],true);
		$this->pagedata['cartCount'] = $_COOKIE['CART_COUNT'];
		$this->pagedata['cartNumber'] = $_COOKIE['CART_NUMBER'];
		$this->__tmpl = $mini?'cart/mini_cart.html':'cart/view.html';
		$this->output();
	}





	function shipping(){
          if($_POST['isgroupbuy']==2){
        require_once(PLUGIN_DIR.'/app/group_activity/group_activity_shop_ctl_cart.php');
         $group_activity_ctl_product=new group_activity_shop_ctl_cart();
         $group_activity_ctl_product->shipping();
        }else{
		//$sale = &$this->system->loadModel('trading/sale');
		$sale=&new sale_mdl();
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
		}
		$this->pagedata['shippings'] = $aShippings;
		$this->display('cart/checkout_shipping.html');
        }
	}






	function applycoupon(){
		$this->begin($this->system->mkUrl('cart','index'),null,E_ERROR | E_USER_ERROR | E_USER_WARNING);
		$oCoupon = &$this->system->loadModel('trading/coupon');
		if (!empty($_POST['coupon'])) {
			//$oSale = &$this->system->loadModel('trading/sale');
			$oSale=&new sale_mdl();
			$oPromotion = &$this->system->loadModel('trading/promotion');
			$trading = $oSale->getCartObject($this->cart, $GLOBALS['runtime']['member_lv'], true);
			if ($trading['ifCoupon']) {
				if(!$oPromotion->apply_coupon_pmt($trading, $_POST['coupon'], $GLOBALS['runtime']['member_lv'])){
					$this->end(false, __('无效优惠券'),$this->system->mkUrl('cart','index') );
				}
			}else{
				//                $this->setError('10000');
				trigger_error(__('有促销活动期间是不否允许使用优惠券'),E_USER_ERROR);
				$this->end(false, __('有促销活动期间是不否允许使用优惠'), $this->system->mkUrl('cart', 'index'));
			}
		}else{
			trigger_error(__('请输入优惠券'),E_USER_ERROR);
			$this->end(false, __('请输入优惠券'), $this->system->mkUrl('cart', 'index'));
		}

		$this->end(true, __('成功加入购物车'), $this->system->mkUrl('cart', 'index'));
	}

	function total(){
        if($_POST['isgroupbuy']==2){
        require_once(PLUGIN_DIR.'/app/group_activity/group_activity_shop_ctl_cart.php');
         $group_activity_ctl_product=new group_activity_shop_ctl_cart();
         $group_activity_ctl_product->total();
        }else{
		$tarea = explode(':', $_POST['area'] );
		$_POST['area'] = $tarea[count($tarea)-1];
		$this->pagedata['trading'] = $this->objCart->checkoutInfo($this->cart, $this->member, $_POST);
		//$cartModel=new cart_mdl();
		//$this->pagedata['trading']=$cartModel->checkoutInfo($this->cart, $this->member, $_POST);
		$this->__tmpl='cart/checkout_total.html';
		$this->output();
        }
	}
	function updateCart($objType='g', $key=''){
		parent::updateCart($objType,$key);

	}
	/**
     * checkout
     * 切记和admin/order:create保持功能上的同步
     *
     * @access public
     * @return void
     */
	function checkout($isfastbuy=0){
        if($_POST['isgroupbuy']==2){
         require_once(PLUGIN_DIR.'/app/group_activity/group_activity_shop_ctl_cart.php');
         $group_activity_ctl_product=new group_activity_shop_ctl_cart();
         $group_activity_ctl_product->checkout($isfastbuy);
        }else{
		$this->pagedata['_MAIN_']=CORE_DIR.'/shop/view/cart/checkout.html';
		if($isfastbuy){
			$this->cart = $this->objCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
			$this->products = $this->cart['g'];
			$_POST['isfastbuy'] = 1;
		}
		$this->title = __('填写购物信息');
		if(count($this->products['cart'])+count($this->pkggoods)+count($this->gifts) == 0){
			$this->redirect('cart');
			exit;
		}

		//判断购物车中否是存在限时抢购商品，如果存在则要求登陆
		if ($this->objCart->limitGoodsInCart($this->cart['g']['cart'])&&!$this->member['member_id']) {
			$this->redirect('cart','loginBuy');
			exit;
		}
		if(!$this->member['member_id'] && !$_COOKIE['ST_ShopEx-Anonymity-Buy']){
			$this->redirect('cart','loginBuy',array($_POST['isfastbuy']));
			exit;
		}
		//$cartModel=new cart_mdl();
		//$this->pagedata['trading']=$cartModel->checkoutInfo($this->cart, $this->member, $_POST);
		$aOut = $this->objCart->getCheckout($this->cart, $this->member, $this->system->request['cur']);
		//$aOut = $cartModel->getCheckout($this->cart, $this->member, $this->system->request['cur']);
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
	}

	function addGoodsToCart($gid=0, $pid=0, $stradj='', $pmtid=0, $num=1) {

	
		$aParams = $this->objCart->getParams($_POST['goods'],$gid, $pid, $stradj, $pmtid);
      
		//限时抢购
		$scareModel=new mdl_scare();
		$scareInfo=$scareModel->getFieldByGoodsId($gid);
      

        $scareCart=$this->objCart->getCart();
      
        
        $scarenum_id=0;
      foreach($scareCart['g'] as $k=>$v){
          foreach($v as $kal=>$val){
            $kala=explode('-',$kal);
            if($gid!=0){
            if($kala[0]==$gid){
          $scarenum_id=$scarenum_id+$val;
            }
          }else{
                if($kala[0]==$_POST['goods']['goods_id']){
               $scarenum_id=$scarenum_id+$val;
            }

          }
          }
      }
     

    
		if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')) {
        if($scareInfo['buycountlimit']){
             if(!$_POST['goods']['num']){
       $goods_nums=$scarenum_id+$num;
      }else{
        $goods_nums=$scarenum_id+$_POST['goods']['num'];
      }
		if (intval($scareInfo['buycountlimit'])<intval($goods_nums)) {
		$this->begin($_SERVER['HTTP_REFERER']);
		$this->setError(10003);
		if($_POST['mini_cart']){
		header("HTTP/1.0 404 Not Found");
		}
		trigger_error('加入购物车失败：超出限购数量！',E_USER_ERROR);
		$this->end();
		exit;
		}
		}
        }
         if($aParams['pid'] == -1){
            $this->begin($_SERVER['HTTP_REFERER']);
            trigger_error(__('加入购物车失败：无此货品'),E_USER_ERROR);
            $this->end();
        }
        $_num = intval($aParams['num']);
        if($_num){
            $num = $_num;
        }else{
            $num = intval($num);
        }
        if(!$num) $num = 1;

        $status = $this->objCart->addToCart('g', $aParams, $num);
       
        if($status === 'notify'){
            $this->begin($this->system->mkUrl("product","gnotify",array($gid, $pid)));
            $this->setError(10001);
            if($_POST['mini_cart']){
                header("HTTP/1.0 404 Not Found");
            }
            trigger_error(__('加入购物车失败：商品缺货，转入缺货登记'),E_USER_ERROR);
            $this->end();
        }elseif(!$status){
            $this->begin($_SERVER['HTTP_REFERER']);
            $this->setError(10002);
            if($_POST['mini_cart']){
                header("HTTP/1.0 404 Not Found");
            }
            trigger_error(__('加入购物车失败: 商品库存不足或者提交参数错误！'),E_USER_ERROR);
            $this->end();
        }else{
            if($_POST['fastbuy']){
                $this->checkout();
            }else{
                if($_POST['mini_cart']){
                    $this->view(1);
                    exit;
                }
                $this->redirect('cart');
            }
        }
        
         
        //parent::addGoodsToCart($gid=0, $pid=0, $stradj='', $pmtid=0, $num=1);
          
	


	}

}
?>