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
<!--min[hapj /static/js/hapj.min.js]-->
<script src="http://192.168.0.249:8042/static/js/jquery.js"></script>
<script src="http://192.168.0.249:8042/static/js/hapj.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/switchable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/verifiable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/floatable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/menuable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/ajaxable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/dialog.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/selectable.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/cal.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/lazyload.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/rotateimg.js"></script>
<script src="http://192.168.0.249:8042/static/js/lib/serial.js"></script>
<script src="http://192.168.0.249:8042/static/js/ui/sortable.js"></script>
<script src="http://192.168.0.249:8042/static/js/mod/adorn.js"></script>
<script src="http://192.168.0.249:8042/static/hapj.conf.js"></script>
<script src="http://192.168.0.249:8042/static/js/hapj.hook.js"></script>
<!--min[hapj]-->

<script src="http://192.168.0.249:8042/static/js/mod/msrows.js"></script>
<script src="http://s1.tthunbohui.cn/static/js/hapj.editor.b5f411a0.js"></script>
<link rel="stylesheet" type="text/css" href="http://s1.tthunbohui.cn/static/css/jiehun.min.d697d5e9.css"/>
<style>
body{background:#F0F0F0;}
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
	opacity: 0.96;
	padding:20px 20px;
	border:solid 2px #999;
	border-radius: 5px;
	box-shadow: 0 0 4px #999;
	max-height:600px;
	overflow-y:auto;
	width:620px;
	min-height:300px;
	font-size:14px;
}
.TplContainer_Form .tabs li{ font-size:18px;color:#369;width:12.5%;font-weight:bold;text-align:center;height:30px;line-height:30px;}
.TplContainer_Form .tabs li:hover,.TplContainer_Form .tabs li.on{background:#369;color:#FFF;cursor:pointer;}
.TplContainer_Form .items dt{width:20%;}
.TplContainer_Form .items dd{width:80%;}
.TplContainer_Form .group_tabs dt {cursor: pointer;}
.TplContainer_Form .group_tabs{}
.TplContainer_Form .group_dropdown {position:relative;}
.TplContainer_Form .group_dropdown b{
border-color: #666666 #F5F5F5 #F5F5F5;
    border-style: solid;
    border-width: 4px;
    font-size: 0;
    height: 0;
    line-height: 0;
    position: absolute;
    right: 10px;
    top: 7px;
    transition: transform 0.2s ease-in 0s;
    width: 0;
 }
.TplContainer_Form .group_tabs dd ul{position:absolute;width:100%;}
.TplContainer_Form .group_tabs .on{cursor:pointer;background:#CCC;color:#369;}
.TplContainer_Form .group_tabs dd{border:solid 1px #F0F0F0;}

.TplContainer_Form .group_item {padding:4px 0;}
.TplContainer_Form .group_item label{width:80px;display:inline-block;vertical-align:top}


#hd{background: transparent;}
#hd,#bd {width:1000px;margin:0 auto;}
#bd{padding:0;background:transparent;}
#tplItems .item{margin-bottom:10px;}
#tplItems .item .itemli{margin-bottom:5px;background:#FFF;}
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

</style>
	</head>
	<body>
		<div id="hd">头部</div>

		<div id="bd">
			<div id="tplItems" >
				<script type="text/x-hapj-tmpl">
				<li class="itemli">
					<label>选择模板：</label>
					<select key="_TEMPLATE_">
						<option value="">请选择</option>
						<option value="tpl0">模板1</option>
						<option value="tpl1">模板2</option>
					</select>
					<div></div>
				</li>
				</script>
				<ul class="item"></ul>
				<a class="_add">添加</a>
			</div>
		</div>
		<script>
		hapj(function(H){
			var ms = hapj.get('msrows')('tplItems', {
				sort:true
			});

			H.ui.id('tplItems').on('select', 'change', function(e) {
				var t = e.target, value = t.value;
				if (t.getAttribute('key') != '_TEMPLATE_') return;
				var panel = H.ui(t).next('div');
				if (!value) {
					panel.html('');
					return;
				}
				H.ajax({
					url:'/HapPhp/tpl/tpl.php?tpl=' + value,
					dataType:'json',
					success:function(data) {
						panel.html(data.html, true);
					}
				});
			});
			H.ui.id('tplItems').tag('ul').sortable();
		})
			
		</script>

		
	</body>
</html>