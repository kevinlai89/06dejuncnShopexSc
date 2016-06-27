<?php

class api_type extends shop_api_object {
    public function __construct() {
        parent::__construct();
        $this->typeMdl = $this->system->loadModel('goods/gtype');
        $this->brandMdl = &$this->system->loadModel('goods/brand');
        $this->schMdl = &$this->system->loadModel('goods/schema');
    }

    //类型列表
    public function type_list($params){
        $data = array();
        $pageno = +$params['page_no'] ? +$params['page_no'] : 1;
        $pagesize = +$params['page_size'] ? +$params['page_size'] : 20;
        
        $list = $this->typeMdl->getList('*','',($pageno-1)*$pagesize,$pagesize);

        foreach($list as $key=>$value){
            $id = +$value['type_id'];
            $setting = unserialize($value['setting']);
            //品牌
            $brand= $this->brandMdl->getTypeBrands($id);
            $_brand = array();
            foreach($brand as $bkey=>$bval){
                $_brand[$id][$bval['brand_id']]=$bval['brand_name'];
            }

            //规格
            $spec = $this->db->select('SELECT s.spec_id,s.spec_name,s.spec_memo,s.alias FROM sdb_goods_type_spec t INNER JOIN sdb_specification s ON t.spec_id = s.spec_id WHERE t.type_id ='.$id);
            $_spec_names = array();
            $_spec_alias = array();
            foreach($spec as $speck=>$specv){
                $_spec_names[$id][$specv['spec_id']] = $specv['spec_name'];
                $_spec_alias[$id][$specv['spec_id']] = $specv['spec_memo'];
            }

            //扩展属性
            $props = unserialize($value['props']);
            $_props = array();
            foreach($props as $k=>$prop){
                if('select' == $prop['type'] && 'nav' == $prop['search']){
                    $type = "2";
                }elseif('select' == $prop['type'] && 'disabled' == $prop['search']){
                    $type = "4";
                }elseif('select' == $prop['type'] && 'select' == $prop['search']){
                    $type = "3";
                }elseif('input' == $prop['type'] && 'input' == $prop['search']){
                    $type = "0";
                }elseif('input' == $prop['type'] && 'disabled' == $prop['search']){
                    $type = "1";
                }

                if(empty($prop['options'])){
                    $options_prop = "";
                }else{
                    $options_prop =implode(',',$prop['options']);
                }
                $_props[$k] = array(
                    'prop_name' => $prop['name'],
                    'alias'=>(string)$prop['alias'],
                    'show_type'=>(string)$type,
                    'order_by'=>(int)$prop['ordernum'],
                    'is_show'=>($prop['show'])?'true':'false',
                    'prop_type'=>1,
                    'prop_value'=>$options_prop,
                );
            }
            //详细参数
            $params = unserialize($value['params']);
            $_params=array();
            foreach($params as $pkey=>$pval){
                $values=array_keys($pval);
                if(empty($values)){
                    $prop_value="";
                }else{
                    $prop_value=implode(',',$values);
                }
                $_params[] = array(
                    'prop_name'=>$pkey,
                    'prop_type'=>2,
                    'show_type'=>'',
                    'is_show'=>'true',
                    'prop_value'=>$prop_value,
                );
            }
            //购物必填信息
            $minfo = unserialize($value['minfo']);
            $_minfo=array();
            foreach($minfo as $mkey=>$mval){
                if(empty($mval['options'])){
                    $options="";
                }else{
                    $options=implode(',',$mval['options']);
                }

                if('input' == $mval['type']){
                    $m_show_type = '0';
                }elseif('text' == $mval['type']){
                    $m_show_type = '1';
                }elseif('select' == $mval['type']){
                    $m_show_type = '2';
                }

                $_minfo[$mkey] = array(
                    'prop_name'=>$mval['label'],
                    'show_type'=>$m_show_type,
                    'prop_type'=>3,
                    'is_show'=>'true',
                    'prop_value'=>(string)$options,
                );
            }
            $data[]=array(
                'name' => (string)$value['name'],
                'alias' => (string)$value['alias'],
                'is_default' => $value['is_def'],
                'is_physical' => ($value['is_physical'])?'true':'false',
                'is_has_brand' => ($setting['use_brand'])?'true':'false',
                'is_has_prop' => ($setting['use_props'])?'true':'false',
                'is_has_params' => ($setting['use_params'])?'true':'false',
                'is_must_minfo'=> ($setting['use_minfo'])?'true':'false',
                'disabled'=>($value['name'] == '通用商品类型')?'true':'false',
                'spec_names'=>implode(',',$_spec_names[$id]),
                'spec_alias'=>implode('->',$_spec_alias[$id]),
                'brand_names'=>implode(',',$_brand[$id]),
                'props'=>$_props,
                'params'=>$_params,
                'must_minfo'=>$_minfo,
                'last_modify'=>(int)$value['lastmodify'],
            );

        }
        
        $data['item_total'] = $this->typeMdl->count();
        
        $this->api_response('true','',$data);
    }

