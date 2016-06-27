<?php
$menu['setting']['items'][] = 
array(
	"type" => "group",
	"label" => "商品标签设置",
	"items" => array(
		array(
			"type" => "menu", 
			"label" => "商品标签图片", 
			"link" => "index.php?ctl=qingfeng/tag&act=setTag"
		)
	)	
);

$cusmenu['member']=array(
    'items'=>array(
        array(
            'type'=>'group',
            'label'=>'商品评论',
            'position'=>'after|begin|end|before',
            'items'=>array(
                array(
                    'type'=>'menu',
                    'label'=>'评分项目设置',
                    'link'=>'index.php?ctl=comment/comment_goods_type&act=index'
                    )
                )
            )
        )
);

$cusmenu['goods'] = array(
'items'=>array(
		array(
			"type" => "group",
      'position'=>'after',
      'reference'=>'商品管理',
			"label" => "团购管理",
			"items" => array(
        array("type" => "menu", "label" => "团购列表", "link" => "index.php?ctl=tuan/tuangou_team&act=index"),
        array("type" => "menu", "label" => "团购设置", "link" => "index.php?ctl=tuan/tuangou_team&act=setting"),
				array("type" => "menu", "label" => "团购分类", "link" => "index.php?ctl=tuan/tuangou_cat&act=index"),
				array("type" => "menu", "label" => "添加团购分类", "link" => "index.php?ctl=tuan/tuangou_cat&act=addNew"),
        )
      ),
     )
);
$cusmenu['order']=array(
    'items'=>array(
        array(
            'type'=>'group',
            'label'=>'订单管理',
            'position'=>'after|begin|end|before',
            'items'=>array(
                array(
                    'type'=>'menu',
                    'label'=>'团购订单列表',
                    'link'=>'index.php?ctl=order/order&act=index_tuan',
                    )
                )
            )
        )
    );


/*author: http://www.shopextool.cn */
$cusmenu['goods'] = array(
    'items'=>array(
        array(
            "type" => "group",
            'position'=>'after',
            'reference'=>'商品管理',
            "label" => "询价管理",
            "items" => array(
                array("type" => "menu", "label" => "询价设置", "link" => "index.php?ctl=goods/product&act=qtnconfig"),
                array("type" => "menu", "label" => "询价模板", "link" => "index.php?ctl=goods/product&act=qtntemplate"),
            )
        )
     )
);