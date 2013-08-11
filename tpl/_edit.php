<?php
require 'Template3.php';
$tpl = Template::LoadTemplate('templates/tpl1.php');

if ($_POST['tpl_action'] == 'save') {
	file_put_contents('datas/data0.php', "<?php\nreturn ".var_export($_POST['data'], true).';');
}
header("Content-type:text/html;charset=utf-8");
$html = $tpl->Compile($_POST['data']);
echo json_encode(array(
	'err' => 'hapn.ok',
	'data' => array(
		'html' => $html,
	),
));