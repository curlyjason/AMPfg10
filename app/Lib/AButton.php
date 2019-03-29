<?php

/**
 * AButton defines an <BUTTON> tag
 *
 * @author dondrake
 */
class AButton {
	
	protected $label;
	
	protected $attributes;
		
	public function __construct($label, $attributes = array()) {
		$this->label = $label;
		$this->attributes = $this->attributesToString($attributes);
	}

	public function button() {
		return "\r<button $this->attributes>$this->label</button>\r";
	}
	
	protected function attributesToString($attributes) {
		if (empty($attributes)) {
			return '';
		}
		$string = '';
		foreach ($attributes as $name => $value) {
			$string .= sprintf('%s="%s" ', $name, $value);
		}
		return $string;
	}
}
