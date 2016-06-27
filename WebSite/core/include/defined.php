<?php
//define('STAGE', 'dev');
define('STAGE', 'release');
switch ( true ) {
   case 'alpha' == constant('STAGE') || 'beta' == constant('STAGE'): //外网测试
      define('MATRIX_DATA_EX_URI', 'http://matrix-demo.ecos.ex-sandbox.com');
      define('MATRIX_DATA_EX_ASYNC_URI', 'http://matrix-demo.ecos.ex-sandbox.com/async');
      
      define('MATRIX_NODE_BIND_URI','http://sws.ex-sandbox.com'); // node绑定地址 手动
      define('MATRIX_NODE_BIND_API','http://sws.ex-sandbox.com/api.php'); // node绑定地址 api自动
      
      define('LICENSE_CENTER','http://service.ex-sandbox.com/openapi/api.php');
      define('LICENSE_CENTER_INFO','http://service.ex-sandbox.com');
      define('SHOP_USER_ENTERPRISE_API','http://passport.ex-sandbox.com/api.php');
      define('TRUST_LOGIN_URL','http://openid.ex-sandbox.com/redirect.php');
      define('SHOP_USER_ENTERPRISE','http://passport.ex-sandbox.com/index.php');
      break;
   case 'release' == constant('STAGE'): //外网正式
      define('MATRIX_DATA_EX_URI', 'http://matrix.ecos.shopex.cn');// 同步接口
      define('MATRIX_DATA_EX_ASYNC_URI', 'http://matrix.ecos.shopex.cn/async'); // 异步接口
      
      define('MATRIX_NODE_BIND_URI','http://www.matrix.ecos.shopex.cn/');
      define('MATRIX_NODE_BIND_API','http://www.matrix.ecos.shopex.cn/api.php');
      
      define('LICENSE_CENTER','http://service.shopex.cn/openapi/api.php'); // 获取证书地址
      define('LICENSE_CENTER_INFO','http://service.shopex.cn');
      define('SHOP_USER_ENTERPRISE_API','http://my.shopex.cn/api.php');
      define('TRUST_LOGIN_URL','http://openid.ecos.shopex.cn/redirect.php');
      define('SHOP_USER_ENTERPRISE','http://my.shopex.cn/index.php');
      define('SDS_API', 'http://sds.ecos.shopex.cn/api.php'); //
      define('SDS_PAYMENT_INSTALLONLINE_URI', 'http://sds.ecos.shopex.cn/payments/apps/');
      break;
   case 'dev' == constant('STAGE'): // 内网测试
      define('MATRIX_DATA_EX_URI', 'http://rpc.ex-sandbox.com/sync'); // 矩阵数据交换地址
      define('MATRIX_DATA_EX_ASYNC_URI', 'http://rpc.ex-sandbox.com/async'); // 矩阵数据交换地址 异步
      
      define('MATRIX_NODE_BIND_URI','http://sws.ex-sandbox.com'); // node绑定地址 手动
      define('MATRIX_NODE_BIND_API','http://sws.ex-sandbox.com/api.php'); // node绑定地址 api自动
      
      define('LICENSE_CENTER','http://service.ex-sandbox.com');
      define('LICENSE_CENTER_INFO','http://service.ex-sandbox.com');
      define('SHOP_USER_ENTERPRISE_API','http://passport.ex-sandbox.com/api.php');
      define('TRUST_LOGIN_URL','http://openid.ex-sandbox.com/redirect.php');
      define('SHOP_USER_ENTERPRISE','http://passport.ex-sandbox.com/index.php');
      break;
}

