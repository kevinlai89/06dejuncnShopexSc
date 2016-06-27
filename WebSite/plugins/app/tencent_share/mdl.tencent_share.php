<?php
class mdl_tencent_share extends shopObject{


    function get_product($gid,$mlv=0){
        $goods=$this->system->loadModel('trading/goods');
        $return=$goods->getGoods($gid,$mlv);
        if($return['brand_id']>0){
            $brand_sel=$this->system->loadModel('goods/brand');
            $return['brand_name']=$brand_sel->getFieldById($return['brand_id'],array('brand_name'));
        }
        return $return;
    }

    function get_by_goods_id($gid){
        foreach($this->db->select('select * from sdb_gimages where goods_id='.intval($gid)) as $row){
            $return[$row['gimage_id']] = $row;
        }
        return $return;
    }

}