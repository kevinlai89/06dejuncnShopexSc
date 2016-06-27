<?php

class api_tag extends shop_api_object {
    function __construct() {
        parent::__construct();
    }
    
    public function tags_list($params){
        $pageno = +$params['page_no'] ? +$params['page_no'] : 1;   
        $pagesize = +$params['page_size'] ? +$params['page_size'] : 20;
        
        $tagsMdl = $this->system->loadModel('system/tag');
        $data=array();
        $tags=$tagsMdl->getList('*',array('tag_type'=>'goods'),($pageno-1)*$pagesize,$pagesize);
        foreach($tags as $key=>$val){
            $id=$val['tag_id'];
            $data[$id]=array(
                'tag_name'=>$val['tag_name'],
                'tag_type'=>$val['tag_type'],
            );
        }
        
        $data['item_total'] = $tagsMdl->count();
        
        $this->api_response('true','',$data);
    }
}
