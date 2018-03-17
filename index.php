<?php
	require_once'./WeChat.class.php';
	header('content-type:image/jpeg');
    define('APPID', 'wx741b04eddc07c90d');
    define('APPSECRET', '429b11c19e27ee6e4557e778bd7ae827');
    define('TOKEN', 'weixinopen');

	$WeChat = new Wechat(APPID,APPSECRET,TOKEN);        //实例化对象;
	//获取AccessToken
	//echo $WeChat->_getAccessToken();
	//获取验证码
	echo $WeChat->_getQRCode(30);
    
	//后台信息自动回复
	//$WeChat->responseMsg();

    /*
	*创建菜单
	*/
		//设置菜单列表信息
        $menu ='{                               
                "button":[
                {    
                     "type":"click",
                     "name":"新闻",
                     "key":"news"
                 },
                 {
                      "name":"菜单",
                      "sub_button":[
                      {    
                          "type":"view",
                          "name":"搜索",
                          "url":"http://www.soso.com/"
                       },
                       {
                            "type":"miniprogram",
                            "name":"wxa",
                            "url":"http://mp.weixin.qq.com",
                            "appid":"wx286b93c14bbf93aa",
                            "pagepath":"pages/lunar/index"
                        },
                       {
                          "type":"click",
                          "name":"赞一下我们",
                          "key":"V1001_GOOD"
                       }]
                       }]
                 }';
   //$WeChat->_createManu($menu);