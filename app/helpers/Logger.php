<?php
namespace app\helpers;

class Logger{
    
    public static function truncate(){
        $_SESSION['log'] = [];
    }
    
    public static function add($params){
        $_SESSION['log'][] = $params;
    }
    
    public static function get(){
        return $_SESSION['log'];
    }
}
