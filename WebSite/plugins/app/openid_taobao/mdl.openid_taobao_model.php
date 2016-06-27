<?php 
class mdl_openid_taobao_model extends shopObject{
    function mdl_openid_taobao_model(){
       $this->system=&$GLOBALS['system'];
	   $this->db=$this->system->database();
	}

	function save_tb_addr($member_id,$data){
		
       $data_count=count($data);

	   for($i=0;$i<$data_count;$i++){
		   
		   unset($data[$i]['nick']);
            $aData[$i]['member_id'] = $member_id;
            $aData[$i]['addr_id'] = substr($data[$i]['id'],-7);

			$aData[$i]['province'] = $data[$i]['province'];
			$aData[$i]['city'] = $data[$i]['city'];

            if((empty($data[$i]['area']))||($data[$i]['area']=='')){
                 $region_id=$this->db->selectrow("select region_id from sdb_regions where local_name ='".$data[$i]['city']."'");
                 $data[$i]['area'] = 'mainland:'.$data[$i]['province'].'/'.$data[$i]['city'].':'.$region_id['region_id'];
			     $aData[$i]['area'] = $data[$i]['area'];
            }else{
			    $region_id=$this->db->selectrow("select region_id from sdb_regions where local_name ='".$data[$i]['area']."'");
                $data[$i]['area'] = 'mainland:'.$data[$i]['province'].'/'.$data[$i]['city'].'/'.$data[$i]['area'].':'.$region_id['region_id'];
                $aData[$i]['area'] = $data[$i]['area'];
			}
			
			$aData[$i]['addr'] = $data[$i]['address_detail'];
			$aData[$i]['zip'] = $data[$i]['post_code'];
			$aData[$i]['name'] = $data[$i]['name'];
			$aData[$i]['tel'] = $data[$i]['phone'];
			$aData[$i]['mobile'] = $data[$i]['mobile'];
			$aData[$i]['def_addr'] = ($i==0)?1:0;
			$this->save_addr($aData[$i]['addr_id'],$aData[$i]);
	   }
	}

	function save_addr($Mid,$data){
        $aRs_id = $this->db->selectrow("SELECT addr_id FROM sdb_member_addrs WHERE addr_id=".intval($Mid));
        if($aRs_id['addr_id']!=''){
			$aRs = $this->db->query("SELECT * FROM sdb_member_addrs WHERE addr_id=".intval($Mid));
            $sSql = $this->db->getUpdateSql($aRs,$data);
			return (!$sSql || $this->db->query($sSql));
		}else{
           $aRs_sql = $this->db->query('SELECT * FROM sdb_member_addrs WHERE 1=1');
           $sql = $this->db->getInsertSql($aRs_sql,$data);
           return $this->db->exec($sql);
		}
	}

}

?>