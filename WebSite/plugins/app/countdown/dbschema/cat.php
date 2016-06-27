<?php
/**
* @table gift_cat;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['cat']=array (
  'columns' => 
  array (
    'cat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat' => 
    array (
      'type' => 'varchar(255)',
      'label' => __('抢购活动名称'),
      'width' => 280,
      'searchname' => true,
      'editable' => true,
    ),
	'start_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'label' => __('抢购开始时间'),
      'width' => 280,
      'searchname' => true,
      'editable' => true,
    ),
	'end_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'label' => __('抢购结束时间'),
      'width' => 280,
      'searchname' => true,
      'editable' => true,
    ),
	'intro' => 
    array (
      'type' => 'longtext',
      'label' => __('活动简介'),
      'width' => 200,
    ),
    'orderlist' => 
    array (
      'type' => 'mediumint(6) unsigned',
      'label' => __('排序'),
      'width' => 110,
      'editable' => true,
    ),
    'shop_iffb' => 
    array (
      'type' => 'intbool',
      'default' => 1,
      'label' => __('是否发布'),
      'width' => 110,
      'editable' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '抢购活动分类表',
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