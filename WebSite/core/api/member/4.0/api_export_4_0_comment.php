<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_export_4_0_comment extends shop_api_object{
    var $api_type="native_api";
    
    /**
     * 获取商品评论/咨询列表
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/05/15
     */
    function export_comment_list($data){
        $result = $this->db->select("select comment_id from sdb_comments where 1=1 ");
        $advance_list = array();
        $advance_list['comment_nums'] = count($result);
        if($data['page_no']){
            $advance_list['comment_list'] = $result;
        }
        $this->api_response('true',false,$advance_list);
    }
    
    /**
     * 获取商品评论/咨询
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/05/16
     */
    function export_comment($data){
        $page = "50";
        $limit='';
        if($data['page_no']){
            $page_l = ($data['page_no']-1) * $page;
            $limit = "limit ".$page_l.",".$page;
        }
        $result = $this->db->select("select * from sdb_comments where 1=1 ".$limit);
        
        if(!$result) {
            $this->api_response('fail','获取 评论/咨询 信息失败或者获取数据为空,');
        }
        
        $sql = "SELECT m.uname,g.bn,c.* 
                FROM sdb_comments as c 
                LEFT JOIN sdb_members as m ON c.author_id=m.member_id 
                LEFT JOIN sdb_goods as g ON c.goods_id=g.goods_id";
        $result = $this->db->select($sql);
        //组织to_id
        foreach($result as $k=>$v){
            $result[$k]['for_comment_id'] = ($v['for_comment_id'])? $v['for_comment_id'] : 0;
            if($v['for_comment_id']){
                $to_id[] = $v['for_comment_id'];
            }
        }
        if( $to_id ){
            $to_id = array_unique($to_id);
            $a = implode(',',$to_id);
            $to_ids = $this->db->select("SELECT comment_id,author_id FROM sdb_comments WHERE comment_id IN (".implode(',',$to_id)." )");
        }
        if($to_ids){
            foreach($result as $k=>$v){
                foreach($to_ids as $t_k=>$t_v){
                    if($v['for_comment_id'] == $t_v['comment_id']){
                        $result[$k]['to_id'] = (NULL !== $t_v['author_id']) ? $t_v['author_id'] :0;
                    }   
                }    
            }
        }
        
        //过滤无效数据
        foreach($result as $k=>$v){
            if(!$v['bn']){
                unset($result[$k]);
            }
            unset($result[$k]['author_id'],$result[$k]['goods_id'],$result[$k]['levelname']);
        }
        
        $this->api_response('true',false,$result);
    }
}
