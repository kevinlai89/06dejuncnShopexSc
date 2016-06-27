<?php
$db['log']=array(
	 'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'mediumint(8)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'mediumint(8)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'object:goods/products',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'number' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'createtime' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'order_id' =>
    array(
    	'type' =>'bigint(20)',
    	'editable' =>false,
    	'required' => true,
    )
  ),
  'index' => 
  array (
    'idx_goods_id' => 
    array (
      'columns' => 
      array (
        0 => 'member_id',
        1 => 'product_id',
        2 => 'goods_id',
      ),
    ),
  ),
);