$constants = array(
    'OBJ_PRODUCT'=>1,
    'OBJ_ARTICLE'=>2,
    'OBJ_SHOP'=>0,
    'MIME_HTML'=>'text/html',
    'P_ENUM'=>1,
    'P_SHORT'=>2,
    'P_TEXT'=>3,
    'SCHEMA_DIR'=>BASE_DIR.'/plugins/schema/',
    'HOOK_BREAK_ALL'=>-1,
    'HOOK_FAILED'=>0,
    'HOOK_SUCCESS'=>1,
    'MSG_OK'=>true,
    'SHOP_DEMO'=>false,
    'MSG_WARNING'=>E_WARNING,
    'MSG_ERROR'=>E_ERROR,
    'MNU_LINK'=>0,
    'PAGELIMIT'=>20,
    'MNU_BROWSER'=>1,
    'MNU_PRODUCT'=>2,
    'MNU_ARTICLE'=>3,
    'MNU_ART_CAT'=>4,
    'PLUGIN_BASE_URL'=>'plugins',
    'MNU_TAG'=>5,
    'TABLE_REGEX'=>'([]0-9a-z_\:\"\`\.\@\[-]*)',
    'PMT_SCHEME_PROMOTION'=>0,
    'PMT_SCHEME_COUPON'=>1,
    'APP_ROOT_PHP'=>'',
    'SET_T_STR'=>0,
    'SET_T_INT'=>1,
    'SET_T_ENUM'=>2,
    'SET_T_BOOL'=>3,
    'SAFE_MODE'=>false,
    'SET_T_TXT'=>4,
    'SET_T_FILE'=>5,
    'SET_T_DIGITS'=>6,
    'LC_MESSAGES'=>6,
    'BASE_LANG'=>'zh_CN',
    'DEFAULT_LANG'=>'zh_CN',
    'DEFAULT_INDEX'=>'',
    'ACCESSFILENAME'=>'.htaccess',
    'DEBUG_TEMPLETE'=>false,
    'PRINTER_FONTS'=>'',
    'APP_WLTX_ID'=>'se_001',
    'APP_WLTX_VERSION'=>'ve_001',
    'APP_WLTX_URL'=>constant('LICENSE_CENTER'),
    'GENERALIZE_URL'=>'http://b2b.fenxiaowang.com',
    'OUTER_SERVICE_URL'=>'http://allinweb-service.shopex.cn',
    'RSC_PORTAL'=>'http://app.shopex.cn/exuser',
    'RSC_RPC'=>'http://rpc.app.shopex.cn',
    'TPL_INSTALL_URL'=>'http://addons.shopex.cn/templates/',
    'APP_ONLINE_URL'=>'http://app.shopex.cn/web/exuser/',
    'DAPI_URL'=>'http://rpc.app.shopex.cn/dapi',
    'MLV_NOLOGIN' => -1,

    'HTTP_HOST'=>$_SERVER['HTTP_HOST'],
    'NOW' =>time(),

    // 发货状态定义
    'SHIPMENT_NOTYET' => 0,
    'SHIPMENT_SHIPOUT' => 1,
    'SHIPMENT_SHIPOUT_PART' => 2,
    'SHIPMENT_RETURN_PART' => 3,
    'SHIPMENT_RETURN' => 4,

    // 支付状态定义
    'PAYMENT_PAY_ALL' => 1,
    'PAYMENT_PAY_MEDIUM' => 2,
    'PAYMENT_PAY_PART' => 3,
    'PAYMENT_REFUNDS_ALL' => 5,
    'PAYMENT_REFUNDS_PART' => 4,
    'PAYMENT_PAY_NOTYET' => 0,

    // 相对与网站根目录地址
    'SHOPADMIN_PATH'=>'shopadmin',

);

foreach($constants as $k=>$v){
    defined($k) || define($k, $v);
}
unset($constants); // rocky 08.23

switch( true ) {
   default:
       define('PHP_SELF', $_SERVER['PHP_SELF']); break;
   case !empty($_SERVER['ORIG_SCRIPT_NAME']):
       define('PHP_SELF', $_SERVER['ORIG_SCRIPT_NAME']); break;
   case !empty($_SERVER['SCRIPT_NAME']):
       define('PHP_SELF', $_SERVER['SCRIPT_NAME']); break;
   case !empty($_SERVER['DOCUMENT_URI']):
       define('PHP_SELF', $_SERVER['DOCUMENT_URI']); break;
}

$_SERVER['REQUEST_URI'] = 'http://'.HTTP_HOST.PHP_SELF.($_SERVER["QUERY_STRING"]?'?'.$_SERVER["QUERY_STRING"]:'');


define('API_DIR', CORE_DIR.'/api');
define("VERIFY_APP_ID","shopex_b2c");

define('PLATFORM','platform');
define('PLATFORM_HOST','api-b2c.shopex.cn');
define('PLATFORM_PATH','/api.php');
define('PLATFORM_PORT',80);
define('IMAGESERVER','imgserver');
define('IMAGESERVER_HOST','imgsrvs.shopex.cn');
define('IMAGESERVER_PATH','/api.php');
define('IMAGESERVER_PORT',80);
define('SERVER_PLATFORM_NOTICE','/maintenance.txt');

define('API_VERSION','3.0');

#短信接口配置数据2011-10-18

// 获取用户信息，发送短信，短信转移
#define('SMS_WEBPY_API_URL','http://webpy.ex-sandbox.com/');//内网
define('SMS_WEBPY_API_URL','http://api.sms.shopex.cn');//外网

// 充值，黑名单，辅服务器时间 是否成功
#define('NEWSMS_API_URL','http://newsms.ex-sandbox.com/sms_webapi/');//内网
define('NEWSMS_API_URL','http://webapi.sms.shopex.cn');//外网

// 请求基本url
#define('SMS_USER_API_URL','http://newsms.ex-sandbox.com/');
define('SMS_USER_API_URL','http://sms.shopex.cn/');

// 易开店和485在短信平台那边认为是两个东西
// 易开店短信产品id token(短信平台定义)
//define('SOURCE_ID','262712');
//define('SOURCE_TOKEN','1a1a17a50ca7138856c2657657295325');

// 485短信产品id token(短信平台定义)
define('SOURCE_ID','66684');
define('SOURCE_TOKEN','05c03727136872b38345564387500af3');
