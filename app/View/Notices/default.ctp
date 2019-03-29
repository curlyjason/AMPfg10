 <?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * 
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Emails.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php
App::uses('User', 'Model');
//$this->FgHtml->ddd($contacts);

//array(
//	'ddrake@dreamingmind.com' => array(
//		'Approval' => array(
//			'WP46' => array(
//				(int) 0 => '1407-ADFY',
//				(int) 1 => 'I157',
//			'WP22' => array(
//				(int) 0 => '1407-ADFZ'
//		'Notify' => array(
//			'WP46' => array(
//				(int) 0 => '1407-ADFY',
//	'jason@curlymedia.com' => array(
//		'Approval' => array(
//			'WP22' => array(
//				(int) 0 => '1407-ADFZ'

foreach ($contacts as $contact => $observationSets) { // This loop level will be in the controller to send individual notifications in series
	foreach ($observationSets as $type => $pointMessages) {
		foreach ($pointMessages as $point => $keys) {
			$slug = $WatchPoints[$point]->slug();
			foreach ($keys as $key) {
				if (preg_match('/\d{4}-[A-Z]{4}/', $key)) {
					if ($type === 'Approval' && $messages[$key]->statusIs('Submitted')) {
						$name = "Approval-$slug";
						$this->Notice->startNoticeBlock($name, $WatchPoints[$point]);
						$this->prepend($name);
						// different element on this one
						echo $this->element('Email/approval', array('order' => $messages[$key]));
						$this->end();
					} else {
						$name = $type.'-'.$slug;
						$this->Notice->startNoticeBlock($name, $WatchPoints[$point]);
						$this->append($name);
						echo $this->element('Email/submitted', array('order' => $messages[$key]));
						$this->end();
					}
					//}
				} elseif (preg_match('/I\d+/', $key)) {
					$this->append("$type-$slug");
					echo $this->element('Email/lowInventory', array('data' => $messages[$key]));
					$this->end();
				}
			}			//	$object->output();
		}	
	}
	
	// Now go through the blocks and put them on a sorted heap to establish the output order
	$output = $this->Notice->outputHeap(); //sorted queue to control output order
	$priority = Observer::emailTypes(); // array to control output order for messages in each observation category
	foreach($this->blocks() as $block){
//		preg_match("/([A-Za-z]*)-/", $block, $match); // the first word, followed by '-' is the type
		if ($block != 'content') {
			$output->insert($block);
		}	
	}
	
	echo "<h1>$contact</h1>"; // this will be removed when the view goes back to email, after testing
	
	$currentType = '';
	foreach($output as $type => $name){
		$block = $this->fetch($name);
		if ($block != '') {
			$block = str_replace("</ul><ul>\r\n", '', $block, $count);
			$block = str_replace('<ul>', '<ul style="padding: 0;">', $block);
			if ($currentType !== $type) {
				echo $this->Html->div($type, NULL);
				echo $this->Html->tag('h2', "Observation type: $type");
			}
			echo str_replace("</ul><ul>\r\n", '', $block);
			if ($currentType !== $type) {
				$currentType = $type;
				echo '</div>';
			}
			$this->assign($name, '');
		}
	}
//	</ul><ul class="low">

}