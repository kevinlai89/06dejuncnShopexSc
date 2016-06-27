<?php
$include_dir = !constant( "SHOP_DEVELOPER" ) || version_compare( PHP_VERSION, "5.0", ">=" ) ? "include_v5" : "include";
require_once( CORE_DIR."/".$include_dir."/shopObject.php" );

class mdl_countdown extends shopObject{
		var $idColumn = "cat_id";
		var $textColumn = "cat";
		var $defaultCols = "cat_id,cat,start_time,end_time,orderlist,shop_iffb";
		var $adminCtl = "plugins/ctl_countdown";
		var $defaultOrder = array("orderlist","desc");
		var $tableName = "sdb_countdown_cat";

		function &_columns( ){
			$schema =& $this->system->loadmodel( "utility/schemas" );
			$table = substr( $this->tableName, 4 );
			if ( file_exists( dirname( __FILE__ )."/dbschema/cat.php" ) ){
				$define = require( dirname( __FILE__ )."/dbschema/cat.php" );
				$this->__table_define =& $db['cat']['columns'];
			}
			return $this->__table_define;
		}
		
		

		function getcolumns( )
		{
			$columns = array( );
			$this->__table_define = $this->_columns( );
			foreach ( $this->__table_define as $k => $v )
			{
				if ( isset( $v['label'] ) )
				{
								$columns[$k] = $v;
				}
			}
			$ret = array(
				"_cmd" => array(
								"label" => __( "操作" ),
								"width" => 70,
								"html" => dirname( __FILE__ )."/view/command.html"
				)
			);
			return array_merge( $ret, $columns );
		}
		

		//添加
		function save( $aData ){

			$goodsArray = $aData['goods_id'];
			//活动的开始时间、结束时间自动从商品中的最小值和最大值。
			$startTimes = array();
			$endTimes = array();
			$goodsIds = array();
			foreach($goodsArray as $goods_id){
				$startTimes[] = $aData['limit_start_time_'.$goods_id];
				$endTimes[] = $aData['limit_end_time_'.$goods_id];
				$goodsIds[] = $goods_id;
			}

			$aData['start_time'] = min($startTimes);
			$aData['end_time'] = max($endTimes);

			if ( $aData['cat_id'] )//表示修改
			{	
				$cat_id = $aData['cat_id'];
				$aRs = $this->db->query( "SELECT * FROM sdb_countdown_cat WHERE cat_id=".$cat_id );
				$sSql = $this->db->getupdatesql( $aRs, $aData );
				$this->db->exec( $sSql );//修改
			}else{//新增
				$aRs = $this->db->query( "SELECT * FROM sdb_countdown_cat WHERE 0" );
				$sSql = $this->db->getinsertsql( $aRs, $aData );
				$this->db->exec( $sSql );
				$cat_id = $this->db->lastinsertid();
			}
			
			if(!$cat_id){
				return false;
			}

			//添加商品明细
			//先删除明细
			//$sSql = "delete from sdb_countdown_list where cat_id = '$cat_id'";
			//$this->db->exec( $sSql );
						
			foreach($goodsArray as $goods_id){
				//$aRs = $this->db->query( "SELECT * FROM sdb_countdown_list WHERE 0" );
				//$sSql = $this->db->getinsertsql( $aRs, $aData );
				//删除价格历史
				//$sSql = "delete from sdb_countdown_hisprice where goods_id = '$goods_id'";
				//$this->db->exec( $sSql );
				//记录历史价格（没记录就记录，已有记录就不再记了）
				if($this->db->count("select count(*) from sdb_countdown_hisprice h where h.goods_id='$goods_id'")<=0){
					$this->db->exec( "insert into sdb_countdown_hisprice(product_id, goods_id, old_price) select product_id,goods_id,price from sdb_products where goods_id='$goods_id'" );
				}

				$num = $aData['countdown_num'][$goods_id];
				$price = $aData['countdown_price'][$goods_id];
				$limit_num = $aData['limit_num'][$goods_id]?$aData['limit_num'][$goods_id]:0;
				$limit_start_time = $aData['limit_start_time_'.$goods_id];
				//print_r($aData);
				$limit_end_time = $aData['limit_end_time_'.$goods_id];
				
				//有则更新，无则插入
				if($row = $this->db->selectRow("select * from sdb_countdown_list l where l.cat_id='$cat_id' and l.goods_id='$goods_id'")){
					$countdown_id = $row['countdown_id'];
					$sSql = "update sdb_countdown_list set countdown_num='$num', countdown_price='$price', limit_num='$limit_num', limit_start_time='$limit_start_time', limit_end_time='$limit_end_time' where countdown_id='$countdown_id'";
				}else{
					$sSql = "INSERT INTO sdb_countdown_list (cat_id, goods_id, countdown_num, countdown_price, limit_num, limit_start_time, limit_end_time) values ('$cat_id', '$goods_id', '$num', '$price', '$limit_num', '$limit_start_time', '$limit_end_time')";
				}
				$this->db->exec( $sSql );
			}
			//本次在存在的ID删除掉
			$this->db->exec("delete from sdb_countdown_list where cat_id = '$cat_id' and goods_id not in(".implode( ",", $goodsIds ).")");
			return $cat_id;
			
		}

