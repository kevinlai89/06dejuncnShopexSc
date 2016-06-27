<?php
/**
* @table spike_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['hisprice']=array (
  'columns' => 
  array (
    'product_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
	'goods_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
	'old_price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('产品原价格'),
      'vtype' => 'bbsales',
      'editable' => false,
    ),
  ),
  'comment' => '商品原价表，用于自动更改价格用于抢购',
);