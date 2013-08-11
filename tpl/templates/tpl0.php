<?php
return array(
	'tpl_name' => '版式1',
	'tpl_key'  => 'tpl0',
	'tpl_vars' => array(
		'leftPic' => array(
			'name' => '左图',
			'vars' => array(
				'title' => array(
					'type' => 'text',
					'name' => '左图标题',
					'maxlength' => 20,
				),
				'src' => array(
					'type' => 'image',
					'name' => '左图图片',
					'width' => 200,
					'height' => 160,
				),
				'url' => array(
					'type' => 'url',
					'name' => '左图链接',
					'maxlength' => 20,
				),
				'desc' => array(
					'type' => 'text',
					'name' => '左图描述',
					'maxlength' => 200,
				),
			)
		),
		'rightHeadPic' => array(
			'name' => '右图1',
			'count' => 2,
			'vars' => array(
				'title' => array(
					'type' => 'text',
					'name' => '右图1标题',
					'maxlength' => 20,
				),
				'src' => array(
					'type' => 'image',
					'name' => '右图1图片',
					'width' => 200,
					'height' => 120,
				),
				'url' => array(
					'type' => 'url',
					'name' => '右图1链接',
					'maxlength' => 200,
				),
			),
		),
		'rightFootPic' => array(
			'name' => '右图3',
			'vars' => array(
				'title' => array(
					'type' => 'text',
					'name' => '右图3标题',
					'maxlength' => 20,
				),
				'src' => array(
					'type' => 'image',
					'name' => '右图3图片',
					'width' => 400,
					'height' => 120,
				),
				'url' => array(
					'type' => 'url',
					'name' => '右图3链接',
					'maxlength' => 200,
				),
			),
		),		
	),
	'tpl_content' => <<<HTML
<div class="g" style="width:840px;">
	<div class="g-u" style="width:200px;margin:10px;">
		<a href="{{leftPic.url}}"><img src="{{leftPic.src}}" alt="{{leftPic.title}}" style="width:{{leftPic.src:width}}px;height:{{leftPic.src:height}}px;"/></a>
		<h3><a href="{{leftPic.url}}">{{leftPic.title}}</a></h3>
		<p>{{leftPic.desc}}</p>
	</div>
	<div class="g-u" style="width:600px;margin:10px;">
		<ul class="g">
			<li class="g-1-2">
				<a href="{{rightHeadPic@0.url}}"><img src="{{rightHeadPic@0.src}}" alt="{{rightHeadPic@0.title}}" style="width:{{rightHeadPic.src:width}}px;height:{{rightHeadPic.src:height}}px;"></a>
				<h3>{{rightHeadPic@0.title}}</h3>
			</li>
			<li class="g-1-2">
				<a href="{{rightHeadPic@1.url}}"><img src="{{rightHeadPic@1.src}}" alt="{{rightHeadPic@1.title}}" style="width:{{rightHeadPic.src:width}}px;height:{{rightHeadPic.src:height}}px;"></a>
				<h3>{{rightHeadPic@1.title}}</h3>
			</li>
		</ul>
		<div>
			<a href="{{rightFootPic.url}}"><img src="{{rightFootPic.src}}" alt="{{rightFootPic.title}}" style="width:{{rightFootPic.src:width}}px;height:{{rightFootPic.src:height}}px;"></a>
			<h3>{{rightFootPic.title}}</h3>
		</div>
	</div>
</div>
HTML
);