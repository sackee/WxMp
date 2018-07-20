<?php
class WxCache{
    
    public static function get($name) {
       return S($name);
    }
    
    public static function put($name, $value='', $options = null) {
        return S($name, $value, $options);
    }
    
}
