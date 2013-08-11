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
	private static $minId = 100;

	const LEFT_SPLITTER = '{{';
	const RIGHT_SPLITTER = '}}';
	const LEFT_SPLITTER_LEN = 2;
	const RIGHT_SPLITTER_LEN = 2;
	const VAR_FLAG = ':';
	const GROUP_FLAG = '.';
	const INDEX_FLAG = '@';
	const COMMAND_FLAG = '#';

	public static function LoadTemplate($path, $data = null)
	{
		$arr = include $path;
		$tpl = new Template();
		foreach($arr as $key => $value) {
			$tpl->$key = $value;
		}
		$tpl->id = self::$minId++;
		$tpl->tpl_data = $data;
		$tpl->init();
		return $tpl;
	}

	private function __construct()
	{

	}

	private function init()
	{
		
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

		$ret[] = '<ul class="tabs">';
		foreach($this->tpl_vars as $groupKey => $group) {
			$ret[] = "<li groupKey=\"group_{$groupKey}\">{$group['name']}</li>";
		}
		$ret[] = '</ul>';

		foreach($this->tpl_vars as $groupKey => $group) {
			$group['key'] = $groupKey;
			$group['idKey'] = 'group_'.$groupKey;
			$group['dataKey'] = $groupKey;
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
		}
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
		$count = $group['count'];
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
				$var['dataKey'] = sprintf("%s%s%d%s%s", $group['dataKey'], self::INDEX_FLAG, $i, self::GROUP_FLAG, $varKey);
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

	/**
	 * 编译
	 * @param array $data
	 */
	public function Compile($data = null)
	{
		$this->preCompile();
		return $this->doCompile($data);
	}

	private function doCompile($data)
	{
		$content = $this->tpl_pre_content;
		$ret = array();
		while($content) {
			$leftPos = mb_strpos($content, self::LEFT_SPLITTER);
			if ($leftPos === false) {
				$ret[] = $content;
				break;
			}
			$rightPos = mb_strpos($content, self::RIGHT_SPLITTER, $leftPos);
			if ($rightPos === false) {
				$ret[] = $content;
				break;
			}
			$ret[] = mb_substr($content, 0, $leftPos);
			$tag = mb_substr($content, $leftPos + self::LEFT_SPLITTER_LEN, $rightPos - $leftPos - 2);
			
			if (!empty($data[$tag])) {
				$ret[] = $data[$tag];
			}
			$content = mb_substr($content, $rightPos + self::RIGHT_SPLITTER_LEN);
		} 
		return implode('', $ret);
	}

	
	private function preCompile()
	{
		if ($this->tpl_pre_content) {
			return;
		}
		$ctx = $this->tpl_content;
		$ret = array();
		$commands = array();
		$cmdStarted = false;
		while($ctx) {
			$leftPos = mb_strpos($ctx, self::LEFT_SPLITTER);
			if ($leftPos === false) {
				$ret[] = $ctx;
				break;
			}
			$rightPos = mb_strpos($ctx, self::RIGHT_SPLITTER);
			if ($rightPos === false) {
				$ret[] = $ctx;
				break;
			}

			$ret[] = mb_substr($ctx, 0, $leftPos);
			$segment = mb_substr($ctx, $leftPos + self::LEFT_SPLITTER_LEN, $rightPos - $leftPos - 2);
			if ($segment[0] == self::COMMAND_FLAG) { //
				$cmdEndPos = mb_strpos($segment, " ");
				if ($cmdEndPos === false) {
					$tag = strtolower(substr($segment, 1));
					if (!$commands) {
						throw new Exception("template.u_errorCloseTag tag=".$tag);
					}
					$lastCommand = array_pop($commands);
					if ($tag != $lastCommand->tag) {
						throw new Exception("template.u_tagNotClosed tag=".$lastTag);
					}

					$lastCommand->DealResult($ret);
					$cmdStarted = false;
				} else {
					$tag = substr($segment, 1, $cmdEndPos - 1);
					$param = trim(substr($segment, $cmdEndPos + 1));
					$cmd = TemplateCommand::GetCommand($tag, count($ret), $param);
					if ( $len = count($commands) ) {
						$cmd->parent = $commands[$len - 1];
					}
					$cmd->PreHandle($this->tpl_vars, $this->tpl_data);
					$commands[] = $cmd;
					$cmdStarted = true;
				}
			} else {
				if ($cmdStarted) {
					$lastCommand = $commands[count($commands) - 1];

					$varPos = strpos($segment, self::VAR_FLAG);
					if ($varPos !== false) {
						if (substr($segment, 0, 1) == self::GROUP_FLAG) {
							$segment = substr($segment, 1);
							$vars = $lastCommand->GetNsVars($this->tpl_vars);
							$vars = $vars['vars'];
						} else {
							$vars = $this->tpl_vars;
						}

						list($ns, $varName) = explode(self::VAR_FLAG, $segment);
						$ns = explode(self::GROUP_FLAG, $ns);
						for($i = 0, $len = count($ns); $i < $len; $i++) {
							if (!isset($vars[$ns[$i]])) {
								throw new Exception('template.u_varNotFound');
							}
							$vars = $vars[$ns[$i]];
							if ($i < $len - 1) {
								if (!isset($vars['vars'])) {
									throw new Exception('template.u_varNotFound');
								}
								$vars = $vars['vars'];
							}
						}
						$ret[] = $vars[$varName];
					} else {
						$ret[] = self::LEFT_SPLITTER.$lastCommand->HandleVar($segment). self::RIGHT_SPLITTER;
					}
				} else {
					$ret[] = self::LEFT_SPLITTER.$lastCommand->HandleVar($segment). self::RIGHT_SPLITTER;
				}
			}
			$ctx = mb_substr($ctx, $rightPos + self::RIGHT_SPLITTER_LEN);
		}
		$this->tpl_pre_content = implode('', $ret);
	}
}

