<?php

/**
 * Collect data from an running program and save it for use as testing Fixture data
 *
 * @author dondrake
 */
class FixtureCollector {
	
	public $path;
	
	static function __construct($name, Array $args) {
		
		$this->path = TEST . 'Fixture/';
		$this->name = str_replace('::', '__', $name);
		$this->openFile();
	}
	
	private function openFile() {
		try {
			$this->file = fopen($this->path . $this->name, 'a');
		} catch (Exception $exc) {
			echo $exc->getTraceAsString();
		}

		
	}
	
}
