<?php
App::uses('AppModel', 'Model');
/**
 * InvoiceItem Model
 *
 * @property Invoice $Invoice
 */
class InvoiceItem extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'invoice_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Invoice' => array(
			'className' => 'Invoice',
			'foreignKey' => 'invoice_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'OrderItem' => array(
			'className' => 'OrderItem',
			'foreignKey' => 'order_item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Customer' => array(
			'className' => 'Customer',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
	
    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
//        $this->virtualFields['name'] = $this->discoverName($id);
        $this->virtualFields['subtotal'] = sprintf('%s.quantity * %s.price', $this->alias, $this->alias);
    }
	
	public $message = array();
	/**
	 * Get the total of open InvoiceItem charges for a customer
	 * 
	 * There will be charges for Orders and OrderItems that are not yet at Shipped status
	 * $orderInList protects against including them
	 * But ALL general charges (those not for a specific Order or OrderItem) will be included.
	 * 
	 * @param int $id customer user id
	 * @param array $orderInList The orders at 'Shipped' status
	 */
	public function fetchTotalCharges($id) {
//		$orderInList = array();
		$orderInList = $this->Order->fetchShippedOrderInList($id);
		$total = $this->find('all', array(
			'conditions' => array(
				'InvoiceItem.customer_id' => $id,
				'InvoiceItem.invoice_id IS NULL',
				'OR' => array(
					'InvoiceItem.order_id' => $orderInList,
					'InvoiceItem.order_id IS NULL'
				)
				
			),
			'fields' => array(
				'SUM(InvoiceItem.price * InvoiceItem.quantity) as total'
			)
		));
		return $total[0][0]['total'];
	}
	
	/**
	 * Get the total of open InvoiceItem general charges for a customer
	 * 
	 * @param int $id customer user id
	 */
	public function fetchTotalGeneralCharges($id) {
		$total = $this->find('all', array(
			'conditions' => array(
				'InvoiceItem.customer_id' => $id,
				'InvoiceItem.invoice_id IS NULL',
				'InvoiceItem.order_id IS NULL'
			),
			'fields' => array(
				'SUM(InvoiceItem.price * InvoiceItem.quantity) as total'
			)
		));
		return $total[0][0]['total'];
	}
	
	/**
	 * Get the total of open InvoiceItem charges for an order
	 * 
	 * @param int $id order id
	 */
	public function fetchTotalOrderCharges($id, $invoiceId = NULL) {
		if($invoiceId === NULL){
			$conditions = array(
				'InvoiceItem.order_id' => $id,
				'InvoiceItem.invoice_id IS NULL'
			);
		} else {
			if ($id != 'general') {
				$conditions = array(
					'InvoiceItem.order_id' => $id,
					'InvoiceItem.invoice_id' => $invoiceId
				);
			} else {
				$conditions = array(
					'InvoiceItem.order_id IS NULL',
					'InvoiceItem.invoice_id' => $invoiceId
				);
			}			
		}
		$total = $this->find('all', array(
			'conditions' => $conditions,
			'fields' => array(
				'SUM(InvoiceItem.price * InvoiceItem.quantity) as total'
			)
		));
		return ($total[0][0]['total'] == NULL) ? 0 : $total[0][0]['total'];
	}
	
	/**
	 * Link the current working set of InvoiceItems to an Invoice record
	 * 
	 * On successful insertion of an invoice into EPMS, the invoice
	 * line items must be linked to an invoice record. In this way
	 * they are marked as 'done' and will be ignored for future invoices
	 * 
	 * @param string $invoiceId
	 * @return boolean 
	 */
	function linkInvoiceItems($invoiceId) {
		foreach ($this->lineItems as $group => $lines) {
			foreach ($lines as $line) {
                if ($group == 'general' || !$line['Order']['exclude']) {
                    $this->data[]['InvoiceItem'] = array(
                        'id' => $line['InvoiceItem']['id'],
                        'invoice_id' => $invoiceId
                    );
                }                
			}
		}
		$data = $this->data;
		if ($this->saveAll($this->data)) {
			return TRUE;
		} else {
			//log the data array or some such on the failure of this save
			$this->ddd($data);
			$this->logLinkError($data);
			return FALSE;
		}
	}
	
	/**
	 * Fetch a list of the existing Shipping records for provided customer 
	 * 
	 * @param type $customerUserId
	 * @return array list of existing shpiping charges, indexed by the order id
	 */
	public function fetchExistingShipments($customerUserId) {
		return $this->find('list', array(
			'conditions' => array(
				'customer_id' => $customerUserId,
				'name' => 'Shipping',
				'invoice_id IS NULL'
			),
			'fields' => array('order_id', 'id')
		));
	}
	
	/**
	 * Fetch a list of the existing Total Item Charges records for provided customer 
	 * 
	 * @param type $customerUserId
	 * @return array list of existing order charges, indexed by the order id
	 */
	public function fetchExistingOrderCharges($customerUserId) {
		return $this->find('list', array(
			'conditions' => array(
				'customer_id' => $customerUserId,
				'name' => 'Total Item Charges',
				'invoice_id IS NULL'
			),
			'fields' => array('order_id', 'id')
		));
	}
	
	private function logLinkError($data) {
		$in = '';
		foreach ($data as $record) {
			$in .= "'{$record['InvoiceItem']['id']}',";
		}
		$in = trim($in, ',');
		$iiSelect = "SELECT `invoice_items`.`id`, `invoice_items`.`invoice_id` FROM `invoice_items` WHERE `invoice_items`.`id` IN ($in);\n\r";
		$iSelect = "SELECT * FROM `invoices` WHERE `invoices`.`id` = '{$data[0]['InvoiceItem']['invoice_id']}';\n\r";
		
		$dir = LOGS.'ErrorData/';
		$filename = $dir . 'invoiceItemError' . time() . '.txt';
		
		$logMessage = "The invoice items couldn't be linked. Check $filename for details. InvoiceId: {$data[0]['InvoiceItem']['invoice_id']} InList: ";
		CakeLog::write('error', $logMessage);
		
		touch($filename);
		if(!$handle = @fopen($filename, "a")){
			$this->message[] = array(
				'message' => "Cannot open $filename",
				'type' => 'error'
			);
			return FALSE;
		}
		if(fwrite($handle, $logMessage . "\n\r") === FALSE){
			$this->message[] = array(
				'message' => "Could not write log message header",
				'type' => 'error'
			);
		}
		if(fwrite($handle, debug($data) . "\n\r") === FALSE){
			$this->message[] = array(
				'message' => "Could not write data debug",
				'type' => 'error'
			);
		}
		if(fwrite($handle, serialize($data) . "\n\r") === FALSE){
			$this->message[] = array(
				'message' => "Could not write serialized data",
				'type' => 'error'
			);
		}
		if(fwrite($handle, $iiSelect) === FALSE){
			$this->message[] = array(
				'message' => "Could not write iiSelect statement",
				'type' => 'error'
			);
		}
		if(fwrite($handle, $iSelect) === FALSE){
			$this->message[] = array(
				'message' => "Could not write iSelect statement",
				'type' => 'error'
			);
		}
	}

}