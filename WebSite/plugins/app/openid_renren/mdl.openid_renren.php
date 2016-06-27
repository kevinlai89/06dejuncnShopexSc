<?php
class mdl_openid_renren extends shopObject{


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
    function auth_insert($data){
        
        $data['member_uname']=$data['member_uname'];
        $data['authorize_item']=$data['publish_feed'];
        $user_id = $this->db->selectrow('select * from sdb_members where uname="'.$data['member_uname'].'"');
        $row = $this->db->selectrow('select * from sdb_openid_renren_authorize where member_id='.$user_id['member_id']);
        
        if($row){
            $aUpdate['authorize_item'] = $data['authorize_item'];
            $aUpdate['member_id'] = $row['member_id'];
            //更新信息
            $sSql='UPDATE sdb_openid_renren_authorize SET authorize_item="'.$aUpdate['authorize_item'].'" WHERE member_id='.$aUpdate['member_id'];
            $this->db->exec($sSql);
        }else{
            $data['member_id']=$user_id['member_id'];
            $data['authorize_item']=$data['publish_feed'];
            $sql = 'INSERT INTO sdb_openid_renren_authorize(member_id,authorize_item)VALUES("'.$data['member_id'].'","'.$data['authorize_item'].'")';
            $this->db->exec($sql);
            
        }
    }

    function get_auth_value($data){
        
         $user_id = $this->db->selectrow('select * from sdb_members where uname="'.$data.'"');
         $row = $this->db->selectrow('select * from sdb_openid_renren_authorize where member_id='.$user_id['member_id']);
         return $row;
    }

}