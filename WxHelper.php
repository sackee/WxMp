<?php
require_once dirname(__FILE__)."/WxAuth.php";
class WxHelper {
    
    /**
     * 获取当前url
     */
    public static  function getUrl() {
        $sys_protocal=self::httpType();
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
    
    /**
     * http类型
     */
    public static function httpType(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol;
    }
    
    
    public static function baseGetCurl($url, $post_data = [], $header = []) {
        //get、post数据
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        if(!empty($post_data)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    
    /**
     * 通过curl来获取数据
     */
    public static function getCurl($url, $post_data = [], $header = []){
        //get、post数据
        $output = self::baseGetCurl($url, $post_data, $header);
        //token失效处理
        $res = json_decode($output,true);
        if (isset($res['errcode']) && $res['errcode'] == 40001) {
            //重新获取token
            $auth = new WxAuth();
            $token = $auth->getToken(true);
            
            //重新请求
            $url = self::urlSetValue($url, "access_token", $token);
            if (isset($post_data['access_token'])) {
                $post_data['access_token'] = $token;
            }
            $output = self::baseGetCurl($url, $post_data, $header);
        }
        return $output;
    }
    
    /**
     * 替换设置url参数值
     */
    public static function urlSetValue($url,$key,$value)
    {
        $a=explode('?',$url);
        $url_f=$a[0];
        $query=$a[1];
        parse_str($query,$arr);
        $arr[$key]=$value;
        return $url_f.'?'.http_build_query($arr);
    }
    
}
