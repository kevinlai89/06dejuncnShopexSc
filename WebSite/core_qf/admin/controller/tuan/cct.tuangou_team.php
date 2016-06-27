<?php
include_once('objectPage.php');
class cct_tuangou_team extends objectPage{

    var $object = 'tuan/tuangou_team';
    var $finder_action_tpl = null; //默认的动作html模板,可以为null
    var $finder_filter_tpl = null; //默认的过滤器html,可以为null
    var $finder_default_cols = '_cmd,goods_id,cat_id,name,price,mktprice,min_number,max_number,now_number,now_users,pre_number,begin_time,expire_time,brief';//default columns
    var $workground = 'goods';
    var $filterUnable = false;
	function skv(){
			if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}}
			preg_match('/[\w][\w-]*\.(?:com\.cn|co\.nz|co\.uk|com|cn|co|net|org|gov|cc|biz|info|hk)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$dms=@file_get_contents(CUSTOM_CORE_DIR.'/core/s.cer');
			$ardms=explode('/',$dms);
			$nroot=rtrim($domain[0], '/');
			$nrootmd=md5(base64_encode(substr(md5($nroot.'apchwinwy%%qf2013'),8,16)));
			if(strpos($_SERVER['HTTP_HOST'],'localhost')===false && strpos($_SERVER['HTTP_HOST'],'127.0.0.1')===false && strpos($_SERVER['HTTP_HOST'],'192.168.1.')===false){
				if(!in_array(${'nrootmd'},${'ardms'})){
						exit(0);
				}
			}
	}  
  function cct_tuangou_team(){
			$this->skv();
      parent::objectPage();
  } 
	function newpic(){
         $this->display('tuan/newPic.html'); 
	}
	function donewpic(){
    $rt="";
    if($_FILES['tuan_big_pic']){
          $objtuan = &$this->system->loadModel('tuan/tuangou_team');
          $rt=$objtuan->savetuanpic($_POST);
    }
    if($rt){
      $simpleimgsrc=$this->dospximgs($rt);
      echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><span style="color:red;line-height: 25px;">上传成功</span><script>parent.document.getElementById("tuan_big_pic").value="'.$rt.'";if(parent.document.getElementById("img_tuan_big_pic")){parent.document.getElementById("img_tuan_big_pic").src="'.$simpleimgsrc.'"}else{parent.document.getElementById("img_tuan_big_pic_no").style.display="";parent.document.getElementById("img_tuan_big_pic_no").src="'.$simpleimgsrc.'"}</script></body></html>';
      echo ",<a href='./index.php?ctl=tuan/tuangou_team&act=newPic'>若需要修改,请重新上传</a></body></html>";
    }
    else{
       echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><a href="./index.php?ctl=tuan/tuangou_team&act=newPic">上传失败,请重新上传</a></body></html>';
    }
	}
	function dospximgs($imgstr){
    	if(strpos($imgstr,'|fs_storage'))
    	{
        	$aimg=explode('|',$imgstr);
        	if(strpos($aimg[0],'http://')!==false){
              return $aimg[0];  
        	}
        	else{
             return '../'.$aimg[0];
        	}
    	}
    	else{
    	   if(strpos($imgstr,'http://')!==false){
            return $imgstr;
        	}
        	else{
             return $imgstr;
        	}
    	}
	}
    function setting(){
      $this->path[] = array('text'=>'团购设置');        
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
                $this->system->setConf('site.tuan.priceRange', $_POST['priceRange'], true);  
          $this->system->setConf('site.tuan.nums', $_POST['nums'], true);  
          $this->system->setConf('site.tuan.showcat', $_POST['showcat'], true);
          $this->system->setConf('site.tuan.default_list_width', $_POST['default_list_width'], true);    
          $this->system->setConf('site.tuan.nums_more', $_POST['nums_more'], true);  
          $this->system->setConf('site.tuan.nums_history', $_POST['nums_history'], true);  
          $this->system->setConf('site.tuan.display', $_POST['display'], true);            
          $this->system->setConf('site.tuan.orderBy', $_POST['orderBy'], true);
          $this->system->setConf('site.tuan.nav_index', $_POST['nav_index'], true);
          $this->system->setConf('site.tuan.title_index', $_POST['title_index'], true);
          $this->system->setConf('site.tuan.keyword_index', $_POST['keyword_index'], true);
          $this->system->setConf('site.tuan.desc_index', $_POST['desc_index'], true);       
          $this->splash('success','index.php?ctl=tuan/tuangou_team&act=setting','保存成功!');
      }
      $this->pagedata['nums']=$this->system->getConf('site.tuan.nums'); 
         $this->pagedata['priceRange']=$this->system->getConf('site.tuan.priceRange');    
      $this->pagedata['showcat']=$this->system->getConf('site.tuan.showcat'); 
      $this->pagedata['default_list_width']=$this->system->getConf('site.tuan.default_list_width'); 
      $this->pagedata['nums_more']=$this->system->getConf('site.tuan.nums_more'); 
      $this->pagedata['display']=$this->system->getConf('site.tuan.display');   
      $this->pagedata['orderBy']=$this->system->getConf('site.tuan.orderBy'); 
      $this->pagedata['nav_index']=$this->system->getConf('site.tuan.nav_index'); 
      $this->pagedata['title_index']=$this->system->getConf('site.tuan.title_index'); 
      $this->pagedata['keyword_index']=$this->system->getConf('site.tuan.keyword_index'); 
      $this->pagedata['desc_index']=$this->system->getConf('site.tuan.desc_index'); 

      $this->page('tuan/setting.html');
  }  
}
?>
