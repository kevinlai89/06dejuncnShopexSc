<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_export_4_0_member extends shop_api_object{
    var $api_type="native_api";
    /**
     * 获取会员列表
     * @author yanglish
     * @return array
     * @date 2012/12/17
     */
    function export_member_list($data){
        
        $page = "50";
        $limit='';
        if($data['page_no']){
            $page_l = ($data['page_no']-1)*$page;
            $limit= "limit ".$page_l.",".$page;
        }
        $result=$this->db->select("select member_id from sdb_members where 1=1 ".$limit);
        $member_list=array();
        $member_list['member_nums']=count($result);
        if($data['page_no']){
            $member_list['member_list']=$result;
        }
        $this->api_response('true',false,$member_list);
       
    }
    
    /**
     * 获取会员等级
     * @author fuxiudong
     * @return array
     * @date 2013/04/28
     */
    function export_member_lv($data){
        $m_result = $this->db->select("select * from sdb_member_lv");
        $member_lv['member_lv_list']= $m_result;
       
        $this->api_response('true',false,$member_lv);
    }
    
    /**
     * 获取会员详细
     * @author yanglish
     * @param  $date 
     * @return array
     * @date 2012/12/17
     */
    function export_member_detail($date){
        $m_id=$date['member_id'];
        $result=$this->db->selectrow("select * from sdb_members where member_id='{$m_id}'");
        $m_addrs=$this->db->selectrow("select * from sdb_member_addrs where member_id='{$m_id}'");
        unset($result['member_id']);
        $result['member_addrs']=$m_addrs;
        $result['member_loca']='0';
        //$member_detail=array();
        //$member_id=$m_id;
        $this->api_response('true',false,$result);
    }
    
    
    /**
     * 获取会员积分日志列表
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/04/24
     */
    function export_point_list($data){
        $result = $this->db->select("select id from sdb_point_history where 1=1 ");
        $point_list = array();
        $point_list['point_nums'] = count($result);
        if($data['page_no']){
            $point_list['point_list'] = $result;
        }
        $this->api_response('true',false,$point_list);
    
    }
    
    /**
     * 获取会员积分日志
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/04/24
     */
    function export_member_point($data){
        $page = "50";
        $limit='';
        if($data['page_no']){
            $page_l = ($data['page_no']-1) * $page;
            $limit = "limit ".$page_l.",".$page;
        }
        $result = $this->db->select("select * from sdb_point_history where 1=1 ".$limit);
      
        if(!$result) {
            $this->api_response('fail','获取积分信息失败或者获取数据为空,');
        }
       
        foreach($result as $k=>$v){
            $mem_id[] = $v['member_id'];
        }
        $mem_id = array_unique($mem_id);
        $sql_ = "SELECT member_id,uname,point FROM sdb_members WHERE member_id in (".implode(',',$mem_id).")";
        $uname = $this->db->select($sql_);
        
        foreach($uname as $k=>$v){
            foreach($result as  $rk=>$rv){
                if($v['member_id'] == $rv['member_id']) {
                    $result[$rk]['uname'] = $v['uname'];
                    $result[$rk]['point'] = $v['point'];
                    $result[$rk]['change_point'] = $rv['point'];
                }
            }
        }
        foreach($result as $k=>$v){
            if(!$v['uname']) {
                unset($result[$k]);
            }
        }
            
        $HistoryMdl = $this->system->loadModel('trading/pointHistory');
        $reasons = $HistoryMdl->getHistoryReason();
        foreach($result as $k=>$v){
           $result[$k]['reason'] = $reasons[$v['reason']]['describe'];
           $result[$k]['addtime'] =  $v['time'];
           //$result[$k]['expiretime'] =  $v['time'];//过期时间暂时跟添加时间保持一致
           
           unset($result[$k]['time'],$result[$k]['member_id']);
        }
        $this->api_response('true',false,$result);      
    }
    
    /**
     * 获取会员预存款记录列表
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/04/24
     */
    function export_advance_list($data){
        $result = $this->db->select("select log_id from sdb_advance_logs where 1=1 ");
        $advance_list = array();
        $advance_list['advance_nums'] = count($result);
        if($data['page_no']){
            $advance_list['advance_list'] = $result;
        }
        $this->api_response('true',false,$advance_list);
    }
    
    /**
     * 获取会员预存款记录
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/04/24
     */
    function export_member_advance($data){
        $page = "50";
        $limit='';
        if($data['page_no']){
            $page_l = ($data['page_no']-1) * $page;
            $limit = "limit ".$page_l.",".$page;
        }
        $result = $this->db->select("select * from sdb_advance_logs where 1=1 ".$limit);
        
        if(!$result) {
            $this->api_response('fail','获取预存款信息失败或者获取数据为空,');
        }
        
        foreach($result as $k=>$v){
            $mem_id[] = $v['member_id'];
        }
        
        $mem_id = array_unique($mem_id);  //去除重复会员ID
      
        $sql_ = "SELECT member_id,uname,advance FROM sdb_members WHERE member_id in (".implode(',',$mem_id).")";
        $uname = $this->db->select($sql_);
       
        foreach($uname as $k=>$v){
            foreach($result as  $rk=>$rv){
                if($v['member_id'] == $rv['member_id']) {
                    $result[$rk]['uname'] = $v['uname'];
                    $result[$rk]['money'] = (0 > $rv['money'])? $rv['money']*-1 : $rv['money']; //价格转成正数
                    $result[$rk]['message'] = $rv['memo'];
                    $result[$rk]['memo'] = $rv['message'];
                    $result[$rk]['paymethod'] = ('预存款支付' !== $rv['paymethod']) ? $rv['paymethod'] : 'deposit';
                    unset($result[$rk]['member_id']);
                }
            }
        }
        //没有uname的记录直接删掉
        foreach($result as $k=>$v){
            if(!$v['uname']) {
                unset($result[$k]);
            }
        }
        $this->api_response('true',false,$result);   
    }
    
    /**
     * 获取会员购买记录列表
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/05/15
     */
    function export_sell_log_list($data){
        $result = $this->db->select("select log_id from sdb_sell_logs where 1=1 ");
        $sell_log_list = array();
        $sell_log_list['sell_log_nums'] = count($result);
        if($data['page_no']){
            $sell_log_list['sell_log_list'] = $result;
        }
        $this->api_response('true',false,$sell_log_list);
    }
    /**
     * 获取会员购买记录
     * @author fuxiudong
     * @param  $date 
     * @return array
     * @date 2013/05/15
     */
    function export_sell_log($data){
        $page = "50";
        $limit='';
        if($data['page_no']){
            $page_l = ($data['page_no']-1) * $page;
            $limit = "limit ".$page_l.",".$page;
        }
        $result = $this->db->select("select * from sdb_sell_logs where 1=1 ".$limit);
        
        if(!$result) {
            $this->api_response('fail','获取购买记录失败或者获取数据为空,');
        }
        
        foreach($result as $k=>$v){
            $mem_id[] = $v['member_id'];
        }
        
        $mem_id = array_unique($mem_id);  //去除重复会员ID
        
        foreach($result as  $rk=>$rv){
            $result[$rk]['spec_info'] = $rv['pdt_desc'];
            unset($result[$rk]['member_id'],$result[$rk]['pdt_desc']);
        }
        
         //没有uname的记录直接删掉
        foreach($result as $k=>$v){
            if(!$v['name']) {
                unset($result[$k]);
            }
        }
        $this->api_response('true',false,$result);   
    }
}
