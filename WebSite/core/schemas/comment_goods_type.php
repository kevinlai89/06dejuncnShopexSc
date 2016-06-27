<?php
/**
* @table gift;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['comment_goods_type']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('评分项目名称'),
      'searchtype' => 'has',
      'width' => 230,
      'required' => true,
      'editable' => true,
    ),
    'addon' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('设置'),
      'searchtype' => 'has',
      'width' => 230,
      'required' => true,
      'editable' => true,
    ),
    'orderlist' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('排序'),
      'width' => 30,
      'editable' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
      'hidden'=>true,
    ),
  ),
  'comment' => '评分项目表',
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