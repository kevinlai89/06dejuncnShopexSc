<?php
class cmd_products extends mdl_products{
    var $defaultCols = 'bn,name,cat_id,price,store,marketable,brand_id, brand,weight,d_order,uptime,type_id,supplier_id,buy_count,view_count,tag_pic,qtn_show';
	function getList($cols,$filter='',$start=0,$limit=20,$orderType=null){
        if(!function_exists('goods_list')) require(CORE_INCLUDE_DIR.'/core/goods.list.php');         
         $glist  = goods_list($cols,$filter,$start,$limit,$orderType, $this);
         $gids = array();
         return $glist;
    }
	
	      function _filter($filter,$tbase=''){
        $cnd = parent::_filter($filter,$tbase);
        if (isset($filter['cat_id'])){
            $gids = array();
            
            if (is_array($filter['cat_id']) && isset($filter['cat_id']['v'])){
                $cid = $filter['cat_id']['v'];
            }else if (is_array($filter['cat_id']) && count($filter['cat_id']) > 0 && is_numeric($filter['cat_id'][0])){
                $cid = $filter['cat_id'][0];
            }else if (is_numeric($filter['cat_id'])){
                $cid = $filter['cat_id'];
            }
            
            if ($cid){            
                $cids = array($cid);
                $oCat = $this->system->loadModel('goods/productCat');
                $carr = $oCat->getAll($cid);
                foreach ($carr as $ca) $cids[] = intval($ca['id']);
                            
                $ret = $this->db->select("select goods_id from sdb_goods_cat_mrel where cat_id in (".implode(',',$cids).')');
                foreach ($ret as $item){
                    $gids[] = $item['goods_id'];
                }
                if (count($gids) > 0)
                    $cnd .= ' OR goods_id in ('.implode(',',$gids).')';
            }
        }        
        return $cnd;
    }

}