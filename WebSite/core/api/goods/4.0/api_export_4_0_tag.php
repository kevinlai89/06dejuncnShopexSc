<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_export_4_0_tag extends shop_api_object{
    
    /**
     * 获取商品分类
     * @author yanglish
     * @return array
     * @date 2013/4/23
     */
    function export_tag_list(){
        $tag_list=$this->db->select("select * from sdb_tags");
        $n_rel_list=array();
        
        foreach($tag_list as $t_k=>$t_v){
            $rel_list=array();
            $rel_str='';
            $rel_list=$this->db->select("select rel_id from sdb_tag_rel where tag_id=".$t_v['tag_id']);
            foreach($rel_list as $r_k=>$r_v){
                $goods_bn_list=$this->db->select("select bn from sdb_goods where goods_id=".$r_v['rel_id']);
                $rel_str .=$goods_bn_list[0]['bn'].",";
            }
            $rel_str=rtrim($rel_str,",");
            $n_rel_list[$t_v['tag_id']]=$rel_str;
        }
        $cats_list=array();
        $cats_list['tags_list']=$tag_list;
        $cats_list['rel_list']=$n_rel_list;
        $this->api_response('true',false,$cats_list);
    }
    
}
