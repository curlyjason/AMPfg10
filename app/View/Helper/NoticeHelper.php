<?php
class HeapHelper extends SplHeap {
	
	/**
	 * array to control output order for messages in each observation category
	 *
	 * @var array 
	 */
	protected $priority;
	
	/**
	 * Place entries in the list based on the observations priority array order
	 * 
	 * entries like 'Approval-AtlasHub' or 'Notify-SadNewVistasinTesting' 
	 * have the first word extracted and checked for position in the Observation priority array. 
	 * Earlier entries will be placed earlier in the list. 
	 * Entries that aren't found will throw exceptions
	 * 
	 * @param string $value1
	 * @param string $value2
	 * @return int
	 */
    public function  compare( $value1, $value2 ) {		
		$this->priority = Observer::emailTypes();
        return ( $this->position($value2) - $this->position($value1) );
    }
	
	private function position($string) {
		$type = $this->type($string);
		$position = array_keys($this->priority, $type);
		if (empty($position)) {
			debug('bad position', 'bad position');
			throw new BadFunctionCallException("$string can't be sorted because its type ($type) is not an email observation type.");
		}
		return $position[0];
		
	}
	private function type($string) {
		preg_match("/([A-Za-z]*)-/", $string, $match); // the first word, followed by '-' is the type
		if (empty($match)) {
			debug('bad type', 'bad type');
			throw new BadFunctionCallException("$string can't be sorted because it does not begin with an email observation type followed by a '-'.");
		}
		return $match[1];
	}
	
	public function key() {
		return $this->type($this->current());
	}
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP NoticeHelper
 * @author dondrake
 */
class NoticeHelper extends AppHelper {

	public $helpers = array('Html');

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
	}

	public function beforeRender($viewFile) {
		
	}

	public function afterRender($viewFile) {
		
	}

	public function beforeLayout($viewLayout) {
		
	}

	public function afterLayout($viewLayout) {
		
	}
	
//	public function startNoticeBlock($name, WatchPoint $watchPoint) {
	public function startNoticeBlock($name) {
		if ($this->_View->fetch($name) === '') {
			$this->_View->start($name);
			echo $this->Html->tag('h4', ucwords($name) . ' Notifications');
			$this->_View->end();
		}
	}
	
	public function outputHeap() {
		return new HeapHelper();
	}
}