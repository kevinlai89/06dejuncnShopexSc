<?php
/**
* @table goods;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['tuangou_team']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('团购ID'),
      'width' => 110,
      'hidden' => false,
      'editable' => false,
    ),
    'goods_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('商品id'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'cat_id' => 
    array (
      'type' => 'object:tuan/tuangou_cat',
      'label' => __('团购分类'),
      'comment' => __('团购分类'),
      'width' => 70,
      'editable' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('团购名称'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
    ),
    'brief' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('团购商品简介'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
    ),
    'thumbnail_pic' =>
      array (
      'type' => 'varchar(255)',
      'label' => __('缩略图'),
      'width' => 75,
      'editable' => true,
    ),
    'small_pic' =>
      array (
      'type' => 'varchar(255)',
      'label' => __('小图'),
      'width' => 75,
      'editable' => true,
    ),
    'big_pic' =>
      array (
      'type' => 'varchar(255)',
      'label' => __('大图'),
      'width' => 75,
      'editable' => true,
    ),
    'price' =>
    array (
      'type' => 'money',
      'label' => __('团购价'),
      'width' => 75,
      'vtype' => 'positive',
      'editable' => true,
      'filtertype'=>'number',
    ),
    'mktprice' =>
    array (
      'type' => 'money',
      'label' => __('市场价'),
      'width' => 75,
      'vtype' => 'positive',
      'editable' => true,
      'filtertype'=>'number',
    ),
    'intro' =>
    array (
      'type' => 'longtext',
      'label' => __('团购详细介绍'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'per_number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('每人限购'),
      'width' => 75,
      'hidden'=>true,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),

    'min_number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('成团最低数量'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'max_number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('团购数量上限'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'now_number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('当前团购数量'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'now_users' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('当前团购人数'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'pre_number' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('虚拟购买数量'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'state' =>
    array (
      'type' => "enum('none','success','soldout','failure','refund')",
      'required' => true,
      'default' => 'none',
      'hidden'=>true,
      'label' => __('状态'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'conduser' =>
    array (
      'type' => "enum('Y','N')",
      'required' => true,
      'default' => 'Y',
      'hidden'=>true,
      'label' => __('状态'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'buyonce' =>
    array (
      'type' => "enum('Y','N')",
      'required' => true,
      'default' => 'Y',
      'hidden'=>true,
      'label' => __('只购买一次'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'team_type' =>
    array (
      'type' => "varchar(20)",
      'required' => true,
      'default' => 'normal',
      'hidden'=>true,
      'label' => __('团类型'),
      'width' => 75,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'begin_time' =>
    array (
      'type' => 'time',
      'label' => __('团购起始时间'),
      'width' => 110,
      'editable' => true,
    ),
    'expire_time' =>
    array (
      'type' => 'time',
      'label' => __('团购截止时间'),
      'width' => 110,
      'editable' => true,
    ),
    'close_time' =>
    array (
      'type' => 'time',
      'label' => __('团购结束时间'),
      'width' => 110,
      'hidden'=>true,
      'editable' => false,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'label' => __('排序'),
      'width' => 110,
      'editable' => true,
      'filtertype'=>'yes',
      'filterdefalut'=>false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '团购表',
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
