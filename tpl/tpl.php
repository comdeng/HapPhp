<?php
require 'Template3.php';

$tplName = $_GET['tpl'];
$path = 'datas/data0.php';
if (is_file($path)) {
	$data = include 'datas/data0.php';
} else {
	$data = array();
}
$tpl = Template::LoadTemplate('templates/'.$tplName.'.php', $data);
echo json_encode(array(
	'err' => 'hapn.ok',
	'html' => $tpl->Edit('/HapPhp/tpl/_edit.php?pageId=34')
));
