<?php

/*
    [UCenter] (C)2001-2008 Comsenz Inc.
    This is NOT a freeware, use is subject to license terms

    $Id: base.php 12186 2008-01-17 06:40:29Z heyond $
*/

!defined('IN_UC') && exit('Access Denied');

if(!function_exists('getgpc')) {
    function getgpc($k, $var='G') {
        switch($var) {
            case 'G': $var = &$_GET; break;
            case 'P': $var = &$_POST; break;
            case 'C': $var = &$_COOKIE; break;
            case 'R': $var = &$_REQUEST; break;
        }
        return isset($var[$k]) ? $var[$k] : NULL;
    }
}

class base {

    var $time;
    var $onlineip;
    var $db;
    var $key;
    var $settings = array();
    var $cache = array();
    var $app = array();
    var $user = array();

    function base() {
        $this->init_var();
        $this->init_db();
        $this->init_note();
    }

    function init_var() {
        $this->time = time();
        $cip = getenv('HTTP_CLIENT_IP');
        $xip = getenv('HTTP_X_FORWARDED_FOR');
        $rip = getenv('REMOTE_ADDR');
        $srip = $_SERVER['REMOTE_ADDR'];
        if($cip && strcasecmp($cip, 'unknown')) {
            $this->onlineip = $cip;
        } elseif($xip && strcasecmp($xip, 'unknown')) {
            $this->onlineip = $xip;
        } elseif($rip && strcasecmp($rip, 'unknown')) {
            $this->onlineip = $rip;
        } elseif($srip && strcasecmp($srip, 'unknown')) {
            $this->onlineip = $srip;
        }
        preg_match("/[\d\.]{7,15}/", $this->onlineip, $match);
        $this->onlineip = $match[0] ? $match[0] : 'unknown';
    }

    function init_db() {
        require_once UC_ROOT.'lib/db.class.php';
        $this->db = new db();
        $this->db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, '', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
    }

    function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if(empty($_ENV[$model])) {
            require_once UC_ROOT."./model/$model.php";
            eval('$_ENV[$model] = new '.$model.'model($base);');
        }
        return $_ENV[$model];
    }

    function date($time, $type = 3) {
        if(!$this->settings) {
            $this->settings = $this->cache('settings');
        }
        $format[] = $type & 2 ? (!empty($this->settings['dateformat']) ? $this->settings['dateformat'] : 'Y-n-j') : '';
        $format[] = $type & 1 ? (!empty($this->settings['timeformat']) ? $this->settings['timeformat'] : 'H:i') : '';
        return gmdate(implode(' ', $format), $time + $this->settings['timeoffset']);
    }

    function page_get_start($page, $ppp, $totalnum) {
        $totalpage = ceil($totalnum / $ppp);
        $page =  max(1, min($totalpage,intval($page)));
        return ($page - 1) * $ppp;
    }

    function implode($arr) {
        return "'".implode("','", (array)$arr)."'";
    }

    function &cache($cachefile) {
        $_CACHE = array();
        $cachepath = UC_DATADIR.'./cache/'.$cachefile.'.php';
        if(!@include_once $cachepath) {
            $this->load('cache');
            $_ENV['cache']->updatedata('', $cachefile);
        }
        return $_CACHE;
    }

    function get_setting($k = array(), $decode = FALSE) {
        $return = array();
        $sqladd = $k ? "WHERE k IN (".$this->implode($k).")" : '';
        $settings = $this->db->fetch_all("SELECT * FROM ".UC_DBTABLEPRE."settings $sqladd");
        if(is_array($settings)) {
            foreach($settings as $arr) {
                $return[$arr['k']] = $decode ? unserialize($arr['v']) : $arr['v'];
            }
        }
        return $return;
    }

    function cutstr($string, $length, $dot = ' ...') {
        if(strlen($string) <= $length) {
            return $string;
        }

        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

        $strcut = '';
        if(strtolower(UC_CHARSET) == 'utf-8') {

            $n = $tn = $noc = 0;
            while($n < strlen($string)) {

                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1; $n++; $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $tn = 2; $n += 2; $noc += 2;
                } elseif(224 <= $t && $t < 239) {
                    $tn = 3; $n += 3; $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $tn = 4; $n += 4; $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $tn = 5; $n += 5; $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $tn = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }

                if($noc >= $length) {
                    break;
                }

            }
            if($noc > $length) {
                $n -= $tn;
            }

            $strcut = substr($string, 0, $n);

        } else {
            for($i = 0; $i < $length; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            }
        }

        $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

        return $strcut.$dot;
    }

    function init_note() {
        if($this->note_exists()) {
            $this->load('note');
            $_ENV['note']->send();
        }
    }

    function note_exists() {
        $noteexists = $this->db->fetch_first("SELECT value FROM ".UC_DBTABLEPRE."vars WHERE name='noteexists'");
        if(empty($noteexists)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}

?>