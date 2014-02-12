<?php
$last = mktime(0,0,0,1,1,date('Y'));
$cond = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;

if ($cond && $_SERVER['REQUEST_METHOD'] == 'GET' && strtotime($cond) >= $last) {
 header('HTTP/1.0 304 Not Modified');
 exit;
}

// TODO: consider caching the results of combo + minify

include_once('cssmin-v2.0.2.2.php');
$output = '';

$output .= "\n/********* jquery.ui.css *********/\n";
$output .= file_get_contents('vendor/jquery.ui/css/jquery.ui.css');

$output .= "\n/********* bootstrap *********/\n";
$output .= file_get_contents('vendor/bootstrap/css/bootstrap.css');

$output .= "\n/********* bootstrap *********/\n";
$output .= file_get_contents('vendor/bootstrap/css/bootstrap-responsive.css');

$minify = (array_key_exists('r', $_REQUEST) && 'dev' != substr($_REQUEST['r'],0,3)); // ability to view source unpacked
if ($minify && class_exists('CssMin'))
{
  $output = CssMin::minify($output);
}

// http://www.w3.org/International/questions/qa-css-charset.en.php
$output = "@charset \"UTF-8\";\n" . $output;

// we'll never have a release go longer than 90 days. never. ever ever. haha.
$expires = 60 * 60 * 24 * 90;

// send the right headers
session_cache_limiter('public');
//public
header("Content-Type: text/css");
header('Pragma: public');
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last) . " GMT");
header("Content-Length: " . strlen($output));

echo $output;
exit;
