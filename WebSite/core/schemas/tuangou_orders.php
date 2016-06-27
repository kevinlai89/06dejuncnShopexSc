<?php
/**
* @table goods;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['tuangou_orders']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
    ),
    'user_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('用户id'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'team_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('团购id'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'state' =>
    array (
      'type' => "enum('pay','unpay')",
      'label' => __('支付状态'),
      'default'=>'pay',
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'quantity' =>
      array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('数量'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'create_time' =>
    array (
      'type' => 'time',
      'label' => __('订单时间'),
      'width' => 110,
      'editable' => false,
    ),   
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '团购订单表',
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled'
      ),
    ),
  ),
);
