<?php

class api_shop extends shop_api_object {
    public function __construct(){
        parent::__construct();
        $this->operatorMdl = $this->system->loadmodel('admin/operator');
    }
    
    //添加店铺接口
    public function shop_add($data){

        $certiMdl =& $this->system->loadModel('service/certificate');
        if ( !$data['certificate_id'] || ($data['certificate_id'] != $certiMdl->getCerti()) ) {
            $this->api_response('fail','证书ID与本店铺证书ID不匹配');
        }
        $image_size = array(
            '0' => '500',
            '1' =>'1000',
            '2' =>'2000',
            '3' =>'3000',
            '4' =>'4000',
            '5' =>'0',
        );
        $data_ = array(
            'site_name'=>$this->system->getConf('system.shopname'),
            'site_phone'=>$this->system->getConf('store.mobile'),
            'site_type'=>'1',
            'site_desc'=>$this->system->getConf('site.certtext'),
            'site_address'=>$this->system->getConf('store.address'),
            'image_size'=>$image_size[$this->system->getConf('system.upload.limit')],
        );
        
        $this->api_response('true','',$data_);
    }
    
    
    
    //店铺登陆
    public function shop_login($login){

        if($login['user_name'] && $login['password']){
            $login_data=array(
                'usrname'=>$login['user_name'],
                'passwd'=>$login['password'],
                'save_login_name'=>'on',
                'login'=>'',
            );
        }
        
        $is_admin=$login['is_admin'];
        $result = $this->operatorMdl->tryLogin($login_data,$is_admin);
        //记录session
        if($result){
            $session = md5(remote_addr().$result['op_id']);
        }else{
            $this->api_response('fail','veriy fail');
        }    
        
        $image_size = array(
            '0' => '500',
            '1' =>'1000',
            '2' =>'2000',
            '3' =>'3000',
            '4' =>'4000',
            '5' =>'0',
        );
        
        $data['session']=$session;
        $data['image_size'] = $image_size[$this->system->getConf('system.upload.limit')];
        $this->api_response('true','',$data);
    }
    
    public function shop_check($params){

        $arr_check = array(
            'cat',     //检测店铺分类是否有重复（同一路径的分类分类名称不允许重复）
            'type',    //类型是否有重复（类型名不允许相同）
            'spec',    //规格是否有重复（规格+规格别名不允许相同）
            'brand',         //品牌是否有重复（品牌名不允许相同）
        );
        foreach($arr_check as $arr){
            $this->_check($arr);
        }
        $this->api_response('true');
    }
    
    //检查分类、类型、品牌、规格是否有重复
    function _check($type){
        switch(true){
            case "cat" == $type:
                $sql = 'SELECT cat_name as name,count(*) as num FROM `sdb_goods_cat` WHERE 1 group by cat_name,cat_path order by num desc';
                $type_name = '商品分类';
                $error_msg = '在同一路径下,分类名称有重复';
                break;
            case "type" == $type:
                $sql = 'SELECT COUNT(*) as num , name FROM  `sdb_goods_type`  WHERE 1  GROUP BY name order by num desc';
                $type_name = '商品类型';
                $error_msg = '类型名称有重复';
                break;
            case "spec" == $type:
                $sql = 'SELECT count(*) as num,spec_name as name,alias FROM `sdb_specification` WHERE 1 AND `disabled` = "false" group by spec_name,alias order by num desc';
                $type_name = '商品类型规格';
                $error_msg = '（规格+规格别名）有重复';
                break;
            case "brand" == $type:
                $sql = 'SELECT count(*) as num,brand_name as name  FROM `sdb_brand` WHERE 1 AND `disabled` = "false" group by brand_name order by num desc';
                $type_name = '商品品牌';
                $error_msg = '商品品牌有重复';
                break;
        }

        if( $lists = $this->db->select($sql) ){
            foreach($lists as $value){
                if($value['num']>1){
                    if(isset($value['alias'])){
                        $name = '('.$value['name'].') + ('.$value['alias'].')';
                    }else{
                        $name = $value['name'];
                    }
                    $msg = $error_msg.":".$name;
                    $this->api_response('fail',$msg);
                }
            }
        }/*else{
            $msg = $type_name.'数据为空，或者数据库执行失败';
            $this->api_response('fail',$msg);
        }*/
        return true;
    }
}
