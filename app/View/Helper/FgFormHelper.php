<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('Form', 'Helper');

/**
 * CakePHP Helper
 * @author dondrake
 */
class FgFormHelper extends FormHelper {
    //============================================================
    // ID/HASH SECURITY ROUTINES TO MOVE TO SEPARATE CLASS
    //============================================================

    /**
     * Create a verifilbly secure record id hidden input
     * 
     * To prevent deliberate or accidental record id changes
     * write the id and a secure hash as hidden fields. The hash
     * will allow user/session values to be used to validate 
     * the id when the form arrives back at the server
     * 
     * @param string $indexPath Dot notation string to control the name attribute
     * @param string $id Record id for hidden input field
     * @return string Two hidden inputs, 'id' and 'secure'
     */
    public function secureId($indexPath, $id) {
        $steps = explode('.', $indexPath);
        array_pop($steps);
        $secureIndexPath = implode('.', $steps) . '.secure';
        return
                $this->input($indexPath, array(
                    'type' => 'hidden',
                    'value' => $id
                ))
                . $this->input($secureIndexPath, array(
                    'type' => 'hidden',
                    'value' => $this->secureHash($id)
        ));
    }

    //============================================================
    // GRAIN DISPLAY AND GRAIN EDIT 
    //============================================================

    /**
     * Create an Edit button for grain edit pages
     * 
     * The buttons can be used in all grain contexts,
     * Javascript will make them act appropriatly based
     * on that page context
     * 
     * @param array $attributes The button attributes
     * @return string The Edit button
     */
    public function editRequestButton($attributes = array()) {
        $defaults = array('type' => 'button', 'class' => 'grainEdit');
        if (!empty($attributes)) {
            if (isset($attributes['class'])) {
                $attributes['class'] = $attributes['class'] . ' grainEdit';
            }
            $attributes = array_merge($defaults, $attributes);
        } else {
            $attributes = $defaults;
        }
        return $this->button('Edit', $attributes);
    }

    public function newRequestButton($attributes = array()) {
        $defaults = array('type' => 'button', 'class' => 'grainNew', 'text' => 'New');
        $attributes = array_merge($defaults, $attributes);
        return $this->button($attributes['text'], $attributes);
    }

    public function deleteRequestButton($attributes = array()) {
        $defaults = array('type' => 'button', 'class' => 'grainDelete');
        if (!empty($attributes)) {
            if (isset($attributes['class'])) {
                $attributes['class'] = $attributes['class'] . ' grainDelete';
            }
            $attributes = array_merge($defaults, $attributes);
        } else {
            $attributes = $defaults;
        }
        return $this->button('Delete', $attributes);
    }
    
    public function grainDetail($attributes = array()){
        $defaults = array('type' => 'button', 'class' => 'grainDetail');
        $attributes = array_merge($defaults, $attributes);
        return $this->button('Detail', $attributes);
    }

    public function folderCheck($model = 'User', $field = 'folder', $attributes = array()) {
		$label = Inflector::humanize($field);
		$defaultAttributes = array(
						'options' => array(
							1 => $label
						),
						'empty' => false,
						'type' => 'checkbox',
						'legend' => FALSE
			);
		$returnAttributes = array_merge($defaultAttributes, $attributes);
        return $this->input("$model.$field", $returnAttributes);
    }

    public function activeRadio($model = 'User') {
        return $this->input($model . '.active', array(
                    'options' => array(
                        1 => 'Active',
                        0 => 'Inactive'
                    ),
                    'type' => 'radio',
                    'legend' => false,
                    'empty' => false,
                    'default' => 1
        ));
    }
	
	public function stateInput($model, $stateList) {
		if (is_array($stateList)) {
			return $this->input("$model.state", array(
				'type' => 'select',
				'options' => $stateList,
				'empty' => 'Choose a state or province',
				'label' => 'State/Province'
			));
		} else {
			return $this->input("$model.state", array(
				'label' => 'State/Province'
			));
		}
	}
	
	public function countryInput($model, $countryList) {
		return $this->input("$model.country", array(
			'type' => 'select',
			'options' => $countryList, 
			'bind' => 'change.countryCode', 
			'default' => 'US',
			'label' => 'Country',
			'empty' => false
		));
	}
	
	/**
	 * Create indented checkbox list of accessible nodes
	 * 
	 * Provided with a data packet
	 * $listPacket => array(
	 *		'list' => array(the data itself
	 *		'ancestors' => array(a matching keyed array with number of ancestors for each node
	 *		'selected' => array(the members of the array which are selected
	 * Return a set of checkboxes, with properly set hooks for css indentation
	 * So that a checkbox set can follow a basic tree-type hierarchy in display
	 *
	 * 
	 * @param array $listPacket
	 * @param string $class
	 */
	public function accessibleCheckboxes($listPacket, $class = 'catalog', $nodeRoots) {
//		debug(func_get_args());die;
//		echo $var;
//		$this->ddd($var, 'the return');
//		die;
//		$min = min($listPacket['ancestors']);
//		$alias = Inflector::classify($class);
//
		$alias = '';
		echo '<div class="'.$class.'" id="'.$alias.'">';
		echo '<label for="'.$alias.'">'.$alias.' Group Access</label>';
		
//		debug($listPacket);
//		debug($rootNodes);
		
		echo $this->recursiveTree('checkbox', $listPacket, $nodeRoots);
//
//		foreach ($listPacket['list'] as $index => $value) {
//			$indent = intval($listPacket['ancestors'][$index])-$min;
//			echo $this->input($alias.str_replace('/', '', $index),array(
//				'class' => "indent-$indent",
//				'type' => 'checkbox',
//				'hiddenField' => false,
//				'label' => $value,
//				'value' => $index,
//				'name' => 'data['.$alias.'][]',
//				'checked' => isset($listPacket['selected'][$index]),
//				'div' => array('class' => 'checkbox')
//			));
//		}
		echo '</div>'; // close the catalog checkbox wrapper div
	}

}

?>