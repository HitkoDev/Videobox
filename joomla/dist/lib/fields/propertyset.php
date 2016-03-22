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
		
		$fields = array();
		$keymap = array();
		foreach($this->form->getFieldsets($this->group) as $fldset){
			foreach($this->form->getFieldset($fldset->name) as $field){
				$keymap[$field->name] = $field->fieldname;
				if($field->name != $this->name) $fields[$field->name] = $field->value;
			}
		}
		
		$value = json_decode($this->value, true);
		if(!$value) $value = array();
		$value['__keymap'] = $keymap;
		if(!$value['default']) $value['default'] = array_merge($fields, array(
			'property_set' => 'default'
		));
		
		$data = array_merge($fields, isset($value[$set]) ? $value[$set] : array());
		foreach($data as $key => $val){
			if($keymap[$key]) $this->form->setValue($keymap[$key], $this->group, $val);
		}
		if($set == 'default') $value[$set] = $data;
		
		$document = JFactory::getDocument();
		JHtml::script(JUri::base() . '../libraries/videobox/js/propertyset.min.js');
        JHtml::stylesheet(JUri::base() . '../libraries/videobox/css/propertyset.min.css');
		
		$keyInput = '';
		
		if($set != 'default'){
			$name = $this->name;
			$id = $this->id;
			
			$this->value = $set == 'new' ? '' : $set;
			$this->name = 'property_set';
			$this->id = $id . '-key';
			
			$keyInput = parent::getInput();
			
			$this->value = json_encode($value);
			$this->name = $name;
			$this->id = $id;
		}
		
		$juri = JURI::getInstance();
		$query = $juri->getQuery(true);
		if(!$query) $query = array();
		
		$out = '<ul class="prop-set-items" id="' . $this->id . '-list">';
		foreach($value as $key => $data) {
			if($key == '__keymap') continue;
			if($key == 'default'){
				unset($query['property_set']);
			} else {
				$query['property_set'] = $key;
			}
			$uri = JURI::buildQuery($query);
			$out.= '<li data-set="' . htmlspecialchars($key) . '"><a href="' . JURI::current() . '?' . $uri . '" title="' . $key . '">' . $key . '</a>' . ($key != 'default' ? '<span class="icon-cancel btn btn-small"></span>' : '') . '</li>';
		}
		if($set == 'new'){
			$out.= '<li data-set="new">__new_set</li>';
		}
		$query['property_set'] = 'new';
		$uri = JURI::buildQuery($query);
		$out.= '<li data-set=""><a href="' . JURI::current() . '?' . $uri . '" title="New set">New set</a></li></ul>';
		
		if($set == 'new') $value['new'] = array(
			'property_set' => 'new'
		);
		
		$prefix = $this->name;
		$i = strpos($prefix, '['.$this->fieldname.']');
		if($i){
			$prefix = substr($prefix, 0, $i);
		} else {
			$prefix = '';
		}
		
		return '<input type="hidden" class="vb-prop-set" id="' . $this->id . '" name="' . $this->name . '" value="' . htmlspecialchars(json_encode($value)) . '" data-key="' . htmlspecialchars($set) . '" data-prefix="' . $prefix . '" >' . $out . $keyInput;
		
	}
}