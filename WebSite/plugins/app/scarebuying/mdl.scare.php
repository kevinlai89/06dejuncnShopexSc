<?php
define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
require_once(CORE_INCLUDE_DIR.'/shopObject.php');
class mdl_scare extends shopObject {
	//var $idColumn = 'id';
	//var $textColumn = 'id';
	//var $appendCols = '';
	//var $adminCtl = '';
	//var $defaultCols = 'goods_id,s_time,e_time,scare_count,scare_price,scare_mprice';
	//var $defaultOrder = array('e_time','DESC');
	var $tableName = 'sdb_scarebuying_scare_buying';

	function getFieldByGoodsId($goods_id,$aField=array('*')){
		$sql='SELECT '.implode(',',$aField).' FROM '.$this->tableName.' WHERE goods_id='.$goods_id.' AND disabled=\'false\'';
		return $this->db->selectrow($sql);
	}
    function getScareOrder($good,$scareinfo){
        $sql = "select order_id from sdb_orders where tostr like ('%".$good."%') and createtime  between ".$scareinfo['s_time']." and ".$scareinfo['e_time']." and pay_status ='1'";
        $result = $this->db->select($sql);
        foreach($result as $k=>$v){
            $order[]=$v['order_id'];
        }
        $goodstr = "('".implode("','",$order)."')";
        $goodssql = "select nums as count from sdb_order_items where order_id in".$goodstr." and name ='".$good."'";
        $count = $this->db->select($goodssql);
        foreach($count as $ck=>$cv){
            $countnum =$countnum + $cv['count'];
        }
        return $countnum;

    }
    function getScareByGoodsId($goods_id,$aField=array('*')){
		$sql='SELECT '.implode(',',$aField).' FROM '.$this->tableName.' WHERE goods_id='.$goods_id;
		return $this->db->selectrow($sql);
	}
	function delByGoodsId($goods_id){
		$sql='DELETE FROM '.$this->tableName.' WHERE goods_id='.$goods_id;
		$this->db->exec($sql);
	}
    function getList(){
        $sql='select * from '.$this->tableName.'  where "e_time" <= '.time();
        return   $this->db->select($sql);
    }
    function getListbyid($goods_id){
        $sql='select * from '.$this->tableName.'  where "e_time" <= '.time().' and goods_id='.$goods_id;
        return   $this->db->select($sql);
    }
    function getListbyidtrue($goods_id,$limit){
        if($goods_id){
         $str_goods_id=implode(',',$goods_id);
        $sql='select * from '.$this->tableName.' WHERE  goods_id  in ('.$str_goods_id.')  limit 0,'.$limit;
        }else{
         $sql='select * from '.$this->tableName.'  limit 0,'.$limit;

        }
        return   $this->db->select($sql);
    }
    //-----------------关联--------------------by zhangxuehui 2011-7-7
    function getOrderGoodsById($order_id){
        $allsql = 'select c.goods_id,i.nums from sdb_order_items as i left join sdb_products as p on i.product_id=p.product_id right join sdb_scarebuying_scare_buying as c on p.goods_id = c.goods_id where order_id = '.$order_id;
        return $this -> db -> select($allsql);
    }
    //通过订单号获取订单的状态
    function getOrderStastus($order_id,$status_type){
        $sql = 'select '.$status_type.' from sdb_orders where order_id = '.$order_id;
        $result = $this -> db -> select($sql);
        return $result[0][$status_type];
    }
    //-----------------关联--------------------关联结束
	function save($data){
		if (empty($data)) {
			return false;
		}
		$scareInfo=$this->getFieldByGoodsId($data['goods_id']);
		if ($scareInfo) {
			$id=$scareInfo['id'];
			$rs = $this->db->query('SELECT * FROM '.$this->tableName.' WHERE id="'.$scareInfo['id'].'"');
			$sql = $this->db->GetUpdateSQL($rs, $data);
			if($sql && !$this->db->exec($sql)){
				trigger_error('SQL Error:'.$sql,E_USER_NOTICE);
				return false;
			}
		}else {
			$rs = $this->db->query('SELECT * FROM '.$this->tableName);
			$sql = $this->db->GetInsertSQL($rs, $data);
			if($sql && !$this->db->exec($sql)){
				trigger_error('SQL Error:'.$sql,E_USER_NOTICE);
				return false;
			}
			$id = $this->db->lastInsertId();
		}
		return $id;
	}
	//减少库存
	function reduceCount($gid,$count){
		$sql='UPDATE '.$this->tableName.' SET scare_count=scare_count-'.$count.' WHERE goods_id='.$gid;
		$this->db->exec($sql);
	}
}
