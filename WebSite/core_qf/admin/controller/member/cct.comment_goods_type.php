<?php
include_once('objectPage.php');
class cct_comment_goods_type extends objectPage{

    var $object = 'goods/tuangou_team';
    var $finder_action_tpl = 'goods/tuangou_team/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = 'goods/tuangou_team/finder_filter.html'; //默认的过滤器html,可以为null
    var $finder_default_cols = 'goods_id,name,price,mktprice,min_number,max_number,now_number,now_users,pre_number,begin_time,expire_time';//default columns
    var $workground = 'goods';
    var $filterUnable = true;
/*     function delete(){
      if($this->system->op_is_super==0){
          //$this->deleteAble=false;//huishouchan button
         // $this->disabledMark = "delete";
					echo __( "没有权限!" );exit;
      }        
      parent::delete();
    }*/

	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}

}
?>
