<?php
/**
* @table countdown;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['list']=array (
  'columns' =>
  array (
    'countdown_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_id' =>
    array (
      'type' => 'number',
      'label' => __('所属活动分类'),
      'width' => 110,
      'editable' => true,
    ),
    'insert_time' =>
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('插入时间'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'update_time' =>
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => __('更新时间'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
	'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'countdown_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('商品抢购数量'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'countdown_price' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('抢购价格'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'orderlist' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('排序'),
      'width' => 30,
      'editable' => true,
    ),
    'shop_iffb' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => __('发布'),
      'width' => 30,
      'editable' => false,
    ),
    'limit_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('每人限购数量'),
      'width' => 110,
      'editable' => true,
    ),
    'limit_start_time' =>
    array (
      'type' => 'time',
      'label' => __('开始时间'),
      'width' => 75,
      'inputType' => 'date',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'limit_end_time' =>
    array (
      'type' => 'time',
      'label' => __('结束时间'),
      'width' => 75,
      'inputType' => 'date',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'limit_level' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('允许兑换等级'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'freez' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('冻结库存'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '抢购商品列表',
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
);