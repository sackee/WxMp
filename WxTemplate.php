<?php
use Think\Auth;

require_once dirname(__FILE__)."/WxException.php";
require_once dirname(__FILE__)."/WxConfig.php";
require_once dirname(__FILE__)."/WxCache.php";
require_once dirname(__FILE__)."/WxHelper.php";
require_once dirname(__FILE__)."/WxAuth.php";

class WxTemplate {
    
   
    
    /**
     * 构造函数
     */
    public function __construct($config = []) {
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
    }
    
    /**
     * 发送模版消息
     * @param unknown $openid
     * @param unknown $template_id
     * @param unknown $url
     * @param unknown $data
     */
    public function send($openid, $template_id, $url, $data) {
        $param = [
            "touser" => $openid,
            "template_id" => $template_id,
            "url" => $url,
            "data" => $data
        ] ;
        $auth = new WxAuth();
        $token = $auth->getToken();
        $res = WxHelper::getCurl(WxCofig::templateSendUrl($token), json_encode($param));
        if(isset($res['errcode']) && $res['errcode'] != 0){
            throw new WxException("发送模版消息失败 errcode:" . $res['errcode'].", errmsg:" . $res['errmsg']);
        }
        return $res;
        
    } 
    
    
}
