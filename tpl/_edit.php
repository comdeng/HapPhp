<?php
require 'Template.php';
$tpl = Template::LoadTemplate('templates/tpl0.php');

if ($_POST['tpl_action'] == 'save') {
	file_put_contents('datas/data0.php', "<?php\nreturn ".var_export($_POST['data'], true).';');
}
$html = $tpl->Compile($_POST['data']);
echo json_encode(array(
	'err' => 'hapn.ok',
	'data' => array(
		'html' => $html,
	),
));