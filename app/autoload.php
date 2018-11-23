<?php
function autoload($className){

    $basePath = dirname(__DIR__);

    $path = $basePath . '/'. str_replace('\\', '/', $className) . '.php';
    
    if (file_exists($path)){
        include($path);
    }
}
spl_autoload_register('autoload');