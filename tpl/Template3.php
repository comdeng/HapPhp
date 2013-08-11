<?php
class Template
{
	var $tpl_name;
	var $tpl_key;
	var $tpl_vars;
	var $tpl_content;
	var $tpl_data;
	private $tpl_pre_content;

	var $id;
	private static $minId;

	public static function LoadTemplate($path, $data = null)
	{
		if (!self::$minId) {
			self::$minId = time().rand(1000, 9999);
		}

		$arr = include $path;
		$tpl = new Template();
		foreach($arr as $key => $value) {
			$tpl->$key = $value;
		}
		$tpl->id = self::$minId++;
		$tpl->tpl_data = $data;
		return $tpl;
	}

	public function Compile(array $data)
	{
		$root = new TemplateRootNode();
		$root->Orig = $this->tpl_content;
		$root->Id = $this->id;
		$root->Compile();
		return $root->Render($this->tpl_vars, $data);
	}

	/**
	 * 编辑商家信息
	 * @param array | null $data 数据
	 */
	public function Edit($postUrl, $className="TplContainer")
	{
		$ret = array();
		$divId = 'tpl_div_'.$this->id;
		$ret[] = '<div id="'.$divId.'" class="'.$className.'_Div">';
		$ret[] = $this->Compile($this->tpl_data ? $this->tpl_data : array());
		$ret[] = '</div>';
		$formId = 'tpl_form_'.$this->id;
		$ret[] = '<form id="'.$formId.'" method="post" action="'.$postUrl.'" class="'.$className.'_Form">';
		$ret[] = '<input type="hidden" name="tpl_key" value="'.$this->tpl_key.'"/>';
		$ret[] = '<input type="hidden" name="tpl_action" value="preview"/>';

		$ret[] = '<ul class="tabs g">';
		foreach($this->tpl_vars as $groupKey => &$group) {
			$group['key'] = $groupKey;
			$group['idKey'] = 'group_'.$this->id.'_'.$groupKey;
			$group['dataKey'] = $groupKey;
			$ret[] = "<li Key=\"{$group['idKey']}\">{$group['name']}</li>";
		}
		unset($group);
		$ret[] = '</ul>';

		foreach($this->tpl_vars as $groupKey => $group) {
			$this->renderGroup(0, $group, $ret);
		}

		$ret[] = '<div><input type="submit" value="预览" onclick="this.form.tpl_action.value=\'preview\'"/> <input type="submit" value="保存" onclick="this.form.tpl_action.value=\'save\'"/></div>';
		$ret[] = '</form>';
		$ret[] = <<<JSCODE
<script>
hapj(function(H) {
	H.com('verify').active('{$formId}', {
		'ok': function(data){
			H.ui.id('{$divId}').html(data.html);
			
			if (this['tpl_action'].value == 'save') {
				this.style.display = 'none';
				H.ui.dialog.ok('保存成功');
			}
		}
	});
	H.ui.id('{$divId}').on('click', function(){
		var form = this.nextSibling;
		if (form.style.display != 'block') {
			form.style.display = 'block';
			H.ui(form).floatable().center().middle();
		} else {
			form.style.display = 'none';
		}
		return false;
	});

	var form = H.ui.id('{$formId}');
	var sw = form.cls('tabs').switchable({
		tag:'li',
		map:function(i) {
			console.log(H.ui._id(this.getAttribute('key')));
			return H.ui._id(this.getAttribute('key'));
		},
		trigger:function(ts) {
			ts.removeClass('on');
			this.className = 'on';
		},
		target:function(ts){
			ts.hide();
			this.style.display = 'block';
		}
	});
	sw.first();

	form.cls('group_tabs').on('change', function(){
		var id = this.value;
		var pid = this.getAttribute('key') + '_dd';
		H.ui.id(pid).childs().hide();
		H.ui.id(id).show();
	}).selectable({
		selectedClassName:'on',
		showEvent:'mouseenter',
		hideDelayTime:10
	});
})
</script>
JSCODE;

		return implode('', $ret);
	}

