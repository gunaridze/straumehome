<?php
$arUrlRewrite=array (
  6 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  8 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/favorites/#',
    'RULE' => '',
    'ID' => 'imedia:favorites',
    'PATH' => '/favorites/index.php',
    'SORT' => 100,
  ),
  11 => 
  array (
    'CONDITION' => '#^/personal/#',
    'RULE' => '',
    'ID' => 'bitrix:sale.personal.section',
    'PATH' => '/personal/index.php',
    'SORT' => 100,
  ),
  14 => 
  array (
    'CONDITION' => '#^/product/#',
    'RULE' => '',
    'ID' => 'imedia:product',
    'PATH' => '/product/index.php',
    'SORT' => 100,
  ),
  15 => 
  array (
    'CONDITION' => '#^/catalog/#',
    'RULE' => '',
    'ID' => 'imedia:catalog',
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  12 => 
  array (
    'CONDITION' => '#^/search/#',
    'RULE' => '',
    'ID' => 'imedia:search',
    'PATH' => '/search/index.php',
    'SORT' => 100,
  ),
  13 => 
  array (
    'CONDITION' => '#^/brands/#',
    'RULE' => '',
    'ID' => 'imedia:brands',
    'PATH' => '/brands/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/blog/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/blog/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
);
