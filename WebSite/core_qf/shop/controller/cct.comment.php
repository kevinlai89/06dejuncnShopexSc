<?php
class cct_comment extends ctl_comment{
	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}
    //发表评论
    function toComment($goodsid, $item){
        if ($this->system->getConf('comment.verifyCode.'.$item)=="on"){
            if (md5($_POST[$item.'verifyCode'])<>$_COOKIE[strtoupper($item)."_RANDOM_CODE"]){
                if ($item=="ask")
                    $stp=__("咨询");
                elseif($item=="discuss")
                    $stp=__("评论");
                $this->splash('failed','back',$stp.__('验证码录入错误，请重新输入'));
            }
        }
        $objComment = &$this->system->loadModel('comment/comment');
        if(!$objComment->toValidate($item, $goodsid, $this->member, $message)){
            $this->splash('failed', 'back',  $message);
        }else{
            $aData['title'] = $_POST['title'];
            $aData['comment'] = $_POST['comment'];
            $aData['goods_id'] = $goodsid;
            $aData['object_type'] = $item;
            $aData['author_id'] = $this->member['member_id'];
            $aData['author'] = ($this->member['member_id'] ? $this->member['uname'] : __('非会员顾客'));
            $oLv = &$this->system->loadModel('member/level');
            $aLevel = $oLv->getFieldById($GLOBALS['runtime']['member_lv'], array('name'));
            $aData['levelname'] = $aLevel['name'];
            $aData['contact'] = ($_POST['contact']=='' ? $this->member['email'] : $_POST['contact']);
            $aData['time'] = time();
            $aData['lastreply'] = 0;
            $aData['ip'] = remote_addr();
            $aData['display'] = ($this->system->getConf('comment.display.'.$item)=='soon' ? 'true' : 'false');
            
            $returnid=$objComment->toComment($aData, $item, $message);
            /*add by showker 6-13 to add  goods_point if have*/
            $aData['goods_point'] = $_POST['point_type'];
           if ($aData['object_type']=='discuss' && $aData['goods_point'] )
            {
                    $goods_point = &$this->system->loadModel( "comment/comment_goods_point" );
                    $_pointsdf['comment_id'] =  $returnid;
                    foreach ( $aData['goods_point'] as $key => $val )
                    {
                            $_pointsdf['goods_id'] = $aData['goods_id'];
                            $_pointsdf['goods_point'] = ( double )$val['point'];
                            if ( !( $_pointsdf['goods_point'] <= 5 ) )
                            {
                                    $_pointsdf['goods_point'] = 5;
                            }
                            $_pointsdf['member_id'] = $this->member['member_id'];
                            $_pointsdf['type_id'] = $key;
                            $goods_point->save( $_pointsdf );
                            unset( $_pointsdf['point_id'] );
                    }
                    //insert sdb_goods points begin
                    $allpoints=$goods_point->get_single_point($aData['goods_id']);
                    $sql="update sdb_goods set points=".$allpoints['avg_num'].' where goods_id='.intval($aData['goods_id']);
                    $goods_point->db->query($sql);
                    //insert sdb_goods points end
            }
            /*add by showker 6-13/*/
            $this->splash('success', $this->system->mkUrl('product','index', array($goodsid)), $message);
        }
    }
    function commentlist($goodsid, $item, $nPage=1){
        $comment_goods_type = $this->system->loadModel('comment/comment_goods_type');//add by showker 6-16
        $objPoint = $this->system->loadModel('comment/comment_goods_point');//add by showker 6-16
        $objGoods = &$this->system->loadModel('trading/goods');
        $this->pagedata['goods'] = $objGoods->getFieldById($goodsid);
        $this->title = $this->pagedata['goods']['name'];

        $this->pagedata['goods']['setting']['mktprice'] = $this->system->getConf('site.market_price');
        $this->pagedata['goods']['setting']['saveprice'] = $this->system->getConf('site.save_price');
        $this->pagedata['goods']['setting']['buytarget'] = $this->system->getConf('site.buy.target');

        $switchStatus = $this->system->getConf('comment.switch.'.$item);
        if($switchStatus == 'on'){
            $objComment= &$this->system->loadModel('comment/comment');
            $aComment = $objComment->getGoodsCommentList($goodsid, $item, $nPage);
            $aId = array();
            foreach($aComment['data'] as $rows){
                $aId[] = $rows['comment_id'];
            }
            if(count($aId)) $aReply = $objComment->getCommentsReply($aId, true);
            reset($aComment['data']);
            foreach($aComment['data'] as $key => $rows){
                foreach($aReply as $rkey => $rrows){
                    if($rows['comment_id'] == $rrows['for_comment_id']){
                        $aComment['data'][$key]['items'][] = $aReply[$rkey];
                    }
                }
                reset($aReply);
              /*add comment.list.discuss.goods_point by showker 6-15*/
              $commentpointone=$objPoint->get_comment_point_total($rows['comment_id']);
              $commentpointone_num=$objPoint->get_comment_point($rows['comment_id']);
              $aComment['data'][$key]['goods_point']=$commentpointone?$commentpointone:"5";//if no star,set 5 stars
              $tmpTotalPoint+=$commentpointone_num;
               $commentRows+=1;
              /*/add comment.list.discuss.goods_point by showker 6-15*/
            }
        }else{
            $this->system->error(404);
            exit;
        }

        switch($item){
            case 'ask':
            $this->pagedata['askshow'] = $this->system->getConf('comment.verifyCode.ask');
            $this->path[]=array('title'=>__('商品咨询'));
            $pagetitle = __('商品咨询');
            break;
            case 'discuss':

            $this->pagedata['discussshow'] = $this->system->getConf('comment.verifyCode.discuss');
            $this->path[]=array('title'=>__('商品评论'));
            $pagetitle = __('商品评论');
            break;
            case 'buy':
            $this->path[]=array('title'=>__('商品经验'));
            $pagetitle = __('商品经验');
            break;
        }

        $comment['data']['member_lv']=$GLOBALS['runtime']['member_lv'];
        $this->pagedata['ask'] = $comment['data'];
        $this->pagedata['commentData'] = $aComment['data'];
        if($item=='ask'){
            $this->pagedata['comment_all'] = $this->system->getConf('comment.power.ask');
        }else{
            $this->pagedata['comment_all'] = $this->system->getConf('comment.power.discuss');

        }
        /*add by showker 6-13*/
        $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
        $this->pagedata['point_status'] =  'on';
        $this->pagedata['goods_point'] = $objPoint->get_single_point($goodsid);
        $this->pagedata['_all_point'] = $objPoint->get_goods_point($goodsid);
                $this->pagedata['point_peoples']=$objPoint->point_peoples($goodsid);
        /*/add by showker 6-13*/       
        $this->pagedata['comment']['total'] = $aComment['total'];
        $this->pagedata['comment']['pagetitle'] = $pagetitle;
        $this->pagedata['comment']['item'] = $item;
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $aComment['page'],
                'link'=> $this->system->mkUrl('comment','commentlist', array($goodsid,$item,($tmp = time()))),
                'token'=> $tmp);

        $this->output();
    }

}
?>