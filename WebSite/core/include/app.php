<?php
class app{

    function app(){
        $this->ident = substr(get_class($this),4);
        $this->system = &$GLOBALS['system'];
        $this->db = &$this->system->database();
        $this->appdir = PLUGIN_DIR.'/app/'.$this->ident;
    }

    function install(){
        $schemaObj = $this->system->loadModel('utility/schemas');
        foreach($this->dbtables() as $k=>$v){
            if(!preg_match('/^[a-z0-9\_]+$/',$k)){
                trigger_error(E_ERROR,'Error Table name');
            }
            $this->db->exec('drop table if exists '.DB_PREFIX.$this->ident.'_'.$k);
            $arr = &$schemaObj->load_array($v);
            $sql = $schemaObj->get_sql($this->ident.'_'.$k,$arr);
            $this->db->exec($sql);
        }
        return true;
    }

    function uninstall(){
        foreach($this->dbtables() as $k=>$v){
            $this->db->exec($a='drop table if exists sdb_'.$this->ident.'_'.$k);
        }
        return true;
    }


    
    function getConf($key){
        return $this->system->getConf($key);        
    }
    
    function setConf($key,$value){
        return $this->system->setConf($key,$value);
    }

    function dbtables(){
        $app_path = PLUGIN_DIR.'/app/'.$this->ident.'/dbschema';
        if(is_dir($app_path)){
            $db = array();
            $path = @opendir($app_path);
            while($file = readdir($path)){
                $schema_name = substr($file,0,-4);
                if(substr($file,-4)=='.php'){
                    require_once($app_path.'/'.$file);
                }
            }
            return $db;
        }else{
            return array();
        }
        return array();
    }

    function update(){
        $appmgr = $this->system->loadModel('system/appmgr');
        $diff_tables = $appmgr->get_app_diff($this->ident);
        foreach($diff_tables as $key=>$value){
             foreach($value as $g=>$d){
                $this->db->exec($d);
             }
        }
        return true;
    }
    function setting(){
        $setting_file = PLUGIN_DIR.'/app/'.$this->ident.'/setting.php';
        if(file_exists($setting_file)){
            include($setting_file);
        }
        return is_array($setting)?$setting:array();
    }
    function listener(){ return array(); }
    function ctl_mapper(){ return array(); }
    function output_modifiers(){ return array(); }
    function getMenu(&$menu){}
    function crontab_queue(){return array();}
    function passport_verify($mem){
        $mem['member_refer'] = $this->member_refer;
        if($mem['uname']&&$mem['password']&&$mem['email']){
            $mem_data = $this->db->selectrow("SELECT * FROM sdb_members  WHERE uname = '".$mem['uname']."'");
            $_POST['login'] = $mem['uname'];
            $_POST['passwd'] = $mem['password'];
            $mem['password'] = md5($mem['password']);
            if($mem_data){
                $mem_temp = $this->db->exec("SELECT * FROM  sdb_members WHERE uname='".$mem['uname']."'");
                $sql = $this->db->getUpdateSql($mem_temp,$mem);
                if($sql){
                    if(!$this->db->exec($sql)){
                        echo '请联系开发者';
                        exit;
                    }
                }
                return $mem_data;
            }else{
                $mem_level = $this->system->loadModel('member/level');
                if($mem_le_id = $mem_level->getDefauleLv()){
                    $mem['member_lv_id'] = $mem_le_id;  
                }             
                $aRs = $this->db->exec('SELECT * FROM sdb_members WHERE 1=0');
                $sql = $this->db->getInsertSql($aRs,$mem);
                if($this->db->exec($sql)){
                    $mem['member_id'] = $this->db->lastinsertid();
                    return $mem;
                }else{
                    echo $sql.'插入错误';
                    echo '请联系开发者';
                }
            }
        }else{
            echo '请联系开发者';
        }
        exit;
    }
    
    function backup($fromdir,$todir,$rewrite=false) {
        // 无需覆盖旧文件
        if ( !is_dir($fromdir) ) return true;
        
        // 备份目录不存在且创建失败则按照失败
        if ( !is_dir($this->appdir.'/backup') && !mkdir($this->appdir.'/backup',0755) ) {
            return false;
        }
        if ( !is_dir($todir) && !mkdir($todir,0755,true)) return false;
        
        if ( !$dirhandler = opendir($fromdir) ) return false;
        
        while( $tmp = readdir($dirhandler) ) {
            if ( '.' == substr($tmp,0,1) ) continue;
            
            $fromdir_ = $fromdir.'/'.$tmp;
            $todir_ = $todir.'/'.$tmp;
            if ( is_file($fromdir_) ) {
                // 对原来存在的文件备份
                $backupdir = str_replace(BASE_DIR,($this->appdir).'/backup',$todir_);
                if ( !is_dir(dirname($backupdir)) && !mkdir_p(dirname($backupdir)) ) return false;
                //比较时间戳
                if ( filemtime($fromdir_) <= filemtime($todir_) ) continue; 
                // 备份
                if ( is_file($todir_) && !copy($todir_,$backupdir) ) return false;
                
                if ( !copy($fromdir_,$todir_) ) return false;
            } else {
                if (!$this->backup($fromdir_,$todir_)) return false;
            }
        }
        closedir($dirhandler);
        return true;
    }
    
    function enable() {
    
    }
    
    function disable() {
        $listeners = $this->system->getConf('system.event_listener');
        foreach($listeners as $key=>$handlers){
            foreach($handlers as $k=>$v){
                if ( 0 === strpos($v,$this->ident) ) {
                    unset($listeners[$key][$k]);
                }
            }
        }
        $this->system->setConf('system.event_listener',$listeners);
        
        $modifiers = $this->system->getConf('system.output_modifiers');
        foreach($modifiers as $key=>$handlers){
            foreach($handlers as $k=>$v){
                if ( 0 === strpos($v,$this->ident) ) {
                    unset($modifiers[$key][$k]);
                }
            }
        }
        $this->system->setConf('system.output_modifiers',$modifiers);
        
        $ctlmaps = $this->system->getConf('system.ctlmap');
        foreach($ctlmaps as $key=>$handler){
            if ( 0 === strpos($handler,$this->ident) ) {
                unset($ctlmaps[$key]);
            }
        }
        
        $appmgrMdl = $this->system->loadModel('system/appmgr');
        return $appmgrMdl->disable($this->ident);
    }

}
