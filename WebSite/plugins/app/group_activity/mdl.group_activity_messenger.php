<?php
/**
 * @author chenping
 * @version mdl.group_activity_messenger.php 2010-4-8 10:05:11 $ 
 * @package group_activity
 * @uses mdl_messenger
 *
 */
if (!class_exists('mdl_messenger')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/system/mdl.messenger.php');
}
class mdl_group_activity_messenger   extends mdl_messenger{

	function __construct(){
		parent::mdl_messenger();
	}

	/**
     * actions
     * 所有自动消息发送列表，只要触发匹配格式的事件就会发送
     *
     * 格式：
     *            对象-事件 => array(label=>名称 , level=>紧急程度)
     *
     * 如果不存在匹配的事件，则需要手动通过send()方法发送
     *
     * @access public
     * @return void
     */
	function actions(){
		$actions=parent::actions();
		$actions['order-scheduleCreate']=array(
			'label'=>__('团购预订单创建时'),	
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;总价&nbsp;<{$total_amount}>&nbsp;&nbsp;&nbsp;&nbsp;物流公司&nbsp;<{$shipping}><br>收货人手机&nbsp;<{$ship_mobile}>&nbsp;&nbsp;&nbsp;&nbsp;收货人电话&nbsp;<{$ship_tel}>&nbsp;&nbsp;&nbsp;&nbsp;收货人地址&nbsp;<{$ship_addr}><Br>收货人Email&nbsp;<{$ship_email}>&nbsp;&nbsp;&nbsp;&nbsp;收货人邮编&nbsp;<{$ship_zip}>&nbsp;&nbsp;&nbsp;&nbsp;收货人姓名&nbsp;<{$ship_name}>'),
		);
		$actions['order-schedulePayed']=array(
			'label'=>__('团购预订单付款时'),	
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;付款时间&nbsp;<{$pay_time}>&nbsp;&nbsp;&nbsp;&nbsp;付款金额&nbsp;<{$money}>')
		);
		$actions['order-scheduleCancel']=array(
			'label'=>__('团购预订单作废时'),
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>')
		);
		$actions['order-scheduleRefund']=array(
			'label'=>__('团购预订单退款时'),
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>')
		);
		$actions['group-activitySuccess']=array(
			'label'=>__('团购活动宣布成功'),
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>'),
		);
		$actions['group-activityFailure']=array(
			'label'=>__('团购活动宣布失败'),
			'level'=>9,
			'varmap'=>__('团购预订单号&nbsp;<{$order_id}>'),
		);
		return $actions;
	}
}
?>
