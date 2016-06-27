<?php
class cmd_finderPdt extends mdl_finderPdt
{
	function getlist( $cols, $filter = "", $start = 0, $limit = 20, $orderType = null )
	{
		$rows = shopObject::getlist( $cols, $filter, $start, $limit, $orderType );
        
		$ogoods = $this->system->loadmodel("trading/goods");
		foreach ( $rows as $k => $v )
		{
			$rows[$k]['name'] .= $v['pdt_desc'] ? ' ['.$v['pdt_desc'].']' : '';
			$products_row = $ogoods->getFieldById($v['goods_id'], array('thumbnail_pic') );
			$rows[$k]['product_img'] = $products_row['thumbnail_pic'];
		
		}
		return $rows;
	}

}

?>
