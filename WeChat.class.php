<?php
class Wechat{
    private $_appid;
    private $_appsecret;
    private $_token;
    
    //构造函数，初始化变量
    public function __construct($_appid, $_appsecret, $_token) {
        $this->_appid = $_appid;
        $this->_appsecret = $_appsecret;
        $this->_token = $_token;
    }
    
    //微信信息真实性验证
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
        echo $echoStr;
        exit;
        }
    }
    //后台信息自动回复
    public function responseMsg(){
         //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
         //extract post data
         if (!emptyempty($postStr)){
             $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); //获取Element对象
             //判断消息类型
             switch ($postObj->MsgType){
                case 'event':
                    $this->_doEvent($postObj);
                    exit;
                case 'text':
                    $this->_doText($postObj);
                    exit;
                case 'image':
                    $this->_doImage($postObj);
                    exit;
                case 'voice':
                    $this->_doVoice($postObj);
                    break;
                 default:
                 ;
             }
         }else{
            echo "";
            exit;
         }
    }
    //处理文本信息        
    private function _doText(){
             $fromUsername = $postObj->FromUserName;
             $toUsername = $postObj->ToUserName;
             $keyword = trim($postObj->Content);
             $time = time();
             $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";
            if(!emptyempty( $keyword )){
               $msgType = "text";
               $contentStr = "Welcome to wechat world!";
               //值替换
               $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
               echo $resultStr;
            }else{
            echo "Input something...";
            }
         
    }
    
    //处理事件信息        
    private function _doEvent(){
       if(isset($postObj->EventKey)){
           switch ($postObj->EventKey){
               case 'news':
                   $this->_sendNews($postObj);
               default :
                   ;
           }
       } 
    }
    
    //处理图片信息        
    private function _doImage(){
        
    }
    
    //处理语音信息        
    private function _doVoice(){
        
    }
    
    //回复菜单新闻事件处理
    private function _sendNews(){
        $newstpl = '<xml>
                <ToUserName>< ![CDATA[%s] ]></ToUserName>
                <FromUserName>< ![CDATA[%s] ]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType>< ![CDATA[news] ]></MsgType>
                <ArticleCount>2</ArticleCount>
                <Articles>
                    %s;
                </Articles>
                </xml>';
        $news_item = '<item>
                <Title>< ![CDATA[%s] ]></Title> 
                <Description>< ![CDATA[%s] ]></Description>
                <PicUrl>< ![CDATA[%s] ]></PicUrl>
                <Url>< ![CDATA[%s] ]></Url>
                </item>';
        $news = array(
            array(
                'title'=>'德章泰-默里：马刺不会缺席季后赛，要依赖防守',
                'des'=>'他表示：“我们不会缺席季后赛的。”在昨天的比赛中，马刺以108-72大胜魔术，默里得到11分8篮板3抢断。',
                'pic'=>'https://c1.hoopchina.com.cn/uploads/star/event/images/180315/3794c7e4b8767b5390e950c29424b523cfd80818.png',
                'url'=>'https://voice.hupu.com/nba/2275133.html',
            ),
            array(
                'title'=>'还能不能做小伙伴了？小女孩运球晃飞小男孩',
                'des'=>'今天美国媒体Sports Center在推特上发布了一段视频，视频中一位小女孩在单挑中两次在防守者头上得分。',
                'pic'=>'https://c2.hoopchina.com.cn/uploads/star/event/images/180315/899d1d274f4c90b3700b07c32da48b3ada809844.png',
                'url'=>'https://voice.hupu.com/nba/2275136.html',
            )
        );
        $news_list ='';
        foreach ($new as $n){
            $news_list.= sprintf($news_item,$new['title'],$news['des'],$news['pic'],$news['url']);
        }
        echo sprintf($newsTpl, $postObj->fromUsername, $postObj->toUsername, time(), $news_list);
    }
    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->_token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    
    /*
     * 创建菜单
     */
    public function _createManu($menu){
        $curl = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->_getAccessToken();
        $content = json_decode($this->_request($curl,ture,'POST',$menu));
        if($content->errocode == 0){
            echo "菜单创建成功";
        }
    }
    
    /*
     * 连接方法
     */
    public function _request($curl, $https = true, $method = 'GET', $data = null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl);        //需要获取的url地址
        curl_setopt($ch, CURLOPT_HEADER, false);          //放弃url头信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    //返回字符串，而不直接输出
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //不做服务器的认证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);    //做服务器的证书认证
        }
        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, false);         //设置POST请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);   //设置POST请求数据
        }
        $content = curl_exec($ch);            //开始访问指定url
        curl_close($ch);                  //关闭url，释放资源
        return $content;
    }
    
    /*
     * 获取token值并保存
     */
    public function _getAccessToken(){
        $file = './accesstoken';  //设置token存储文件地址
        if(file_exists($file)){
            $content = file_get_contents($file);      //读取文档
            $content = json_decode($content);         //解释json数据
            if(time()- filemtime($file)<$content->expires_in){     //判断Access是否过期
                return $content->access_token;
            }
        }
        $curl ='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->_appid.'&secret='.$this->_appsecret;
        $content =$this->_request($curl);
        file_put_contents($file,$content);
        $content = json_decode($content);
        return $content->access_token;
    }
     /*
     * 获取Ticket
     */
    public function _getTicket($sceneid, $type='temp', $expire_seconds=604800){
        //设置临时二维码请求
        if($type=='temp'){
            $data = '{"expire_seconds": %s, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": %s}}}';
            $data = sprintf($data, $expire_seconds,$sceneid);
        }else{
        //设置永久性二维码请求
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": %s}}}';
            $data = sprintf($data, $sceneid);
        }
        //通过token获取二维码ticke
        $curl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->_getAccessToken();
        $content = $this->_request($curl,true,'POST',$data);
        $content = json_decode($content);
        return $content->ticket;
    }
    
    /*
     * 获取二维码图片
     */
    public function _getQRCode($sceneid, $type='temp', $expire_seconds = 604800){
        $ticket = $this->_getTicket($sceneid, $type, $expire_seconds);
        $curl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
        $content = $this->_request($curl);
        return $content;
    }
    
}