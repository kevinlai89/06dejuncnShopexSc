<?php
$db['matrix_bind'] =array(
    'columns'=>array(
      'bind_id'=>
        array(
          'type'=>'number', 
          'pkey'=>true,
          'label' => __('ID号'),
          'width' => 110,
          'required'=>true,
          'extra'=>'auto_increment',
        ),
       'shop_name' =>  
        array(
           'label' => __('对方店铺名称'),
           'width' => 110,
           'type' =>'varchar(50)',
           'default' =>'',
           'hidden'=>true,
           'filterdefalut'=>true,
         ),
      'to_node_id' =>  
        array(
            'label' => __('对方店铺节点号'),
            'width' => 110,
            'type' =>'varchar(75)',
            'default' =>'',
            'hidden'=>true,
         ),
      'to_node_type' =>  
         array (
           'label' => __('对方店铺节点类型'),
           'width' => 110,
           'type' => 'varchar(50)',
           'default' => '',
         ),
       'bind_status' =>  
         array (
           'label' => __('绑定状态'),
           'width' => 110,
           'required' => true,
		   'type' =>
		      array (
				0 => __('未绑定'),
				1 => __('已绑定'),
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
       ),
   'index'=>array(
        'ind_bind_status'=>array(
          'columns'=>array(
            0=>'bind_status',
          ),
        ),
        
     ),
);
?>