class TemplateCommand
{
	var $tag;
	var $startIndex;
	var $param;
	var $parent;
	var $id;
	var $ns;

	private static $commandId = 100;

	/**
	 *
	 * @return TemplateCommand 命令对象
	 */
	public static function GetCommand($cmd, $startIndex, $param)
	{
		$className = "Template".ucfirst($cmd)."Command";
		$instance = new $className();
		$instance->tag = $cmd;
		$instance->startIndex = $startIndex;
		$instance->param = $param;
		$instance->id = self::$commandId++;
		return $instance;
	}
}

interface TemplateCommandInterface
{
	function DealResult(array &$ret);
	function PreHandle($vars, $data);
	function HandleVar($var);
	function GetNsVars($vars);
}

class TemplateForCommand extends TemplateCommand implements TemplateCommandInterface
{
	private $visibles = null;
	function DealResult(array &$ret)
	{
		$slice = array_slice($ret, $this->startIndex);
		for ($i = $this->startIndex, $len = count($ret); $i < $len; $i++) {
			$ret[$i] = $this->replace($ret[$i], '0');
		}
		if ($this->count > 1) {
			for($i = 1; $i < $this->count; $i++) {
				if ($this->visibles && $this->visibles[$i]) {
					foreach($slice as $s) {
						array_push($ret, $this->replace($s, $i));
					}
				}
			}
		}
		// 如果一个也不显示，则直接去掉
		if ($this->visibles && !$this->visibles[0]) {
			$ret = array_slice($ret, 0, $this->startIndex);
		}
	}

	private function replace($orig, $replace)
	{
		if (strpos($orig , "{{") === false) {
			return $orig;
		}
		$orig = str_replace("{{_INDEX_}}", $replace, $orig);
		$orig = str_replace("{{{$this->id}.index}}", $replace, $orig);
		return $orig;
	}

	function PreHandle($vars, $data)
	{
		// 处理命名空间的问题
		$namespace = array($this->param.'@'.'{{'.$this->id.'.index}}');
		$ns = array($this->param);
		if (substr($namespace[0], 0, 1) == '.') {
			$ns[0] = substr($ns[0], 1);
			$parent = $this->parent;
			while($parent) {
				if ($parent->tag != 'for') {
					break;
				}
				array_unshift($namespace, $parent->param.'@'.'{{'.$parent->id.'.index}}');
				array_unshift($ns, $parent->param);
				if ($parent->param[0] != '.') {
					break;
				}
				$ns[0] = substr($ns[0], 1);
				$parent = $parent->parent;
			}
		}
		$this->namespace = implode('', $namespace);
		$this->ns = $ns;

		$vars = $this->GetNsVars($vars);
		$this->count = isset($vars['count']) ? intval($vars['count']) : 1;
		if ($this->count < 1) {
			$this->count = 1;
		}
		
		$hasVisibleVar = isset($vars['vars']['visible']) ? true : false;
		$visibles = array();
		if ($hasVisibleVar) {
			// 检查变量中是否有visible
			for($i = 0; $i < $this->count; $i++) {
				$key = $this->namespace.'@'.$i.'.visible';
				var_dump($nam);
				$visibles[$i] = isset($data[$key]) ? $data[$key] == 1 : true;
			}
		}
		$this->visibles = $visibles;
	}

	function GetNsVars($vars)
	{
		foreach($this->ns as $i => $n) {
			if ($i > 0) {
				$vars = $vars['vars'];
			}
			if (!isset($vars[$n])) {
				throw new Exception('template.u_varNotDefined n='.$n.' ns='.implode('.', $ns));
			}
			$vars = $vars[$n];
			if (!isset($vars['vars'])) {
				throw new Exception('template.u_varsParamNotDefined');
			}
		}
		return $vars;
	}

	function HandleVar($var)
	{
		if ($var[0] == '.') {
			return $this->namespace.$var;
		}
		return $var;
	}
}

// test
//$tpl = Template::LoadTemplate('templates/tpl1.php');
// $ret = $tpl->Compile(array(
// 	'default@0.links@0.url' => 'http://www.jiehun.com.cn',
// 	'default@0.links@0.title' => '中国婚博会',
// 	'default@0.links@1.url' => 'http://www.baidu.com/',
// 	'default@0.links@1.title' => '百度',
// ));
//echo $tpl->Edit('/tpl/_edit');