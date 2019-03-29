<?php

/**
 * Description of FixtureDataNet
 *
 * @author dondrake
 */
class FixtureDataNet {
	
	static function record($method, $args) {
//		$args = array(
//			'arg1',
//			array('a21', 'a22'),
//			array('this' => 'that', 'then' => 'now')
//		);
		$argLine = self::prepareArgs($args);
		$name = str_replace('::', '__', $method);
		$path = TESTS . 'Fixture/';
		$fhandle = fopen($path . $name, 'a');
		fwrite($fhandle, "\n".$argLine);
		fclose($fhandle);
	}
	
	private static function prepareArgs($args) {
		return var_export($args, TRUE);
//		$patterns = array(
//			'/\n {3,}/',
//			'/(,)\n  (\))/',
//			'/(>)\n  (a)/'
//		);
//		
//		$replacements = array(
//			' ',
//			'/$1 $2/',
//			'/$1 $2/'
//		);
//		
//		return preg_replace($patterns, " ", var_export($args, TRUE));
	}
}
/*
 => true,
  ),
  2 => 60,
)array (
  0 => 
  arr
 */