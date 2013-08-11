<?php
require 'Template3.php';
$tplName = $_GET['tpl'];
$tpl = Template::LoadTemplate('templates/'.$tplName.'.php');

if ($_POST['tpl_action'] == 'save') {
	file_put_contents('datas/'.$tplName.'.php', "<?php\nreturn ".var_export($_POST['data'], true).';');
}
header("Content-type:text/html;charset=utf-8");
$html = $tpl->Compile($_POST['data']);
echo json_encode(array(
	'err' => 'hapn.ok',
	'data' => array(
		'html' => $html,
	),
));