<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$name = explode('.', $_SERVER['SERVER_NAME']);

$id_polki = array(
    //'BCM2708-000e-0000000050892ebd' => 'sabmiller',
    //'BCM2708-000e-000000007ff2bd95' => 'pepsi',
    //'BCM2708-000e-000000004abbdfba' => 'molsoncoors',
    'BCM2708-000e-00000000bf374d38' => 'pepsi',
    'BCM2708-000e-00000000a784a97b' => 'pepsi',
    'BCM2708-000e-00000000a28160ae' => 'inbev',
);

if(isset($_POST['system'])) {
    foreach($id_polki as $id=>$n){
        if(strpos($_POST['system'],$id)!==false){
            $name[0] = $n;
            break;
        }
    }
}

if(isset($_POST['Login'])){
    foreach($id_polki as $id=>$n){
        if($_POST['Login']==$id){
            $name[0] = $n;
            break;
        }
    }
}
$kernel = new AppKernel($name[0], true);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