	/**
	 * 渲染组
	 * @param  int $level	组的深度
	 * @param  array $group    组
	 * @param array $ret 渲染html的数组
	 */
	private function renderGroup($level, $group, array &$ret)
	{
		$width = 600 - $level * 80;
		$count = isset($group['count']) ? intval($group['count']) : 1;
		$ret[] = "<dl id=\"{$group['idKey']}\" class=\"g\">";
		$ddWidth = $width;
		if ($count > 1) {
			$ret[] = '<dt style="width:80px" class="group_dropdown">';
			$ddWidth = $width - 80;
			$ret[] = '<select class="group_tabs " key="'.$group['idKey'].'" autocomplete="off">';
			for($i = 0; $i < $group['count']; $i++) {
				$num = $i+1;
				$ret[] = "<option value=\"{$group['idKey']}_{$i}\">{$group['name']}{$num}</option>";
			}
			$ret[] = '</select>';
			$ret[] = '<b></b></dt>';
		}
		$ret[] = '<dd style="width:'.$ddWidth.'px;" id="'.$group['idKey'].'_dd">';
		for($i = 0; $i < $count; $i++) {
			$ret[] = "<div id=\"{$group['idKey']}_{$i}\">";
			foreach($group['vars'] as $varKey => $var) {
				$var['dataKey'] = sprintf("%s%s%d%s%s", $group['dataKey'], TemplateNode::INDEX_FLAG, $i, TemplateNode::GROUP_FLAG, $varKey);
				$var['idKey'] = "{$group['idKey']}_{$i}_{$varKey}";
				if (isset($var['vars'])) {
					$this->renderGroup($level + 1, $var, $ret);
				} else {
					$this->renderItem($var, $ret);
				}
			}
			$ret[] = '</div>';
		}
		$ret[] = '</dd>';
		$ret[] = '</dl>';
	}