    //类型添加接口
    public function type_add($type){

        $add_data=$this->_check_data($type);

        if(!$add_data){
            $this->api_response('fail','应用参数为空');
        }

        $rs = $this->db->query('select * from sdb_goods_type where 0=1');
        $sql = $this->db->getInsertSQL($rs,$add_data);
        if($this->db->exec($sql)){
            $typeid = $this->db->lastInsertId();
            $this->typeMdl->checkDefined();
            $this->schMdl->save_brand($typeid, $add_data['brand']);
            $this->schMdl->save_spec($typeid, $add_data['spec_id'], $spec_type);
            $data['last_modify']=$add_data['lastmodify'];
            $this->api_response('true','',$data['last_modify']);
        }
        $this->api_response('fail','类型添加失败');
    }

    //添加修改类型
    public function type_update($type){

        $add_data=$this->_check_data($type);

        if(!$add_data){
            $this->api_response('fail','应用参数为空');
        }

        $typelist=$this->typeMdl->getList('type_id,name,lastmodify',array('name'=>$add_data['name']),0,1);
        if(count($typelist) > 0){
            $onevalue = array_shift($typelist);
            if(!empty($onevalue)){
                foreach($typelist as $val){
                   $this->typeMdl->toRemove($val['type_id']);
                }
            }
            $type_id = $onevalue['type_id'];
            
        }else{
            $this->type_add($type);
        }
        
        if('false'==$type['is_force_update']){
            if($type['last_modify'] < $typelist['lastmodify'] && $typelist['lastmodify'] && $type['last_modify']){
                $this->api_response('fail','Data Newer');
            }
        }
        if($type['new_name']){
            $add_data['name'] = $type['new_name'];
        }
        $rs = $this->db->query('select * from sdb_goods_type where type_id='.$this->db->quote($type_id));
        $sql = $this->db->getUpdateSQL($rs,$add_data);

        if(!$sql || $this->db->exec($sql)){
            $this->typeMdl->checkDefined();
            $this->schMdl->save_brand($type_id, $add_data['brand']);
            $this->schMdl->save_spec($type_id, $add_data['spec_id'], $spec_type);
            $data['last_modify']=$add_data['lastmodify'];
            $this->api_response('true','',$data['last_modify']);

        }

        $this->api_response('fail','Db error');
    }

    //删除类型
    public function type_delete($type){

        $name=$type['name'];
        $typelist=$this->typeMdl->getList('*',array('name'=>$name),0,1);
        $type_id=$typelist[0]['type_id'];
        if('true' == $type['is_physical_delete']){
            if($this->typeMdl->toRemove($type_id)){
                $this->api_response('true','');
            }
        }else{
            $type_id['type_id'][0]=$type_id;
            if($this->typeMdl->recycle($type_id)){
                $this->api_response('true','');
            }
        }
        $this->api_response('fail','Db error');
    }

