<?php
class cmd_article extends mdl_article {
    var $defaultCols = 'title,uptime,ifpub,iftop,node_id,_preview,pubtime,picture,intro,source,view,editor,keywords';    
        
    function getColumns(){
        $ret = parent::getColumns();
        $ret['source'] = array('label'=>'来源','class'=>'span-4');
        $ret['editor'] = array('label'=>'编辑','class'=>'span-4');
        $ret['view'] = array('label'=>'阅读次数','class'=>'span-2');
		$ret['iftop'] = array('label'=>'是否置顶','class'=>'span-2');
        return $ret;
    }

     /**
     *添加文章
     */
    function addArticle($data){        
        $data['editor']= $this->system->op_name;
        return parent::addArticle($data);
    }

    function addViewCount($articleid){
		$this->db->exec( "update sdb_articles set view=view+1 WHERE article_id=".$this->db->quote($articleid ));
	}

    function getList($cols,$filter='',$start=0,$limit=20,$orderType=null){    
        $ident=md5(var_export(func_get_args(),1));
        if(!$this->_dbstorage[$ident]){
            if(!$cols){
                $cols = $this->defaultCols;
            }
            if(!empty($this->appendCols)){
                $cols.=','.$this->appendCols;
            }
            $orderType = $orderType?$orderType:$this->defaultOrder;
            $sql = 'SELECT '.$cols.' FROM '.$this->tableName.' WHERE '.$this->_filter($filter);

            if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
            $this->_dbstorage[$ident]=$this->db->selectLimit($sql,$limit,$start);
        }
        return $this->_dbstorage[$ident];        
    }
}
?>
