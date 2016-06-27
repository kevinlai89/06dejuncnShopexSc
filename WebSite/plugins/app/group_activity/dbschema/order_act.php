<?php
/**
 * //======================
 * extension_code
 * 分为三种情况
 * 1、group_buy：表示为团购预订单
 * 2、succ：表示为已经生成正式订单
 * 3、fail：表示为生成正式订单失败
 * //======================
 */
$db['order_act']=array(
 'columns' =>
  array (
    'id' =>
    array (
      'type' => 'mediumint(8)',
      'required' => true,
      'pkey' => true,
      'extra'=>'auto_increment',
    ),
    'order_id' =>
    array (
      'type' => 'object:trading/order',
      'label' => __('订单号'),
      'width' => 110,
    ),
    'act_id' =>
    array (
      'type' => 'mediumint(8)',
      'default' => '0',
      'label' => __('团购ID'),
      'width' => 75,
    ), 
    'extension_code' =>
    array(
    	'type' => 'varchar(30)',
    	'default'=>'succ',
    	'editable' => false,
    ),
    'group_total_amount'=>array(
    	'type' =>'money',
    	'default'=>'0',
		'required' => true,
		'filtertype'=>'number',
    ),
    'last_change_time'=>array(
      'type' => 'int(11)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'index' =>
  array(
  	'ind_order'=>
  	array(
  		 'columns' =>
  		 array(
  		 	0=>'order_id'
  		 )
  	),
  	'ind_act'=>
  	array(
  		'columns' =>
  		array(
  			0=>'act_id'
  		)
  	)
  ),
);