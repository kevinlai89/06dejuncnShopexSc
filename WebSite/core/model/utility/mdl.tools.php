<?php
class mdl_tools{

    function test_fake_html($is_set=false,&$msg){
        $system=&$GLOBALS['system'];
        $server=$system->loadModel('utility/serverinfo');
        $url = parse_url($system->base_url());
        $code = substr(md5(time()),0,6);
        $content = $server->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");
        if(!strpos($content,'[*['.md5($code).']*]')){
            if(false===strpos(strtolower($_SERVER["SERVER_SOFTWARE"]),'apache')){
                $msg='您的服务器不是apache,无法使用htaccess文件。请手动启用rewrite，否则无法启用伪静态';
                //trigger_error(__('您的服务器不是apache,无法使用htaccess文件。请手动启用rewrite，否则无法启用伪静态'),E_USER_ERROR);
                return false;
            }
            if(file_exists(BASE_DIR.'/'.ACCESSFILENAME)){
                $msg=__('您的系统存在无效的').ACCESSFILENAME.__(', 无法启用伪静态');
                //trigger_error(__('您的系统存在无效的').ACCESSFILENAME.__(', 无法启用伪静态'),E_USER_ERROR);
                return false;
            }else{
                if(($content = file_get_contents(BASE_DIR.'/root.htaccess'))){
                    $content = preg_replace('/RewriteBase\s+.*\//i','RewriteBase '.$url['path'],$content);
                    if(file_put_contents(BASE_DIR.'/'.ACCESSFILENAME,$content)){
                        $content = $server->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");
                        if(!strpos($content,'[*['.md5($code).']*]')){
                            unlink(BASE_DIR.'/'.ACCESSFILENAME);
                            $msg=__('您的系统不支持apache的').ACCESSFILENAME.__(',启用伪静态失败.');
                            //trigger_error(__('您的系统不支持apache的').ACCESSFILENAME.__(',启用伪静态失败.'),E_USER_ERROR);
                            return false;
                        }
                    }else{
                        $msg=__('无法自动生成').ACCESSFILENAME.__(',可能是权限问题,启用伪静态失败');
                        //trigger_error(__('无法自动生成').ACCESSFILENAME.__(',可能是权限问题,启用伪静态失败'),E_USER_ERROR);
                        return false;
                    }
                }else{
                    $msg=__('系统不支持rewrite,同时读取原始root.htaccess文件来生成目标').ACCESSFILENAME.__('文件,因此无法启用伪静态');
                    //trigger_error(__('系统不支持rewrite,同时读取原始root.htaccess文件来生成目标').ACCESSFILENAME.__('文件,因此无法启用伪静态'),E_USER_ERROR);
                    return false;
                }
            }
        }
        if($is_set){
            return $system->setConf('system.seo.emuStatic',true);
        }
        return true;
    }
	
	function is_email($email){
		//preg_match('/^.+@.+$/',$email);
		return (!empty($email)) && preg_match('/^([a-zA-Z0-9]+[_|\-\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/',$email);
	}
	function is_mobile($mobile){
		return (!empty($mobile)) && preg_match('/^1[0-9]{2}[0-9]{8}$/',$mobile);
	}
	
	function is_img_url($url){
		return (!empty($url)) && preg_match('!^http(s|)://!i',$url);
	}
	
	function check_name($name){
		return (!empty($name)) && preg_match('/^[^\x00-\x2f^\x3a-\x40]{2,20}$/i', $name);
	}
	
	function check_uname($uname){
		return (!empty($uname)) && preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $uname);
	}
	
	function is_ipv4($ipaddr) {
		return (!empty($ipaddr)) && preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ipaddr );
	}
	
	function is_ipv6($ipaddr) {
		return true;
		//return (!empty($ipaddr)) && preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ipaddr );
	}
	
	function is_valid_tablename($tbl) {
		return (!empty($tbl)) && preg_match( '/^[a-z0-9\_]+$/', $tbl );
	}
}
