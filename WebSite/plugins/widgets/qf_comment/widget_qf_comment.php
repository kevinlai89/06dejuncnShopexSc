<?php
function widget_qf_comment(&$setting,&$system){
    $o = &$system->loadModel('comment/comment');
    $setting['content_length']=$setting['content_length']?$setting['content_length']:'200';
	return $o->db->select("SELECT g.name,g.thumbnail_pic,c.comment_id,c.author,c.goods_id,c.comment,c.time FROM sdb_comments as c left join sdb_goods as g on g.goods_id=c.goods_id WHERE c.for_comment_id is Null and c.display='true' and g.disabled='false' GROUP By c.goods_id ORDER BY c.comment_id desc limit 0,".intval($setting['limit']));
}
?>
