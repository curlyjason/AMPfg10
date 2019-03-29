<?php

/**
 * Description of Message Abstract
 *
 * @author jasont
 */

App::uses('WatchPoint', 'Lib/Notifiers');

abstract class MessageAbstract {
	
	protected $data = array();
	
	private $watchPoint;


	public function __construct($data) {
		$this->data = $data;
	}
	
	public function setTime($time) {
		$this->data['time'] = $time;
	}
	
	public function isOlder($time) {
		if($this->data['time'] < $time){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	public function watchPoint($data = NULL) {
		if ($data !== NULL) {
			$this->watchPoint = "WP{$data['id']}";
		}
		return $this->watchPoint;
	}
	
	public function data($data = NULL) {
		if ($data !== NULL) {
			$this->data = array_merge($this->data, $data);
		}
		return $this->data;
	}

	public function output(){
		$this->data['out'] = isset($this->data['out']) ? ++$this->data['out'] : 1 ;
		
		echo '<dl>';
		foreach ($this->data as $key => $val){
			echo "<dt>$key</dt><dd>$val</dd>";
		}
		echo '</dl>';	
	}
}
