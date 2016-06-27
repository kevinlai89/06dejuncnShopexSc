<?php

class api_goods extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->objGoods = &$this->system->loadModel('trading/goods');
        $this->goodsMdl = $this->system->loadModel('goods/products');
        $this->typeMdl=$this->system->loadModel('goods/gtype');
        $this->catMdl=$this->system->loadModel('goods/productCat');
        $this->brandMdl=$this->system->loadModel('goods/brand');
        $this->imgMdl = $this->system->loadModel('goods/gimage');
        $this->tagsMdl = $this->system->loadModel('system/tag');
        $this->toolMdl = $this->system->loadModel('utility/tools');
    }
    
    function search($goods){
        
        $where = 'WHERE 1 AND goods_type = "normal" ';
         if($goods['cat_name']){
            $cat_id = $this->_getcat($goods['cat_name'],'name');
            $where .= ' AND cat_id = '.$this->db->quote($cat_id);
        }  
        
        if($goods['type_name']){
            $type_id = $this->_gettype($goods['type_name'],'name');
            $where .= ' AND type_id = '.$this->db->quote($type_id);
        }
        
        if($goods['brand_name']){
            $brand_id = $this->_getbrand($goods['brand_name'],'name');
            $where .= ' AND brand_id = '.$this->db->quote($brand_id);
        }

        if( $goods['start_time'] > 0){
            $where .= ' AND last_modify > '.$goods['start_time'];
        }

        if( $goods['end_time'] > 0){
            $where .= ' AND last_modify < '.$goods['end_time'];
        }

        if('false' == $goods['is_contain_recycle']){
            $where .= ' AND disabled = "false"';
        }

        //要返回的列
        $columns = '*';
        if( $goods['columns']){
            $columns = explode('|',$goods['columns']);
        }
        
        $goodscount = $this->db->select('SELECT COUNT(1) c FROM sdb_goods '.$where);
        if ( !$data['item_total'] = $goodscount[0]['c']) {
            $data['goods'] = array();
            $this->api_response('true','',$data);
        }
        
        //分页处理
        $data['page_no'] = $goods['page_no'] ? max(1,+$goods['page_no']) : 1;
        $data['page_size'] = (+$goods['page_size'] > 0) ? +$goods['page_size'] : 20;
        $page_offset = ($data['page_no']-1) * $data['page_size'];

        $sql = 'SELECT * FROM sdb_goods '.$where.' limit '.$page_offset.','.$data['page_size'];
        foreach($this->db->select($sql) as $k=>$value){
            if( $tmp = $this->_getgoods($value) ){
                $data['goods'][$k] = $tmp;
            }
        }

        $this->api_response('true','',$data);
    }

    function goods_add($goods){
        $data=$this->_newdata($goods);

        //查看是否已有相同bn的商品，无-添加、有-更改
        if($data['goods_id']){
           $this->goods_update($goods);
        }
        $goods_id = $this->objGoods->save($data);
        
        $keywords = array();
        foreach( $this->objGoods->getKeywords($goods_id) as $keywordvalue ){
            $keywords[] = $keywordvalue['keyword'];
            $keyword = implode('|', $keywords);
        }

        if($keyword != $goods['goods_keywords']){
            $this->objGoods->deleteKeywords($goods_id);
            if( $goods['goods_keywords'] )
                $this->objGoods->addKeywords($goods_id, explode('|',$goods['goods_keywords']) );
        }
        
        if(!$goods_id){
            $this->api_response('fail','商品添加失败');
        }

        //处理tags
        if(!($this->_savetags($goods['tags'],$goods_id))){
            $this->api_response('fail','商品添加失败');
        }

        // 商品图片处理
        $_images = json_decode($goods['goods_images'],true);
        $_images['default'] = $goods['default_image_path'];
        if( !($this->_saveimg($_images,$goods_id)) ){
            $this->api_response('fail','商品添加失败');
        }

        $data_['goods_id'] = $goods_id;
        $data_['goods_url'] = $this->system->realUrl('product','index',array($goods_id));
        $data_['last_modify'] = time();
        
        $this->api_response('true','',$data_);

    }

    function goods_update($goods){
        $data=$this->_newdata($goods);

        if($data['goods_id']){
            if('false' == $goods['is_force_update']){
                if($goods['last_modify'] < $goodp['last_modify ']){
                    $this->api_response('fail','Data Newer');
                }
            }
        }
        //删除原有图片
        //$this->imgMdl->clean($data['goods_id']);

        //删除原有tags
        $this->tagsMdl->removeObjTag($data['goods_id']);

        $result = $this->objGoods->save($data);
        
        $keywords = array();
        foreach( $this->objGoods->getKeywords($data['goods_id']) as $keywordvalue ){
            $keywords[] = $keywordvalue['keyword'];
            $keyword = implode('|', $keywords);
        }

        if($keyword != $goods['goods_keywords']){
            $this->objGoods->deleteKeywords($data['goods_id']);
            if( $goods['goods_keywords'] )
                $this->objGoods->addKeywords($data['goods_id'], explode('|',$goods['goods_keywords']) );
        }
        
        if(!$result){
            $this->api_response('fail','商品更改失败');
        }

        //处理tags
        if(!($this->_savetags($goods['tags'],$data['goods_id']))){
            $this->api_response('fail','商品更改失败');
        }

        // 商品图片处理
        $_images = json_decode($goods['goods_images'],true);
        $_images['default'] = $goods['default_image_path'];
        if(!$this->_saveimg($_images,$data['goods_id'])){
            $this->api_response('fail','商品更改失败');
        }
        
        $data_['goods_id'] = $data['goods_id'];
        $data_['goods_url'] = $this->system->realUrl('product','index',array($data['goods_id']));
        $data_['last_modify'] = time();
        
        $this->api_response('true','',$data_);
    }

    function goods_delete($goods){

        $goods_bns=explode(',',$goods['bns']);
        $good=array();
        foreach((array)$goods_bns as $bn){
            $sql = 'SELECT * FROM sdb_goods WHERE bn = '.$this->db->quote($bn).' AND disabled = '.$this->db->quote($goods['is_physical_delete']);
            $goods_list = $this->db->selectrow($sql);
            if($goods_list){
                $good['goods_id'][]=$goods_list['goods_id'];
            }
        }
        if(empty($good)){
            $this->api_response('true');
        }

        if('true' == $goods['is_physical_delete']){
            foreach($good['goods_id'] as $id){
                $result = $this->objGoods->toRemove($id);
                $this->imgMdl->clean($id);
            }
        }else{
            $result = $this->goodsMdl->recycle($good);
            $this->goodsMdl->setDisabled($good['goods_id'],'true');
        }

        if($result){
            $this->api_response('true');
        }
        $this->api_response('fail','商品删除失败');
    }

    function goods_recover($goods){

        $goods_bns=explode(',',$goods['bns']);
        foreach((array)$goods_bns as $bn){
            $goods_list = $this->db->selectrow('SELECT * FROM sdb_goods where bn = '.$this->db->quote($bn));
            $good['goods_id'][]=$goods_list['goods_id'];
        }
        $this->goodsMdl->active($good);
        $this->goodsMdl->setDisabled($list['goods_id'],'false');
        $this->api_response('true');
    }

    function goods_listing($goods){

        $bns=explode(',',$goods['bns']);
        $goods_list=$this->goodsMdl->getList('goods_id',array('bn'=>$bns));
        foreach($goods_list as $list){
            $data['goods_id'][]=$list['goods_id'];
        }
        if($this->goodsMdl->setEnabled($data,$goods['listing'])){
            $this->api_response('true','',$data['last_modify']=time());
        }
        $this->api_response('fail');
    }

    //还未完成，暂时没有此需求
    function products_update($goods){

        $data=array();
        $good=$this->db->selectrow('SELECT goods_id FROM sdb_goods where bn ='.$this->db->quote($goods['bn']));
        $this->api_response('true');
    }

    //处理添加和修改时post上来的数据
    function _newdata($goods){
        
        $goods['member_lps']=json_decode($goods['member_lps'],true);
        $goods['prop_values']=json_decode($goods['prop_values'],true);
        $goods['params_values']=json_decode($goods['params_values'],true);
        $goods['products']=json_decode($goods['products'],true);
        $spec_desc = array();
        $good=$this->db->selectrow('SELECT goods_id FROM sdb_goods where bn ='.$this->db->quote($goods['bn']));
        if($good){
            $goods_id = $good['goods_id'];
        }
        
        //属性参数处理
        if($goods['params_values']['params_values']){
            foreach($goods['params_values']['params_values'] as $pkey=>$pval){
                foreach($pval['options'] as $ookey=>$ooval){
                    $params[$pval['name']][$ooval['key']] = $ooval['value'];
                }
            }
        }
        
        foreach($goods['member_lps']['member_lps'] as $gk=>$gv){
            $id=$this->_lvname($gv['member_lv_name'],'name');
            $g_mprice[$id]=$gv['price'];
        }
        
        //处理商品货品数据

        foreach($goods['products']['products'] as $value){
            if($this->objGoods->checkProductBn($value['bn_code'],$goods_id)){
                $this->api_response('fail','货号已被使用，请检查！');
            }
            foreach($value['member_lps'] as $ke=>$val){
                $id=$this->_lvname($val['member_lv_name'],'name');
                $mprice[$id]=$val['price'];
            }
            $props = array();

            //处理货品中的规格数据
            foreach($value['spec_values'] as $sval){
                $spec_name=trim($sval['spec_name']); // 规格名
                $spec_memo =trim($sval['spec_alias_name']); // 规格别名
                $spec_self_v_img = str_replace($this->system->base_url(),'',trim($sval['customer_spec_value_image'])); // 自定义规格值图片
                $spec_self_v_value = trim($sval['customer_spec_value_name']); // 自定义规格值

                if($spec_memo){
                    $sqlString = 'SELECT spec_id,spec_name,spec_type FROM sdb_specification WHERE spec_name = '.$this->db->quote($spec_name).' AND spec_memo = '.$this->db->quote($spec_memo);
                } else {
                    $sqlString = 'SELECT spec_id,spec_name,spec_type FROM sdb_specification WHERE spec_name ='.$this->db->quote($spec_name);
                }

                $aData = $this->db->selectrow($sqlString);
                $specid = +$aData['spec_id'];

                $sval_id = $this->db->selectrow('SELECT * FROM sdb_spec_values WHERE spec_value = '.$this->db->quote($sval['spec_value_name']).' AND spec_id = '.$specid);

                $private = time().$sval_id['spec_value_id']; //规格的唯一标示

                $props['spec'][$specid]=$sval['spec_value_name'];
                $props['spec_private_value_id'][$specid]=$private;
                $props['spec_value_id'][$specid]=$sval_id['spec_value_id'];

                $spec=array(
                    'spec_value' => $spec_self_v_value ? $spec_self_v_value : $sval_id['spec_value'],
                    'spec_value_id' => $sval_id['spec_value_id'],
                    'spec_image' =>  $spec_self_v_img ? $spec_self_v_img : $sval_id['spec_image'],
                    'spec_type'=>$aData['spec_type'],
                    'spec_goods_images' => '',
                );

                if ( !$spec_desc[$specid] ) $spec_desc[$specid] = array();

                $spec_desc[$specid][$private] = $spec;
            }
            
            $products[]=array(
                'price'=>$value['price'],
                'bn'=>$value['bn_code'],
                'store'=>$value['store'],
                'cost'=>$value['cost'],
                'weight'=>$value['weight'],
                'mktprice'=>floatval($value['mktprice']),
                'store_place'=>$value['goods_space'],
                'marketable'=>(string)$goods['marketable'],
                'props'=>$props,
                'marketable'=>'true',
                'pdt_desc'=>implode(',',$props['spec']),
                'mprice'=>$mprice,
            );
        }
        
        //入库数据
        $data=array(
            'goods_id'=>$goods_id,
            'db_thumbnail_pic'=>'',
            'name'=>$goods['name'],
            'bn'=>$goods['bn'],
            'unit'=>$goods['unit'],
            'marketable'=>(string)$goods['marketable'],
            'spec_desc'=>serialize($spec_desc),
            'products'=>$products,
            'mprice'=>$g_mprice,
            'seo'=>array(
                'seo_title'=>$goods['page_title'],
                'meta_keywords'=>$goods['meta_keywords'],
                'meta_description'=>$goods['meta_description'],
            ),
            'cat_id'=>(int)$this->_getcat($goods['cat_name'],'name'),
            'type_id'=>(int)$this->_gettype($goods['type_name'],'name'),
            'brand_id'=>$this->_getbrand($goods['brand_name'],'name'),
            'brief'=>$goods['brief'],
            'intro'=>$goods['intro'],
            'spec'=>serialize($props['spec']),
            'params'=>$params,
            'price'=>$goods['price'],
            'cost'=>$goods['cost'],
            'weight'=>$goods['weight'],
            'mktprice'=>floatval($goods['mktprice']),
            's_time'=>0,
            'e_time'=>0,
        );

        //扩展属性
        $props=$this->_gettype($goods['type_name'],'props');
        if($props){
            foreach($goods['prop_values']['items'] as $km=>$pval){
                $post_props[$pval['key']] = $pval['value'];
            }
            foreach((array)$props as $kpro=>$vpro){
                $_options = array_flip($vpro['options']);
                $data['p_'.$kpro] = $_options[$post_props[$vpro['name']]];
            }
        }
        return $data;
    }

    //获取商品详细信息
    function _getgoods($value){
        if(!$value) return array();

        $memLvPrice=$this->_getLvPrice($value['goods_id'],'goods',$value['bn']);
        $product=$this->_getproductBn($value['goods_id'],'goods',$value['bn']);

        //获取分类、类型、品牌名称
        if($value['cat_id']){
            $cat_name = $this->_getcat($value['cat_id'],'id');
            $_cat_path = explode(',',$cat_name['cat_path']);
            $cat_paths = array();
            if($_cat_path){
                foreach((array)$_cat_path as $val){
                    if($val){
                        $name = $this->_getcat($val,'id');
                        $cat_paths[] = $name['cat_name'];
                    }
                }
            }
            $cat_path = implode('->',$cat_paths);
        }

        if($value['type_id']){
            $type_name = $this->_gettype($value['type_id'],'id');
        }

        if($value['brand_id']){
            $brand_name = $this->_getbrand($value['brand_id'],'id');
        }

        //获取图片
        $images=array();
        $images_list = $this->db->select('SELECT * FROM sdb_gimages WHERE goods_id = '.$this->db->quote($value['goods_id']));

        foreach($images_list as $list){
            if('false'==$list['is_remote'] && $list['source'] != 'N'){
                $list['big'] = $this->imgMdl->getUrl( $list['big'] );
                //$list['big'] = $this->system->base_url().$list['big'];
            }else{
                unset($list);  //过滤掉非法图片
            }
            $images[] = array(
                'is_remote' => $list['is_remote'] == true ? 'true' : 'false',
                'source' => (string)$list['big'],
                'last_modify' => intval($list['up_time']),
                'order_by' => 0,
            );
            error_log(var_export($images,1),3,__FILE__.".images.log");
        }

        //商品默认图片
        $has_default_image = 'false';
        $default_image_path = '';
        if($value['image_default']){
            $default_img = $this->db->selectrow('SELECT * FROM sdb_gimages WHERE gimage_id = '.$this->db->quote($value['image_default']));
            if($default_img){
                $has_default_image = 'true';
                if('false' == $default_img['is_remote'] && $default_img['source'] != 'N'){
                    $default_image_path = $this->imgMdl->getUrl( $default_img['big'] );
                   // $default_image_path = (string)$this->system->base_url().$default_img['big'];
                }else{
                    $default_image_path = (string)$value['big_pic'];
                }
            }
        }

        //获取商品tags
        $tags='';
        $tagsMdl = $this->system->loadModel('system/tag');
        $tagslist = $this->db->select('SELECT tag_name FROM sdb_tag_rel r
                        INNER JOIN sdb_tags t
                        ON r.tag_id = t.tag_id
                        WHERE r.rel_id ='.$this->db->quote($value['goods_id']).' AND tag_type ="goods"');
        foreach($tagslist as $ktag=>$vtag){
            $tags[]=$vtag['tag_name'];
        }
        $tags=implode(',',$tags);

        //扩展属性
        $prop_values = array();
        $params_values = array();
        if($value['type_id']){
            $type_list = $this->typeMdl->getList('props',array('type_id'=>$value['type_id']),0,1);
            if($prop = $type_list[0]['props']){
                $prop = unserialize($prop);
                if(!empty($prop)){
                    foreach($prop as $k_p=>$v_p){
                        $p = $value['p_'.$k_p];
                        $prop_values['key'] = (string)$v_p['name'];
                        $prop_values['value'] = (string)$v_p['options'][$p];
                        if($v_p['type'] == 'input'){
                             $prop_values['value'] = (string)$p;
                        }
                        
                        
                        $props_values[] = $prop_values;
                    }
                }
            }
        }
        //详细参数
        $params_v = unserialize($value['params']);
        if(!empty($params_v)){
            foreach($params_v as $k_a=>$v_a){
                $options=array();
                foreach($v_a as $pname=>$pvalue){
                    $options[] = array(
                            'key'=>(string)$pname,
                            'value'=>(string)$pvalue,
                    );
                }
                $params_values[]=array(
                    'name'=>(string)$k_a,
                    'options'=>$options,
                );
            }

        }

        //seo关键字、页面描述
        $seo=array();
        $seolist = $this->db->select('SELECT * FROM sdb_seo WHERE type = "goods" AND source_id = '.$this->db->quote($value['goods_id']));
        if(!empty($seolist)){
            foreach($seolist as $seokey=>$seoval){
                $seo[$seoval['store_key']]=$seoval['value'];
            }
        }

        //商品关键词
        $goods_keyword = $this->db->select('SELECT * FROM sdb_goods_keywords WHERE res_type = "goods" AND goods_id = '.$this->db->quote($value['goods_id']));
        foreach($goods_keyword as $v){
            $goods_key[] = $v['keyword'];
        }
        $goods_keywords = implode('|',$goods_key);

        $data = array(
            'goods_id'=>$value['goods_id'],
            'goods_url'=>$this->system->realUrl('product','index',array($value['goods_id'])),
            'type_name'=>(string)$type_name,
            'cat_name'=>(string)$cat_name['cat_name'],
            'cat_path'=>(string)$cat_path,
            'brand_name'=>(string)$brand_name,
            'goods_type'=>$value['goods_type'],
            'default_image_path'=>$default_image_path,
            'has_default_image'=>$has_default_image ,
            'mktprice'=>floatval($val['mktprice']),
            'cost'=>floatval($value['cost']),
            'price'=>floatval($value['price']),
            'member_lps'=>$memLvPrice,
            'bn'=>$value['bn'],
            'name'=>$value['name'],
            'weight'=>floatval($value['weight']),
            'unit'=>(string)$value['unit'],
            'store'=>(int)$value['store'],
            'goods_space'=>(string)$value['store_place'],
            'score_setting'=>$value['score_setting'],
            'score'=>floatval($value['score']),
            'marketable'=>$value['marketable'],
            'list_time'=>(int)$value['uptime'],
            'delist_time'=>(int)$value['downtime'],
            'disabled'=>(string)$value['disabled'],
            'order_by'=>(int)$value['p_order'],
            'd_order'=>(int)$value['d_order'],
            'page_title'=>(string)$seo['title'],
            'brief'=>$value['brief'],
            'intro'=>str_replace(array('<![CDATA[','<![cdata[',']]>'),'',$value['intro']),
            'tags'=>$tags,
            'products'=>$product,
            'goods_images'=>$images,
            'prop_values'=>$props_values,
            'params_values'=>$params_values,
            'meta_description'=>$seo['descript'],
            'meta_keywords'=>$seo['keywords'],
            'goods_keywords'=>$goods_keywords,
            'is_unlimit'=>'false',
            'last_modify'=>(int)time(),
        );
        error_log(var_export($data,1),3,__FILE__.".1.log");
        return $data;
    }

    //保存商品图片
    function _saveimg($_images,$goods_id){
        //处理默认图片
        if($default = $_images['default_image_path']){
            $host = trim($this->system->base_url());
            $imgpath_d = strstr($image['source'],$host);            
            $w_imgpath = $this->toolMdl->is_img_url($default);
            if($imgpath_d){
                $imgpath_d = substr($default,strlen($host.str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/'));
                $sql = 'SELECT * FROM sdb_gimages WHERE is_remote = "false" AND source like "%'.$imgpath_d.'%"';
                $img_url = $this->db->selectrow($sql);
                if(!empty($img_url)){
                    $default_id = $img_url['gimage_id'];
                }
            }elseif($w_imgpath){
                $sql = 'SELECT * FROM sdb_gimages WHERE small ='.$this->db->quote($default);
                $img_list = $this->db->selectrow($sql);
                if(empty($img_list)){
                    $d_imgdata=array(
                        'is_remote'=>'true',
                        'source'=>'N',
                        'small'=>$default,
                        'big'=>$default,
                        'thumbnail'=>$default,
                        'orderby'=>0,
                        'up_time'=>time(),
                    );
                    $default_id = $this->imgMdl->insert_new($d_imgdata,$goods_id);
                }else{
                    $default_id=$img_list['gimage_id'];
                }
            }
        }

        //处理其他图片
        $imgid=array();
        if($_images['images']){
            foreach($_images['images'] as $k=>$image){
                $host = trim($this->system->base_url());
                $imgpath = strstr($image['source'],$host);
                $w_imgpath = $this->toolMdl->is_img_url($image['source']);

                if($imgpath){
                    $imgpath = substr($image['source'],strlen($host.str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/'));
                    $sql = 'SELECT * FROM sdb_gimages WHERE is_remote = "false" AND source like "%'.$imgpath.'%"';
                    $img_url = $this->db->selectrow($sql);
                    if($img_url['gimage_id']){
                        $imgid[] = $img_url['gimage_id'];
                    }
                }elseif($w_imgpath){
                    $sql = 'SELECT * FROM sdb_gimages WHERE small ='.$this->db->quote($image['source']);
                    $img_list = $this->db->selectrow($sql);
                    if(empty($img_list)){
                        $imgdata=array(
                            'is_remote'=>'true',
                            'source'=>'N',
                            'small'=>$image['source'],
                            'big'=>$image['source'],
                            'thumbnail'=>$image['source'],
                            'orderby'=>0,
                            'up_time'=>time(),
                        );
                        $id = $this->imgMdl->insert_new($imgdata,$goods_id);
                        if($id){
                            $imgid[] = $id;
                        }
                    }else{
                        $imgid[]=$img_list['gimage_id'];
                    }
                }

            }
        }
        if($imgid){
            $result = $this->imgMdl->saveImage($goods_id,'',$default_id,$imgid,'false');
        }
        return true;
    }

    //保存商品标签
    function _savetags($tags,$goods_id){
        if($tags){
            $_tags = array_filter(explode(',',$tags));
        }
        foreach((array)$_tags as $val){
            $tags_id = $this->tagsMdl->getTagByName('goods',$val);
            $this->tagsMdl->addTag($tags_id,$goods_id);

        }
        return true;
    }

        //获取分类id或name
    function _getcat($name,$type='id'){
        if('id' == $type){
            $cat=$this->catMdl->getList('cat_name,cat_path',array('cat_id'=>$name),0,1);
            return $cat[0];
        }else{
            if($name){
                $cat=$this->catMdl->getList('cat_id',array('cat_name'=>$name),0,1);
                if($cat){
                    return $cat[0]['cat_id'];
                }
            }
            return 0;
        }

    }

    //获取类型id或name、props（扩展属性）
    function _gettype($name,$type='id'){
        if('id' == $type){
            if($name){
                $type=$this->typeMdl->getList('name',array('type_id'=>$name),0,1);
            }
            return trim($type[0]['name']);
        }elseif('name'==$type){
            if($name){
                $type=$this->typeMdl->getList('type_id',array('name'=>$name),0,1);
                if($type[0]['type_id']){
                    return $type[0]['type_id'];
                }
            }
            return 1;
        }else{
            if($name){
                $type=$this->typeMdl->getList('type_id,props',array('name'=>$name),0,1);
                if($type){
                    return unserialize($type[0]['props']);
                }
            }
            return 1;

        }
    }

    //获取品牌id或name
    function _getbrand($name,$type='id'){
        if('id' == $type){
            $brand=$this->brandMdl->getList('brand_name',array('brand_id'=>$name),0,1);
            return trim($brand[0]['brand_name']);
        }else{
            if($name){
                $brand=$this->brandMdl->getList('brand_id',array('brand_name'=>$name),0,1);
                if($brand[0]['brand_id']){
                    return $brand[0]['brand_id'];
                }
            }
            return null;
        }
    }

    //处理会员价格
    function _getLvPrice($id=0,$type='goods',$bn){

        $leveprice=array();
        if('goods' == $type){
            $levelist=$this->db->select("SELECT * FROM sdb_goods_lv_price where goods_id = ".$this->db->quote($id));
        }elseif('product' == $type){
            $levelist=$this->db->select("SELECT * FROM sdb_goods_lv_price where product_id = ".$this->db->quote($id));
        }

        foreach((array)$levelist as $k=>$v){
            $bn_code=$this->_getproductBn($v['product_id'],'product');
            $leve_name=$this->_lvname($v['level_id'],'id');
            $leveprice[$k] = array(
                'member_lv_name'=>(string)$leve_name,
                'price'=>floatval($v['price']),
                'bn'=>$bn,
                'bn_code'=>$bn_code,
                'last_modify'=>time(),
            );
        }
        return $leveprice;
    }

    //处理货品bn
    function _getproductBn($id,$type='goods',$bn=''){
        if('product' == $type){
            $product=$this->db->selectrow("SELECT bn FROM sdb_products where product_id = ".intval($id));
            return $product['bn'];
        }elseif('goods' == $type){
            $goods = $this->db->selectrow("SELECT * FROM sdb_goods where goods_id = ".intval($id));
            $specs = unserialize($goods['spec_desc']);
            
            $productlist=$this->db->select("SELECT * FROM sdb_products where goods_id = ".intval($id));
            $product=array();

            foreach((array)$productlist as $k=>$val){
                $leveprice=$this->_getLvPrice($val['product_id'],'product',$bn);
                $product_spec=unserialize($val['props']);

                if( !$product_spec['spec'] ){
                    continue;
                }
                
                $spec=array();
                foreach((array)$product_spec['spec_value_id'] as $kk=>$vv){
                    $sql = 'SELECT s.spec_name,s.spec_memo,v.spec_value,s.spec_id
                        FROM sdb_spec_values v
                        INNER JOIN sdb_specification s ON v.spec_id = s.spec_id
                        WHERE (v.spec_value_id = '.$this->db->quote($vv).'
                        OR v.alias = '.$this->db->quote($product_spec['spec'][$kk]).'
                        OR v.spec_value = '.$this->db->quote($product_spec['spec'][$kk]).')
                        AND s.spec_id = '.intval($kk);
                    $list = $this->db->selectrow($sql);

                    if( !$list['spec_name'] || !$list['spec_value']){
                        continue 2;
                    }

                    if ( $specs[$list['spec_id']]['spec_goods_images'] ) {
                        $customer_spec_value_image = $this->system->base_url().$specs[$list['spec_id']]['spec_goods_images'];
                    } else {
                        $customer_spec_value_image = '';
                    }
                    
                    $spec_image = $specs[$list['spec_id']][$product_spec['spec_private_value_id'][$list['spec_id']]]['spec_image'];
                    if ( $spec_image ) {
                        $spec_image = $this->system->loadModel('storager')->parse($spec_image);
                        $spec_image = $this->system->base_url().$spec_image['url'];
                    } else {
                        $spec_image = '';
                    }
                    
                    $spec[]=array(
                        'spec_name'=>$list['spec_name'],
                        'spec_alias_name'=>$list['spec_memo'],
                        'spec_value_name'=>$list['spec_value'],

                        'customer_spec_value_name'=>$specs[$list['spec_id']][$product_spec['spec_private_value_id'][$list['spec_id']]]['spec_value'],
                        'customer_spec_value_image'=>$spec_image,
                        'rela_goods_images'=>$specs[$list['spec_id']][$product_spec['spec_private_value_id'][$list['spec_id']]]['spec_goods_images'],
                    );
                }
                
                $product[]=array(
                    'barcode'=>(string)$val['barcode'],
                    'bn_code'=>$val['bn'],
                    'price'=>floatval($val['price']),
                    'member_lps'=>$leveprice,
                    'cost'=>floatval($val['cost']),
                    'weight'=>floatval($val['weight']),
                    'mktprice'=>floatval($val['mktprice']),
                    'store'=>(int)$val['store'],
                    'goods_space'=>(string)$val['store_place'],
                    'spec_values'=>$spec,
                    'last_modify'=>(int)$val['last_modify'],
                    'is_unlimit'=>'false',
                );
            }
            return $product;
        }
    }

    //会员等级名
    function _lvname($lv,$type='id'){
        if('id' == $type){
            $leve=$this->db->selectrow("SELECT name FROM sdb_member_lv where member_lv_id = ".$this->db->quote($lv));
            return $leve['name'];
        }elseif('name' == $type){
            if($lv){
                $leve=$this->db->selectrow("SELECT member_lv_id FROM sdb_member_lv where name = ".$this->db->quote($lv));
                if($leve){
                    return $leve['member_lv_id'];
                }
            }
        }
    }
}
