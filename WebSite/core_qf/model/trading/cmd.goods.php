	<?php
/*author:http://www.shopextool.cn*/
class cmd_goods extends mdl_goods{
    function save(&$data){
        $gid = parent::save($data);        
        if ($gid){            
            $this->db->exec("delete from sdb_goods_cat_mrel where goods_id=".intval($gid));            
            if (isset($_POST['mgoodscat'])){
                foreach ($_POST['mgoodscat'] as $cid){
                    $this->db->exec("insert into sdb_goods_cat_mrel(goods_id,cat_id) values(".intval($gid).",".intval($cid).")");
                }
            }
        }
        return $gid;
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
    function process_qtn_data(&$g){
        if ($g){            
        }
    }
    
    function getGoods($goods_id, $levelid=0){
        $g = parent::getGoods($goods_id, $levelid);
		
		     if ($g){
            $mgoodsrel = array();
            $catids = $this->db->select("select cat_id from sdb_goods_cat_mrel  where goods_id=".intval($goods_id));
            foreach ($catids as $cat){
                $mgoodsrel[] = $cat['cat_id'];
            }
            $g['mgoodscat'] = implode(',',$mgoodsrel);
        }
        if (empty($g['mgoodscat'])) $g['mgoodscat'] = '-1';        
		
        $this->process_qtn_data($g);
        return $g;
    }

    function get_qtn_config(){
        $config['qtn_show'] = $this->system->getConf('system.qtn_show');
        return $config;
    }

    function save_qtn_config($data){
        $this->system->setConf('system.qtn_show', $data['qtn_show'], true);
    }

    function genQtnId(){
        return md5(uniqid(rand().date("U").((double)microtime()*rand(0,1000000))));
    }

    function getBlankQtnTemplate(){
        $qtn_template = array(
            'id' => $this->genQtnId(),
            'name' => '',            
            );
        for ($i = 0; $i <= 8; $i++){
            $qtn_template['cus_items'][] = array('name' => '', 'required' => false);
        }
        return $qtn_template;
    }

    function saveQtnTemplate($data){
        if ($data['id']){
            $template['id'] = $data['id'];
            $template['name'] = $data['name'];
            foreach ($data['cusitems']['name'] as $key => $name) {
                if ($name){
                    $cus_item = array('name' => $name, 'required' => isset($data['cusitems']['required'][$key]) ? true : false);
                    $template['cus_items'][] = $cus_item;
                }
            }

            $qtn_template_list = $this->getQtnTemplateList();            
            $qtn_template_list[$template['id']] = $template;
            $this->system->setConf('system.qtn_template_list', $qtn_template_list, true);
        }
    }

    function getQtnTemplate($id){
        $qtn_template_list = $this->getQtnTemplateList();
        $template = null;
        if (array_key_exists($id, $qtn_template_list)){
            $template = $qtn_template_list[$id];
        }
        return $template;
    }

    function getQtnTemplateList(){
        $data = $this->system->getConf('system.qtn_template_list');
        if (!$data){
            $data = array();
        }
        return $data;
    }
    
}