		public function getCountdownById( $catId )
		{
			$sql = "SELECT c.*, if(c.start_time<unix_timestamp(now()), 0, c.start_time-unix_timestamp(now())) as starttime,if(c.end_time<unix_timestamp(now()), 0, c.end_time-unix_timestamp(now())) as lefttime FROM sdb_countdown_cat c WHERE c.cat_id=".$catId;
			return $this->db->selectRow( $sql );
		}


		//按开始时间顺序查询当前抢购商品。
		public function getCountdownGoods( $catId, $limit, $shownotstart=true, $showovertime=true)
		{	
			$memlevel = $GLOBALS['runtime']['member_lv']?$GLOBALS['runtime']['member_lv']:-1;
			if($limit){
				$limit = " limit 0, ".intval($limit);
			}

			$where = "";
			if(!$shownotstart){//不显示未开始的
				$where .= " and c.limit_start_time < unix_timestamp(now())";
			}
			if(!$showovertime){//不显示已结束的
				$where .= " and c.limit_end_time > unix_timestamp(now())";
			}

			$sSql = "SELECT c.*,if(c.limit_start_time<unix_timestamp(now()), 0, c.limit_start_time-unix_timestamp(now())) as starttime,if(c.limit_end_time<unix_timestamp(now()), 0, c.limit_end_time-unix_timestamp(now())) as lefttime FROM sdb_countdown_list c\n LEFT JOIN sdb_goods g ON c.goods_id = g.goods_id\n WHERE c.cat_id = ".intval( $catId ).$where." order by c.limit_start_time asc ".$limit;
			$list = $this->db->select( $sSql );
			$o = $this->system->loadModel('goods/products');

			$arry = array();
			foreach($list as $v){
				$filter['goods_id'] = $v['goods_id'];
				$goods = $o->getList($o->defaultCols.',small_pic,view_count,buy_count,brief,store,marketable',$filter);
				$o->getSparePrice($goods, $memlevel);
				$array[] = array_merge($v, $goods[0]);
			}
			return $array;
		}

		function getCountdownCats(){
			$sql = "SELECT c.*, if(c.end_time<unix_timestamp(now()), 0, c.end_time-unix_timestamp(now())) as lefttime FROM sdb_countdown_cat as c where c.disabled='false' and c.shop_iffb='1' order by orderlist asc";
			$list = $this->db->select( $sql );
			return $list;
		}
		
		//查询换购活动列表
		function getCountdownList(){
			$sql = "SELECT c.*, if(c.end_time<unix_timestamp(now()), 0, c.end_time-unix_timestamp(now())) as lefttime FROM sdb_countdown_cat as c where c.disabled='false' and c.shop_iffb='1' order by orderlist asc";
			$list = $this->db->select( $sql );
			
			//此抢购活动下的商品列表
			$arr = array( );
			foreach ( $list as $key => $s ){
				$arr[$key] = $s;
				$cat_id = $s['cat_id'];
				$sql = "select l.*,if(l.limit_end_time<unix_timestamp(now()), 0, l.limit_end_time-unix_timestamp(now())) as lefttime, g.* from sdb_countdown_list l left join sdb_goods g on l.goods_id = g.goods_id where l.cat_id = '$cat_id'";
				$gList = $this->db->select( $sql );
				$arr[$key]['goods'] = $gList;
			}
			return $arr;
		}
		
		//获得某抢购活动中一条信息
		function getcountdowninfobyid($countdown_id){
			$sSql = "SELECT c.*,if(c.limit_end_time<unix_timestamp(now()), 0, c.limit_end_time-unix_timestamp(now())) as lefttime FROM sdb_countdown_list c\n LEFT JOIN sdb_goods g ON c.goods_id = g.goods_id\n WHERE c.countdown_id = ".intval( $countdown_id );
			return $this->db->selectRow( $sSql );
		}

