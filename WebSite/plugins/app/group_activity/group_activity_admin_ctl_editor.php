<?php
   if (!class_exists('ctl_editor')) {
	require_once(CORE_DIR.'/admin/controller/ctl.editor.php');
}
class group_activity_admin_ctl_editor extends ctl_editor{
   
	function selectobj() {
		parent::selectobj();
       
            }
    function _select_obj($filter){
      
       if(!isset($filter['marketable'])){
          $filter['marketable']="true";
       }
        $o = &$this->system->loadModel($_GET['object']);//  trading/package
        $limit = 10;
        if(!$_GET['page']){
            $_GET['page'] = 1;
        }
        $start = ($_GET['page']-1) * $limit;
        $this->pagedata['data'] = &$o->getColumns();
        if($_COOKIE['LOCALGOODS']){
            $this->pagedata['items'] = $o->getBindList($start,$limit,$count,$filter);
        }else{
            $this->pagedata['items'] = $o->getList($o->idColumn.','.$o->textColumn,$filter,$start,$limit);
            $count = $o->count($filter);
        }
        

        $this->pagedata['textColumn'] = $o->textColumn;
        $this->pagedata['idColumn'] = $o->idColumn;
        $this->pagedata['ipt_type'] = $_GET['select']=='checkbox'?'checkbox':'radio';

        $this->pagedata['pager'] = array(
            'current'=> $_GET['page'],
            'total'=> ceil($count/$limit),
            'link'=> 'javascript:update_'.$_GET['obj_id'].'(_PPP_)',
            'token'=> '_PPP_'
        );
  
        $this->_filter();
    }
     
      

	
	 
}
?>
