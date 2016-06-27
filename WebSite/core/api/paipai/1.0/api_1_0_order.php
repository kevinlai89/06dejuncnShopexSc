<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API matrix_order模块部份
 * @package
 * @version 3.0: 
 * @copyright 2003-2011 ShopEx
 * @author yangminsheng
 * @license Commercial
 */
class api_1_0_order extends shop_api_object {
    /**
     *创建订单
     *@author yangminsheng
     *@date 2011-11-23
     *@params  $data 订单结构体
     */

    function paipai_trade_order_add($data){
        $matrix = &$this->system->loadModel('system/matrix');
        $matrix->check_task_exists($data);

        $order_info = json_decode($data['trade_data'],true);
        $order_info = $order_info['trade'];

        if($order_info['tid'] && $order_info['tid'] != '0'){
            $order_status = $this->status2local($order_info['status']);
            $order_paystatus = $this->pay_status2local($order_info['pay_status']);
            //$order_isdelivery = $this->is_delivery_status2local($order_info['is_delivery']);
            $order = array_merge($order_status,$order_paystatus);

            $order['paipai_order_id']=$order_info['tid'];
            $order['createtime'] = trim(strtotime($order_info['created']));
            $order['last_change_time'] =trim(strtotime($order_info['modified']));
            //$order['consign_time'] = strtotime($order_info['consign_time']);
            //$order['pay_time'] = trim(strtotime($order_info['pay_time']));
            $order['cost_item'] = $order_info['total_goods_fee'];
            $order['total_amount'] = trim($order_info['total_trade_fee']);
            $order['payed'] = trim($order_info['payed_fee']);
            $order['currency'] = $order_info['currency'];
            $order['cur_rate'] = $order_info['currency_rate'];
            $order['score_g'] = $order_info['buyer_obtain_point_fee'];
            $order['score_u'] = $order_info['point_fee'];
            $order['shipping_id'] = $order_info['shipping_tid'];
            $order['shipping'] = $order_info['shipping_type'];
            $order['cost_freight'] = $order_info['shipping_fee'];
            $order['is_protect'] = ($order_info['is_protect'] == '')?"false":"true";
            $order['cost_protect'] = $order_info['protect_fee'];
            /*if($order_info['payment_tid']=='0'){
                $order_info['payment_tid'] = '-1';
            }*/
            if($order_info['payment_tid']=='TENPAY'){
                $order['payment'] = 27;
            }elseif($order_info['payment_tid']=='OFF_LINE'){
            
            }elseif($order_info['payment_tid']=='UNKNOW'){
            
            }
            $order['payment'] = 27;
            //$order['payment_type'] = $order_info['payment_type'];
            $order['ship_name'] = $order_info['receiver_name'];
            $order['ship_email'] = $order_info['receiver_email'];
            if(trim($order_info['receiver_district'])){
                  $order['ship_area'] .= 'mainland:'.$order_info['receiver_state'].'/'.$order_info['receiver_city'].'/'.$order_info['receiver_district'].':'.$this->getdeliverid($order_info['receiver_city'],$order_info['receiver_district']);
            }else {
                  $order['ship_area'] .= 'mainland:'.$order_info['receiver_state'].'/'.$order_info['receiver_city'].':'.$this->getdeliverid($order_info['receiver_city'],false);
            }
            $order['ship_addr'] = $order_info['receiver_address'];
            $order['ship_zip'] = $order_info['receiver_zip'];
            $order['ship_mobile'] = $order_info['receiver_mobile'];
            $order['ship_tel'] = $order_info['receiver_phone'];
            $order['ship_time'] = $order_info['receiver_time'];
            $order['weight'] = $order_info['total_weight'];
            $order['tostr'] = $order_info['title'];
            $itemnum = 0;
            foreach($order_info['orders']['order'] as $k=>$v){
                $itemnum += $v['items_num']; 
            }
            $order['itemnum'] = $itemnum;
            $order['is_tax'] = 'false';
            $order['cost_tax'] = '0';
            $order['cost_payment'] = $order_info['pay_cost'];
            $order['advance'] = '0';
            $order['discount'] = trim($order_info['discount_fee']);
            $order['final_amount'] = trim($order_info['total_trade_fee'])-trim($order_info['discount_fee']);
            $order['markstar'] = 'N';
            $order['memo'] = $order_info['trade_memo'];
            $order['print_status'] = '0';
            $order['disabled'] = 'false';
            //$order['alipay_payid'] = $order_info['buyer_alipay_no'];
			$order['order_refer'] = "paipai";
            //插入会员信息
            //$paipai=$this->loadPlugin('paipai');

            $member_id=$this->save_member_info($order_info,$order['ship_area']);
			//会员信息 todo
            $order['member_id'] = $member_id;
            if(count($order_info['orders']['order'])>0){
                $order['tostr']='';
                foreach ($order_info['orders']['order'] as $k_tostr => $v_tostr){
                    $order['tostr'].= addslashes($v_tostr['title']).'('.$v_tostr['items_num'].')';
                }
            }
            $sel_order = $this->db->selectrow("SELECT order_id,ship_status FROM sdb_orders WHERE paipai_order_id = '".$order_info['tid']."'");
            if(!empty($sel_order['order_id'])){
               $order['order_id'] = $sel_order['order_id'];
			   $local_order_id = $order['order_id'];
               if($sel_order['ship_status'] == 1){
                  $order['ship_status'] = 1;
			   }else{
                  $order_shipstatus = $this->ship_status2local($order_info['ship_status']);
                  $order['ship_status'] = $order_shipstatus['ship_status'];
			   }
			}else{
               $order['order_id'] = $this->gen_order_id();
			   $local_order_id = $order['order_id'];
			   $this->addLog($local_order_id,'远程订单创建',null, null , '订单创建','success',$order['createtime'],$order['last_change_time']);
               $order_shipstatus = $this->ship_status2local($order_info['ship_status']);
               $order['ship_status'] = $order_shipstatus['ship_status'];

			}


            if($order_paystatus['pay_status']==1){
                  $order['payed'] = $order['total_amount'];
                  $order['pay_status'] = 1;
            }else{
                  $order['pay_status'] = 0;
                  $order['payed'] = 0;
			}
            $order_tmp = $this->db->exec("SELECT * FROM sdb_orders WHERE paipai_order_id = '".$order_info['tid']."'",true,true);
            $sql = $this->db->GetUpdateSql($order_tmp,$order,true);

            /*暂时不计积分
             * if ($order['status']=="TRADE_FINISHED"){
                    $oMemberPoint=$this->system->loadModel('trading/memberPoint');
                    $oMemberPoint->payAllGetPoint($member_id,$order_info['tid']);
                }*/
           $modTag = $this->system->loadModel('system/tag');
           $paipai_order = $modTag->getTagByName('order','拍拍');
           if(!$paipai_order){
                $modTag->newTag('拍拍','order');
           }
           $paipai_tag_id = $modTag->getTagByName('order','拍拍');
           if(!$modTag->getTagRel($paipai_tag_id,$order['order_id'])){
                $modTag->addTag($paipai_tag_id,$order['order_id']);
           }            //添加拍拍订单标签

            if($this->db->exec($sql)){
				$lastInsert_id = $this->db->selectrow('select order_id from sdb_orders where paipai_order_id = "'.$order_info['tid'].'"');

                //同步订单商品
                $order_item = array();
                //$tb_pro = $this->system->loadModel("trading/goods");
                foreach($order_info['orders']['order'] as $o_key=>$o_value){
                    foreach($o_value['order_items']['order_item'] as $order_items_k=>$order_items_v){
                        $order_item['name'] = addslashes($order_items_v['name']);
                        /*
                        if($order_items_v['sku_properties']){
                            $sku_props = explode(';',$order_items_v['sku_properties']);
                            $props_name = '';
                            $props_name_arr = array();
                            foreach ($sku_props as $pro_k=>$pro_v){
                                $props_name_arr = explode(':',$pro_v);
                                $props_name .= $props_name_arr[1].',';
                            }
                            $props_name = trim($props_name,',');
                            $order_item['name'] .= '('.$props_name.')';
                        }*/
                        $order_item['order_id'] = $local_order_id;
                        //$order_item['product_id'] = 000;
                        //$order_item['paipai_iid'] = $order_items_v['iid'];
                        $order_item['cost'] = $order_items_v['cost']*$order_items_v['num'];
//                        if ($order_info['status']=="TRADE_FINISHED"){
//                            $tb_pro->getTbStock($o_value['num_iid']);
//                        }
//                        if($o_value['refund_status']=='WAIT_SELLER_AGREE'){
//                            $order_item['refund_status'] = 6;
//                        }elseif($o_value['refund_status']=='WAIT_SELLER_CONFIRM_GOODS5'){
//                            $order_item['refund_status'] = 5;
//                        }elseif($o_value['refund_status']=='SUCCESS'){
//                            $order_item['refund_status'] = 7;
//                        }else{
//                            $order_item['refund_status'] = 0;
//                        }
//                        $order_item['taobao_iid'] = $o_value['num_iid'];
//                        $dly_status = $this->get_tb_dly_status($v['status']);
//                        if($dly_status){
//                            $order_item['dly_status'] = $dly_status;
//                        }else{
//                            $order_item['dly_status'] = 'storage';
//                        }
                        $order_item['dly_status'] = 'storage';
                        $order_item['price'] = $order_items_v['price'];
                        $order_item['amount'] = $order_items_v['price']*$order_items_v['num'];
                        $order_item['nums'] = $order_items_v['num'];
                        //$order_item['img_path'] =isset($o_value['pic_path'])?$o_value['pic_path']:"http://assets.taobaocdn.com/sys/common/img/nopic_50.png";
                        $order_item['product_id'] = 0;
                        $order_item['bn'] = $order_items_v['bn'];
                        $order_item['paipai_sku_id'] = $order_items_v['sku_id'];

                        $orderitem_tmp = $this->db->query("SELECT * FROM sdb_order_items WHERE order_id='".$local_order_id."' and bn='".$order_item['bn']."'",true,true);
                        $item_sql = $this->db->GetUpdateSql($orderitem_tmp,$order_item,true);

                        $this->db->exec($item_sql,true,true);
                    }
                }

            //生成收款单
            if($order['pay_status']==1){
                $pay_count = $this->db->select("SELECT * FROM sdb_payments WHERE order_id='".$local_order_id."'");
                if(count($pay_count)==0){
                    $pay['member_id'] = $member_id;
                    $pay['order_id'] = $local_order_id;
                    $pay['t_end'] = strtotime($order_info['created']);
                    $pay['t_begin'] = strtotime($order_info['created']);
                    $pay['trade_no'] = $order_info['alipay_no'];
                    $pay['bank'] = 'paipai';
                    $pay['payment_id'] = $this->gen_payment_id();
                    $pay['currency'] = 'CNY';
					$pay['op_id'] = 1;
                    $pay['money'] = $order['total_amount'];
                    $pay['cur_money'] = $order['total_amount'];
                    $pay['pay_type'] = 'online';
                    $pay['payment'] = 27;//??
                    $pay['paymethod'] = '财付通';
                    $pay['status'] = 'succ';
                    $pay['memo'] = '此订单支付来自于拍拍';
                    $pay_tmp = $this->db->exec("SELECT * FROM sdb_payments WHERE order_id='".$local_order_id."'",true,true);
                    $pay_sql = $this->db->GetUpdateSql($pay_tmp,$pay,true);
                    $this->db->exec($pay_sql,true,true);
                    $this->addLog($local_order_id,'订单'.$local_order_id.'付款'.$order['total_amount'],null, null , '付款','success',$pay['t_begin'],$pay['t_end']);
                }
            }

            //生成发货单
            if($order['ship_status']==1){
                $ship_count = $this->db->select("SELECT * FROM sdb_delivery WHERE order_id='".$local_order_id."'");
                if(count($ship_count)==0){
                    $ship_id = $local_order_id;
                    $ship['ship_addr'] =$order['ship_addr'];
                    $ship['ship_zip'] = $order['ship_zip'];
                    $ship['ship_tel'] = $order['ship_tel'];
                    $ship['ship_mobile'] = $order['ship_mobile'];
                    $ship['ship_name'] = $order['ship_name'];
                    $ship['ship_area'] = $order['ship_area'];

                    $ship['delivery_id'] = $this->getNewNumber('delivery');
                    $ship['order_id'] = $ship_id;
                    $ship['member_id'] = $member_id;
                    $ship['money'] = $order['cost_freight'];
                    $ship['type'] = 'delivery';
                    $ship['is_protect'] = 'false';
                    $ship['delivery'] = isset($order['shipping'])?$order['shipping']:'不需要物流';
                    $ship['logi_id'] = 'other';
                    $ship['logi_name'] = isset($order['shipping'])?$order['shipping']:'不需要物流';
                    $ship['logi_no'] = $order['shipping_id'];
                    $ship['ship_name'] = $order_info['receiver_name'];
                    $ship['t_begin'] = strtotime($order_info['created']);

                    $ship_tmp = $this->db->exec("SELECT * FROM sdb_delivery WHERE order_id='".$ship_id."'");
                    $ship_sql = $this->db->GetUpdateSql($ship_tmp,$ship,true);
                    
                    if($this->db->exec($ship_sql)){
                        $ship_id = $ship['delivery_id'];
                        foreach($order_info['orders']['order'] as $orders_k=>$orders_v){
                            foreach($orders_v['order_items']['item'] as $items_k=>$items_v){
                                $ship_item['delivery_id'] = $ship['delivery_id'];
                                $ship_item['item_type'] = 'goods';
                                $ship_item['product_id'] = 0;
                                $ship_item['product_bn'] = $items_v['bn'];
                                $ship_item['product_name'] = $items_v['name'];
                                $ship_item['number'] = $items_v['num'];
                                $ship_item_tmp = $this->db->exec("SELECT * FROM sdb_delivery_item WHERE delivery_id='".$ship_id."' and product_bn='".$items_v['bn']."'");
                                $ship_item_sql = $this->db->GetUpdateSql($ship_item_tmp,$ship_item,true);
                                $this->db->exec($ship_item_sql);
                            }
                        }  
                    }
					$Corplog['logi_id'] = $ship['logi_id'];
					$Corplog['order_id'] = $ship['order_id'];
					$Corplog['delivery_id']= $ship['delivery_id'];
					$Corplog['logi_no'] = $ship['logi_no'];
					$Corplog['op_name'] = null;
					$tradCorp = &$this->system->loadModel('trading/order');
					$tradCorp->Corp_log($Corplog);

					$this->_add_order_log($local_order_id,$ship['delivery_id']);
                }
            }
          }

          $this->api_response('true','',array('tid'=>$order_info['tid']),'','order');

        }else{
            $this->api_response('fail',false,'tid验证不通过','','order');
            exit;
        }
        
    }
    
