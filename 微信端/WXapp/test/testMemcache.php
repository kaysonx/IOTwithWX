<?php
 
$memcache = new Memcache;
$memcache->connect('127.0.0.1',11211) or die('connect error!');
 
$memcache->set('key','hello memcache!');
 
$out = $memcache->get('key1');
 
echo $out;