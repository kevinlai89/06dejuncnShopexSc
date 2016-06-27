<?php

class api_spec extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->specMdl = $this->system->loadModel('goods/specification');
        $this->storager = &$this->system->loadModel('system/storager');
    }
    
    public function spec_list($params){
        $data=array();
        
        $pageno = +$params['page_no'] ? +$params['page_no'] : 1;
        $pagesize = +$params['page_size'] ? +$params['page_size'] : 20;
        
        $spec = $this->specMdl->getList('*',array('disabled'=>'false'),($pageno-1)*$pagesize,$pagesize);
        
        foreach($spec as $key=>$value){
            $id = $value['spec_id'];
           /* $spec_values =$this->specMdl->getValueList($id);
            $_spec_values=array();
            foreach($spec_values as $skey=>$sval){
                if('image' == $value['spec_type']){
                    $image_url = $this->storager->getUrl( $sval['spec_image'] );
                }else{
                    $image_url ="";
                }
                $_spec_values[] = array(
                    'spec_value'=>(string)$sval['spec_value'],
                    'spec_value_alias'=>(string)$sval['alias'],
                    'image_url'=>(string)$image_url,
                    'order_by'=>$sval['p_order'],
                );
                
                
            }*/
            $data[]= array(
                'spec_name'=>$value['spec_name'],
                'alias'=>(string)$value['spec_memo'],
                'memo'=>$value['alias'],                
                'spec_type'=>('text' == $value['spec_type'])?"文字":"图片",
                'spec_show_type'=>('flat' == $value['spec_show_type'])?"平铺":"下拉",
//                'spec_values'=>$_spec_values,
                'order_by'=>$value['p_order'],
                'disabled'=>$value['disabled'],
                'is_show'=>'true',
                'last_modify'=>(int)$value['lastmodify'],
            );
            
        }
        
        $data['item_total'] = $this->specMdl->count();
        
        $this->api_response('true','',$data);
    }
    
    /** 
     * 商品规格值列表
     * @param null 
     * @access public
     * @author zhoulei
     * @return array 商品规格值列表
     * @deprecated 1.0 - 2012-5-10
     */
    function spec_values_list($params){
        $page_no = +$params['page_no'] ? +$params['page_no'] : 1;
        $page_size = +$params['page_size'] ? +$params['page_size'] : 20;
        
        
        $filter=array(
            'spec_name' => trim($params['spec_names']),
        );
        
        if($params['spec_alias']){
            $filter['spec_memo'] = trim($params['spec_alias']);
        }

        $speclists=$this->specMdl->getList('*',$filter,0,1);
        if(count($speclists) > 0){
            $spec_id = $speclists[0]['spec_id'];
            
            $where .= ' limit '.($page_no-1)*$page_size.','.$page_size;
            $sql ='select * from sdb_spec_values where spec_id = '.intval($spec_id).' '.$where;
            $spec_values=$this->db->select($sql);
            
            foreach($spec_values as $k=>$v){
                if($v['spec_image']){
                    $image_url = $this->storager->getUrl($v['spec_image']);
                }else{
                    $image_url ="";
                }
                $rdata[$k]['spec_value'] = $v['spec_value'];
                $rdata[$k]['new_spec_value'] = $v['spec_value'];
                $rdata[$k]['spec_value_alias'] = $v['alias'];
                $rdata[$k]['image_url'] = $image_url;
                $rdata[$k]['order_by'] = intval($v['p_order']);
            }
            
        }else{
            $this->api_response('fail','the spec not exsist!');
        }
        
        $data = $rdata;
        $sql_count = "select count(*) as total from ".DB_PREFIX."spec_values where spec_id = ".intval($spec_id);
        $res_count=$this->db->selectrow($sql_count);
        $data['item_total'] = $res_count['total'];
        
        $this->api_response('true','',$data);
    }
    
    //规格添加接口
    public function spec_add($spec){
    
        $spec_data=$this->_check_data($spec);    
        
        //$aSpec['spec_id'] = $spec_data['spec_id'];
        $aSpec['spec_name'] = trim($spec_data['spec_name']);
        $aSpec['alias'] = trim($spec_data['spec_alias']);
        $aSpec['spec_memo'] = trim($spec_data['spec_memo']);
        $aSpec['spec_show_type'] = $spec_data['spec_show_type'];
        $aSpec['spec_type'] = $spec_data['spec_type'];
        
        $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE 0=1");
        $sSql = $this->db->getInsertSql($aRs,$aSpec);
        
        if($sSql && $this->db->exec($sSql)){
            $aSpec['spec_id'] = $this->db->lastInsertId();
            $result = $this->specMdl->saveValue($aSpec['spec_id'],$spec_data['spec_value'],$spec_data['val'],$spec_data['spec_image'],$spec_data['alias']);
            if($result){
                $data['last_modify']=$spec_data['lastmodify'];
                
                //触发规格保存的接听事件
                $eventData = array(
                    'spec_id'=>$aSpec['spec_id'],
                );
                $this->specMdl->fireEvent('save',$eventData);
                
                $this->api_response('true','',$data);
            }
        }
        
        $this->api_response('fail','规格添加失败');
        
    }
    
    //规格修改（添加）接口
    public function spec_update($spec){     
        $filter=array(
            'spec_name' => $spec['spec_name'],
        );
        
        if($spec['alias']){
            $filter['spec_memo'] = $spec['alias'];
             $speclists=$this->specMdl->getList('*',$filter,0,20);
            if(count($speclists) > 0){
                $onevalue = array_shift($speclists);
                if(!empty($speclists)){
                    foreach($speclists as $val){
                        $val_s['spec_id'][]= $val['spec_id'];
                    }
                    $this->specMdl->delete($val_s);
                }
                $spec_id = $onevalue['spec_id'];
            }else{
                $this->spec_add($spec);
            }
        }else{
            $speclists=$this->db->select('SELECT * FROM sdb_specification WHERE spec_name = '.$this->db->quote($filter['spec_name']).' AND (spec_memo="" OR spec_memo IS NULL)');
            if(count($speclists) > 0){
                $onevalue = array_shift($speclists);
                if(!empty($speclists)){
                    foreach($speclists as $val){
                        $val_s['spec_id'][]= $val['spec_id'];
                    }
                    $this->specMdl->delete($val_s);
                }
                $spec_id = $onevalue['spec_id'];
            }else{
                $this->spec_add($spec);
            }
        }

        $spec_data=$this->_check_data($spec,$spec_id);
        if($spec['new_spec_name']){
            $spec_data['spec_name'] = $spec['new_spec_name'];
        }
        
        if($spec['new_alias']){
            $spec_data['spec_memo'] = $spec['new_alias'];
        }
        
        if('false'==$spec['is_force_update']){
            if($spec['last_modify'] < (int)$specs['lastmodify'] && $specs['lastmodify'] && $spec['last_modify']){
                $this->api_response('fail','Data Newer');
            }
        }

        $aSpec['spec_name'] = trim($spec_data['spec_name']);
        $aSpec['alias'] = trim($spec_data['spec_alias']);
        $aSpec['spec_memo'] = trim($spec_data['spec_memo']);
        $aSpec['spec_show_type'] = $spec_data['spec_show_type'];
        $aSpec['spec_type'] = $spec_data['spec_type'];
    
        $tdata = $this->specMdl->getFieldById($spec_id,array('spec_type'));
        
        //判断是否做过类型切换
        if($tdata['spec_type'] != $aSpec['spec_type']){
            $this->db->exec("DELETE FROM sdb_spec_values WHERE spec_id =".intval($spec_id));
        }
        $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE spec_id=".intval($spec_id));
        $sSql = $this->db->getUpdateSql($aRs,$aSpec);
        if(!$sSql || $this->db->exec($sSql)){
            $result = $this->specMdl->saveValue($spec_id,$spec_data['spec_value'],$spec_data['val'],$spec_data['spec_image'],$spec_data['alias']);
            if($result){
                $data['last_modify']=$spec_data['lastmodify'];
                
                //触发规格保存的接听事件
                $eventData = array(
                    'spec_id'=>$spec_id,
                );
                $this->specMdl->fireEvent('save',$eventData);
                
                $this->api_response('true','',$data);
            }
        }
        
        $this->api_response('fail','规格修改失败');
    }
    
    public function spec_delete($spec){
        $filter = array(
            'spec_name'=>$spec['spec_name'],
        );
        if ( $spec['alias'] ) {
            $filter['spec_memo'] = $spec['alias'];
        }
        $speclist=$this->specMdl->getList('spec_id',$filter,0,1);
        $spec_id['spec_id'][]=$speclist[0]['spec_id'];
        
        if( !$speclist || $this->specMdl->delete($spec_id)){
            $this->api_response('true','');
        }
        
        $this->api_response('true','');
    }
    
    function _check_data($spec,$spec_id =''){        
        
        $spec_data=array(
            'spec_name'=>$spec['spec_name'],
            'spec_memo'=>$spec['alias'],
            'spec_alias'=>$spec['memo'],
            'spec_type'=>('文字' == $spec['spec_type'])?'text':'image',
            'spec_show_type'=>('平铺' == $spec['spec_show_type'])?'flat':'select',
            'p_order'=>$spec['order_by'],
            'lastmodify'=>time(),
        );
        foreach(json_decode($spec['spec_values'],true) as $key=>$value){
            foreach($value as $k=>$v){
                //处理规格图片
                if($v['image_url']){
                    $host = trim($this->system->base_url());
                    $imgpath = strstr($v['image_url'],$host);                    
                    if($imgpath){
                        $imgpath = substr($v['image_url'],strlen($host));
                        $imgpath2 = substr($imgpath,str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/');
                        if($imgpath2){
                            $sql = 'SELECT * FROM sdb_gimages WHERE is_remote = "false" AND source like "'.$imgpath2.'%"';
                            $img_url = $this->db->selectrow($sql);    
                            $this->db->exec('delete from sdb_gimages where gimage_id = '.$this->db->quote($img_url['gimage_id']));
                        }
                        $spec_image = $imgpath.'||';
                    }else{
                        $spec_image = $v['image_url'];    
                    }
                }else{
                    $spec_image = '';
                }
                $valuelist = $this->db->select('SELECT * FROM sdb_spec_values 
                    WHERE spec_value = '.$this->db->quote($v['spec_value']).' 
                    AND spec_id = '.$this->db->quote($spec_id));
                if(count($valuelist)>1){
                    $value = $this->db->select('SELECT * FROM sdb_spec_values
                        WHERE spec_value = '.$this->db->quote($v['spec_value']).' 
                        AND alias = '.$this->db->quote($v['spec_value_alias']).'
                        AND spec_id = '.$this->db->quote($spec_id));
                    if(!empty($value)){
                        $val = $value['spec_value_id'];
                    }
                }elseif(count($valuelist) == 1){
                    $val = $valuelist[0]['spec_value_id'];
                }else{
                    $val = '';
                }  
                
                $spec_data['val'][$k] = $val;
                $spec_data['spec_value'][$k]=$v['spec_value'];
                $spec_data['alias'][$k]=$v['spec_value_alias'];
                $spec_data['spec_image'][$k]=$spec_image;
                if($v['new_spec_value']){
                    $spec_data['spec_value'][$k]=$v['new_spec_value'];
                }
            }
        }
        return $spec_data;
    }
}
