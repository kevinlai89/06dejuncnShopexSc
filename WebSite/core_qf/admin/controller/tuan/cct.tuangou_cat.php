<?php
include_once('objectPage.php');
class cct_tuangou_cat extends objectPage{

    var $workground = 'goods';
    var $object = 'tuan/tuangou_cat';
    var $finder_action_tpl = '';
    var $finder_filter_tpl = 'tuan/tuangou_cat/finder_filter.html';
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
  function cct_tuangou_cat(){
			$this->skv();
      parent::objectPage();
  } 
    function addNew($id=0){
        $this->path[] = array('text'=>__('团购分类新增'));
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        $aCat = $objCat->get_cat_list(true);
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>__('----无----'),'step'=>1);
        if(empty($aCat)){
            $aCat = $aCatNull;
        }else{
            $aCat = array_merge($aCatNull, $aCat);
        }
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        if($id){
            $aCat = $objCat->getFieldById($id);
            $this->pagedata['cat']['parent_id'] = $aCat['cat_id'];
            $this->pagedata['cat']['type_id'] = $aCat['type_id'];
        }else{
            $aTmp = $oGtype->getDefault();
            $this->pagedata['cat']['type_id'] = $aTmp[0]['type_id'];
        }
        $this->pagedata['cat']['p_order'] = 0;

        $this->page('tuan/tuangou_cat/info.html');
    }

    function doAdd(){
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        if($objCat->addNew($_POST['cat']))
            $this->splash('success','index.php?ctl=tuan/tuangou_cat&act=index',__('保存成功'));
        else
            $this->splash('failed','index.php?ctl=tuan/tuangou_cat&act=index',__('保存失败'));
    }

    function view($catid){
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        if($views = $objCat->getTabs($catid)){
            $this->pagedata['views'] = $views;
        }
        $this->pagedata['params'] = array('cat_id'=>$catid);
        $this->pagedata['catid'] = $catid;
        $this->page('tuan/tuangou_cat/view.html');
    }

    function edit($catid){
        $this->path[] = array('text'=>__('团购分类编辑'));
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        $aCat = $objCat->getFieldById($catid);
        $aCat['addon'] = unserialize($aCat['addon']);
        $this->pagedata['cat'] = $aCat;

        $aCat = $objCat->get_cat_list();
        $aCatNull[] = array('cat_id'=>0,'cat_name'=>__('----无----'),'step'=>1);
        $aCat = array_merge($aCatNull, $aCat);
        $this->pagedata['catList'] = $aCat;
        $this->pagedata['gtypes'] = $objCat->getTypeList();
        $oGtype = &$this->system->loadModel('goods/gtype');
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();

        $this->page('tuan/tuangou_cat/info.html');
    }

    function addView($catid){
        $this->pagedata['params'] = array('cat_id'=>$catid);
        $this->pagedata['item'] = array('label'=>'New View '.mydate('H:i:s'));
        $this->display('tuan/tuangou_cat/view_row.html');
    }

    function saveView($catid){
        foreach($_POST['view'] as $k=>$v){
            if($v) $views[] = array('label'=>$v,'filter'=>$_POST['filter'][$k]);
        }
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        $this->splash($objCat->setTabs($catid,$views),'index.php?ctl=tuan/tuangou_cat&act=index');
    }


    function index(){
        $objCat = &$this->system->loadModel('tuan/tuangou_cat');
        if($objCat->checkTreeSize()){
            $this->pagedata['hidenplus']=true;
        }
        $tree=$objCat->get_cat_list();
        $this->pagedata['tree_number']=count($tree);
        foreach($tree as $k=>$v){
            $tree[$k]['link'] = array('cat_id'=>array(
                            'v'=>$v['cat_id'],
                            't'=>__('团购类别').__('是').$v['cat_name']
                        ));
        }
        $this->pagedata['tree']= &$tree;
        $depath=array_fill(0,$objCat->get_cat_depth(),'1');
        $this->pagedata['depath']=$depath;
        $this->page('tuan/tuangou_cat/map.html');
    }


    function toRemove($id){
        $this->begin('index.php?ctl=tuan/tuangou_cat&act=index');
        $objType=&$this->system->loadModel('tuan/tuangou_cat');
        if($objType->toRemove($id)){
            $this->end(true,__('分类删除成功'));
        }else{
            $this->end(false,__('分类删除失败'));
        }
    }

    function update(){
        $objType=&$this->system->loadModel('tuan/tuangou_cat');
        if($objType->updateOrder($_POST['p_order'])){
            $this->splash('success','index.php?ctl=tuan/tuangou_cat&act=index',__('更新成功'));
        }else{
            $this->splash('failed','index.php?ctl=tuan/tuangou_cat&act=index',__('更新失败'));
        }
    }
}
?>
