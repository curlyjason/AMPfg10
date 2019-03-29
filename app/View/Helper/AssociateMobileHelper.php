<?php
/**
 * CakePHP AssociateMobileHelper
 * 
 * Mobile (narrow screen) gets a list of ul block rather than a table of rows
 * 
 * @author jasont
 */
App::uses('AssociateTableHelper', 'View/Helper');

class AssociateMobileHelper extends AssociateTableHelper {

	
	/**
	 * No table on mobile
	 * 
	 * @param array $attributes
	 * @return string
	 */
	public function startTable() {
		
	}
	
	/**
	 * Ignore the header row
	 * 
	 * @param array $trOptions
	 * @param array $thOptions
	 * @return string
	 */
	public function headerRow() {
		
	}
	
	/**
	 * Step through the columns to create a tagged ul containing li's
	 * 
	 * @param object $row
	 * @param array $tdAttributes
	 * @param array $trAttributes
	 * @return string
	 */
	protected function dataRow($row) {
		$cells = array();
		foreach ($this->columns as $label => $field) {
			$cells[] = $this->Html->tag('li', "$label: <b>{$row->$field}</b>", $this->td_attributes);
		}
		return $this->Html->tag('ul', implode("\r", $cells), $this->tr_attributes) . '<br />';
	}
	
	public function toolRow() {
		$output = $cell = '';
		if(!empty($this->tools)){
			foreach ($this->tools as $button) {
				$cell .= $button->button() . "<br />\r";
			}
			$td = $this->Html->tag('div', $cell, $this->tool_td_attributes);
			return $td;
//			$output = $this->Html->tag('tr', $td, $this->tool_tr_attributes);
		}
		return $output;
	}
	
	/**
	 * No table on mobile
	 * 
	 * @return string
	 */
	public function endTable() {
		
	}
	
}
