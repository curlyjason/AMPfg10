<?php
App::uses('Status', '/View/Helper');
App::uses('Text', 'Helper');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP SearchHelper
 * @author dondrake
 */
class SearchHelper extends StatusHelper {

	public $helpers = array('Text');

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

	/**
	 * Create a User result block 
	 * 
	 * Given a found record, create the User type result grain
	 * 
	 * @param array $user One discovered user record
	 * @param string $query The user's query string
	 * @return string The html block for one result
	 */
	public function foundUser($user, $query) {
		$hitLines = '';
		foreach ($user['User'] as $field => $value){
			if (stristr($value, $query)) {
				$hitLines[] = $this->FgHtml->decoratedTag('User ' . Inflector::humanize($field), 'p', $this->Text->highlight($value, $query));
			}
		}
		$link = $this->Html->link($this->FgHtml->discoverName($user['User']), $this->userLink($user['User']['id']));
		return "\r".$this->Html->div('search user', "\r\t".$this->FgHtml->tag('h4',"\r\t\t".$link."\r\t")."\r\t" . $this->Html->div(null, "\r\t\t".implode("\r\t\t", $hitLines)."\r\t") . "\r") . "\r";
		// http://localhost/amp-fg/users/edit_userGrain/3/fcbe2cb721ddef6ab525cdb6438dc92b04e93770
	}
	
	/**
	 * Build a link array for a user record
	 * 
	 * Goes to edit_userGrain
	 * 
	 * @param string $id A user record id
	 * @return array A user grain link
	 */
	public function userLink($id) {
		return array(
			'controller' => 'users',
			'action' => 'edit_userGrain',
			$id,
			$this->secureHash($id)
		);		
	}
	
	/**
	 * Create a Customer result block 
	 * 
	 * Given a found Customer record, create the User type result grain
	 * 
	 * @param array $user One discovered customer record
	 * @param string $query The user's query string
	 * @return string The html block for one result
	 */
	public function foundCustomer($customer, $query) {
		$hitLines = '';
		foreach ($customer['Customer'] as $field => $value){
			if (stristr($value, $query)) {
				$hitLines[] = $this->decoratedTag('Customer ' . Inflector::humanize($field), 'p', $this->Text->highlight($value, $query));
			}
		}
		$link = $this->link($this->discoverName($customer['User']), $this->userLink($customer['User']['id']));
		return "\r".$this->div('search user', "\r\t".$this->tag('h4',"\r\t\t".$link."\r\t")."\r\t" . $this->div(null, "\r\t\t".implode("\r\t\t", $hitLines)."\r\t") . "\r") . "\r";
		// http://localhost/amp-fg/users/edit_userGrain/3/fcbe2cb721ddef6ab525cdb6438dc92b04e93770
	}
}