    //类型增加和修改时的数据处理
    public function _check_data($type){
        $brand=$this->brandMdl->getBrandsByNames(explode(',',$type['brand_names']));

        //获取规格
        if($type['spec_names']){
            $spec=array();
            foreach(explode(',',$type['spec_names']) as $skey=>$sval){
                $sval=trim($sval);
                $length_n = strpos($sval,'[');
                $length_a = strpos($sval,']');
                if($length_n && $length_a){
                    $spec_name = substr($sval,0,$length_n);
                    $alias = substr($sval,$length_n);
                    $alias = ltrim($alias,'[');
                    $alias = rtrim($alias,']');
                    if($spec_name && $alias){
                        $sqlString = "SELECT spec_id,spec_name FROM sdb_specification WHERE spec_name = '".$spec_name."' AND spec_memo = '".$alias."'";
                        $aData = $this->db->selectrow($sqlString);
                        $spec[$skey] = $aData['spec_id'];
                    }
                }else{
                    $sqlStringe = "SELECT spec_id,spec_name FROM sdb_specification WHERE spec_name ='".$sval."'";
                    $aDatae = $this->db->selectrow($sqlStringe);
                    $spec[$skey] = $aDatae['spec_id'];
                }
                $spec_type[$skey] = 'flat';
            }
        }

        //获取扩展属性
        foreach(json_decode($type['props'],true) as $pkey=>$pvalue){
            $props=array();
            foreach($pvalue as $pp=>$pv){
                switch(true){
                    case '0'==$pv['show_type']:
                        $ptype='input';$search='input';break;
                    case '1'==$pv['show_type']:
                        $ptype='input';$search='disabled';break;
                    case '2'==$pv['show_type']:
                        $ptype='select';$search='nav';break;
                    case '3'==$pv['show_type']:
                        $ptype='select';$search='select';break;
                    case '4'==$pv['show_type']:
                        $ptype='select';$search='disabled';break;
                }
                $props[++$pp]=array(
                    'name'=>$pv['prop_name'],
                    'alias'=>$pv['alias'],
                    'type'=>$ptype,
                    'search'=>$search,
                    'options'=>explode(',',$pv['prop_value']),
                    'show'=>$pv['is_show'],
                    'ordernum'=>$pv['order_by'],
                );
            }
        }

        //处理详细参数数据
        foreach(json_decode($type['params'],true) as $akey=>$avalue){
            $params=array();
            foreach($avalue as $mm=>$vv){
                foreach(explode(',',$vv['prop_value']) as $kv){
                    $params[$vv['prop_name']][$kv] ='';
                }
                //$params[$vv['prop_name']][$vv['prop_value']] = $vv['alias'];
            }
        }
        
        //处理购物必填信息数据
        foreach(json_decode($type['must_minfo'],true) as $mkey=>$mvalue){
            $minfo=array();
            foreach($mvalue as $kk=>$uu){
                $minfo[$kk]['label']=$uu['prop_name'];
                $minfo[$kk]['name']='M'.md5($uu['prop_name']);
                $minfo[$kk]['type']=$uu['show_type'];
                if($minfo[$kk]['type']==2){
                    $minfo[$kk]['options']=preg_split("/[\s,]+/",trim($uu['prop_value']));
                }
            }
        }

        $add_data=array(
            'name'=>$type['name'],
            'alias'=>$type['alias'],
            'brand'=>$brand,
            'props'=>$props,
            'spec_id'=>$spec,
            'params'=>$params,
            'minfo'=>$minfo,
            '__'=>1,
            'spec_type'=>$spec_type,
            'is_physical'=>('true' == $type['is_physical'])?1:0,
            'setting'=>array(
                'use_brand'=>('true' == $type['is_has_brand'])?1:0,
                'use_props'=>('true' == $type['is_has_prop'])?1:0,
                'use_params'=>('true' == $type['is_has_params'])?1:0,
                'use_minfo'=>('true' == $type['is_must_minfo'])?1:0,
                'use_spec'=>'',
            ),
            'dly_func' => 0,
            'ret_func' => 0,
            'reship' => 'normal',
            'disabled'=>$type['disabled'],
            'is_def'=>$type['is_default'],
            'lastmodify'=>time(),
        );

        return $add_data;
    }



}
