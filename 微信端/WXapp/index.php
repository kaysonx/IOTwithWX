<?php
/*
    微信公众平台基础功能搭建
*/
//微信Token
define("TOKEN", "PLBRT");
define("SMPADDR","http://liusha.s1.natapp.cc");

//定义服务器MySql相关信息
define("MYSQL_HOST","utfire.com.cn");
define("MYSQL_PORT","3306");
define("MYSQL_USER","root");
define("MYSQL_PASS","utfire");

//定义实况截图路径
//define("CAMPIC","http://123.207.111.63/WXapp/camPic/CamPic.jpg");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            header('content-type:text'); //解决token验证不通过
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R \r\n".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            if (($postObj->MsgType == "event") && ($postObj->Event == "subscribe" || $postObj->Event == "unsubscribe")){
                //过滤关注和取消关注事件
            }else{
                
            }
            
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                   if (strstr($postObj->Content, "第三方")){
                        $result = $this->relayPart3("http://123.207.111.63/test.php".'?'.$_SERVER['QUERY_STRING'], $postStr);
                    }else{
                        $result = $this->receiveText($postObj);
                    }
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T \r\n".$result);
			header("Content-Type:text/html; charset=utf-8");
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
    private function doCurlGetRequest($url,$data,$timeout = 5)
    {
         if($curl == "" || $timeout <= 0){
         return false;
         }
         $url = $url.'?'.http_bulid_query($data);
         $con = curl_init((string)$url);
         curl_setopt($con, CURLOPT_HEADER, false);
         curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
         curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
         
         return curl_exec($con);
    }
	//获取实况截图
	private function getCamPic()
	{
		$img = file_get_contents(SMPADDR."/?action=snapshot"); 
		$date = date('U');
		$memcache = new Memcache;
		$memcache->connect('127.0.0.1',11211) or die('connect error!');
		$memcache->set('time',$date);
		file_put_contents('./camPic/'.$date.'.jpg',$img); 
		$picUrl = "http://".MYSQL_HOST."/WXapp/camPic/".$date.".jpg";
		return $picUrl;
	}
	//消息自定义响应
    private function responseTextMsg($content)
    {
        if (strstr($content, "温度")) {
			
            $con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
            mysql_select_db("app_991067661", $con);//修改数据库名
			mysql_query("SET NAMES 'utf8'");//注意一定要用双引号...

            $result = mysql_query("SELECT * FROM sensor");
            while($arr = mysql_fetch_array($result)){
              if ($arr['ID'] == 1) {//只用一条数据，保存当时的温度
                $tempr = $arr["temperature"];
                $humidity = $arr["humidity"];
				$place = $arr["place"];
				$time = $arr["timestamp"];
              }
            }
            mysql_close($con);
			//使时区一致
			date_default_timezone_set('prc');
			$time = time();
			$time = date("Y-m-d h:i:s", $time);
			
            $retMsg = $time.":\n"."报告："."\n"."当前【".$place."】的室温为".$tempr."℃，湿度为：".$humidity ."%，\n感谢主人的关心~~~";
            return $retMsg;
            
        }elseif (strstr($content, "开灯")) {
			/*
            $con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
            $date = date("h:i:sa");
            mysql_select_db("app_991067661", $con);//修改数据库名

            $sql ="UPDATE switch SET timestamp='$date',state = '1'
            WHERE ID = '1'";//修改开关状态值

            if(!mysql_query($sql,$con)){
                die('Error: ' . mysql_error());
            }else{
                mysql_close($con);
                $retMsg = "即将为你开灯，主人~~~";
                return $retMsg;
            }
			*/
			$memcache = new Memcache;
			$memcache->connect('127.0.0.1',11211) or die('connect error!');
			$memcache->set('switch','1');
			$retMsg = "即将为你开灯，主人~~~";
            return $retMsg;
			
        }elseif (strstr($content, "关灯")) {
			/*
            $con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
            mysql_select_db("app_991067661", $con);//修改数据库名
            $date = date("h:i:sa");
            $sql ="UPDATE switch SET timestamp='$date',state = '0'
            WHERE ID = '1'";//修改开关状态值

            if(!mysql_query($sql,$con)){
                die('Error: ' . mysql_error());
            }else{
                mysql_close($con);
                $retMsg = "即将为你关灯，主人~~~";
                return $retMsg;
            }	
			*/
			$memcache = new Memcache;
			$memcache->connect('127.0.0.1',11211) or die('connect error!');
			$memcache->set('switch','0');
			$retMsg = "即将为你关灯，主人~~~";
            return $retMsg;
        }elseif (strstr($content,"截图")){

            $picUrl = $this->getCamPic();
            $sourUrl = MYSQL_HOST."/WXapp/camera/camPic.html";
            $retMsg = array();
            $retMsg[] = array("Title"=>"实况截图",  "Description"=>"点击查看实况截图大图", "PicUrl"=>"$picUrl", "Url" =>"$sourUrl");
			//$retMsg[] = array("Title"=>"实况截图",  "Description"=>"$picUrl", "PicUrl"=>"$picUrl", "Url" =>"$sourUrl");
            return $retMsg;
            
        }elseif (strstr($content,"监控")){
            $picUrl = $this->getCamPic();
            $retCamUrl = MYSQL_HOST."/WXapp/camera/camera.html";
            
            $retMsg = array();
            $retMsg[] = array("Title"=>"实时监控",  "Description"=>"点击进入实时监控", "PicUrl"=>"$picUrl", "Url" =>"$retCamUrl");
           return $retMsg;
        }else{
            $retMsg = "暂时不支持该命令，主人~~~";
            return $retMsg;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注微信物联网平台！";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "V1_Video":
                        $content = $this->responseTextMsg("监控");
                        break;
					case "V1_Pic":
                        $content = $this->responseTextMsg("截图");
                        break;
					case "V2_LightOn":
                        $content = $this->responseTextMsg("开灯");
                        break;
					case "V2_LightClose":
                        $content = $this->responseTextMsg("关灯");
                        break;
					case "V2_Tepr":
                        $content = $this->responseTextMsg("温度");
                        break;
                    default:
                        $content = "by PLBRT.2016";
                        break;
                }
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "scancode_waitmsg":
                if ($object->ScanCodeInfo->ScanType == "qrcode"){
                    $content = "扫码带提示：类型 二维码 结果：".$object->ScanCodeInfo->ScanResult;
                }else if ($object->ScanCodeInfo->ScanType == "barcode"){
                    $codeinfo = explode(",",strval($object->ScanCodeInfo->ScanResult));
                    $codeValue = $codeinfo[1];
                    $content = "扫码带提示：类型 条形码 结果：".$codeValue;
                }else{
                    $content = "扫码带提示：类型 ".$object->ScanCodeInfo->ScanType." 结果：".$object->ScanCodeInfo->ScanResult;
                }
                break;
            case "scancode_push":
                $content = "扫码推事件";
                break;
            case "pic_sysphoto":
                $content = "系统拍照";
                break;
            case "pic_weixin":
                $content = "相册发图：数量 ".$object->SendPicsInfo->Count;
                break;
            case "pic_photo_or_album":
                $content = "拍照或者相册：数量 ".$object->SendPicsInfo->Count;
                break;
            case "location_select":
                $content = "发送位置：标签 ".$object->SendLocationInfo->Label;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }

        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        //多客服人工回复模式
        if (strstr($keyword, "请问在吗") || strstr($keyword, "在线客服")){
            $result = $this->transmitService($object);
            return $result;
        }
        
        //自定义回复规则
        $content = $this->responseTextMsg($keyword);
        
        if(is_array($content)){
            if (isset($content['Title'])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MediaId'])){
                $result = $this->transmitImage($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，经度为：".$object->Location_Y."；纬度为：".$object->Location_X."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            //$content = "你刚才说的是：".$object->Recognition;
			$content = $this->responseTextMsg($object->Recognition);
			 if(is_array($content)){
				if (isset($content[0])){
					$result = $this->transmitNews($object, $content);
				}else if (isset($content['MusicUrl'])){
					$result = $this->transmitMusic($object, $content);
				}
			}else{
				$result = $this->transmitText($object, $content);
			}
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }
        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)){
            return "";
        }

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);

        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return "";
        }
        $itemTpl = "        <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
$item_str    </Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        if(!is_array($musicArray)){
            return "";
        }
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
    </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
    </Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
        <MediaId><![CDATA[%s]]></MediaId>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
    </Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复第三方接口消息
    private function relayPart3($url, $rawData)
    {
        $headers = array("Content-Type: text/xml; charset=utf-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    //字节转Emoji表情
    function bytes_to_emoji($cp)
    {
        if ($cp > 0x10000){       # 4 bytes
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)).chr(0x80 | (($cp & 0x3F000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x800){   # 3 bytes
            return chr(0xE0 | (($cp & 0xF000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x80){    # 2 bytes
            return chr(0xC0 | (($cp & 0x7C0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else{                    # 1 byte
            return chr($cp);
        }
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
?>