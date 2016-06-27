<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : showker6-13     */
/*  Comment : 071223 */
/*                   */
/*********************/

class cmd_comment extends mdl_comment
{
	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}
    function toDisplay($comment_id, $status){
        $this->db->exec('UPDATE sdb_comments SET display = '.$this->db->quote($status).' WHERE comment_id = '.intval($comment_id));
        $aData=$this->getFieldById(intval($comment_id),array('object_type','goods_id'));
        //insert sdb_goods points begin
        if ($aData['object_type']=='discuss')
        {
            $goods_point = &$this->system->loadModel( "comment/comment_goods_point" );
            $allpoints=$goods_point->get_single_point($aData['goods_id']);
            $sql="update sdb_goods set points=".$allpoints['avg_num'].' where goods_id='.intval($aData['goods_id']);
            $this->db->query($sql);
        }

        //insert sdb_goods points end
        return true;
    }
  function toComment( $data, $item, &$message )
				{
								$returnid=$this->toInsert( $data );
								if ( $this->system->getConf( "comment.display.".$item ) == "soon" )
								{
												$message = $this->system->getConf( "comment.submit_display_notice.".$item );
								}
								else
								{
												$message = $this->system->getConf( "comment.submit_hidden_notice.".$item );
								}
								$objGoods =& $this->system->loadModel( "trading/goods" );
								$objGoods->updateRank( $data['goods_id'], "comments_count", 1 );
								$status =& $this->system->loadModel( "system/status" );
								$status->count_gdiscuss( );
								$status->count_gask( );
								$data['member_id'] = substr( $_COOKIE['MEMBER'], 0, strpos( $_COOKIE['MEMBER'], "-" ) );
								if ( $item == "ask" )
								{
												$type = "advisory_new";
								}
								else if ( $item == "discuss" )
								{
												$type = "discuzz_new";
								}
								$this->modelName = "member/account";
								$this->fireEvent( $type, $data, $data['member_id'] );
								return $returnid;
				}

				public function toInsert( &$data )
				{
								$data['title'] = $data['title'];
								$data['comment'] = safehtml( $data['comment'] );
								$rs = $this->db->query( "SELECT * FROM sdb_comments WHERE 0=1" );
								$sql = $this->db->GetInsertSQL( $rs, $data );
								if($this->db->exec( $sql )){
									return $this->db->lastInsertId( );
								}else{
								return false;
								};
				}

}

?>
