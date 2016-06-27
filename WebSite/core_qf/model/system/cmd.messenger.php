<?php
/*author: http://www.shopextool.cn*/
class cmd_messenger extends mdl_messenger {        
    function send_email($email, $title , $content){
        $title = $title."[".$this->system->getConf('system.shopname')."]";                
        //file_put_contents(HOME_DIR.'/email.txt', print_r($email,true)."\r\n", FILE_APPEND);
        //file_put_contents(HOME_DIR.'/email.txt', print_r($title,true)."\r\n", FILE_APPEND);
        //file_put_contents(HOME_DIR.'/email.txt', print_r($content,true)."\r\n", FILE_APPEND);
        $sender =& $this->_load( 'email' );        
        //$this->_ready( $sender );
        //$ret = $sender->send( $email, $title , $content, $sender->config, $sms_type );

        $objEmail = $this->system->loadModel('system/email');
        if ($objEmail->ready($sender->config)){
            $ret = $objEmail->send($email,$title,$content,$sender->config);            
        }                

        return $ret;        
    }
    
}

