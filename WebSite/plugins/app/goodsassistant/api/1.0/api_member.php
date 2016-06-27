<?php

class api_member extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->memlvMdl = &$this->system->loadModel('member/level');
        $this->goodsMdl = $this->system->loadModel('goods/products');
    }

    function level_list($params){

        $memlist = $this->memlvMdl->getList('*','',0,-1);
        foreach($memlist as $key=>$value){
            $id=$value['member_lv_id'];
            switch(true){
                case 'retail'==$value['lv_type']:
                    $lvtype = 1;break;
                case 'wholesale'==$value['lv_type']:
                    $lvtype = 2;break;
            }
            $data[$id]=array(
                'name'=>$value['name'],
                'dis_count'=>$value['dis_count'],
                'default_lv'=>($value['default_lv'])?'true':'false',
                'lv_type'=>$lvtype,
                'point'=>$value['point'],
                'experience'=>$value['experience'],
                'show_other_price'=>$value['show_other_price'],
                'last_modify'=>time(),
            );
        }

        $this->api_response('true','',$data);
    }

    function level_price_list($list){
        $goods_bn = explode(',',$list['bns']);
        $goods_list = $this->goodsMdl->getList('goods_id',array('bn'=>$goods_bn),$list['page_no'],$list['page_size']);
        foreach($goods_list as $k=>$v){
            $goodid[$k]=$v['goods_id'];
        }
        $levelist = $this->db->select("SELECT * FROM sdb_goods_lv_price WHERE goods_id IN (".implode(','.$goodid).")");
        $total=count($levelist);
        foreach($levelist as $kk=>$vv){
            $leve=$this->db->selectrow("SELECT name FROM sdb_member_lv where member_lv_id = ".$this->db->quote($vv['level_id']));
            $prodect=$this->db->selectrow("SELECT bn FROM sdb_products where product_id = ".$this->db->quote($vv['product_id']));
            $data[$kk] = array(
                'Item_total'=>$total,
                'member_level_prices'=>array(
                    'member_lv_name'=>$leve['name'],
                    'price'=>$vv['price'],
                    'bn'=>$vv['bn'],
                    'bn_code'=>$prodect['bn'],
                    'last_modify'=>time(),
                ),
            );
        }

        $this->api_response('true','',$data);
    }
}