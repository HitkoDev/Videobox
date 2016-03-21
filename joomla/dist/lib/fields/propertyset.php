<?php

// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('text');

class JFormFieldPropertySet extends JFormFieldText {
    
	protected $type = 'PropertySet';

	public function getInput() {
		
		$jinput = JFactory::getApplication()->input;
		$set = $jinput->get('property_set', 'default', 'STRING');
		
		$value = json_decode($this->value, true);
		if(!$value) $value = array();
		if(!$value['default']) $value['default'] = array(
			'property_set' => 'default'
		);
		if(isset($value[$set])){
			foreach($value[$set] as $key => $val){
				preg_match('/jform\[' . $this->group . '\]\[([^\]]*)\]/', $key, $matches);
				if($matches[1]) $key = $matches[1];
				$this->form->setValue($key, $this->group, $val);
			}
		}
		
		if($set == 'new') $value['new'] = array(
			'property_set' => 'new'
		);
		
		$document = JFactory::getDocument();
		JHtml::script(JUri::base() . '../libraries/videobox/js/propertyset.min.js');
        JHtml::stylesheet(JUri::base() . '../libraries/videobox/css/propertyset.min.css');
		
		$keyInput = '';
		
		$this->value = json_encode($value);
		
		if($set != 'default'){
			$val = $this->value;
			$name = $this->name;
			$id = $this->id;
			
			$this->value = $set == 'new' ? '' : $set;
			$this->name = 'property_set';
			$this->id = $id . '-key';
			
			$keyInput = parent::getInput();
			
			$this->value = $val;
			$this->name = $name;
			$this->id = $id;
		}
		
		$juri = JURI::getInstance();
		$query = $juri->getQuery(true);
		if(!$query) $query = array();
		
		$out = '<ul class="prop-set-items" id="' . $this->id . '-list">';
		foreach($value as $key => $data) {
			if($key == 'default'){
				unset($query['property_set']);
			} else {
				$query['property_set'] = $key;
			}
			$uri = JURI::buildQuery($query);
			$out.= '<li data-set="' . htmlspecialchars($key) . '"><a href="' . JURI::current() . '?' . $uri . '" title="' . $key . '">' . $key . '</a>' . ($key != 'default' ? '<span class="icon-cancel btn btn-small"></span>' : '') . '</li>';
		}
		if($set == 'new') {
			$out.= '<li data-set="new"></li>';
		}
		$query['property_set'] = 'new';
		$uri = JURI::buildQuery($query);
		$out.= '<li data-set=""><a href="' . JURI::current() . '?' . $uri . '" title="New set">New set</a></li></ul>';
		
		return '<input type="hidden" class="vb-prop-set" id="' . $this->id . '" name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '" data-key="' . htmlspecialchars($set) . '" >' . $out . $keyInput;
		
	}
}