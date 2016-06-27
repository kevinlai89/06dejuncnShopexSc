<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_4_0_order extends shop_api_object {
    /**
    * 店掌柜->直销平台订单转换API
    * @param 要查找的订单信息
    * @author lsc
    * @return 订单号列表
    */
    function search_order_id_list($data){
        $page = "50";
        $page_l = ($data['page_no']-1)*$page;
        $order_num = $this->db->selectrow('select count(*) as counts from sdb_orders where last_change_time>='.intval($data['last_modify_st_time']).' and last_change_time<'.intval($data['last_modify_en_time']).' and disabled="false"');
        $return_data['counts'] = $order_num[counts];
        $order_ids = $this->db->select('select order_id from sdb_orders where last_change_time>='.intval($data['last_modify_st_time']).' and last_change_time<'.intval($data['last_modify_en_time']).' and disabled="false"'.' limit '.$page_l.','.$page);
        
        $return_data['order_ids'] = $order_ids;
        
        $this->api_response('true',false,$return_data);
    }
    
    /**
    * 店掌柜->直销平台订单转换API
    * @param 要查找的订单信息
    * @author lsc
    * @return 订单详细信息
    */
    function search_matrix_order_detail($data){
        
        if(!($order_info=$this->db->selectrow('select * from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','date fail',$data);
        }
        if($order_info['member_id']){
            $member_info=$this->db->selectrow('select uname from sdb_members where member_id='.$order_info['member_id']);
        }
        
        $matrix_order_info['tid'] = $order_info['order_id'];
        $matrix_order_info['title'] = $order_info['tostr'];
        $matrix_order_info['created'] = date('Y-m-d H:m:s',$order_info['createtime']);
        $matrix_order_info['modified'] = date('Y-m-d H:m:s',$order_info['last_change_time']);
        $matrix_order_info['status'] = $this->local2status($order_info['status']);
        $matrix_order_info['pay_status'] = $this->local2pay_status($order_info['pay_status']);
        $matrix_order_info['ship_status'] = $this->local2ship_status($order_info['ship_status']);
        $matrix_order_info['is_delivery'] = $this->local2is_delivery_status($order_info['is_delivery']);
        $matrix_order_info['has_invoice'] = $order_info['is_tax'];
        $matrix_order_info['invoice_title'] = $order_info['tax_company'];
        $matrix_order_info['invoice_fee'] = $order_info['cost_tax'];
        $matrix_order_info['total_goods_fee'] = $order_info['cost_item'];
        $matrix_order_info['total_trade_fee'] = $order_info['total_amount'];
        $matrix_order_info['discount_fee'] = $order_info['discount'];
        $matrix_order_info['payed_fee'] = $order_info['payed'];
        $matrix_order_info['currency'] = $order_info['currency'];
        $matrix_order_info['currency_rate'] = $order_info['cur_rate'];
        $matrix_order_info['buyer_obtain_point_fee'] = $order_info['score_g'];
        $matrix_order_info['point_fee'] = $order_info['score_u'];
        $matrix_order_info['shipping_tid'] = $order_info['shipping_id'];
        $matrix_order_info['shipping_type'] = $order_info['shipping'];
        $matrix_order_info['shipping_fee'] = $order_info['cost_freight'];
        $matrix_order_info['is_protect'] = $order_info['is_protect'];
        $matrix_order_info['protect_fee'] = $order_info['cost_protect'];
        $matrix_order_info['payment_tid'] = $order_info['payment'];
        //$matrix_order_info['pay_time'] = $order_info['pay_time'];
        //$matrix_order_info['consign_time'] = $order_info['consign_time'];
        $matrix_order_info['receiver_name'] = $order_info['ship_name'];
        $matrix_order_info['receiver_email'] = $order_info['ship_email'];
        $ship_area_arr = split(':',$order_info['ship_area']);
        $ship_area = split('/',$ship_area_arr[1]);
        $matrix_order_info['receiver_state'] = $ship_area[0];//省
        $matrix_order_info['receiver_city'] = $ship_area[1];//市
        $matrix_order_info['receiver_district'] = $ship_area[2];//区
        $matrix_order_info['receiver_address'] = $order_info['ship_addr'];
        $matrix_order_info['receiver_zip'] = $order_info['ship_zip'];
        $matrix_order_info['receiver_mobile'] = $order_info['ship_mobile'];
        $matrix_order_info['receiver_phone'] = $order_info['ship_tel'];
        $matrix_order_info['receiver_time'] = $order_info['ship_time'];
        $matrix_order_info['trade_memo'] = $order_info['memo'];
        $matrix_order_info['protect_fee'] = $order_info['cost_protect'];
        $matrix_order_info['pay_cost'] = $order_info['cost_payment'];
        $matrix_order_info['total_weight'] = $order_info['weight'];
        //$matrix_order_refer = $this->local2order_refer($order_info['import_refer']);
        $matrix_order_info['order_refer'] = 'local';
        $matrix_order_info['buyer_uname'] = $member_info['uname']?$member_info['uname']:'';
        
        $order_items_arr=$this->db->select('select * from sdb_order_items where order_id='.$data['order_id']);
        foreach ($order_items_arr as $key => $value) {
            $order_item[$key]['bn'] =  $value['bn'];
            $order_item[$key]['name'] =  $value['name'];
            $order_item[$key]['sku_id'] =  $value['item_id'];
            $order_item[$key]['iid'] =  $value['product_id'];
            $order_item[$key]['score'] =  $value['score'];
            $order_item[$key]['cost'] =  $value['cost'];
            $order_item[$key]['price'] =  $value['price'];
            $order_item[$key]['total_item_fee'] =  $value['amount'];
            $order_item[$key]['num'] =  $value['nums'];
            $order_item[$key]['sendnum'] =  $value['sendnum'];
            $order_item[$key]['item_type'] =  'product';
            $order_item[$key]['item_status'] =  'normal';
        }
        $orders['order_items']['oid'] = $order_info['order_id'];
        $orders['order_items']['type'] = 'goods';
        $orders['order_items']['orderItem'] = $order_item;
        $matrix_order_info['orders']['order'][0] = $orders;
        $result['order_info'] = $matrix_order_info;
        $this->api_response('true',false,$result);
    }

    /**
     *订单状态转换
     *@author lushengchao
     *@date 2011-8-12
     *@params  $status 订单状态
     */
    function local2status($status){
        $array=array('active'=>'TRADE_ACTIVE',
        'finish'=>'TRADE_FINISHED',
        'dead'=>'TRADE_CLOSED');
        return $array[$status];
    }
    
    /**
     *支付状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $pay_status 订单状态
     */
    function local2pay_status($pay_status){
        $array=array('0'=>'PAY_NO',
        '1'=>'PAY_FINISH',
        '2'=>'PAY_TO_MEDIUM',
        '3'=>'PAY_PART',
        '4'=>'REFUND_PART',
        '5'=>'REFUND_ALL');
        return $array[$pay_status];
    }
    
     /**
     *发货状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $ship_status 订单状态
     */
    function local2ship_status($ship_status){
        $array=array('0'=>'SHIP_NO',
        '0'=>'SHIP_PREPARE',
        '2'=>'SHIP_PART',
        '1'=>'SHIP_FINISH',
        '2'=>'RESHIP_PART',
        '4'=>'RESHIP_ALL');
        return $array[$ship_status];
    }
    
     /**
     *是否实体配送状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $is_delivery 是否实体配送
     */
    function local2is_delivery_status($is_delivery){
        $array=array('Y'=>'true',
        'N'=>'false');
        return $array[$is_delivery];
    }
    
    /**
     *订单来源类型转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $is_delivery 是否实体配送
     */
    function local2order_refer($order_refer){
        $array=array('0'=>'local',
        '1'=>'taobao',
        '2'=>'local',
        '3'=>'local',
        '4'=>'local',
        '6'=>'paipai');
        return $array[$order_refer];
    }
    
}