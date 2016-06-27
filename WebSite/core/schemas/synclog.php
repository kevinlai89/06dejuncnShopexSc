<?php
$db['synclog'] =array(
    'columns'=>array(
      'queue_id'=>
        array(
          'type'=>'number', 
          'pkey'=>true,
          'label' => __('队列号'),
          'width' => 110,
          'required'=>true,
          'extra'=>'auto_increment',
        ),
       'message' =>  //路径
        array(
           'label' => __('出错信息提示'),
           'width' => 110,
           'type' =>'varchar(50)',
           'default' =>'',
           'hidden'=>true,
           'filterdefalut'=>true,
         ),
      'msg_id' =>  //msg_id号
        array(
            'label' => __('msg_id号'),
            'width' => 110,
            'type' =>'varchar(75)',
            'default' =>'',
            'hidden'=>true,
         ),
      'store_class' =>  //调用的接口名
         array (
           'label' => __('调用的接口名'),
           'width' => 110,
           'type' => 'varchar(100)',
           'default' => '',
           //'filtertype'=>'yes',
           //'filterdefalut'=>true,
         ),
       'send_num' =>  //发送次数
         array (
           'label' => __('发送次数'),
           'width' => 110,
           'required' => true,
           'type' => 'number',
           'default' =>0,
         ),   
      'mess_queue_id' =>  //消息队列ID
         array (
           'label' => __('消息队列ID'),
           'width' => 110,
           'required' => true,
           'type' => 'varchar(100)',
           'default' =>'',
         ),
      'data_filter' =>  //关键字
         array (
           'label' => __('关键字'),
           'width' => 110,
           'type' => 'longtext',
           'default' => '',
           'filtertype'=>'yes',
           'filterdefalut'=>true,
         ), 
      'data' =>  //信息存储
         array (
           'label' => __('店铺类型'),
           'width' => 110,
           'type' => 'longtext',
           'default' => '',
         ),   
      'to_node_type' =>  
         array (
           'label' => __('对方店铺节点类型'),
           'width' => 110,
           'type' => 'varchar(50)',
           'default' => '',
         ),
      'event_name' =>  //信息存储
         array (
           'label' => __('事件名称'),//订单 商品 商品分类 品牌 规格
           'width' => 110,
		   'type' =>
		      array (
				0 => __('订单'),
				1 => __('商品'),
				2 => __('商品分类'),
				3 => __('品牌'),
				4 => __('规格'),
				5 => __('规格值'),
			  ),
           'default' => '0',
		   'filtertype'=>'yes',
		   'filterdefalut'=>true,
         ),  
       'status' =>  //信息存储
         array (
           'label' => __('状态'),
           'width' => 110,
           'required' => true,
		   'type' =>
		      array (
				0 => __('运行中'),
				1 => __('成功'),
				2 => __('失败'),
			  ),
           'default' => '0',
		  'filtertype'=>'yes',
		  'filterdefalut'=>true,
         ),
         'disabled' =>
            array (
                 'type' => 'bool',
                 'default' => 'false',
                 'editable' => false,
                 'hidden'=>true,
         ),
		'acttime' =>
		array (
		  'label'=>'开始时间',
		  'type' => 'time',
		  'label' => __('开始时间'),
		  'width' => 110,
		  'editable' => false,
		),
       ),
   'index'=>array(
        'queue_id'=>array(
          'columns'=>array(
            0=>'queue_id',
          ),
        ),
        'mess_queue_id'=>array(
          'columns'=>array(
            0=>'mess_queue_id',
          ),
        ),
        
     ),
);
?>