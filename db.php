<?php
define('FILE_SIZE', 1 * 1024 * 1024);
define('THUMB_WIDTH', 300);


ini_set('display_errors', 1);

if(!function_exists('imagecreatetruecolor')) {
  echo 'グラフィックのライブラリがインストールされていません';
  exit;
}