	private function renderItem($item, array &$ret)
	{
		$type = $item['type'];
		switch($type) {
			case 'text':
				$this->getTextElem($item['dataKey'], $item, $ret);
			break;
			case 'image':
				$this->getImageElem($item['dataKey'], $item, $ret);
			break;
			case 'url':
				$this->getUrlElem($item['dataKey'], $item, $ret);
			break;
			case 'bool':
				$this->getBoolElem($item['dataKey'], $item, $ret);
			break;
			default:
			throw new Exception('tempate.u_typeNotSupported type='.$type);
			break;
		}
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getTextElem($key, $var, array &$ret)
	{
		
		$ret[] = '<div class="group_item"><label>'.(isset($var['name']) ? $var['name'] : '文本').'</label>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 20;
		if ($maxlength <= 20) {
			$maxlength = 20;
		}
		if ($maxlength < 150) {
			$ret[] = '<input type="text" maxlength="'.$maxlength.'" name="data['.$key.']" style="width:'.($maxlength*12).'px;" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/>';
		} else {
			$ret[] = '<textarea maxlength="'.$maxlength.'" name="data['.$key.']" style="width:400px;height:100px;">'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'</textarea>';
		}
		
		$ret[] = '</div>';
	}

	/**
	 * 获取单选元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getBoolElem($key, $var, array &$ret)
	{
		
		$ret[] = '<div class="group_item"><label>'.(isset($var['name']) ? $var['name'] : '是否').'</label>';
		$checked = !isset($this->tpl_data[$key]) ? -1 : intval($this->tpl_data[$key]);
		if ($checked > 0 || $checked < -1) {
			$checked = 1;
		}
		$ret[] = '<input type="radio" name="data['.$key.']" value="1"'.($checked === 1 ? ' checked="checked"' : '').'/>&nbsp;是&nbsp;&nbsp;&nbsp;';
		$ret[] = '<input type="radio" name="data['.$key.']" value="0"'.($checked === 0 ? ' checked="checked"' : '').'/>&nbsp;否';
		
		$ret[] = '</div>';
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getUrlElem($key, $var, array &$ret)
	{
		$ret[] = '<div class="group_item"><label>'.(isset($var['name']) ? $var['name'] : '网址').'</label>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 100;
		if ($maxlength <= 100) {
			$maxlength = 100;
		}
		
		$ret[] = '<input type="text" maxlength="'.$maxlength.'" verify-rule="{url:\'网址格式不正确\'}"  name="data['.$key.']" style="width:240px;" placeholder="http://" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/>';
		$ret[] = '</div>';
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getImageElem($key, $var, array &$ret)
	{
		$imgId = 'tpl_form_'.$this->id.'_'.$key;
		$ret[] = '<div class="group_item"><label>'.(isset($var['name']) ? $var['name'] : '图片').'</label>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 100;
		if ($maxlength <= 100) {
			$maxlength = 100;
		}
		
		$ret[] = '<input type="hidden" name="data['.$key.']" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/><span id="'.$imgId.'"></span>';
// 		$ret[] = <<<JSCODE
// <script>
// hapj(function(H) {
// 	H.com('upload').active('{$imgId}').setCallback(function(img){
// 		H.ui.id('{$imgId}').prev().attr('value', img.id)
// 	});
// })
// </script>
// JSCODE;
		$ret[] = '</div>';
	}
}

class TemplateNamespace
{
	var $Absolute = true;
	var $List = array();

	public function AddChild($name, $index)
	{
		$this->List[] = array($name, $index);
		return $this;
	}

	public function GetAbsoluteNamespace($pNs)
	{
		if (!$pNs->Absolute) {
			throw new Exception("tplNs.parentNsMustBeAbsolute");
		}
		if ($this->Absolute) {
			return $this->Copy();
		}
		$ns = $this->Copy();
		$ns->List = array_merge($pNs->List, $ns->List);
		$ns->Absolute = true;
		return $ns;
	}

	public function Copy()
	{
		$ns = new TemplateNamespace();
		$ns->Absolute = $this->Absolute;
		foreach($this->List as $key=>$value) {
			$ns->List[$key] = $value;
		}
		return $ns;
	}

	public static function GetNamespaceByString($str)
	{
		$absolute = true;
		switch ($str[0]) {
			case TemplateNode::GROUP_FLAG:
				$absolute = false;
				$str = substr($str, 1);
				break;
			case TemplateNode::SYSTEM_FLAG:
				$absolute = false;
				break;
			default:
				break;
		}
		$ns = new TemplateNamespace();
		$ns->Absolute = $absolute;
		$arr = explode(TemplateNode::GROUP_FLAG, $str);
		foreach($arr as $a) {
			$pos = strpos($a, TemplateNode::INDEX_FLAG);
			if ($pos !== false) {
				$name = substr($a, 0, $pos);
				$index = intval(substr($a, $pos + 1));
			} else {
				$name = $a;
				$index = 0;
			}
			$ns->List[] = array($name, $index);

		}

		return $ns;
	}

	public function GetKey($pNs = null)
	{
		$ret = array();
		if ($pNs && !$this->Absolute) {
			foreach($pNs->List as $ns) {
				$ret[] = $ns[0].TemplateNode::INDEX_FLAG.$ns[1];
			}
		}

		foreach($this->List as $ns) {
			$ret[] = $ns[0].TemplateNode::INDEX_FLAG.$ns[1];
		}
		if ( ($len = count($ret)) > 0) {
			$ret[$len - 1] = substr($ret[$len - 1], 0, -2);
		}
		return implode(TemplateNode::GROUP_FLAG, $ret);
	}

	/**
	 * 获取变量
	 * @param  [type] $vars   [description]
	 * @param  [type] $parent [description]
	 * @return [type]         [description]
	 */
	public function GetVar($vars, $parent = null)
	{
		// 绝对命名空间不需要父命名空间
		if ($this->Absolute && $parent !== null) {
			throw new Exception('tplNs.absoluteNamespace');
		}

		$vs = $vars;
		if ($parent) {
			$list = array_merge($parent->List, $this->List);
		} else {
			$list = $this->List;
		}
		for($i = 0, $len = count($list); $i < $len; $i++) {
			$ns = $list[$i];
			if (!isset($vs[$ns[0]])) {
				throw new Exception('tplNs.nsNotExist');
			}
			$vs = $vs[$ns[0]];
			if ($i < $len - 1) {
				if (!isset($vs['vars'])) {
					throw new Exception('tplNs.nsNotExist');
				}
				$vs = $vs['vars'];
			}
		}
		return $vs;
	}

	public function SetIndex($index)
	{
		if (!empty($this->List)) {
			$this->List[count($this->List) - 1][1] = $index;
			return true;
		}
		return false;
	}

	public function GetIndex()
	{
		if (empty($this->List)) {
			return 0;
		}
		return $this->List[count($this->List) - 1][1];
	}

	
}

abstract class TemplateNode
{
	var $Tag;
	var $Status;
	var $Orig;
	var $Id;
	var $Root;
	protected $namespace;
	protected $id;
	protected $vars;
	protected $isCommand = false;

