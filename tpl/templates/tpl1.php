<?php
return array(
	'tpl_name' => '模板2',
	'tpl_key' => 'tpl1',
	'tpl_vars' => array(
		'default' => array(
			'name' => '默认',
			'count' => 4,
			'vars' => array(
				'_VISIBLE_' => array(
					'name' => '是否可见',
					'type' => 'bool',
				),
				'tab' => array(
					'name' => 'Tab名称',
					'type' => 'text',
					'maxlength' => 10,
				),
				'title' => array(
					'name' => '标题',
					'type' => 'text',
					'maxlength' => 20,
				),
				'desc' => array(
					'name' => '描述',
					'type' => 'text',
					'maxlength' => 300,
				),
				'links' => array(
					'count' => 2,
					'name' => '链接',
					'vars' => array(
						'title' => array(
							'name' => '标题',
							'type' => 'text',
						),
						'url' => array(
							'name' => '链接',
							'type' => 'url',
						),
					),
				),
				'rightPic' => array(
					'count' => 3,
					'name' => '右侧图片',
					'vars' => array(
						'src' => array(
							'type' => 'image',
							'name' => '图片',
							'width' => 200,
							'height' => 300,
						),
						'title' => array(
							'type' => 'text',
							'name' => '标题',
						),
						'url' => array(
							'type' => 'url',
							'name' => '链接'
						),
					),
				),
			),
		),
	),
	'tpl_content' => <<<HTML
<style>
	.Tpl_MainTab li{width:100px;font-size:16px;height:40px;line-height:40px;text-align:center;border:solid 1px #CCC;}
	.Tpl_MainTab li.on{background: #369;color:#fff;}
</style>
<ul class="Tpl_MainTab g" id="tab_{{_ID_}}">
  {{#for default}}
  	<li key="tab_{{_INDEX_}}_{{_ID_}}">{{.tab}}</li>
  {{#for}}
</ul>
<script>
hapj(function(H){
	var sw = H.ui.id('tab_{{_ID_}}').switchable({
		tag:'li',
		method:'hover',
		map:function(i){
			return H.ui._id(this.getAttribute('key'));
		},
		trigger: function(ts) {
			ts.removeClass('on');
			this.className = 'on';
		},
		target: function(ts) {
			ts.hide();
			this.style.display = 'block';
		}
	});
	sw.first();
});
</script>
{{#for default}}
<div class="g" id="tab_{{_INDEX_}}_{{_ID_}}">
	<div class="g-u" style="width:380px;">
		<h3>{{.title}}</h3>
		<p>{{.desc}}</p>
		<ul>
			{{#for .links}}
			<li><a href="{{.url}}">{{.title}}</a></li>
			{{#for}}
		</ul>
	</div>
	<div class="g-u" style="width:600px;">
		<ul class="g">
			{{#for .rightPic}}
			<li>
				<a href="{{.url}}" title="{{.title}}">
					<img src="{{.src}}" style="width:{{.src:width}}px;height:{{.src:height}}px;" alt="{{.title}}"/>
				</a>
			</li>
			{{#for}}
		</ul>
	</div>
</div>
{{#for}}
HTML
	,
);