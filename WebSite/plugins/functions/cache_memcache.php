<?php
/*
 * 使用memcached作为系统缓存。
 * 使用方法: 添加以下内容到你的config.php中
 *     define('CACHE_METHOD','memcached');
 *     define('MEMCACHED_HOST',你的memcache服务器ip);
 *     define('MEMCACHED_PORT',memcache服务器端口);
 */
require_once('cachemgr.php');
class cache_memcache extends cachemgr{

    var $name='Memcache';
    var $desc='Memcache module provides handy procedural and object oriented interface to memcached, highly effective caching daemon, which was especially designed to decrease database load in dynamic web applications.';

    function cache_memcache(){
        if(!class_exists('Memcache')){
            trigger_error('Missing php_memcache module',E_USER_ERROR);
        }
        $this->obj = new Memcache;
        $ports = explode(',',MEMCACHED_PORT);
        foreach(explode(',',MEMCACHED_HOST) as $i=>$h){
            if(!$this->obj->addServer($h,$ports[0])){
                trigger_error('Can\'t connect memcached server',E_USER_ERROR);
            }
        }
        $this->obj->pconnect();
        parent::cachemgr();
    }

    function store($key,&$value){
        if(!$this->obj->set($key,$value)){
            return $this->obj->replace($key,$value);
        }else{
            return true;
        }
        
    }
    
    function getModified($key){
        $data = $this->obj->get($key);
        if(!$data){
            $data = $this->_base_rev;
        }
        return $data;
    }

    function setModified($key){
        $now = time();
        if(is_array($key)){
            foreach($key as $k){
                if(!$this->obj->set($k,$now)){
                     $this->obj->replace($k,$now);
                }
            }
        }else{
             if(!$this->obj->set($key,$now)){
                 $this->obj->replace($key,$now);
             }
        }
    }

    function fetch($key,&$data){

        $data = $this->obj->get($key);


        if(!$data){
            if($this->obj->get($key.'lock')){
                    sleep(1);
                    $data = $this->obj->get($key);
            }else{
                if(!$this->obj->set($key.'lock',1,0,2)){
                        sleep(1);
                        $data = $this->obj->get($key);
                }
            }
        }
        return $data!==false;
    }


    function clear() {
        // $ports = explode(',',MEMCACHED_PORT);
        foreach ( explode ( ',', MEMCACHED_HOST ) as $i => $h ) {
            $clear_obj = new Memcache;
            $clear_obj->connect($h, '11211') or die ('Could not connect');
            $clear_obj->flush ();
            unset ( $clear_obj );
        }
        return true;
    }

    function status(&$curBytes,&$totalBytes){
        $info = $this->obj->getStats();
        $curBytes = $info['bytes'];
        $totalBytes = $info['limit_maxbytes'];

        $return[] = array('name'=>'子系统运行时间','value'=>timeLength($info['uptime']));
        $return[] = array('name'=>'缓存服务器','value'=>MEMCACHED_HOST.':'.MEMCACHED_PORT." (ver:{$info['version']})");
        $return[] = array('name'=>'数据读取','value'=>$info['cmd_get'].'次 '.formatBytes($info['bytes_written']));
        $return[] = array('name'=>'数据写入','value'=>$info['cmd_set'].'次 '.formatBytes($info['bytes_read']));
        $return[] = array('name'=>'缓存命中','value'=>$info['get_hits'].'次');
        $return[] = array('name'=>'缓存未命中','value'=>$info['get_misses'].'次');
        $return[] = array('name'=>'已缓存数据条数','value'=>$info['curr_items'].'条');
        $return[] = array('name'=>'进程数','value'=>$info['threads']);
        $return[] = array('value'=>$info['pid'],'name'=>'服务器进程ID');
        $return[] = array('value'=>$info['rusage_user'],'name'=>'该进程累计的用户时间(秒:微妙)');
        $return[] = array('value'=>$info['rusage_system'],'name'=>'该进程累计的系统时间(秒:微妙)');
        $return[] = array('value'=>$info['curr_items'],'name'=>'服务器当前存储的内容数量');
        $return[] = array('value'=>$info['total_items'],'name'=>'服务器启动以来存储过的内容总数');

//    $return[] = array('value'=>$info['curr_connections'],'name'=>'连接数量');
//    $return[] = array('value'=>$info['total_connections'],'name'=>'服务器运行以来接受的连接总数 ');
//    $return[] = array('value'=>$info['connection_structures'],'name'=>'服务器分配的连接结构的数量');
        return $return;
    }

}
?>