	// 父节点
	protected $parent;
	// 子节点
	protected $childs = array();
	protected $childNum = 0;

	const COMMAND_START = 1;
	const COMMAND_END = 2;

	const LEFT_SPLITTER = '{{';
	const RIGHT_SPLITTER = '}}';
	const LEFT_SPLITTER_LEN = 2;
	const RIGHT_SPLITTER_LEN = 2;
	const VAR_FLAG = ':';
	const GROUP_FLAG = '.';
	const INDEX_FLAG = '@';
	const COMMAND_FLAG = '#';
	const SYSTEM_FLAG = '_';
	const INDEX_KEY = '_INDEX_';
	const ID_KEY = '_ID_';
	const VISIBLE_KEY = '_VISIBLE_';

	public function IsCommand()
	{
		return $this->isCommand;
	}

	protected function AddChildNode(TemplateNode $node)
	{
		$node->parent = $this;
		array_push($this->childs, $node);
		$this->childNum++;
	}

	protected function getLastChild()
	{
		return $this->childNum > 0 ? $this->childs[$this->childNum - 1] : false;
	}

	public function AddCommandNode($tag, $param)
	{
		$nodeName = 'Template'.ucfirst($tag).'Node';
		if (!class_exists($nodeName)) {
			throw new Exception('template.commandNodeNotFound tag='.$tag);
		}

		$node = new $nodeName($param);
		if (!$node->IsCommand()) {
			throw new Exception('template.nodeIsNotCommandType className='.$nodeName);
		}
		$node->Status = TemplateNode::COMMAND_START;
		$this->AddChildNode($node);
		return $node;
	}


	public function Struct($level = 0)
	{
		$ret = array();
		foreach($this->childs as $child) {
			$ret[] = str_repeat('**', $level * 2). '-'.$child->Tag;
			$ret[] = $child->Struct($level + 1);
		}
		return implode("\n", $ret);
	}

}

class TemplateRootNode extends TemplateNode
{
	var $Tag = 'root';
	protected $isCommand = false;

	public function Compile()
	{
		$ctx = $this->Orig;
		$currentNode = $this;
		while($ctx) {
			$leftPos = mb_strpos($ctx, TemplateNode::LEFT_SPLITTER);
			if ($leftPos === false) {
				$currentNode->AddChildNode(new TemplateStringNode($ctx));
				break;
			}
			$rightPos = mb_strpos($ctx, TemplateNode::RIGHT_SPLITTER);
			if ($rightPos === false) {
				$currentNode->AddChildNode(new TemplateStringNode($ctx));
				break;
			}
			$currentNode->AddChildNode(new TemplateStringNode(mb_substr($ctx, 0, $leftPos)));
			$segment = mb_substr($ctx, $leftPos + TemplateNode::LEFT_SPLITTER_LEN, $rightPos - $leftPos - 2);

			if ($segment[0] == TemplateNode::COMMAND_FLAG) { 
				$cmdEndPos = mb_strpos($segment, ' ');
				if ($cmdEndPos === false) {
					$tag = strtolower(substr($segment, 1));
					if ($tag != $currentNode->Tag) {
						throw new Exception("template.u_tagNotClosed tag=".$currentNode->Tag);
					}
					$currentNode->Status = TemplateNode::COMMAND_END;
					$currentNode = $currentNode->parent;
				} else {
					$tag = substr($segment, 1, $cmdEndPos - 1);
					$param = trim(substr($segment, $cmdEndPos + 1));
					$node = $currentNode->AddCommandNode($tag, $param);
					$node->Root = $this;
					$currentNode = $node;
				}
			} else {
				$var = new TemplateVarNode($segment);
				$var->Root = $this;
				$currentNode->AddChildNode($var);
			}
			$ctx = mb_substr($ctx, $rightPos + TemplateNode::RIGHT_SPLITTER_LEN);
		}
	}

