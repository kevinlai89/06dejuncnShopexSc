<?php

class api_brand extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->brandMdl = &$this->system->loadModel('goods/brand');
        $this->typeMdl = $this->system->loadModel('goods/gtype');
        $this->toolMdl = $this->system->loadModel('utility/tools');
    }

    public function brand_list($params){
        $data=array();
        
        $pageno = +$params['page_no'] ? +$params['page_no'] : 1;
        $pagesize = +$params['page_size'] ? +$params['page_size'] : 20;
        
        $brand=$this->brandMdl->getList('*','',($pageno-1)*$pagesize,$pagesize);
        
        foreach($brand as $key=>$value){
            $logo = $value['brand_logo'];
            $is_https = $this->toolMdl->is_img_url($logo);
            if(!$is_https){
                $gimage = &$this->system->loadModel('goods/gimage');
                $value['brand_logo'] = $gimage->getUrl( $value['brand_logo'] );
            }

            $id=$value['brand_id'];
            $data[] = array(
                'brand_name'=>$value['brand_name'],
                'brand_url'=>$value['brand_url'],
                'brand_desc'=>(string)$value['brand_desc'],
                'brand_logo'=>$value['brand_logo'],
                'brand_alias'=>(string)$value['brand_keywords'],
                'disabled'=>$value['disabled'],
                'order_by'=>($value['ordernum'])?$value['ordernum']:'0',
            );
        }
        
        $data['item_total'] = $this->brandMdl->count();
        
        $this->api_response('true','',$data);
    }

    public function brand_add($brand){

        $add_data=$this->_check_data($brand);
        $add_data['last_modify']=time();

        if($this->brandMdl->save('',$add_data)){
            $this->api_response('true','',$add_data['last_modify']);
        }
        $this->api_response('fail','品牌添加失败');

    }

    public function brand_update($brand){

        //根据品牌名称判断该品牌是否已经存在
        $brand_list=$this->brandMdl->getList('brand_id,brand_name',array('brand_name'=>$brand['brand_name']),0,20);
        if(count($brand_list) > 0){
            $onevalue = array_shift($brand_list);
            if(!empty($brand_list)){
                foreach($brand_list as $val){
                   $brand_ids[]=$val['brand_id'];
                    
                }
                $this->brandMdl->toRemove($brand_ids);
            }
            $brand_id = $onevalue['brand_id'];
        }else{
            $this->brand_add($brand);
        }

        //处理应用参数
        $add_data=$this->_check_data($brand);

        if('false'==$brand['is_force_update']){
            if($brand['last_modify'] < (int)$brand_list['lastmodify'] && $brand_list['lastmodify'] && $brand['last_modify']){
                $this->api_response('fail','Data Newer');
            }
        }

        if($brand['new_brand_name']){
            $add_data['brand_name'] = $brand['new_brand_name'];
        }

        $add_data['last_modify']=time();

        if($this->brandMdl->save($brand_id,$add_data)){
            $this->api_response('true','',$add_data['last_modify']);
        }

        $this->api_response('fail','品牌更新失败');

    }

    public function brand_delete($brand){

        $name=$brand['brand_name'];

        $brandlist=$this->db->selectrow('SELECT * FROM sdb_brand WHERE brand_name ='.$this->db->quote($name));
        if(!$brandlist['brand_id']){
            $this->api_response('true','');
        }else{
            $brand_id[]=$brandlist['brand_id'];
            if('true' == $brand['is_physical_delete']){
                if($this->brandMdl->toRemove($brand_id)){
                    $this->api_response('true','');
                }
            }else{
                $brand_id['brand_id']=$brand_id;
                if($this->brandMdl->recycle($brand_id)){
                    $this->api_response('true','');
                }
            }
            $this->api_response('true','');
        }
    }

     function _check_data($brand){
        $types=explode(',',$brand['types']);
        if(array_filter($types)){
            $type_id=$this->typeMdl->getList('type_id',array('name'=>$types),0,-1);
        }

        if(!empty($type_id)){
            foreach($type_id as $k=>$v){
                $typeid[]=$v['type_id'];
            }
        }

        if(!$brand['brand_url']){
            $brand['brand_url'] ="http://";
        }

        //处理品牌logo
        if($brand['brand_logo']){
            $host = trim($this->system->base_url());
            $imgpath = strstr($brand['brand_logo'],$host);            
            if($imgpath){
                $imgpath = substr($brand['brand_logo'],strlen($host));
                $imgpath2 = substr($imgpath,str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/');
                if($imgpath2){
                    $sql = 'SELECT * FROM sdb_gimages WHERE is_remote = "false" AND source like "'.$imgpath2.'%"';
                    $img_url = $this->db->selectrow($sql);    
                    $this->db->exec('delete from sdb_gimages where gimage_id = '.$this->db->quote($img_url['gimage_id']));
                }
                $spec_image = $imgpath.'||';
            }else{
                $spec_image = $brand['brand_logo'];    
            }
        }
        $add_brand=array(
            'brand_name'=>$brand['brand_name'],
            'ordernum'=>$brand['order_by'],
            'brand_url'=>$brand['brand_url'],
            'brand_desc'=>$brand['brand_desc'],
            'brand_logo'=>$spec_image,
            'brand_keywords'=>$brand['brand_alias'],
            'gtype'=>$typeid,
        );

        return $add_brand;
    }

}