    /**
     *订单状态转换
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $status 订单状态
     */
    function status2local($status){
        $array=array(
            'TRADE_ACTIVE'=>array("status"=>'active',"user_status"=>'null','confirm'=>'N'),
            'TRADE_FINISHED'=>array("status"=>'finish',"user_status"=>'shipped','confirm'=>'Y'),
            'TRADE_CLOSED'=>array("status"=>'dead',"user_status"=>'null','confirm'=>'N'));
        return $array[$status];
    }
    
    /**
     *支付状态转换
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $pay_status 订单状态
     */
    function pay_status2local($pay_status){
        $array=array(
        'PAY_NO'=>array("pay_status"=>0),
        'PAY_FINISH'=>array("pay_status"=>1),
        'PAY_TO_MEDIUM'=>array("pay_status"=>2),
        'PAY_PART'=>array("pay_status"=>3),
        'REFUND_PART'=>array("pay_status"=>4),
        'REFUND_ALL'=>array("pay_status"=>5));
        return $array[$pay_status];
    }
    
    /**
     *发货状态转换
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $ship_status 订单状态
     */
    function ship_status2local($ship_status){
        $array=array(
        'SHIP_NO'=>array("ship_status"=>0),
        'SHIP_PREPARE'=>array("ship_status"=>0),
        'SHIP_PART'=>array("ship_status"=>2),
        'SHIP_FINISH'=>array("ship_status"=>1),
        'RESHIP_PART'=>array("ship_status"=>3),
        'RESHIP_ALL'=>array("ship_status"=>4));
        return $array[$ship_status];
    }
    
