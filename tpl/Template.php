<?php
class Template
{
	var $tpl_name;
	var $tpl_key;
	var $tpl_vars;
	var $tpl_content;
	var $tpl_data;

	var $formId;
	private static $minFormId = 100;

	const LEFT_SPLITTER = '{{';
	const RIGHT_SPLITTER = '}}';
	const LEFT_SPLITTER_LEN = 2;
	const RIGHT_SPLITTER_LEN = 2;

	const VAR_FLAG = '.';
	const GROUP_FLAG = ':';
	const INDEX_FLAG = '@';

	/**
	 * 从文件载入一个模板
	 * @param string $path
	 * @param array $data 数据
	 */
	public static function LoadTemplate($path, $data = null) 
	{
		$arr = include_once $path;
		$template = new Template();
		foreach($arr as $key => $value) {
			$template->$key = $value;
		}
		$template->formId = self::$minFormId++;
		$template->formatVars();
		$template->tpl_data = $data;
		return $template;
	}

	private function formatVars()
	{
		$vars = array(
			0 => array(
				'name' => '未分组',
				'vars' => array(),
			),
		);
		foreach($this->tpl_vars as $key => $value) {
			if (!isset($value['vars'])) {
				$vars[0]['vars'][] = $value;
			} else {
				$grpName = isset($value['name']) ? $value['name'] : '分组';
				if (isset($value['count']) && $value['count'] > 1) {
					for($i = 0; $i < $value['count']; $i++) {
						$k = $key.self::INDEX_FLAG.$i;
						$vars[$k] = array(
							'name' => $grpName,
							'vars' => $value['vars'],
						);
					}
				} else {
					$vars[$key] = array(
						'name' => $grpName,
						'vars' => $value['vars'],
					);
				}
			}
		}
		if (empty($vars[0]['vars'])) {
			unset($vars[0]);
		}
		$this->tpl_groups = $vars;
	}

