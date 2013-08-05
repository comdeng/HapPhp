<?php
class Template2 
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

	const CMD_LEFT_SPLITTER = '{{#';
	const CMD_RIGHT_SPLITTER = '}}';
	const CMD_LEFT_SPLITTER_LEN = 3;
	const CMD_RIGHT_SPLITTER_LEN = 2;

	public static function LoadTemplate($path)
	{
		$arr = include $path;
		$tpl = new Template2();
		foreach($arr as $key => $value) {
			$tpl->$key = $value;
		}
		$tpl->id = self::$minId++;
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
	 * 编译
	 * @param array $data
	 */
	public function Compile($data = null)
	{
		$this->preCompile();
		file_put_contents("a.log", $this->tpl_pre_content);
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

			$ret[] = substr($ctx, 0, $leftPos);
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
					$cmd->PreHandle($this->tpl_vars);
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
	function PreHandle($vars);
	function HandleVar($var);
	function GetNsVars($vars);
}

class TemplateForCommand extends TemplateCommand implements TemplateCommandInterface
{
	function DealResult(array &$ret)
	{
		$slice = array_slice($ret, $this->startIndex);
		for ($i = $this->startIndex, $len = count($ret); $i < $len; $i++) {
			$ret[$i] = $this->replace($ret[$i], '0');
		}
		if ($this->count > 1) {
			for($i = 1; $i < $this->count; $i++) {
				foreach($slice as $s) {
					array_push($ret, $this->replace($s, $i));
				}
			}
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

	function PreHandle($vars)
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
$tpl = Template2::LoadTemplate('templates/tpl1.php');
$ret = $tpl->Compile(array(
	'default@0.links@0.url' => 'http://www.jiehun.com.cn',
	'default@0.links@0.title' => '中国婚博会',
	'default@0.links@1.url' => 'http://www.baidu.com/',
	'default@0.links@1.title' => '百度',
));
var_dump($ret);