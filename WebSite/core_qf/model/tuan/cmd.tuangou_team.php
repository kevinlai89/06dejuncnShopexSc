<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class cmd_tuangou_team extends shopObject
{

				public $idColumn = "id";
				public $textColumn = "name";
				public $defaultCols = "name,goods_id,price,mktprice,per_number,min_number,max_number,now_number,now_users,pre_number,expire_time,begin_time,state,conduser,buyonce,brief";
				public $appendCols = "thumbnail_pic,small_pic,big_pic";
				public $adminCtl = "tuan/tuangou_team";
				public $defaultOrder = array
				(
								0 => "id",
								1 => " DESC"
				);
				public $tableName = "sdb_tuangou_team";
				public $typeName = "tuangou_team";
				public $globalTmp = NULL;
				public $name = "团购记录";
				
	function skv(){
			if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}}
			preg_match('/[\w][\w-]*\.(?:com\.cn|co\.nz|co\.uk|com|cn|co|net|org|gov|cc|biz|info|hk)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$dms=@file_get_contents(CUSTOM_CORE_DIR.'/core/s.cer');
			$ardms=explode('/',$dms);
			$nroot=rtrim($domain[0], '/');
			$nrootmd=md5(base64_encode(substr(md5($nroot.'apchwinwy%%qf2013'),8,16)));
			if(strpos($_SERVER['HTTP_HOST'],'localhost')===false && strpos($_SERVER['HTTP_HOST'],'127.0.0.1')===false && strpos($_SERVER['HTTP_HOST'],'192.168.1.')===false){
				if(!in_array(${'nrootmd'},${'ardms'})){
						exit(0);
				}
			}
	} 
  function savetuanpic($aData){
      $storager =&$this->system->loadModel('system/storager');           
      if($_FILES['tuan_big_pic']){
        return $storager->save_upload($_FILES['tuan_big_pic'],'tuan');
      }
  }
  function cmd_tuangou_team(){
			$this->skv();
      parent::shopObject();
  }  
  function getColumns(){
        $ret = parent::getColumns();
        $ret['_cmd'] = array('label'=>__('操作'),'width'=>100,'html'=>'tuan/finder_command.html');
        return $ret;
    }  
	function getTuanTuijian($ntime,$orderBy='id desc')  {
        $sqltj="select distinct t.* from sdb_tuangou_team t left join sdb_goods g on t.goods_id=g.goods_id left join sdb_tag_rel  a on t.goods_id=a.rel_id left join sdb_tags b on a.tag_id=b.tag_id where (b.tag_name='团购推荐') and t.expire_time>".$ntime." and g.marketable!='false' and t.disabled='false'  {$orderBy}"; //except above product      
       return $this->db->selectRow($sqltj);
	}
		function getTuanAll($cat_id,$ntime,$limit=-1,$orderBy='id desc'){
		if($cat_id)
		{
      $where=" and t.cat_id=".intval($cat_id);
		}
    $sqltuanall="select distinct t.* from sdb_tuangou_team t left join sdb_goods g on t.goods_id=g.goods_id where t.expire_time>".$ntime."  and  g.marketable!='false' and t.disabled='false' {$where} {$orderBy}  {$limit}";
   //print_r($sqltuanall);exit;
     return   $this->db->select($sqltuanall);
   }
   function getTuanAllExceptTj($cat_id,$ntime,$tj_id,$limit=-1,$orderBy='id desc'){
    if($cat_id)
		{
      $where=" and t.cat_id=".intval($cat_id);
		}
 $sqltuanall="select distinct t.* from sdb_tuangou_team t left join sdb_goods g on t.goods_id=g.goods_id where  t.expire_time>".$ntime." and g.marketable!='false' and t.disabled='false' and t.id!='".intval($tj_id)."' {$where}   {$orderBy}  {$limit}";

      return $this->db->select($sqltuanall);  
   }
	function getTunByGoodsId($goods_id){
    $ntime=time();
    $sql="select * from sdb_tuangou_team where goods_id=".intval($goods_id)." and expire_time>=".$ntime."  and disabled!='true' order by id desc";////not guo qi
    return $this->db->selectRow($sql);
	}
   function getTuanInfoById($tid,$ntime){
    $sql="select * from sdb_tuangou_team where id=".intval($tid)." and disabled!='true' ";//and expire_time>=".$ntime;//not guo qi
    return $this->db->selectRow($sql);
   }
   function getTuanNowPage($cat_id,$minprice,$maxprice,$orderBy,$istoday,$ntime,$nPage,$PERPAGE)
   {
		if($cat_id)
		{
      $where.=" and t.cat_id=".intval($cat_id);
		}
		if($minprice){
     $where.=" and t.price>=".$minprice; 
		}
		if($maxprice){
     $where.=" and t.price<=".$maxprice; 
		}		
		if($istoday){

      $today_begin_date=date('Y-m-d');
       $today_end_date=date('Y-m-d').' 23:59:59';     
       
      $where.=" and DATE_FORMAT(FROM_UNIXTIME(t.begin_time),'%Y-%m-%d')='".$today_begin_date."'";
      //print_r($where);exit;
		}
		if(!$orderBy){
      $orderBy='order by t.id desc';
		}
   		$sqltuan="select t.* from sdb_tuangou_team t left join sdb_goods g on t.goods_id=g.goods_id where  t.disabled='false' and (t.expire_time>=".$ntime.")".$where." $orderBy";
   //	print_r($sqltuan);exit;
      return $this->db->selectPager($sqltuan,$nPage-1,$PERPAGE);//rows 的结构$_data['total']，$_data['page']，	$_data['data']
   }
   function getTuanHistoryPage($ntime,$nPage,$PERPAGE)
   {
      $sqltuan="select t.* from sdb_tuangou_team t where t.expire_time<".$ntime;
      return $this->db->selectPager($sqltuan,$nPage-1,$PERPAGE);//rows 的结构$_data['total']，$_data['page']，	$_data['data
   }   
   function getTuanNotBeginPage($ntime,$nPage,$PERPAGE)
   {
      $sqltuan="select t.* from sdb_tuangou_team t where t.begin_time>".$ntime." and  t.expire_time>".$ntime;
      return $this->db->selectPager($sqltuan,$nPage-1,$PERPAGE);//rows 的结构$_data['total']，$_data['page']，	$_data['data
   }
   
   function getAllCatNums($ntime){
       $sqlcat="select count(*) as ct,bc.cat_id,bc.cat_name from sdb_tuangou_team t left join sdb_goods g on t.goods_id=g.goods_id left join sdb_tuangou_cat bc on t.cat_id=bc.cat_id where  t.disabled='false' and g.marketable!='false' and t.expire_time>".$ntime."  group by bc.cat_id order by ct desc,bc.p_order";
       return $this->db->select($sqlcat);
    }     
}
?>
