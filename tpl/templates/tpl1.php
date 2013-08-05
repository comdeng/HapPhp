<?php
return array(
	'tpl_name' => '模板2',
	'tpl_key' => 'tpl1',
	'tpl_vars' => array(
		'default' => array(
			'name' => '默认',
			'count' => 4,
			'vars' => array(
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
<ul>
  {{#for default}}
  	<li key="tab_{{_INDEX_}}">{{.tab}}</li>
  {{#for}}
</ul>
{{#for default}}
<div class="g" id="tab_{{_INDEX_}}">
	<div class="g-u">
		<h3>{{.title}}</h3>
		<p>{{.desc}}</p>
		<ul>
			{{#for .links}}
			<li><a href="{{.url}}">{{.title}}</a></li>
			{{#for}}
		</ul>
	</div>
	<div class="g-u">
		<ul class="g">
			{{#for .rightPic}}
			<li>
				<a href="{{.url}}" title="{{.title}}">
					<img src="{{.src}}" style="width:{{.src:width}}px;height:{{.src:width}}px;" alt="{{.title}}"/>
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