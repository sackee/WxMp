<?php
require_once dirname(__FILE__)."/WxException.php";
require_once dirname(__FILE__)."/WxConfig.php";
require_once dirname(__FILE__)."/WxCache.php";
require_once dirname(__FILE__)."/WxHelper.php";

class WxAuth {
    
    const TOKEN_CACHE = "access_token_cache";
    const TICKET_CACHE = "jsapi_ticket_";
    const USER_INFO_CACHE = "wx_openid_";
    
    public $domain="";
    public $callBackUrl="";
    
    /**
     * 构造函数
     */
    public function __construct($config = []) {
        $this->domain = $_SERVER['HTTP_HOST'];
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
    }
    
    
    /**
     * 获取微信token
     */
    public function  getToken($reset = false) {
        $cacheStr = self::TOKEN_CACHE;
        $accessToken = WxCache::get($cacheStr);
    
        if (empty($accessToken) || $reset) {
            $url = WxCofig::tokenUrl();
            $res = json_decode(WxHelper::getCurl($url), true);
            
            if (isset($res['errcode'])) {
                throw new WxException("获取token失败 errcode:" . $res['errcode'].", errmsg:" . $res['errmsg']);
            }
            
            $accessToken = $res['access_token'];
            WxCache::put($cacheStr, $accessToken, $res['expires_in']);
        }
        return $accessToken;
    }
    
    
    /**
     * 获取jsapi_ticket
     */
    public function getJsapiTicket(){
        $cacheStr = self::TICKET_CACHE.WxHelper::httpType().$this->domain;
        $jsapiTicket = WxCache::get($cacheStr);
        
        if (empty($jsapiTicket)) {
            $accessToken = $this->getToken();
            $url = WxCofig::ticketUrl($accessToken);
            $res = json_decode(WxHelper::getCurl($url), true);
            
            if ($res['errcode'] != 0) {
                throw new WxException("获取ticket失败 errcode:" . $res['errcode'].", errmsg:" . $res['errmsg']);
            }
            
            $jsapiTicket=$res['ticket'];
            WxCache::put($cacheStr,$jsapiTicket,$res['expires_in']-60);
        }
    
        return $jsapiTicket;
    }
    
    
    /**
     * 生成签名
     */
    public function createJsapiSign($url = '') {
        //组装数据
        $url = empty($url) ? WxHelper::getUrl() : $url;
        $time = time();
        $str = "jsapi_ticket=" . $this->getJsapiTicket();
        $str .= "&noncestr=" . WxCofig::NONCE_STR;
        $str .= "&timestamp=" . $time;
        $str .= "&url=" . $url;
        $sign=array(
            "nonceStr" => WxCofig::NONCE_STR,
            "appId" => WxCofig::APPID,
            "timestamp" => $time,
            'signature' => sha1($str),
            'url' => $url,
        );
        return $sign;
    }
    
    /**
     * 跨平台微信授权
     * @param string $scope
     * @param string $back_url  完整回调地址，自己平台地址
     * @param string $session_id 自己平台产生的session_id  用于存放登录信息与memcache
     */
    public function requestCode($scope="snsapi_base", $back_url, $state = ""){
        $back_url = urldecode($back_url);
        
        if (empty($state) || empty($back_url)){
            throw new WxException("参数错误");
        }
        
        $back_url = base64_encode($back_url);
        
        if (empty($this->callBackUrl)) {
            throw new WxException("未设置回调链接");
        }
        
        $redirctUrl = urlencode($this->callBackUrl . '&back_url='. $back_url);
        $url = WxCofig::codeUrl($redirctUrl, $scope, $state);
        header("Location:".$url);
        exit;
    }
    
    /**
     * 获取用户信息
     * @param string $scope //scope参数中的snsapi_base和snsapi_userinfo
     * @return
     */
    public function getUserInfo($session_id, $back_url = ''){
        if (empty($session_id)) return false;
        $userinfo = WxCache::get(self::USER_INFO_CACHE . $session_id);
        
        if (empty($userinfo)) {
            $this->requestCode('snsapi_userinfo', $back_url, $session_id);
        }
        
        return $userinfo; 
    }
    
    
    /**
     * 请求微信用户信息
     * @param unknown $code
     * @param string $state
     */
    public function weixinUserInfo($code, $state = "") {
        $res = WxHelper::getCurl(WxCofig::auth2Url($code));
        $res = json_decode($res,true);
        
        if(isset($res['errcode'])){
            throw new WxException("获取auth token失败 errcode:" . $res['errcode'].", errmsg:" . $res['errmsg']);
        }
        
        $res2 = WxHelper::getCurl(WxCofig::userInfoUrl($res['access_token'], $res['openid']));
        $res2 = json_decode($res2,true);
        
        if(isset($res2['errcode'])){
            throw new WxException("获取user info 失败 errcode:" . $res2['errcode'].", errmsg:" . $res2['errmsg']);
        }
        
        $res['avatar'] = $res2['headimgurl'];
        $res['nickname'] = $res2['nickname'];
    
        unset($res['access_token']);
        unset($res['refresh_token']);
        
        WxCache::put(self::USER_INFO_CACHE . $state, $res, 2592000);
        return $res;
         
    }
    
}