	public function Edit($postUrl, $className="TplContainer")
	{
		$ret = array();
		$divId = 'tpl_div_'.$this->formId;
		$ret[] = '<div id="'.$divId.'" class="'.$className.'_Div">';
		$ret[] = $this->Compile($this->tpl_data ? $this->tpl_data : array());
		$ret[] = '</div>';
		$formId = 'tpl_form_'.$this->formId;
		$ret[] = '<form id="'.$formId.'" method="post" action="'.$postUrl.'" class="'.$className.'_Form">';
		$ret[] = '<input type="hidden" name="tpl_key" value="'.$this->tpl_key.'"/>';
		$ret[] = '<input type="hidden" name="tpl_action" value="preview"/>';
		
		$groups = array();
		$tabs = array();
		foreach($this->tpl_groups as $key=>$value) {
			$tabs[] = '<li key="tpl_group_'.$this->formId.'_'.$key.'">'.$value['name'].'</li>';
			$groups[$key] = array();
			$groups[$key][] = '<table id="tpl_group_'.$this->formId.'_'.$key.'"><tbody>';
			foreach($value['vars'] as $k => $v) {
				$k = $key.self::GROUP_FLAG.$k;
				$groups[$key][] = $this->generateItem($k, $v);
			}
			$groups[$key][] = '</tbody></table>';
		}
		$tabId = 'tpl_tab_'.$this->formId;
		$ret[] = '<ul class="g" id="'.$tabId.'">';
		$ret[] = implode('', $tabs);
		$ret[] = '</ul>';

		$ret[] = <<<JSCODE
<script>
hapj(function(H){
	var lis = H.ui.id('{$tabId}').tag('li'),
	sw = H.ui.id('{$tabId}').switchable({
		tag:'li',
		map:function(i){
			return H.ui._id(lis[i].getAttribute('key'));
		},
		trigger: function(ts, i) {
			ts.removeClass('on');
			this.className = 'on';
		},
		target:function(ts, i, t) {
			ts.hide();
			this.style.display = 'block';
		}
	});
	sw.first();
});
</script>
JSCODE;
		

		foreach($groups as $key => $group) {
			$ret[] = implode('', $group);
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

	private function generateItem($key, $value)
	{
		switch($value['type']) {
			case 'text':
			return $this->getTextElem($key, $value);
			case 'url':
			return $this->getUrlElem($key, $value);
			case 'image':
			return $this->getImageElem($key, $value);
			default:
			throw new Exception('template.u_typeNotSupported');
		}
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getTextElem($key, $var)
	{
		
		$ret[] = '<tr><th>'.(isset($var['name']) ? $var['name'] : '文本').'</th><td>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 20;
		if ($maxlength <= 20) {
			$maxlength = 20;
		}
		if ($maxlength < 150) {
			$ret[] = '<input type="text" maxlength="'.$maxlength.'" name="data['.$key.']" style="width:'.($maxlength*12).'px;" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/>';
		} else {
			$ret[] = '<textarea maxlength="'.$maxlength.'" name="data['.$key.']" style="width:400px;height:100px;">'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'</textarea>';
		}
		
		$ret[] = '</td></tr>';
		return implode('', $ret);
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getUrlElem($key, $var)
	{
		$ret[] = '<tr><th>'.(isset($var['name']) ? $var['name'] : '网址').'</th><td>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 100;
		if ($maxlength <= 100) {
			$maxlength = 100;
		}
		
		$ret[] = '<input type="text" maxlength="'.$maxlength.'" verify-rule="{url:\'网址格式不正确\'}"  name="data['.$key.']" style="width:240px;" placeholder="http://" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/>';
		$ret[] = '</td></tr>';
		return implode('', $ret);
	}

	/**
	 * 获取文本元素
	 * @param string $key 
	 * @param  array $var
	 * @return string
	 */
	private function getImageElem($key, $var)
	{
		$imgId = 'tpl_form_'.$this->formId.'_'.$key;
		$ret[] = '<tr><th>'.(isset($var['name']) ? $var['name'] : '图片').'</th><td>';
		$maxlength = isset($var['maxlength']) ? intval($var['maxlength']) : 100;
		if ($maxlength <= 100) {
			$maxlength = 100;
		}
		
		$ret[] = '<input type="hidden" name="data['.$key.']" value="'.(!empty($this->tpl_data[$key]) ? $this->tpl_data[$key] : '').'"/><span id="'.$imgId.'"></span>';
		$ret[] = <<<JSCODE
<script>
hapj(function(H) {
	H.com('upload').active('{$imgId}').setCallback(function(img){
		H.ui.id('{$imgId}').prev().attr('value', img.id)
	});
})
</script>
JSCODE;
		$ret[] = '</td></tr>';
		return implode('', $ret);
	}

	/**
	 * 将数据传入模板进行编译，返回html
	 * @param array $data
	 * @return string 模板对应的html代码
	 */
	public function Compile(array $data) 
	{
		$content = $this->tpl_content;
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
			$varPos = strpos($tag, self::VAR_FLAG);
			if ($varPos !== false) {
				$key = substr($tag, 0, $varPos);
				$prop = substr($tag, $varPos + 1);

				$groupPos = strpos($key, self::GROUP_FLAG);
				if ($groupPos === false) {
					if (!empty($this->tpl_vars[$key][$prop])) {
						$ret[] = $this->tpl_vars[$key][$prop];
					}
				} else {
					$groupKey = substr($key, 0, $groupPos);
					$groupVKey = substr($key, $groupPos + 1);

					if (!empty($this->tpl_vars[$groupKey]['vars'][$groupVKey][$prop])) {
						$ret[] = $this->tpl_vars[$groupKey]['vars'][$groupVKey][$prop];
					}
				}
			} else {
				if (!empty($data[$tag])) {
					$ret[] = $data[$tag];
				}
			}
			$content = mb_substr($content, $rightPos + self::RIGHT_SPLITTER_LEN);
		} 
		return implode('', $ret);
	}
}
