<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accumulator
 *
 * @author dondrake
 */
class AccumulatorHelper extends FgHtmlHelper{
	
	public $divCount = array(
		'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven'
	);
	
	public function columns($columns, $classes = '', $attributes = array()) {
		$i = 0;
		if (is_array($columns)) {
			foreach ($columns as $column) {
				echo $this->div("split left $classes {$this->divCount[$i++]}", 
					$this->para(null, $column), 					$attributes
				);

			}
		}		
	}
}

?>