    /**
     *获取地区ID
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $local_name 二级地区名称 $local_areas 三级地区名称
     */
    function getdeliverid($local_name,$local_areas){
        if($local_name){
            $sql="select region_id from sdb_regions where local_name = '".$local_name."'";
            $id = $this->db->selectrow($sql);
        }elseif($local_areas){
            $sql="SELECT region_id FROM sdb_regions WHERE `local_name` LIKE '".$local_areas."'";
            $id = $this->db->selectrow($sql);
        }
        return $id['region_id'];
    }
    
    //生成订单号
    function gen_order_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $order_id = mydate('YmdH').str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('SELECT order_id from sdb_orders where order_id ='.$order_id);
        }while($row);
        return $order_id;
    }
    
    //生成支付单号
    function gen_payment_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $payment_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select payment_id from sdb_payments where payment_id =\''.$payment_id.'\'');
        }while($row);
        return $payment_id;
    }
    
    
    //生成发货单号
    function getNewNumber($type){
        if ($type == 'return'){
            $sign = '9'.date("Ymd");
        }else{
            $sign = '1'.date("Ymd");
        }
        $sqlString = 'SELECT MAX(delivery_id) AS maxno FROM sdb_delivery WHERE delivery_id LIKE \''.$sign.'%\'';
        $aRet = $this->db->selectrow($sqlString);
        if(is_null($aRet['maxno'])) $aRet['maxno'] = 0;
        $maxno = substr($aRet['maxno'], -6) + 1;
        if ($maxno==1000000){
            $maxno = 1;
        }
        return $sign.substr("00000".$maxno, -6);
    }
    
     /**
     *是否实体配送状态转换
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $is_delivery 是否实体配送
     */
    function is_delivery_status2local($is_delivery){
        $array=array(
        'true'=>array("is_delivery"=>'Y'),
        'false'=>array("is_delivery"=>'N'));
        return $array[$is_delivery];
    }
    

     /**
     *存储拍拍的会员信息
     *@author yangminsheng
     *@date 2011-11-27
     *@params  $member_info 拍拍的会员信息
     */
	 function save_member_info($member_info,$ship_area){

	    $local_paipai_member = $this->db->selectrow('select member_id from sdb_members where uname = "'.$member_info['buyer_uname'].'"');
		if(!empty($local_paipai_member['member_id'])){
		   $member_id = $local_paipai_member['member_id'];
           $this->update_memaddr($member_info,$member_id,$ship_area);

			$paipai_member['name']=$member_info['receiver_name'];
			$paipai_member['mobile']=$member_info['receiver_mobile'];
			$paipai_member['tel']=$member_info['receiver_phone'];
			$paipai_member['province'] = $member_info['receiver_city'];
			$paipai_member['city'] = $member_info['receiver_district'];
			$paipai_member['addr'] = $member_info['receiver_address'];
			$paipai_member['zip'] = $member_info['receiver_zip'];
			$paipai_member['area']=$ship_area;
            $paipai_member['email'] = $member_info['buyer_alipay_no'].'@qq.com';
            $aRs1 = $this->db->query('select * from sdb_members where member_id = '.$member_id);

            $sSqll = $this->db->getUpdateSql($aRs1,$paipai_member);

            if($sSqll){
              $this->db->exec($sSqll);
			}
           $modTag = $this->system->loadModel('system/tag');
           $paipai_member = $modTag->getTagByName('member','来自拍拍');
           if(!$paipai_member){
                $modTag->newTag('来自拍拍','member');
           }

           $paipai_tag_id = $modTag->getTagByName('member','来自拍拍');
           if(!$modTag->getTagRel($paipai_tag_id,$member_id)){
                $modTag->addTag($paipai_tag_id,$member_id);
           }            //添加拍拍会员标签
		}else{
            $defcur = $this->db->selectrow('select cur_code from sdb_currency where def_cur="true"');
            $paipai_member['cur'] = $defcur['cur_code'];

            $mem_level = $this->system->loadModel('member/level');
            $paipai_member['member_lv_id'] = $mem_level->getDefauleLv();
            $paipai_member['member_refer'] = 'paipai';
            $paipai_member['password'] = md5(time());
            $paipai_member['regtime'] = trim(strtotime($member_info['created']));
            $paipai_member['lang']="123";
			$paipai_member['name']=$member_info['receiver_name'];
			$paipai_member['uname']=$member_info['buyer_uname'];
			$paipai_member['mobile']=$member_info['receiver_mobile'];
			$paipai_member['tel']=$member_info['receiver_phone'];
			$paipai_member['province'] = $member_info['receiver_city'];
			$paipai_member['city'] = $member_info['receiver_district'];
			$paipai_member['addr'] = $member_info['receiver_address'];
			$paipai_member['zip'] = $member_info['receiver_zip'];
			$paipai_member['area']=$ship_area;
            $paipai_member['email'] = $member_info['buyer_alipay_no'].'@qq.com';
            $aRs = $this->db->query('select * from sdb_members where uname="'.$member_info['buyer_uname'].'"');

            $sSql = $this->db->getInsertSql($aRs,$paipai_member);
            $this->db->exec($sSql);
		    $member_id = $this->db->lastInsertId();
            $this->update_memaddr($member_info,$member_id,$ship_area);
           $modTag = $this->system->loadModel('system/tag');
           $paipai_member = $modTag->getTagByName('member','来自拍拍');
           if(!$paipai_member){
                $modTag->newTag('来自拍拍','member');
           }

           $paipai_tag_id = $modTag->getTagByName('member','来自拍拍');
           if(!$modTag->getTagRel($paipai_tag_id,$member_id)){
                $modTag->addTag($paipai_tag_id,$member_id);
           }            //添加拍拍会员标签

		}
	    return $member_id;
	 }

     function update_memaddr($member_info,$member_id,$ship_area){
            //paipai收货人信息
			$paipai_receiver['name'] = $member_info['seller_alipay_no'];
			$paipai_receiver['area'] = $ship_area;
			$paipai_receiver['country'] = $member_info['receiver_state'];
			$paipai_receiver['province'] = $member_info['receiver_city'];
			$paipai_receiver['city'] = $member_info['receiver_district'];
			$paipai_receiver['addr'] = $member_info['receiver_address'];
			$paipai_receiver['zip'] = $member_info['receiver_zip'];
			$paipai_receiver['tel'] = $member_info['receiver_phone'];
			$paipai_receiver['mobile'] = $member_info['receiver_mobile'];
			$paipai_receiver['def_addr'] = 1;
			$paipai_receiver['member_id'] = $member_id;

            $aRS = $this->db->exec('select * from sdb_member_addrs where member_id = '.$member_id);
            $addr_sql = $this->db->GetUpdateSql($aRS,$paipai_receiver,true);

            $this->db->exec($addr_sql);
	 }

    /**
     * 发货时添上订单操作记录
     * add by hujianxin
     *
     * @param bigint $dealer_order_id
     * @param int $delivery_id，
     * @return boolean
     */
    function _add_order_log($dealer_order_id,$delivery_id){
        if(!$delivery_id){
            $this->api_response('true','data true',$result,'发货单不存在');
      }
        $message_part1 = "";
        $message = "";
        $behavior = "";
        $order_info = $this->db->selectrow("SELECT ship_status FROM sdb_orders WHERE order_id=".$dealer_order_id);
        $ship_status = $order_info['ship_status'];
        
        $delivery_info = $this->db->selectrow("SELECT logi_name,logi_no FROM sdb_delivery WHERE delivery_id=".$delivery_id);
        
        if($ship_status == "1"){   //全部发货
            $message_part1 = "发货完成";
            $behavior = "发货";
        }else if($ship_status == "2"){    //部分发货
            $message_part1 = "已发货";
            $behavior = "发货";
        }else if($ship_status == "3"){  //部分退货
            $message_part1 = "已退货";
            $behavior = "退货";
        }else if($ship_status == "4"){   //全部退货
            $message_part1 = "退货完成";
            $behavior = "退货";
        }
  
        if(!empty($behavior)){
            $message = "订单<!--order_id=".$dealer_order_id."&delivery_id=".$delivery_id."&ship_status=".$ship_status."-->".$message_part1;
            if(!empty($delivery_info['logi_name'])){
                $message .= "，物流公司：".$delivery_info['logi_name'];
            }
            if(!empty($delivery_info['logi_no'])){
                $message .= "，物流单号：".$delivery_info['logi_no'];
            }

            $return1 = $this->_add_log($dealer_order_id,$message,$behavior);
            
            return $return1;
        }else{
            return false;
        }
    }
    
    /**
     * 写入order log发货
     *
     * @param int $order_id
     * @param string $message
     * @param string $behavior
     * @return boolean
     */
    function _add_log($order_id,$message,$behavior){

        $rs = $this->db->query('select * from sdb_order_log where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'order_id'=>$order_id,
            'op_id'=>NULL,
            'op_name'=>NULL,
            'behavior'=>$behavior,
            'result'=>'success',
            'log_text'=>addslashes($message),
            'acttime'=>time()
        ));
        return $this->db->exec($sql);
    }
//支付和创建订单
    function addLog($order_id,$message,$op_id=null, $op_name=null , $behavior = '', $result = 'success',$create_time,$last_change_time){
        if($message){
            $op_name='';
            $rs = $this->db->query('select * from sdb_order_log where 0=1');
            $sql = $this->db->getInsertSQL($rs,array(
                'order_id'=>$order_id,
                'op_id'=>$op_id,
                'op_name'=>$op_name,
                'behavior'=>$behavior,
                'result'=>$result,
                'log_text'=>addslashes($message),
                'acttime'=>$create_time,
                'last_change_time'=>$last_change_time
                ));
            return $this->db->exec($sql);
        }else{
            return false;
        }
    }
}
