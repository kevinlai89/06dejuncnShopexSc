<?php
/**
* @table spike_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['log']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => '0',
      'pkey' => true,
      'editable' => false,
    ),
    'countdown_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'pkey' => true,
      'editable' => false,
    ),
	'goods_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
	'product_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
    'nums' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
   'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('订单创建时的抢购价格'),
      'vtype' => 'bbsales',
      'editable' => false,
    ),
	
  ),
  'comment' => '抢购订单明细表',
);