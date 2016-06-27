<?php
include_once('objectPage.php');
class cct_comment_goods_type extends objectPage{

    var $object = 'comment/comment_goods_type';
    var $finder_action_tpl = 'comment/comment_goods_type/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = null; //默认的过滤器html,可以为null
    var $finder_default_cols = 'type_id ,name ,orderlist';//default columns
    var $workground = 'member';
    var $filterUnable = true;
	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}
    function addType(){
        $this->begin('index.php?ctl=comment/comment_goods_type&act=index');
        if(!$_POST['addon']){
          $atmp['is_total_point']='on';
          $_POST['addon']=serialize($atmp);
        }
        $comment_goods_type = &$this->system->loadModel('comment/comment_goods_type');
        if($comment_goods_type->saveType($_POST)){
            $this->end(true,__('奖品添加成功'));
        }else{
            $this->end(false,__('奖品添加失败'));

        }
    } 

    function showAddType($typeId=null) {
        $this->path[] = array('text'=>__('评分项目'));
        $comment_goods_type = &$this->system->loadModel('comment/comment_goods_type');
        if ($typeId) {
            $this->pagedata['comment_goods_type'] = $comment_goods_type->getTypeById($typeId);
        } else {
            $this->pagedata['comment_goods_type']['orderlist'] = $comment_goods_type->getInitOrder();
        }
        $this->page('comment/comment_goods_type/showAddType.html');
    }
}
?>
