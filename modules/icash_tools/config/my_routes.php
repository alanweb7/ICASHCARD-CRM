<?php

defined('BASEPATH') or exit('No direct script access allowed');
$base = 'admin/icash_tools/article/details';
// a rota esta no formato: endpoint (URL) x rota interna (controller.php/funcao_na_classe)


// TEMPLATES

$route['article/(:any)'] = $base . '/$1';
$route[$base . '/(:any)'] = 'icash_tools/redirect/utilities_article_details/$1';

$route['admin/icash_tools/media'] = 'icash_tools/media/index';
$route['admin/icash_tools/announcements'] = 'icash_tools/media/announcements';
$route['admin/icash_tools/my_link'] = 'icash_tools/templates/templates_tools/my_link';

$route['admin'] = 'icash_tools/custom_dashboard';
