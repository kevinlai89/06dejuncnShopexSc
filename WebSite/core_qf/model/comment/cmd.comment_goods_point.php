<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
include_once( "shopObject.php" );
class cmd_comment_goods_point extends shopObject
{
				public $idColumn = "point_id";
				public $textColumn = "point_id";
				public $defaultCols = "point_id,goods_point ,comment_id ,type_id ,member_id ,goods_id ,addon ,disabled";
				public $adminCtl = "goods/discuss";
				public $defaultOrder = array
				(
								0 => "point_id",
								1 => "desc"
				);
				public $tableName = "sdb_comment_goods_point";
	function skdis(){
		if($ducedis=${'ducemb'}){if(($ducedis=${'ducedis'})<($ducemb=1.0)){exit(0);}}
	}
				public function getColumns( )
				{
								$ret = array(
												"_cmd" => array(
																"label" => __( "²Ù×÷" ),
																"width" => 70,
																"html" => "sale/gift/command.html"
												)
								);
								return array_merge( $ret, parent::getcolumns( ) );
				}
				public function getList( $cols = "*", $filter = array( ), $offset = 0, $limit = -1, $orderType = null )
				{
								$row = parent::getlist( $cols, $filter, $offset, $limit, $orderType );

								        $objComment = &$this->system->loadModel('comment/comment');
								foreach ( $row as $key => $val )
								{       
								//add by showker -passed comment to show-6-17
                      $commentinfo=$objComment->getFieldById($val['comment_id'],array('display'));
                        if($commentinfo['display']=='true')
                        {
                            $val['type_name'] = $this->get_type_name( $val['type_id'] );
                            $aData[] = $val;
												}
												
								}
								return $aData;
				}

				public function get_type_name( $type_id )
				{
								$comment_goods_type = $this->system->loadModel('comment/comment_goods_type');
								$sdf = $comment_goods_type->GetByTypeId( $type_id );
								return $sdf['name'];
				}

				public function get_goods_point( $goods_id = null )
				{
								if ( !$goods_id )
								{
												return null;
								}
								$objType = $this->system->loadModel('comment/comment_goods_type');
								$row = $objType->getList( "*" );
								foreach ( ( array )$row as $val )
								{
												$data = $this->get_type_point( $val['type_id'], $goods_id );
												$data['type_name'] = $val['name'];
												$aData[] = $data;
								}
											//print_r($aData);exit;
								return $aData;
				}

				public function get_type_point( $type_id, $goods_id )
				{
								$row = $this->getList( "goods_point,comment_id", array(
												"goods_id" => $goods_id,
												"type_id" => $type_id
								) );
								//if is before discuzz mouren =5
								/**/
                $dbs = &$this->system->database();
               $sql = "SELECT comment_id FROM sdb_comments WHERE goods_id = ".intval( $goods_id )."  AND for_comment_id is null and  object_type = 'discuss' and disabled='false' AND display = 'true'";
               $allcomment=$dbs->select($sql);
               $tmpcommentids=array();
               $tmphasscoreids=array();//have scores comments
               foreach($allcomment as $k=>$v){
                $tmpcommentids[]=$v['comment_id'];
                }
								/**/
								$num = 0;				
								foreach ( ( array )$row as $val )
								{  
												$num += $val['goods_point'];
												$tmphasscoreids[]=$val['comment_id'];//add by showker6-17 
								}
				
								$arrnothavescore=array_diff($tmpcommentids,$tmphasscoreids);
								
                if(is_array($arrnothavescore) && count($arrnothavescore)>0){
                  $num+=count($arrnothavescore)*5;//have how add how many *5 by showker-6-17
                }
								if ( $num == 0 )
								{
												//$data['avg'] = 0;
												$data['avg']=5;//by showker 6-17
								}
								else
								{
												$data['avg'] = number_format( ( double )$num / count( $allcomment ), 1 );
								}
								$data['total'] = $num;
								//print_r($data);exit;
								return $data;
				}

				public function totalType( )
				{
								$objType = $this->system->loadModel('comment/comment_goods_type');
								$row = $objType->getList( "*" );
								$type_id=array();
								//$type_id[]= 1;
								foreach ( ( array )$row as $val )
								{
												$addon = unserialize( $val['addon'] );
												if ( $addon['is_total_point'] == "on" )
												{
																$type_id[]= $val['type_id'];
												}
								}
								return $type_id;
				}

				public function get_comment_point( $comment_id = null )
				{
								if ( !$comment_id )
								{
												return null;
								}
								$type_id = $this->totalType( );

								$row = $this->getList( "goods_point,comment_id,type_id", array(
												"comment_id" => $comment_id,
												"type_id" => $type_id
								) );

               $tmphasscorecommentypeids=array();//have scores comments type

                $num = 0;
								foreach ( ( array )$row as $val )
								{  
												$num += $val['goods_point'];
												$tmphasscorecommentypeids[]=$val['type_id'];
								}						
								$arrnothavescore=array_diff($type_id ,$tmphasscorecommentypeids);
                if(is_array($arrnothavescore) && count($arrnothavescore)>0){
                  $num+=count($arrnothavescore)*5;//have how add how many *5 by showker-6-17
                }
								$retval=5;
                if ( $num == 0 )
								{
												$retval= "5";
								}
								else
								{
												$retval= number_format( ( double )$num / count( $type_id ), 1 );
								}
								//print_r($retval);exit;
								return  $retval;

				}
      
