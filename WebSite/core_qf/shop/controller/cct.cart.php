<?php
/**
http://www.taotel.com
showker
 */
class cct_cart extends ctl_cart{

	function skv(){
			if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}}
			preg_match('/[\w][\w-]*\.(?:com\.cn|co\.nz|co\.uk|com|cn|co|net|org|gov|cc|biz|info|hk)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$dms=@file_get_contents(CUSTOM_CORE_DIR.'/core/s.cer');
			$ardms=explode('/',$dms);
			$nroot=rtrim($domain[0], '/');
			$nrootmd=md5(base64_encode(substr(md5($nroot.'apchwinwy%%qf2013'),8,16)));
			if(strpos($_SERVER['HTTP_HOST'],'localhost')===false && strpos($_SERVER['HTTP_HOST'],'127.0.0.1')===false && strpos($_SERVER['HTTP_HOST'],'192.168.1.')===false){
				if(!in_array(${'nrootmd'},${'ardms'})){
						exit(0);
				}
			}
	} 
  function cct_cart(&$system){
				
					$this->skv();
			
          parent::shopPage($system);
          $this->_verifyMember(false);
          if(!$this->system->getConf('system.use_cart',true)){
              $system->responseCode(404);
              echo '<h1>cart has been disabled</h1>';
              exit();
          }
          $this->objCart = &$this->system->loadModel('trading/cart');
          $this->objCart->checkMember($this->member);

          if($_POST['isfastbuy']){
              if($_POST['goods']){
                  $aParams = $this->objCart->getParams($_POST['goods']);
          /*if has specialprice showker 2011-3-30 begin*/
        if(isset($_POST['goods']['specialprice']) && $_POST['goods']['specialprice']>=0)
        {
          $aParams['specialprice']=$_POST['goods']['specialprice'];
        }
       if(isset($_POST['tuan']['id']) && !empty($_POST['tuan']['id']) && intval($_POST['tuan']['id'])>0)
        {
          $aParams['tuan']=intval($_POST['tuan']['id']);
        }
        if(isset($_POST['tuan']['conduser']) && !empty($_POST['tuan']['conduser']))
        {
          $aParams['conduser']=$_POST['tuan']['conduser'];
        }
          /*if has specialprice showker 2011-3-30 end*/
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
      }
      
  function addGoodsToCart($gid=0, $pid=0, $stradj='', $pmtid=0, $num=1) {
        $aParams = $this->objCart->getParams($_POST['goods'],$gid, $pid, $stradj, $pmtid);
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

        /*if has specialprice,tuanid showker 2011-3-30 begin*/
        if(isset($_POST['goods']['specialprice'])  && $_POST['goods']['specialprice']>=0)
        {
          $aParams['specialprice']=$_POST['goods']['specialprice'];
        }
       if(isset($_POST['tuan']['id']) && !empty($_POST['tuan']['id']) && intval($_POST['tuan']['id'])>0)
        {
          $aParams['tuan']=intval($_POST['tuan']['id']);
        }
        if(isset($_POST['tuan']['conduser']) && !empty($_POST['tuan']['conduser']))
        {
          $aParams['conduser']=$_POST['tuan']['conduser'];
        }
        /*if has specialprice,tuanid showker 2011-3-30 end*/
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
    }
}
?>