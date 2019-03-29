<?php
/**
 * CakePHP AssociateTableHelper
 * @author jasont
 */
App::uses('HtmlHelper', 'Helpers');

class AssociateTableHelper extends AppHelper {

	public $helpers = array('Html');
	
	/**
	 * An iterator containing data entities
	 * 
	 * @var object 
	 */
	protected $collection;
	
	/**
	 * An array of column labels and their corresponding object calls
	 * 
	 * <pre>
	 * array(
	 *		'label' => 'callpoint',
	 *		'name' => 'discoverNameFromFirstAndLast',
	 *		...
	 * )
	 * </pre>
	 * @var array 
	 */
	protected $columns = array();
	
	/**
	 * A collection of button objects
	 * 
	 * @var object
	 */
	protected $tools;
	
	//Attribute properties
	protected $td_attributes;
	protected $tr_attributes;
	protected $table_attributes;
	protected $header_tr_attributes;
	protected $header_th_attributes;
	protected $tool_tr_attributes;
	protected $tool_td_attributes;
	
	protected $row_count;

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		foreach ($this->settings as $key => $value) {
			$this->$key = $value;
		}
	}

	public function beforeRender($viewFile) {
		
	}

	public function afterRender($viewFile) {
		
	}

	public function beforeLayout($viewLayout) {
		
	}

	public function afterLayout($viewLayout) {
		
	}
	
	/**
	 * Set the data property from provided object
	 * 
	 * @param object $data
	 */
	public function setCollection($collection) {
		$this->collection = $collection;
	}
	
	/**
	 * Set the columns property from provided array of columns
	 * 
	 * @param array $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}
	
	/**
	 * Set the tools property from the provided object
	 * 
	 * @param object $tools
	 */
	public function setTools($tools) {
		$this->tools = $tools;
	}
	
	public function getRowCount() {
		return $this->row_count;
	}
	
	public function loadCss() {
		
	}
	
	public function loadJs() {
		
	}
	
	/**
	 * Create the opening table tag with optional attributes
	 * 
	 * @param array $attributes
	 * @return string
	 */
	public function startTable() {
		if (!empty($this->collection)) {
			return $this->Html->tag('table', NULL, $this->table_attributes);
		}		
	}
	
	/**
	 * Create the table header row, with optional attributes, from the columns property
	 * 
	 * @param array $trOptions
	 * @param array $thOptions
	 * @return string
	 */
	public function headerRow() {
		if (!empty($this->collection)) {
			return $this->Html->tableHeaders(array_keys($this->columns), $this->header_tr_attributes, $this->header_th_attributes);
		}		
	}

	/**
	 * Create a <tr> enclosing <td>s with optional attributes
	 * 
	 * Using the row object (contained in collection) to return data based upon the property
	 * or method named in the columns array, create a tagged and attributed table row
	 * 
	 * @param array $tdAttributes
	 * @param array $trAttributes
	 * @return string
	 */
	public function dataRows() {
		$output = '';
		if (!empty($this->collection)) {
			$this->row_count = 0;
			foreach ($this->collection as $row) {
				$output .= $this->dataRow($row);
				$this->row_count++;
			}
		}		
		return $output;
	}
	
	/**
	 * Step through the columns to create a tagged tr containing td's
	 * 
	 * @param object $row
	 * @param array $tdAttributes
	 * @param array $trAttributes
	 * @return string
	 */
	protected function dataRow($row) {
		$cells = array();
		foreach ($this->columns as $field) {
			$cells[] = $this->Html->tag('td', $row->$field, $this->td_attributes);
		}
		return $this->Html->tag('tr', implode('', $cells), $this->tr_attributes);
	}
	
	public function toolRow() {
		$output = $cell = '';
		if(!empty($this->tools)){
			foreach ($this->tools as $button) {
				$cell .= $button->button();
			}
			$td = $this->Html->tag('td', $cell, $this->tool_td_attributes);
			$output = $this->Html->tag('tr', $td, $this->tool_tr_attributes);
		}
		return $output;
	}
	
	/**
	 * Create the closing table tag
	 * 
	 * @return string
	 */
	public function endTable() {
		if (!empty($this->collection)) {
			return "</table>";
		}		
	}
	
}
