<?php

// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');
jimport('joomla.form.form');

class JFormFieldPropertySet extends JFormField {
    
	protected $type = 'PropertySet';
	protected $subform = false;
	protected $setName = false;
    
	public function setup(SimpleXMLElement $element, $value, $group = null) {
		parent::setup($element, $value, $group);
        
        $attrs = array();
        foreach($element->attributes() as $key => $value) {
            $attrs[$key] = (string)$value;
        }
        
		$this->setName = 'propSet-' . $this->id;
		$xml = '<form><fieldset name="' . $this->setName . '">';
		$xml.= '<field 
            name="__key" 
            type="text"
            description="' . (isset($attrs['keydescription']) ? htmlspecialchars($attrs['keydescription']) : '') . '"
            label="' . (isset($attrs['keylabel']) ? htmlspecialchars($attrs['keylabel']) : '') . '"
        />';
		$xml.= '<field 
            name="__name" 
            type="text"
            description="' . (isset($attrs['namedescription']) ? htmlspecialchars($attrs['namedescription']) : '') . '"
            label="' . (isset($attrs['namelabel']) ? htmlspecialchars($attrs['namelabel']) : '') . '"
        />';
        $xml .= '<field type="spacer" name="key-name-spacer" class="prop-set-content-label" ' . (isset($attrs['contentlabel']) ? 'label="' . htmlspecialchars($attrs['contentlabel']) . '"' : 'hr="true"') . ' />';
		foreach($this->element->children() as $chl) {
			$xml.= $chl->asXML();
		}

		$xml.= '</fieldset></form>';
		$this->subform = new JForm('propForm', array());
		$this->subform->load($xml);
		$value = json_decode($this->value, true);
		if (!$value || !isset($value['default'])) {
			$defaults = array();
			foreach($this->subform->getFieldset($this->setName) as $key => $field) {
				$defaults[$key] = $field->value;
			}

			$defaults['__name'] = 'Default';
			$defaults['__key'] = 'default';
			$value = array(
				$defaults['__key'] => $defaults
			);
		}
		else {
			foreach($this->subform->getFieldset($this->setName) as $key => $field) {
				if (isset($value['default'][$key])) $field->setValue($value['default'][$key]);
			}
		}

		$this->value = json_encode($value);
		return true;
	}

	public function getInput() {
		$document = JFactory::getDocument();
		JHtml::script(JUri::base() . '../libraries/videobox/js/propertyset.min.js');
        JHtml::stylesheet(JUri::base() . '../libraries/videobox/css/propertyset.min.css');
		$value = json_decode($this->value, true);
		$out = '<ul class="prop-set-items" id="list-' . $this->id . '">';
		foreach($value as $key => $set) {
			$out.= '<li data-set="' . $key . '"><span class="set-name">' . ($set['__name'] ? $set['__name'] : $set['__key']) . '</span>' . ($key != 'default' ? '<span class="icon-cancel btn btn-small"></span>' : '') . '</li>';
		}

		$out.= '<li data-set=""><span class="btn btn-small"><span class="icon-new"></span><span class="set-name">New set</span></span></li></ul>';
		return '<input type="hidden" class="vb-prop-set" id="' . $this->id . '" name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '">' . $out . '<div id="fields-' . $this->id . '">' . $this->subform->renderFieldset($this->setName) . '</div>';
	}
}