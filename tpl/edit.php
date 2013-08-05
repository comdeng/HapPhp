<!doctype html>
<html>
	<head>
		<title>模板编辑</title>
		<script>
		_hjc = window['_hjc'] || [];
_hjc.push(
	['id', 3315524500],
	['debug', false],
	['image.lazyload', true]
);</script>
		<script src="http://s1.tthunbohui.cn/static/js/hapj.min.7c27da81.js"></script>
<script src="http://s1.tthunbohui.cn/static/js/hapj.editor.b5f411a0.js"></script>
<link rel="stylesheet" type="text/css" href="http://s1.tthunbohui.cn/static/css/jiehun.min.d697d5e9.css"/>
<style>
.TplContainer_Div {
	cursor: pointer;
}
.TplContainer_Div img{
	background: #FCFCFC;
	display: inline-block;
}
.TplContainer_Div h3 {
	min-height: 20px;
}
.TplContainer_Div p {
	min-height: 20px;
}
.TplContainer_Form {
	display: none;
	background: #F0F0F0;
	opacity: 0.8;
	padding:20px 20px;
	border:solid 2px #999;
	border-radius: 5px;
	box-shadow: 0 0 4px #999;
	max-height:600px;
	overflow-y:auto;
	width:600px;
	min-height:300px;
}
.TplContainer_Form th{
	width:100px;
}
.TplContainer_Form thead th{
	text-align: left;
}
.TplContainer_Form th,.TplContainer_Form td{
	padding:2px;
}
.TplContainer_Form tr{
	line-height:30px;
}
.TplContainer_Form input {
	height:24px;
	line-height:24px;
	font-size:14px;
	border:0;
}
.TplContainer_Form input[type=submit] {
	cursor: pointer;
	padding:10px 20px;
	height:40px;
	font-size:14px;
}
.TplContainer_Form ul {font-size: 14px;}
.TplContainer_Form ul li{padding:2px 10px;cursor:pointer;background:#F0F0F0;color:#369;border-radius:4px;}
.TplContainer_Form ul li.on{background:#369;color:#F0F0F0;}
</style>
	</head>
	<body>
		<div>头部</div>

		<?php
		require 'Template.php';
		$path = 'datas/data0.php';
		if (is_file($path)) {
			$data = include 'datas/data0.php';
		} else {
			$data = array();
		}
		$tpl = Template::LoadTemplate('templates/tpl1.php', $data);
		echo $tpl->Edit('/tpl/_edit.php?pageId=34');
		?>
	<script>
	hapj(function(H){
		H.ui.cls('TplContainer_Div').on('click', function(e){
			var form = this.nextSibling;
			if (form.style.display != 'block') {
				form.style.display = 'block';
				H.ui(form).floatable().center().middle();
			} else {
				form.style.display = 'none';
			}
			return false;
		});
	});
	</script>
	</body>
</html>