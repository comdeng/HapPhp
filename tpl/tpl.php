<?php
require 'Template3.php';

$tplName = $_GET['tpl'];
$path = 'datas/'.$tplName.'.php';
if (is_file($path)) {
	$data = include $path;
} else {
	$data = array();
}
$tpl = Template::LoadTemplate('templates/'.$tplName.'.php', $data);
echo json_encode(array(
	'err' => 'hapn.ok',
	'html' => $tpl->Edit('/HapPhp/tpl/_edit.php?tpl='.$tplName)
));
