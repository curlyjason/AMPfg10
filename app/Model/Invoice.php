<?php
App::uses('AppModel', 'Model');
/**
 * Invoice Model
 *
 * @property InvoiceItem $InvoiceItem
 */
class Invoice extends AppModel {


	public $epmsJobId = FALSE;
	
	public $customer = '';
	
	public $groupedCharges = array();
	
	public $groupedSummary = array(
		'general' => array()
	);
    
    public $orderItems = array();
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'InvoiceItem' => array(
			'className' => 'InvoiceItem',
			'foreignKey' => 'invoice_id',
			'dependent' => false
		)
	);

	/**
	 * Create a new Invoice record
	 * 
	 * During invoicing, after submission of the invoice to the epms sys
	 * a new Invoice record is needed to gather and seal off the involved line items
	 * 
	 * @return $id The id of the new record
	 */
	public function newInvoice() {
		$this->create(array('customer_id' => $this->customer));
		return $this->save();
//		return $this->id;
	}
	
	/**
	 * Return a full invoice, including all line items
	 * based upon the invoice id
	 * 
	 * @param string $id the invoice id
	 * @return array
	 */
	public function fetchInvoice($id){
		$invoice = $this->find('first', array(
			'conditions' => array(
				'Invoice.id' => $id
			),
			'contain' => array(
				'InvoiceItem'
			)
		));
		foreach ($invoice['InvoiceItem'] as $line) {
			if(!empty($line['order_id'])){
				$this->groupedCharges[$line['order_id']][] = $line;
                $this->orderItems[$line['order_id']] = $this->InvoiceItem->OrderItem->fetchByOrder($line['order_id']);
			} else {
				$this->groupedCharges['general'][] = $line;
			}
		}
		foreach ($this->groupedCharges as $index => $charges) {
			$this->groupedSummary[$index]['total'] = $this->InvoiceItem->fetchTotalOrderCharges($index, $id);
			if($index != 'general'){
				$this->groupedSummary[$index]['shipment'] = $this->shipmentBlock($index);
				$this->groupedSummary[$index]['shipment']['shipment_date'] = $this->InvoiceItem->Order->field('ship_date', array('id' => $index));
				$this->groupedSummary[$index]['shipment']['order_date'] = $this->InvoiceItem->Order->field('created', array('id' => $index));
				$this->groupedSummary[$index]['reference'] = $this->InvoiceItem->Order->field('order_reference', array('id' => $index));
				$this->groupedSummary[$index]['label'] = $this->InvoiceItem->Order->field('order_number', array('id' => $index));
			} else {
				$this->groupedSummary[$index]['shipment'] = '';
				$this->groupedSummary[$index]['label'] = 'General Charges';
			}
		}
//		$this->ddd($this->groupedCharges, 'groupedCharges');
//		$this->ddd($this->groupedSummary, 'groupedSummary');
//		die;
		return $invoice;
	}
	
	public function shipmentBlock($order_id) {
		$shipment = $this->InvoiceItem->Order->Shipment->find('first', array('conditions' => array('order_id' => $order_id), 'contain' => false));
		if(!empty($shipment)){
			return $shipment['Shipment'];
		} else {
			return array();
		}
	}
	
	public function fetchInvoiceTotal($id) {
		$full = $this->find('all', array(
			'conditions' => array(
				'id' => $id
			),
			'contain' => array(
				'InvoiceItem' => array(
					'fields' => array(
						'SUM(InvoiceItem.price * InvoiceItem.quantity) as total'
					)
				)
			)
		));
		$total = $full[0]['InvoiceItem'][0]['InvoiceItem'];
		return ($total[0]['total'] == NULL) ? 0 : $total[0]['total'];
	}
	
	/**
	 * Get select list of past invoices for a customer
	 * 
	 * @param string $cust_id
	 * @return array
	 */
	public function fetchInvoices($cust_id) {
		$invoices = $this->find('all', array(
			'fields' => array('id', 'job_number', 'created'),
			'conditions' => array(
				'customer_id' => $cust_id
			)
		));
		$invoiceList = array();
		foreach ($invoices as $index => $invoice) {
			$invoiceList[$invoice['Invoice']['id']] = $invoice['Invoice']['job_number'] . ' on ' . date('m/d/y', strtotime($invoice['Invoice']['created']));
		}
		return $invoiceList;
	}
}
