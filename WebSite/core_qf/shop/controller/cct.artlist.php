<?php
class cct_artlist extends ctl_artlist{
    function index2($cat_id,$page=1) {
        $this->id=array('node_id'=>$cat_id);
        if(intval($cat_id)){
            $objSitemap = $this->system->loadModel('content/sitemap');
            $objArticle = $this->system->loadModel('content/article');
            $filter['node_id'] = intval($cat_id);
            $aInfo = $objSitemap->getPathById($filter['node_id'],false);
            foreach($aInfo as $r){
                if($r['node_id'] == $filter['node_id']){
                    $this->pagedata['cat_name'] = $r['title'];
                    break;
                }
            }
        }
        
        $filter['ifpub'] = 1;
        if($this->system->getConf('system.seo.noindex_catalog')){
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';
        }
        $node_id = array($filter['node_id']);
        $objArticle->modifier_node_name($node_id);
        $pageLimit = 10;
        $result = $objArticle->getList('title,article_id,uptime,member_lv,iftop,level_status,picture,intro,source,view,editor,keywords',$filter,($page-1)*$pageLimit,$pageLimit,$count);
        foreach($result as $key=>$value){
            if($result[$key]['member_lv'] != '0'){
                $userlev = ltrim($result[$key]['member_lv'],",");
                $userlev = rtrim($userlev,",");
                $userlev = explode(",",$userlev);
                $result[$key]['userlevel'] = $userlev;
            }
            if($result[$key]['level_status']=='2'){
                $artlevel=$objArticle->getArtmemid($result[$key]['article_id']);
                $result[$key]['artlevel']=$artlevel;
            }
        }
        $this->_verifyMember(false);
        $this->pagedata['articles'] = $result;
        $this->pagedata['member_level'] = $this->member['member_lv_id'];
        $this->pagedata['member_loginid'] = $this->member['member_id'];
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>floor($count/$pageLimit)+1,
                'link'=>$this->system->mkUrl('artlist','index',array($cat_id,($tmp = time()))),'token'=>$tmp);
        if($page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        $this->path[]=array('title'=>$node_id[$filter['node_id']]);
        $this->getGlobal($this->seoTag,$this->pagedata);
        $this->output();
    }
    
    function index($cat_id,$page=1) {
        $this->customer_source_type='artlist';
        $this->customer_template_type = 'artlist';
        $this->customer_template_id=$cat_id;
        $this->id=array('node_id'=>$cat_id);
        if(intval($cat_id)){
            $objSitemap = &$this->system->loadModel('content/sitemap');
            $filter['node_id'] = intval($cat_id);
            $aInfo = $objSitemap->getPathById($filter['node_id'],false);
            foreach($aInfo as $r){
                if($r['node_id'] == $filter['node_id']){
                    $this->pagedata['cat_name'] = $r['title'];
                    $this->path[] = array('title'=>$r['title'],'link'=>$this->system->mkUrl('artlist','index',array($cat_id)));                    
                    break;
                }
            }
        }
        $filter['ifpub'] = 1;

        if($this->system->getConf('system.seo.noindex_catalog'))
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $pageLimit = 20;
        $objArticle = &$this->system->loadModel('content/article');
        $this->pagedata['articles'] = $objArticle->getList('title,article_id,uptime,ifpub,iftop,node_id,pubtime,picture,intro,source,view,editor,keywords',$filter,($page-1)*$pageLimit,$pageLimit, 'iftop desc,uptime desc');
        
        $count = $objArticle->count($filter);

        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>($count>$pageLimit)?(floor($count/$pageLimit)+1):1,
                'link'=>$this->system->mkUrl('artlist','index',array($cat_id,($tmp = time()))),'token'=>$tmp);
      
        if($page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        $this->path[]=array('title'=>'');
        $this->getGlobal($this->seoTag,$this->pagedata);
        $this->output();
    }
}
?>
