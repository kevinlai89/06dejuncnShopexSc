<?php

class api_cat extends shop_api_object {
    public function __construct(){
        parent::__construct();
        $this->catMdl=$this->system->loadModel('goods/productCat');
        $this->typeMdl=$this->system->loadModel('goods/gtype');
    }

    //分类列表
    public function cat_list($params) {

        $data = array();

        $count = $this->db->select('SELECT COUNT(1) AS c FROM sdb_goods_cat');
        $data['item_total'] = $count[0]['c'];

        //分页处理
        $data['page_no'] = $params['page_no'] ? max(1,+$params['page_no']) : 1;
        $data['page_size'] = (+$params['page_size'] > 0) ? +$params['page_size'] : 20;
        $page_offset = ($data['page_no']-1) * $data['page_size'];

        $catList = $this->db->select('SELECT cat_id,cat_name FROM sdb_goods_cat');

        $_tmp = array();
        foreach($catList as $key=>$cat){
            $_tmp[+$cat['cat_id']] = $cat;
        }

        $catList = $this->db->select('SELECT * FROM sdb_goods_cat'.' LIMIT '.$page_offset.','.$data['page_size']);        

        foreach( $catList as $k=>$v ) {
            $_cat_path = array();
            foreach( explode(',',trim($v['cat_path']," ,\r\n\t")) as $p ) {
                $_cat_path[] = $_tmp[+$p]['cat_name'];
            }

            $data[$k]['cat_path'] = implode('->',$_cat_path);

            $typelist=$this->typeMdl->getList('name',array('type_id'=>$v['type_id']));
            $data[$k]['cat_name'] = $v['cat_name'];
            $data[$k]['order_by'] = (int)$v['p_order'];
            $addon = unserialize($v['addon']);
            $data[$k]['desc'] = trim($addon['meta']['description']);
            $data[$k]['disabled'] = $v['disabled'];
            $data[$k]['type_name'] = $typelist[0]['name'];
        }
        
        $this->api_response('true','',$data);
    }

    //分类添加
    public function cat_add($cat){
        $cat_data = $this->_check_cat($cat);
        $rs = $this->db->exec('SELECT * FROM sdb_goods_cat WHERE 0=1');
        $sql = $this->db->getInsertSQL($rs,$cat_data);
        if(!$sql || $this->db->exec($sql)){
            $cat_id=$this->db->lastInsertId();
            $this->catMdl->updateChildCount($cat_data['parent_id']);
            $this->api_response('true','',$data['last_modify']=time());
        }
        $this->api_response('fail','分类添加失败');

    }

    //分类更新
    public function cat_update($cat){
        $cat_data = $this->_check_cat($cat);
        $sDefine=$this->db->selectrow('SELECT parent_id FROM sdb_goods_cat WHERE cat_id='.intval($cat_data['cat_id']));
        $rs = $this->db->exec('SELECT * FROM sdb_goods_cat WHERE cat_id='.$cat_data['cat_id']);
        $sql = $this->db->getUpdateSQL($rs,$cat_data);
        if(!$sql || $this->db->exec($sql)){
            if($sDefine['parent_id']!=$cat_data['parent_id']){
                $this->catMdl->updatePath($cat_data['cat_id'],$cat_data['cat_path']);
                $this->catMdl->updateChildCount($sDefine['parent_id']);
                $this->catMdl->updateChildCount($cat_data['parent_id']);
            }
            $this->api_response('true','',$data['last_modify']=time());
        }
        $this->api_response('fail','');

    }

    //分类删除
    public function cat_delete($cat){
        $name=explode('->',$cat['cat_path']);
        if(count($name)>0){
            $catlist=$this->catMdl->getList('cat_id',array('cat_name'=>$name),0,-1);
            foreach($catlist as $k=>$v){
                $path .= $v['cat_id'].',';
            }
        }else{
            $path .= ',';
        }
        $catList=$this->catMdl->getList('*',array('cat_name'=>$cat['cat_name'],'cat_path'=>$path),0,1);
        if($carList=$carList[0]){
            $catid=$carList['cat_id'];
            if($this->catMdl->toRemove($catid)) {
                $this->api_response('true','');
            }
        }
        $this->api_response('fail','');
    }

    function _check_cat($cat){
        $typelist = $this->typeMdl->getList('type_id',array('name'=>$cat['type_name']),0,1);
        if($type=$typelist[0]){
            $typeid=$type['type_id'];
        }
        if($cat['new_cat_path']){
            $name=explode('->',$cat['new_cat_path']);
        }else{
            $name=explode('->',$cat['cat_path']);
        }
        if(count($name)>0){
            $catlist=$this->catMdl->getList('cat_id',array('cat_name'=>$name),0,-1);
            foreach($catlist as $k=>$v){
                $path .= $v['cat_id'].',';
                $parent_id=$v['cat_id'];
            }
        }else{
            $path .= ',';
            $parent_id=0;
        }
        $cat_data=array(
            'parent_id'=>$parent_id,
            'cat_path'=>$path,
            'type_id'=>$typeid,
            'cat_name'=>$cat['cat_name'],
            'disabled'=>$cat['disabled'],
            'p_order'=>$cat['order_by'],
        );
        $catList=$this->catMdl->getList('cat_id',array('cat_name'=>$cat['cat_name']),0,1);
        if($catList=$catList[0]){
            if('false'==$cat['is_force_update'] && $cat['last_modify']<$catList['last_modify']){
                $this->api_response('fail','此条数据是旧版本的数据');
            }
            $cat_data['cat_id']=$catList['cat_id'];
            if($cat['new_cat_name']){
                $cat_data['cat_name']=$cat['new_cat_name'];
            }
        }

        return $cat_data;
    }

}
