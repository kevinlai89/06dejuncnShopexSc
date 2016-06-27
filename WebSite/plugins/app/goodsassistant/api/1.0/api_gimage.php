<?php

class api_gimage extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->imgMdl = $this->system->loadModel('goods/gimage');
    }
    
    public function image_upload($img){
        $img['type'] = 'goods';
        $data=array();$file = $_FILES['img'];
        
        if('goods' == $img['type']){
            if(!$result = $this->imgMdl->save_upload($file)){
                $this->api_response('fail','图片上传失败');
            }
            $sql = 'select * from sdb_gimages where gimage_id ='.$this->db->quote($result['gimage_id']);
            $imgurl = $this->db->selectrow($sql);
            
            if($imgurl['source'] != 'N'){
                $data['img_path'] = $this->system->base_url().str_replace(BASE_DIR.'/','',HOME_DIR).'/upload/'.$imgurl['source'];
            }
        }
        
        $this->api_response('true','',$data);
    }
    
}
