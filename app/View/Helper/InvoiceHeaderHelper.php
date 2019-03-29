<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP InvoiceHeaderHelper
 * @author jasont
 */
class InvoiceHeaderHelper extends AppHelper {

	public $helpers = array('Html', 'Form');
	
	public $data = array();
	
	public $orderCount = 0;
	
//	array(
//	'53e4032c-1848-4ab2-9b8c-440e47139427' => array(
//		'id' => '53e4032c-1848-4ab2-9b8c-440e47139427',
//		'order_number' => '1408-AADY',
//		'order_reference' => '',
//		'first_name' => 'Ted',
//		'last_name' => 'Greene',
//		'company' => '',
//		'address' => '1414 Industry Blvd.',
//		'address2' => '',
//		'city' => 'San Jose',
//		'state' => 'CA',
//		'zip' => '99111',
//		'country' => 'US',
//		'order_id' => '53e4032c-1848-4ab2-9b8c-440e47139427'
//	),

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
	
	public function header($data = array()) {
		if(!empty($data)){
			$this->data = $data;
		}
		$r = $this->Html->para('top line', $this->orderNumber());
		$r .= $this->Html->para('first line', $this->orderDate() . ' | ' . $this->shipDate());
		$r .= $this->Html->para('first line', $this->ref() . ' | ' . $this->name() . ' | ' . $this->company());
		$r .= $this->Html->para('second line', $this->address());
		
		return $r;
	}
	
	public function excludeOrder($data = array()) {
		if(!empty($data)){
			$this->data = $data;
		}
		$r = $this->Form->input("$this->orderCount.Order.exclude", array(
			'type' => 'checkbox', 
			'label' => false, 
			'bind' => 'click.exclusionChoice', 
			'checked' => $this->orderExclude()));
		$r .= $this->Form->input($this->orderCount++.".Order.id", array('type' => 'hidden', 'value' => $this->orderId()));
		
		return $r;
	}

	private function orderId() {
		$this->dataValidator('id', 'No Order');
		return $this->data['id'];
	}
	
	private function orderExclude() {
		$this->dataValidator('exclude', '0');
		return $this->data['exclude'];
	}
	
	private function orderNumber() {
		$this->dataValidator('order_number', 'No Order Number');
		return $this->Html->tag('span', $this->data['order_number'], array('class' => 'order_number'));
	}
	
	private function orderDate() {
		$this->dataValidator('order_date', '--');
		if($this->data['order_date'] == '--'){
			$date = '--';
		} else {
			$date = date('m-d-Y', strtotime($this->data['order_date']));
		}
		
		return $this->Html->tag('span', 'Order date: ' . $date, array('class' => 'order_date'));
	}
	
	private function shipDate() {
		$this->dataValidator('ship_date', 'Not Shipped');
		if($this->data['ship_date'] == 'Not Shipped'){
			$date = 'Not Shipped';
		} else {
			$date = date('m-d-Y', strtotime($this->data['ship_date']));
		}
		
		return $this->Html->tag('span', 'Ship date: ' . $date, array('class' => 'ship_date'));
	}
	
	private function ref() {
		$this->dataValidator('order_reference', 'No Order Reference');
		return $this->Html->tag('span', $this->data['order_reference'], array('class' => 'order_reference'));
	}
	
	private function name() {
		$this->dataValidator('first_name', '--');
		$this->dataValidator('last_name', '--');
		return $this->Html->tag('span', $this->data['first_name'] . ' ' . $this->data['last_name'], array('class' => 'name'));
	}
	
	private function company() {
		$this->dataValidator('company', '--');
		return $this->Html->tag('span', $this->data['company'], array('class' => 'company'));
	}
	
	private function address() {
		$this->dataValidator('address', '--');
		$this->dataValidator('address2', '');
		$this->dataValidator('city', 'No City');
		$this->dataValidator('state', 'XX');
		$this->dataValidator('zip', 'No ZIP');
		$this->dataValidator('country', '');
		$csz = $this->data['city'] . ', ' . $this->data['state'] . ' ' . $this->data['zip'];
		$addr2 = ($this->data['address2'] == '') ? '' : $this->data['address2'] . ', ';
		return $this->Html->tag('span', $this->data['address'] . ', ' . $addr2 . $csz . ' ' . $this->data['country'], array('class' => 'address'));
	}
	
	/**
	 * Validate data exisits at index point, or set index point to default
	 * 
	 * @param string $index
	 * @param string $default
	 */
	private function dataValidator($index, $default) {
		if(!isset($this->data[$index]) || empty($this->data[$index])){
			$this->data[$index] = $default;
		}
	}
    
}
