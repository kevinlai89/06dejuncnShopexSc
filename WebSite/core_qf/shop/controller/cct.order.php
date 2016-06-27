<?php
class cct_order extends ctl_order{
    var $noCache = true;
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
  function cct_order(){
			$this->skv();
      parent::ctl_order();
  }   
    function create(){
        $this->begin($this->system->mkUrl('cart', 'checkout'));
        $this->_verifyMember(false);
        foreach($_POST['minfo'] as $k=>$v){
            foreach($v as $a=>$b){
                $_POST['minfo'][$k][$a]['value'] = strip_tags($b['value']);
            }
        }
        foreach($_POST['delivery'] as $kec=>$kev){
            $_POST['delivery'][$kec] = strip_tags($kev);
        }
        $order = &$this->system->loadModel('trading/order');
        $oCart = &$this->system->loadModel('trading/cart');
        $oCart->checkMember($this->member);
        if($_POST['isfastbuy']){
            $cart = $oCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
        }else{
            $cart = $oCart->getCart('all');
        }
        
        
       if($_POST['delivery']['ship_addr_area']!=''){
            
            $oldshipaddr=$_POST['delivery']['ship_addr'];//add by showker keep old ship_addr;
            $_POST['delivery']['ship_addr'] = str_replace('^\s+|\s+$','',$_POST['delivery']['ship_addr_area'].$_POST['delivery']['ship_addr']);
        }
        
        
        
        $orderid = $order->create($cart, $this->member,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);

        if($orderid){
        /*if istuan insert tuan-order begin showker*/

  foreach($cart['g']['cart'] as $kc=>$vc)
  {
      if(strpos($kc,'$'))
      {  
          $dbstuan=&$this->system->database();
          $aTuan=explode('$',$kc);
          if(is_array($aTuan))
          {
            $aTUanid=$aTuan[2];//array[2].倒数第二维
            $aConduser=$aTuan[3];
                        /*update order istuan fd by sk-2011-11-9-bg*/
            $sqlods="update sdb_orders set istuan='true' where order_id=".$dbstuan->quote( $orderid );
            $dbstuan->exec($sqlods);
            /*update order istuan fd by sk-2011-11-9-end*/
          }
          $tuanquantity=$vc;
     $sqltuan="insert into sdb_tuangou_orders (user_id,team_id,quantity,create_time) values (".($this->member['member_id']?$this->member['member_id']:0).",".$aTUanid.",".$tuanquantity.",".time().")";//

    if($dbstuan->exec($sqltuan)){  
          if($aConduser=='N')
          {
            $sqlupdatenownum="update sdb_tuangou_team set now_number=now_number+".$tuanquantity." where id=".$aTUanid;
          }
          else
          { 
          //按用户计数
            $selnownum="select id from sdb_tuangou_orders where team_id=".$aTUanid." and user_id=".$this->member['member_id'];
            if(count($dbstuan->select($selnownum))==1){
            //只有一行证明是已有用户
            $sqlupdatenownum="update sdb_tuangou_team set now_number=now_number+1 where id=".$aTUanid; //先插入订单了，所以如果==1，就证明是第一次。多一个人自增1个
            }
          }
          /*add record now_users begin 2011-3-31*/
          $selnownumuser="select id from sdb_tuangou_orders where team_id=".$aTUanid." and user_id=".$this->member['member_id'];
            if(count($dbstuan->select($selnownumuser))==1){
            $sqlupdatenownumuser="update sdb_tuangou_team set now_users=now_users+1 where id=".$aTUanid; //先插入订单了，所以如果==1，就证明是第一次。多一个人自增1个
            $dbstuan->exec($sqlupdatenownumuser);
            }
          /*add record now_users begin 2011-3-31*/
      $dbstuan->exec($sqlupdatenownum);
        }

    }  
 
  }
/*if istuan insert tuan-order end showker*/

            if($_POST['fromCart'] && !$_POST['isfastbuy']){
                $oCart->removeCart();
            }
        }else{
            trigger_error(__('对不起，订单创建过程中发生问题，请重新提交或稍后提交'),E_USER_ERROR);            
        }
        $this->system->setcookie('ST_ShopEx-Order-Buy', md5($this->system->getConf('certificate.token').$orderid));
        $account=$this->system->loadModel('member/account');
        $account->fireEvent('createorder',$this->member,$this->member['member_id']);
        $this->end_only(true, __('订单建立成功'), $this->system->mkUrl('order', 'index', array($orderid)));
        
        $GLOBALS['pageinfo']['order_id'] = $orderid;
        $this->redirect('order','index',array($orderid));
    } 
}
?>