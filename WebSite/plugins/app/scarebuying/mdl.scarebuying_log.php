<?php
/**
 * @author shopex
 * @package scarebuying
 * @uses shopObject
 * ++++++++++++++++++++++++++
 * 记录用户购买的限时抢购商品
 *
 */
 define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
require_once(CORE_INCLUDE_DIR.'/shopObject.php');
class mdl_scarebuying_log extends shopObject {
	var $tableName = 'sdb_scarebuying_log';

	function save($data){
		$rs = $this->db->query('SELECT * FROM '.$this->tableName.' WHERE 0=1');
		$sql = $this->db->GetInsertSQL($rs, $data);

		if($sql && !$this->db->exec($sql)){
			trigger_error('SQL Error:'.$sql,E_USER_NOTICE);
			return false;
		}
		return  $this->db->lastInsertId();
	}
	
	//得到时间范围段购买的限时商品数
	function getGoodsNumByTime($data){
		$sql = 'SELECT SUM(number) as number FROM '.$this->tableName.' WHERE member_id=\''.$data['member_id'].'\'  AND goods_id=\''.$data['goods_id'].'\'  AND  createtime BETWEEN \''.$data['start_time'].'\' AND \''.$data['end_time'].'\'';
		return $this->db->selectrow($sql);
	}
	
	function deleteByOrderId($order_id){
		$sql = 'DELETE FROM '.$this->tableName.' WHERE order_id=\''.$order_id.'\'';
		$this->db->exec($sql);
	}
}