      public function get_comment_point_total( $comment_id = null )
				{
								if ( !$comment_id )
								{
												return null;
								}
								$type_id = $this->totalType( );

								$row = $this->getList( "goods_point,comment_id,type_id", array(
												"comment_id" => $comment_id,
												"type_id" => $type_id
								) );

               $tmphasscorecommentypeids=array();//have scores comments type

                $num = 0;
								foreach ( ( array )$row as $val )
								{  
												$num += $val['goods_point'];
												$tmphasscorecommentypeids[]=$val['type_id'];
								}						
								$arrnothavescore=array_diff($type_id ,$tmphasscorecommentypeids);
                if(is_array($arrnothavescore) && count($arrnothavescore)>0){
                  $num+=count($arrnothavescore)*5;//have how add how many *5 by showker-6-17
                }
								$retval=5;
                if ( $num == 0 )
								{
												$retval= "5";
								}
								else
								{
												$retval= $this->star_class( number_format( ( double )$num / count( $type_id ), 1 ) );
								}
								//print_r($retval);exit;
								return  $retval;

				}

				public function get_single_point( $goods_id = null )
				{
								if ( !$goods_id )
								{
												return null;
								}
								$type_id = $this->totalType( );
								$_singlepointnum=0;
								foreach($type_id as $k=>$v){
                  $_singlepoint = $this->get_type_point( $v, $goods_id );
                  $_singlepointnum+=$_singlepoint ['avg'];
								}
								$_singlepoint['avg_num'] =number_format( ( double )$_singlepointnum / count( $type_id ), 1 );
								if ( $_singlepoint <=0)
								{
												$_singlepoint['avg'] ="5";
								}
								else
								{
												$_singlepoint['avg'] = $this->star_class( $_singlepoint['avg_num'] );
												
								}
								return $_singlepoint;
				}

				public function star_class( $avg )
				{
								$a = $avg;
								$t = round( $avg );
								if ( $a == $t )
								{
												$r = floor( $a );
								}
								else
								{
												switch ( $a < $t )
												{
												case true :
																do
																{
																				if ( !( $t - $a != 0.5 ) )
																				{
																								break;
																				}
																				else
																				{
																								$r = $t;
																								break;
																				}
																} while ( 0 );
												case false :
																$r = (floor( $a )+1)."_";
												}
								}
								return $r;
				}
				//save points by showker 6-1
				public function save($aData){
             $aRs = $this->db->query( "SELECT * FROM sdb_comment_goods_point WHERE 0" );
            $sSql = $this->db->getInsertSql( $aRs, $aData );
            if ( $this->db->exec( $sSql ) )
            {
              return true;
            }
            else
            {
              return false;
            }
				}
				//points people
				public function point_peoples($goods_id){
              $dbs = &$this->system->database();
              $point_have=array();
              $point_all=array('1','2','3','4','5');
              $point_return=array();
              $point_return_real=array();
              $point_peoples=0;
               $sql = "select count(*) as ct,goods_point from sdb_comment_goods_point p left join sdb_comments c on p.comment_id=c.comment_id where p.goods_id=".intval( $goods_id )." AND c.for_comment_id is null and  c.object_type = 'discuss' and c.disabled='false' AND c.display = 'true'  group by p.goods_point  order by p.goods_point";
               $allcomment=$dbs->select($sql);
               //print_r($allcomment);exit;
               foreach($allcomment as $k=>$v){
                  //  $v['goods_point']=($v['goods_point']<=0?5:$v['goods_point']);//add by shwoker 7-15
                   $point_return[$v['goods_point']]=$v['ct'];
                   $point_peoples+=$v['ct'];
                   $point_have[]=$v['goods_point'];
               }
              
               $point_not=array_diff($point_all,$point_have); 
               
              foreach($point_not as $v){
                $point_return[$v]=0;
                //$point_return['5']+=1;
               }
               /*sk*/
              $sqlTwo = "SELECT comment_id FROM sdb_comments WHERE goods_id = ".intval( $goods_id )."  AND object_type = 'discuss' and disabled='false' AND display = 'true' AND for_comment_id is null";
               $allcommentTwo=$dbs->select($sqlTwo);
								/**/
              if($point_peoples<count($allcommentTwo))
              {
                $point_return['5']+=count($allcommentTwo)-$point_peoples;
              }
              

              // 
               krsort($point_return);
               //
               $arrSum=array_sum($point_return);
               foreach($point_return as $k=>$v){
                $point_return_real[$k]['peps']=$v;
                $point_return_real[$k]['percent']=floor(($v/$arrSum)*100);
               }
              // print_r($point_return_real);exit;
               return $point_return_real;
          
				}

}

?>
