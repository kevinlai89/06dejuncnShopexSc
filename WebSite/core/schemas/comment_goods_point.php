<?php
/**
* @table gift;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['comment_goods_point']=array (
  'columns' =>
  array (
    'point_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),    
    'goods_point' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('评分'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'comment_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('评论ID'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'type_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('评分标准ID'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'member_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('会员'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'goods_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('商品ID'),
      'width' => 30,
      'required' => true,
      'editable' => true,
    ),
    'addon' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('设置'),
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
  'comment' => '评分表',
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'idx_c_comment_id' =>
    array (
      'columns' =>
      array (
        0 => 'comment_id',
      ),
    ),
    'idx_c_type_id' =>
    array (
      'columns' =>
      array (
        0 => 'type_id',
      ),
    ),
    'idx_c_member_id' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
      ),
    ),
    'idx_c_goods_id' =>
    array (
      'columns' =>
      array (
        0 => 'goods_id',
      ),
    ),
  ),
);