<?php

class cmd_brand extends mdl_brand
{
	public $defaultCols = "brand_name,brand_url,brand_logo,ordernum,brand_keywords";

	/*根据商品分类加载关联的品牌*/
	function getBrandsByCatId($catid){
		$oCat = $this->system->loadModel( "goods/productCat" );
		$cat = $oCat->getFieldById($catid, array('type_id'));
		$typeid = $cat['type_id'];
		return $this->getTypeBrands($typeid);
	}

	/*查询没有归类的品牌（没有被商品类型使用的）*/
	function getBrandsNotInType($catid){
		return $this->db->select( "select * from sdb_brand where brand_id not in(select distinct(brand_id) from sdb_type_brand)" );
	}
	
	/*根据ID加载品牌列表*/
	function getBrandsById( $id, $aField = array("*")){
		if ( is_array( $id ) )
		{
			$sqlString = "SELECT ".implode( ",", $aField )." FROM sdb_brand WHERE brand_id in (".implode( $id, " , " ).")";
			return $this->db->select( $sqlString );
		}
		else
		{
			$sqlString = "SELECT ".implode( ",", $aField )." FROM sdb_brand WHERE brand_id = ".intval( $id );
			return $this->db->selectrow( $sqlString );
		}
	}
	

	public function brand2json( $return = false )
	{
		@set_time_limit( 600 );
		$file = MEDIA_DIR."/brand_list.data";
		$contents = $this->db->select( "SELECT brand_id,brand_name,brand_url,ordernum,brand_logo FROM sdb_brand WHERE disabled = 'false' order by ordernum desc" );
		if ( $return )
		{
			file_put_contents( $file, json_encode( $contents ) );
			return $contents;
		}
		else
		{
			return file_put_contents( $file, json_encode( $contents ) );
		}
	}
}

?>
