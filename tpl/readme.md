模板说明文档
======================

模板基本格式
--------------

每一个模板目前定义为一个php文件，返回一个模板数组，其数组的基本键值定义如下：

tpl_name 	模板名称
tpl_key  	模板唯一的key
tpl_vars 	模板的变量定义
tpl_content	模板的内容

## tpl_vars
是一个定义模板使用的变量的类型、参数等的数组。
其键为一个分组的key，在tpl_vars中需要定义至少一个分组。

以下的讨论以如下一段代码作为例子介绍：
tpl_vars => array(
	'default' => array(
		'name' => '默认',
		'count' => 4,
		'vars' => array(
			'left' => array(
				'name' => '左边',
				'vars' => array(
					'title' => array(
						'type' => 'text',
						'name' => '标题',
					),
					'url' => array(
						'type' => 'url',
						'name' => '链接'
					),
					'img' => array(
						'type' => 'image',
						'name' => '图片',
						'width' => 200,
						'height' => 300,
					),
				),
			),
			'rightDesc' => array(
				'name' => '右侧描述',
				'type' => 'text',
				'maxlength' => 300,
			),
		),
	),
)

每个分组的默认属性包括有：
<table>
	<tr>
		<td>name</td>
		<td>必须</td>
		<td>组的中文名称</td>
	</tr>
	<tr>
		<td>count</td>
		<td>可选</td>
		<td>组变量循环最大次数。默认为1</td>
	</tr>
	<tr>
		<td>vars</td>
		<td></td>
		<td>组的变量</td>
	</tr>
</table>

这里需要对vars的定义做详细说明。vars也为一个数组，其成员可以是一个变量，或是一个组，判断标志就是它是否含有vars这个键，有的话，它就是一个组，定义 和组一样；否则是一个变量。
下面详细描述变量的定义规则：
<table>
	<tr>
		<td>name</td>
		<td>必须</td>
		<td>变量的中文名称</td>
	</tr>
	<tr>
		<td>type</td>
		<td>必须</td>
		<td>变量类型。目前支持如下几种：text、image、url、bool。</td>
	</tr>
</table>

下面对变量类型做进一步的说明
<table>
	<tr>
		<td>text</td>
		<td>文本</td>
		<td>
			<dl>
				<dt>maxlength</dt>
				<dd>最大长度，如果超过200，编辑时会使用textarea。</dd>
			</dl>
		</td>
	</tr>
	<tr>
		<td>image</td>
		<td>图片</td>
		<td>
			<dl>
				<dt>width</dt>
				<dd>图片宽度</dd>
				<dt>height</dt>
				<dd>图片高度</dd>
			</dl>
		</td>
	</tr>
	<tr>
		<td>url</td>
		<td>链接</td>
		<td>
			<dl>
				<dt>maxlength</dt>
				<dd>最大长度。可选，默认为20。</dd>
			</dl>
		</td>
	</tr>
	<tr>
		<td>bool</td>
		<td>布尔值</td>
		<td>
			目前仅有一个特殊的键使用此类型:<strong>_VISIBLE_</strong>。这个变量主要用来定义组内的某个版块是否显示。
		</td>
	</tr>
</table>

## tpl_content
模板内容。模板主体使用html，结合css和js，并加以模板变量、常量及命令。

模板变量和常量的格式为{{var_key}}加以区分，如果是命令，其格式为{{#command}}。

### 变量的使用
变量分为普通变量和特殊变量两种。

1、普通变量
普通变量由键值空间及符号组成，具体而言，可见如下示例：


前面的变量定义中，如果在模板中要使用到default组下第2个元素、left组下的title，则需要如下定义：
{{default$1.left$0.title}}

从上可知$是表示索引的标识符，.是表示分组切换的标识符。

如果通过之前的语境，能判断命名空间在default$1.left$0，那么可以直接使用相对命名空间的表示：
{{.title}}
从上可知，直接使用.，是表示使用一个相对命名空间。


2、特殊变量
目前支持两个特殊变量：
  1) _INDEX_ 这个表示for循环时的索引；
  2) _ID_ 这个表示自动为每隔模板分配的id；

### 常量的使用
常量是相对而言的，其实也是对变量设定的变量。比如，我们一个图片时，希望用到变量定义中使用的图片高度，可以这么定义：
{{default$1.left$0.img:width}}

可见，:符号将上述表达变成对width这个tpl_vars中定义的变量的直接引用。由于tpl_vars中定义的变量都是固定的，因此在这里叫做常量。


### 命令的使用
目前的命令仅支持for命令，即循环。
如果我们要将default分组循环，则可以表示为：
<ul>
{{#for default}}
	<li>
		<div class="g">
			<ul class="g-u">
				{{#for .left}}
				<li><a href="{{.url}}"><img alt="{{.title}}" style="width:{{.img#width}}px;height:{{.img#height}}px;"/></a></li>
				{{#for}}
			</ul>
			<p class="g-u">
				{{.rightDesc}}
			</p>
		</div>
	</li>
{{#for}}
</ul>

从上述例子我们可以归纳：
for命令的基本格式，for+空格+命名空间 开始，以{{#for}}结束，和所有的命名空间一样，支持绝对的和相对的。
for的命名空间，直接决定在其之内的变量、常量的命名空间。