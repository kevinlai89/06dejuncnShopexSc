<?php
    class shop_renren extends shopPage{
      var $noCache = true;
        function shop_renren(){

            $this->system=&$GLOBALS['system'];
        }

/*登陆后将人人的数据返回中心*/
        function login(){
            
            if($_GET['session']){

                //header('Location:http://openid.ex-sandbox.com:20133/return.php?session='.$_GET['session']);
                if($_GET['action_renren-login_html'] == 'renren_return'){
                    header('Location:http://openid.ecos.shopex.cn/return.php?refertype=renren_return&session='.$_GET['session']);
                }
                else{
                   header('Location:http://openid.ecos.shopex.cn/return.php?session='.$_GET['session']);
                }
            }//http://openid.ex-sandbox.com:20133内网测试地址openid.ecos.shopex.cn
        }

/*授权设置*/
        function authorize_setting(){
            $authorize=$this->system->loadModel('plugins/openid_renren');
            $authorize->auth_insert($_POST);
        }

/*收藏*///http://openid.ecos.shopex.cn
        function tocollect(){
      //http://openid.ecos.shopex.cn/return.php

         $url = 'http://openid.ecos.shopex.cn/renren_collect.php?template_id='.$_POST['template_id'].'&certi_id='.$_POST['certi_id'].'&session_key='.$_POST['session_key'].'&authorize_item='.$_POST['authorize_item'].'&sign='.$_POST['sign'].'&body_data='.$_POST['body_data'].'&title_data='.$_POST['title_data'].'';

            file_get_contents($url);

        }

/*登陆：从人人那里点击网店连接跳转      http://www.yms.com/index.php?action_renren-returnlogin.html */
        function returnlogin(){
         
           $_session_key=$_REQUEST[$this->key1.'_session_key'];  
           $_user=$_REQUEST[$this->key1.'_user'];
           $base_url=$this->system->base_url();

           header('Location:http://openid.ecos.shopex.cn/redirect.php?certi_id='.$this->system->getConf('certificate.id').'&open=renren&refertype=renren_return&callback_url='.$base_url);
           
        }//http://openid.ecos.shopex.cn/redirect.php


/*退出*/
        function passort_logout(){
          if($api_key = $this->system->getConf('app.openid_renren.apikey')){
             $api_key1 = $api_key;
          }
          $url=$this->system->base_url();
          echo '
          <html xmlns="http://www.w3.org/1999/xhtml" xmlns:xn="http://www.renren.com/2009/xnml">
        <head></head>
        <body>
        <script type="text/javascript" src="http://static.connect.renren.com/js/v1.0/FeatureLoader.jsp"></script>
        <script>
       XN_RequireFeatures(["Connect"], function(){
           XN.Main.init("'.$api_key1.'", "/home/xd_receiver.html");
           XN.Connect.get_status().waitUntilReady(function (login_state) { 
               if (login_state == XN.ConnectState.connected) {
                   XN.Connect.logout(function () {
                       window.location="'.$url.'";
                   });
               }
           });
       });
      </script>
      </body>
      </html>
          ';

        }

    }




?>