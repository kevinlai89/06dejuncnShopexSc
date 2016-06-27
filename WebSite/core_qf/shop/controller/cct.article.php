<?php
class cct_article extends ctl_article{
    function index($articleid) {
        $objArticle = &$this->system->loadModel('content/article');
        $article_info = $objArticle->instance($articleid);        
        $this->keyWords = $article_info['keywords']?$article_info['keywords']:$this->system->getConf('site.article_meta_key_words');
		$objArticle->addViewCount($articleid);        		
		$sql = "SELECT CASE WHEN SIGN(if((article_id-{$articleid})<article_id, (article_id-{$articleid}), 0)) > 0 THEN 'Next' ELSE 'Prev' END AS DIR, CASE WHEN SIGN(if((article_id-{$articleid})<article_id, (article_id-{$articleid}), 0)) > 0 THEN MIN(article_id) ELSE MAX(article_id) END AS article_id FROM sdb_articles WHERE article_id <> {$articleid} and disabled='false'  GROUP BY DIR ORDER BY article_id asc";
		$arcs = $objArticle->db->select($sql);
		foreach($arcs as $a){
			$row = $objArticle->get($a['article_id']);
			$a['title'] = $row['title'];
			$data[$a['DIR']] = $a;
		}
		$this->pagedata['prenext'] = $data;
		$objGoods = $this->system->loadModel('trading/goods');
    	$this->pagedata['qtn_config'] = $objGoods->get_qtn_config();    	
        parent::index($articleid);
    }
}