		//购物车查看Prodect信息
		function getproductbyid($cart){
			if ($cart['product_id']){
				$sSql = "SELECT c.*,if(c.limit_end_time<unix_timestamp(now()), 0, c.limit_end_time-unix_timestamp(now())) as lefttime, p.name,p.pdt_desc,p.price as mkprice,g.thumbnail_pic FROM sdb_countdown_list c\n LEFT JOIN sdb_products p ON c.goods_id = p.goods_id\n left join sdb_goods g on c.goods_id = g.goods_id WHERE c.countdown_id = ".intval($cart['countdown_id'])." and p.product_id=".intval($cart['product_id']);
			}else{
				$sSql = "SELECT c.*,if(c.limit_end_time<unix_timestamp(now()), 0, c.limit_end_time-unix_timestamp(now())) as lefttime, p.name,p.pdt_desc,p.price as mkprice,g.thumbnail_pic FROM sdb_countdown_list c\n LEFT JOIN sdb_products p ON c.goods_id = p.goods_id\n left join sdb_goods g on c.goods_id = g.goods_id WHERE c.countdown_id = ".intval($cart['countdown_id'])." and p.goods_id=".intval($cart['goods_id']);
			}
			//echo $sSql;
			//exit();
			return $this->db->selectRow( $sSql );
		}

		//检查是否可以抢购
		function check( $countdownInfo, $member, $productid = 0, $quantity = 1 )
		{
			//当前会员
			$member_id = $member['member_id'];
			
			if ( !$countdownInfo['shop_iffb'] )
			{
				return "活动已停止";
			}
			if ( time() < $countdownInfo['limit_start_time'])
			{
				return "此商品未到抢购时间，敬请期待！";
			}
			else if($countdownInfo['limit_end_time'] < time())
			{
				return "此商品的抢购时间已过！";
			}

			if($quantity > $countdownInfo['limit_num']){//本次抢购数量大于每人限购数
				return "超过限购数！";
			}
		}
		
		//当前商品是否在有效的抢购活动中（可抢数量大于0），有则返回一条（最近的）。
		//用于更新价格和下单。
		function isCountdown($goodsId){
			$now = time();
			$sql = "select l.*, if(l.limit_end_time<unix_timestamp(now()), 0, l.limit_end_time-unix_timestamp(now())) as lefttime from sdb_countdown_list l left join sdb_countdown_cat c on c.cat_id = l.cat_id where l.goods_id='$goodsId' and $now > l.limit_start_time and $now < l.limit_end_time and l.countdown_num-l.freez>0 and c.disabled='false' order by countdown_id desc";
			//商品是否含在抢购活动中
			$row = $this->db->selectRow($sql);
			return $row;
		}
		
		//检查是否更新商品价格
		function checkPrice($goodsId){
			/*
			$now = time();
			$sql = "select l.*, if(l.limit_end_time<unix_timestamp(now()), 0, l.limit_end_time-unix_timestamp(now())) as lefttime from sdb_countdown_list l left join sdb_countdown_cat c on c.cat_id = l.cat_id where l.goods_id='$goodsId' and $now > l.limit_start_time and $now < l.limit_end_time and c.disabled='false' order by countdown_id desc";
			*/
			//商品是否含在抢购活动中
			$row = $this->isCountdown($goodsId);
			if($row){
				$countdown_price = $row['countdown_price'];
				//更新商品价格
				$this->db->exec("UPDATE sdb_goods SET price='$countdown_price' WHERE goods_id='$goodsId'");
				//更新规格价
				$this->db->exec("UPDATE sdb_products SET price='$countdown_price' WHERE goods_id='$goodsId'");
				return $row;
			}else{//恢复原价
				if($this->db->count("select count(*) from sdb_countdown_list l where l.goods_id='$goodsId'")>0){
					//在活动中，但过期了，要恢复价格
					$his = $this->db->select("select * from sdb_countdown_hisprice where goods_id='$goodsId'");
					$price = array();
					foreach($his as $v){
						$productId = $v['product_id'];
						$pPrice = $v['old_price'];
						$price[]=$pPrice;
						$this->db->exec("UPDATE sdb_products SET price='$pPrice' WHERE goods_id='$goodsId' and product_id='$productId'");
					}
					$gPrice = min($price);//取最小价格
					$this->db->exec("UPDATE sdb_goods SET price='$gPrice' WHERE goods_id='$goodsId'");
				}
				return false;
			}
		}

		function checkOrderInfo($order_data){
			$orderId = $order_data['order_id'];
			foreach($order_data['products'] as $product){//订单商品
				$goodsId = $product['goods_id'];
				$productId = $product['product_id'];
				$nums = $product['nums'];//数量
				$price = $product['price'];//抢购时的单价
				//商品是否含在抢购活动中
				$row = $this->isCountdown($product['goods_id']);
				if($row){//是抢购商品
					$countdown_id = $row['countdown_id'];
					$this->db->exec("insert into sdb_countdown_log(order_id,countdown_id,product_id,goods_id,nums, price) values ('$orderId', '$countdown_id', '$productId', '$goodsId', '$nums', '$price')");

					//更新抢购活动列表中的记录数量
					$this->db->exec("UPDATE sdb_countdown_list SET freez=freez+$nums WHERE countdown_id='$countdown_id'");
				}
			}
		}
}

?>
