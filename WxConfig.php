<?php

class WxCofig {
    
    //微信公众平台 token
    const   TOKEN = 'loonxi';
    //微信公众平台appid
    const   APPID = "wx2e97a35c9398b8e6";
    //微信公众平台 AppSecret
    const   APPSECRET = "2ff7f7fd47ad925f2fd05eeb90daa56f";
    //微信公众平台nonce_str
    const   NONCE_STR = "Wm3WZYTPz0wzccnW";
    //接口链接地址
    const   API_URL = "https://api.weixin.qq.com/cgi-bin/";
    //文档链接地址
    const   FILE_URL = "http://file.api.weixin.qq.com/cgi-bin/";
    //auth2授权地址
    const   AUTH2_URL = "https://api.weixin.qq.com/sns/";
    //code地址
    const   CODE_URL = "https://open.weixin.qq.com/connect/oauth2/";
    
    
    public static function tokenUrl() {
        return self::API_URL . "token?grant_type=client_credential&appid=" . self::APPID . "&secret=" . self::APPSECRET;
    }
    
    public static function ticketUrl($token) {
        return self::API_URL . "ticket/getticket?access_token=" . $token . "&type=jsapi";
    }
    
    public  static function codeUrl ($redirctUrl, $scope, $state='') {
        $url = self::CODE_URL . 'authorize?appid=';
        $url .= self::APPID."&redirect_uri=" . $redirctUrl;
        $url .= "&response_type=code&scope=".$scope;
        $url .="&state=".$state;
        $url .="#wechat_redirect";
        return $url;
    }
    
    public static function auth2Url($code) {
        $url = self::AUTH2_URL . "oauth2/access_token?appid=" . self::APPID . "&secret=" . self::APPSECRET;
        $url .= "&code=" . $code . "&grant_type=authorization_code";
        return $url;
    }
    
    public static function userInfoUrl($token, $open_id) {
        return self::AUTH2_URL . "userinfo?access_token=" . $token . "&openid=" . $open_id . "&lang=zh_CN";
    }

    
    public static function templateSendUrl($token) {
        return self::API_URL . "message/template/send?access_token=" . $token;
    }
    
    public static function templateIdUrl($token) {
        return self::API_URL . "template/api_add_template?access_token=" . $token;
    }
}
