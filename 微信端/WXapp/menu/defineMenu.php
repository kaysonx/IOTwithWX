<?php
/*
	自定义微信公众号菜单
*/
header("Content-type: text/html; charset=utf-8");
define("ACCESS_TOKEN", "N6bOKZmRSq-Kyslk6eqBYBv4c4qjJi6GwP5dmdc8YntOz1tvxpaTg3_sAZ7CQQ5cMzeyBpfqfCXozJHCZP9gaks_IRg1K-dPBu7Sn_ZWVSoJUEjPRbGtLIfKJdX8H0UKJHSjAJAMKV");


//创建菜单
function createMenu($data){
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".ACCESS_TOKEN);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$tmpInfo = curl_exec($ch);
if (curl_errno($ch)) {
  return curl_error($ch);
}

curl_close($ch);
return $tmpInfo;

}

//获取菜单
function getMenu(){
return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".ACCESS_TOKEN);
}

//删除菜单
function deleteMenu(){
return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".ACCESS_TOKEN);
}





$data = '{
     "button":[
      {
           "name":"监控",
           "sub_button":[
            {
               "type":"click",
               "name":"实时监控",
               "key":"V1_Video"
            },
            {
               "type":"click",
               "name":"实况截图",
               "key":"V1_Pic"
            }]
       },
	   {
          "name":"操作",
           "sub_button":[
            {
               "type":"click",
               "name":"开灯",
               "key":"V2_LightOn"
            },
			{
               "type":"click",
               "name":"关灯",
               "key":"V2_LightClose"
            },
			{
               "type":"click",
               "name":"获取温度湿度",
               "key":"V2_Tepr"
            }]
      },
      {
		   "type": "view", 
		   "name": "简介", 
		   "url": "http://utfire.com.cn/WXapp/introduction.html",
           "key":"V3_Introduct"
      }
	  ]
}';




echo createMenu($data);
//echo getMenu();
//echo deleteMenu();