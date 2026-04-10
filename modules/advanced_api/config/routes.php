<?php

$method = $_SERVER['REQUEST_METHOD'];

$route['advanced_api/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)'] = '$1/$2/$2/$3/$4/$5/$6';
$route['advanced_api/(.*)/(.*)/(.*)/(.*)']           = '$1/$2/$2/$3/$4';
$route['advanced_api/(:any)/(:any)']                 = '$1/$2/$2';