	public function Render(array $vars, $data)
	{
		$ret = array();
		$pNs = new TemplateNamespace();
		foreach($this->childs as $child) {
			$ret[] = $child->Render($vars, $data, $pNs);
		}
		return implode('', $ret);
	}

	
}

class TemplateVarNode extends TemplateNode
{
	var $Tag = 'var';
	protected $isCommand = false;

	private $isConst = false;
	private $constKey;

	public function __construct($varName)
	{
		$this->Orig = $varName;

		if ( ($varPos = strpos($this->Orig, TemplateNode::VAR_FLAG)) !== false ) {
			$nsStr = substr($this->Orig, 0, $varPos);
			$constKey = substr($this->Orig, $varPos+1);
			$this->namespace = TemplateNamespace::GetNamespaceByString($nsStr);
			$this->isConst = true;
			$this->constKey = $constKey;
		} else {
			$this->namespace = TemplateNamespace::GetNamespaceByString($this->Orig);
		}
	}


	public function Render(array $vars, $data, $pNs)
	{
		if ($this->Orig == TemplateNode::INDEX_KEY) {
			return $pNs->GetIndex();
		} else if ($this->Orig == TemplateNode::ID_KEY) {
			return $this->Root->Id;
		}
		$ns = $this->namespace->GetAbsoluteNamespace($pNs);	
		if ($this->isConst) {
			$var = $ns->GetVar($vars);
			return isset($var[$this->constKey]) ? $var[$this->constKey] : '';
		}
		$varKey = $ns->GetKey($pNs);
		return isset($data[$varKey]) ? $data[$varKey] : '';
	}
}

class TemplateStringNode extends TemplateNode
{
	protected $isCommand = false;

	public function __construct($str)
	{
		$this->Tag = 'string';
		$this->Orig = $str;
	}

	public function Render(array $vars, $data, $pNs)
	{
		return $this->Orig;
	}
}

class TemplateForNode extends TemplateNode
{
	var $Tag = 'for';
	protected $isCommand = true;

	private $rendered = false;
	private $count = null;
	private $checkVisible = false;

	public function __construct($param)
	{
		$this->Orig = $param;
	}
	public function Render(array $vars, $data, $pNs)
	{
		$ns = false;
		if ($this->count === null) {
			$this->namespace = TemplateNamespace::GetNamespaceByString($this->Orig);
			$ns = $this->namespace->GetAbsoluteNamespace($pNs);

			$var = $ns->GetVar($vars);
			$count = isset($var['count']) ? intval($var['count']) : 1;
			if ($count < 1) {
				$count = 1;
			}

			$this->count = $count;

			// 设置是否需要检查可见性
			if (isset($var['vars'][TemplateNode::VISIBLE_KEY])) {
				$this->checkVisible = true;
			}
		}
		if (!$ns) {
			$ns = $this->namespace->GetAbsoluteNamespace($pNs);
		}


		$ret = array();
		for($i = 0; $i < $this->count; $i++) {
			$ns->SetIndex($i);
			if ($this->checkVisible) {
				$vNs = $ns->Copy();
				$vNs->AddChild(TemplateNode::VISIBLE_KEY, 0);
				$key = $vNs->GetKey();
				// 如果已经设置不显示，则不予渲染
				if (isset($data[$key]) && !$data[$key]) {
					continue;
				}
			}
			foreach($this->childs as $key => $child) {
				$ret[] = $child->Render($vars, $data, $ns);
			}
		}
		return implode('', $ret);
	}
}

// $tpl = Template::LoadTemplate('templates/tpl1.php');
// $ret = $tpl->Compile(array(
// 	'default@0.links@0.url' => 'http://www.jiehun.com.cn',
// 	'default@0.links@0.title' => '中国婚博会',
// 	'default@0.links@1.url' => 'http://www.baidu.com/',
// 	'default@0.links@1.title' => '百度',
// ));
// //echo $ret;
// echo $tpl->Edit('/tpl/_edit');