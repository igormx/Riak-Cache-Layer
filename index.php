<?php
use Riak\Cache;

include 'riak.php';
include 'Riak/Cache.php';

$cacheLayer=new Cache();
$cacheLayer->setLifeTime(30);

$idFecha="idFecha";
if(!$fecha=$cacheLayer->load($idFecha)){
    //$fecha=strftime("%d %m %Y %H:%M:%S",time());
    $fecha=file_get_contents("http://zend.com");
    
    $cacheLayer->save($fecha, $idFecha);
}

echo $fecha;