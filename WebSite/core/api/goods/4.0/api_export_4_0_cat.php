<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_export_4_0_cat extends shop_api_object{
    
    /**
     * 获取商品分类
     * @author yanglish
     * @return array
     * @date 2013/4/23
     */
    function export_cats_list(){
        $sql = "SELECT t.name,c.* FROM sdb_goods_cat as c LEFT JOIN sdb_goods_type as t ON c.type_id=t.type_id";
        $result = $this->db->select($sql);
        $cats_list = array();
        $cats_list['cats_list'] = $result;
        $this->api_response('true',false,$cats_list);
    }
    
}
