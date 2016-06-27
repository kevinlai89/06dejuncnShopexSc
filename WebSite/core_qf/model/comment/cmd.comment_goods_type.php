<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
include_once( "shopObject.php" );
class cmd_comment_goods_type extends shopObject
{
				public $idColumn = "type_id";
				public $textColumn = "name";
				public $defaultCols = "type_id ,name ,orderlist";
				public $adminCtl = "goods/discuss";
				public $defaultOrder = array
				(
								0 => "orderlist",
								1 => "desc"
				);
				public $tableName = "sdb_comment_goods_type";
	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}
				public function getColumns( )
				{
								$ret = array(
												"_cmd" => array(
																"label" => __( "操作" ),
																"width" => 70,
																"html" => "comment/comment_goods_type/command.html"
												)
								);
								return array_merge( $ret, parent::getcolumns( ) );
				}
				
      public function GetByTypeId($typeid){				
        $sSql="select * from sdb_comment_goods_type where type_id=".intval($typeid);
        return $this->db->selectRow($sSql);
      }

      public function insert($aData ){				
      $aRs = $this->db->query( "SELECT * FROM sdb_comment_goods_type WHERE 0=1" );
      $sSql = $this->db->getinsertsql( $aRs, $aData );
      if ( $this->db->exec( $sSql ) )
        {
          return true;
				}
				else
				{
          return false;
				}
      }
      
        public function getInitOrder( )
				{
								$aTemp = $this->db->selectRow( "select max(orderlist) as orderlist from sdb_comment_goods_type" );
								return $aTemp['orderlist'] + 1;
				}
        
        public function saveType( $aData )
				{
								if ( $aData['type_id'] )
								{
												$aRs = $this->db->query( "SELECT * FROM sdb_comment_goods_type WHERE type_id=".$aData['type_id'] );
												$sSql = $this->db->getUpdateSql( $aRs, $aData );
												return !$sSql || $this->db->exec( $sSql );
								}
								else
								{
												$aRs = $this->db->query( "SELECT * FROM sdb_comment_goods_type WHERE 0" );
												$sSql = $this->db->getInsertSql( $aRs, $aData );
												if ( $this->db->exec( $sSql ) )
												{
																$type_id = $this->db->lastInsertId( );
																return $type_id;
												}
												else
												{
																return false;
												}
								}
				}
        public function getTypeById( $nType )
				{
								$sSql = "SELECT g.* FROM sdb_comment_goods_type as g WHERE g.Type_id=".intval( $nType );
								if ( $aTemp = $this->db->selectRow( $sSql ) )
								{
												return $aTemp;
								}
								else
								{
												return false;
								}
				}


}

?>
