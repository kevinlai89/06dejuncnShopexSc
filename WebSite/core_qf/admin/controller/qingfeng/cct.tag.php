<?php
class cct_tag extends adminPage {	
	var $workground = 'setting';

    function setTag(){
    	$this->path[] = array( "text" => "商品标签" );
    	
    	$this->pagedata['tag_status'] = $this->system->getConf('site.tag_status');
    	$tag_rule = unserialize($this->system->getConf('site.tag_rule'));
        $this->pagedata['tag_rule'] = $tag_rule;
    	$this->page('qingfeng/tag_setting.html');   	
    }
    
    function tagEdit(){
    	$tag_rule = $_POST['tag'];
        $this->system->setconf('site.tag_status',$_POST['tag_status']);
        $this->system->setconf('site.tag_rule',serialize($tag_rule));    	
    	$this->splash( "success", "index.php?ctl=qingfeng/tag&act=setTag", __( "保存成功!" ) );
    }
    
    
}