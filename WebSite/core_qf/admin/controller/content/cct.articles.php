<?php
include_once CORE_DIR.'/admin/controller/content/ctl.articles.php';
class cct_articles extends ctl_articles{
    function save($article_id,$node_id){
        if($_POST['iftop']) $_POST['iftop'] = 1;
        else $_POST['iftop'] = 0;
        parent::save($article_id,$node_id);
    }
    
    function &_fetch_compile($file){
        $old_template_dir = $this->template_dir;
        $this->template_dir = $this->_resource_type == 'file' ? '' : $this->template_dir;        
        $output = parent::_fetch_compile($file);
        $this->template_dir = $old_template_dir; 
        return $output;
    }
    
    function _get_resource($file){
        if (file_exists(CORE_DIR.'/admin/view/'.$file)){
            $this->template_dir = CORE_DIR.'/admin/view/';
        }
        return parent::_get_resource($file);
    }
}
?>
