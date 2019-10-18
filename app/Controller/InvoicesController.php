<?php

App::uses('AppController', 'Controller');
App::uses('FileExtension', 'Lib');

/**
 * Invoices Controller
 *
 * @property Invoice $Invoice
 */
class InvoicesController extends AppController {
	
	public $invoiceSOAP = array();

	public $xmlString = '';
	
	/**
	 * 
     *       <Credentials>
     *         <Username>Jason</Username>
     *         <Password>tanstaafl</Password>
     *       </Credentials>
	 */
	public $xmlCredentials = array(
		'Credentials' => array(
			'Username' => 'Jason',
			'Password' => 'tanstaafl'
		)
	);

	public $invoice = array();
	
    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('index', 'listInvoices');
		$this->accessPattern['Guest'] = array('listInvoices');
		$this->accessPattern['Warehousese'] = array('listInvoices');
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->Invoice->recursive = 0;
        $this->set('invoices', $this->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Invoice->exists($id)) {
            throw new NotFoundException(__('Invalid invoice'));
        }
        $options = array('conditions' => array('Invoice.' . $this->Invoice->primaryKey => $id));
        $this->set('invoice', $this->Invoice->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Invoice->create();
            if ($this->Invoice->save($this->request->data)) {
                $this->Flash->set(__('The invoice has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The invoice could not be saved. Please, try again.'));
            }
        }
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Invoice->exists($id)) {
            throw new NotFoundException(__('Invalid invoice'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Invoice->save($this->request->data)) {
                $this->Flash->set(__('The invoice has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->set(__('The invoice could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Invoice.' . $this->Invoice->primaryKey => $id));
            $this->request->data = $this->Invoice->find('first', $options);
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Invoice->id = $id;
        if (!$this->Invoice->exists()) {
            throw new NotFoundException(__('Invalid invoice'));
        }
        $this->request->onlyAllow('post', 'delete');
        if ($this->Invoice->delete()) {
            $this->Flash->set(__('Invoice deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Flash->set(__('Invoice was not deleted'));
        $this->redirect(array('action' => 'index'));
    }
    
    public function listInvoices(){
        $this->index();
        $this->render('index');
    }
	
	public function invoice($id = null) {
		if ($this->request->is('post')) {
			$customer = explode('/',$this->request->data['Invoice']['customers']);
			$this->redirect(array('controller' => 'invoiceItems', 'action' => 'fetchInvoiceLis', $customer[0], 'Customer'));
		}

		$customers = $this->User->getPermittedCustomers($this->Auth->user('id'));
		$this->set(compact('customers'));
	}
	
	/**
	 * The process to finalize an invoice
	 * 
	 * Send it to EPMS
	 * Link the line items up to an Invoice record
	 * Possibly show the invoice again, though this seems unnecessary
	 * 
	 * @param type $cust_id
	 */
	public function submitInvoice($cust_id) {
		$this->Invoice->customer = $cust_id;
		$this->invoice = $this->requestAction(array('controller' => 'invoice_items', 'action' => 'fetchInvoiceLis', $cust_id, 'Customer'));
//		debug($this->invoice);die;
		
		if ($this->invoice) {
			$this->packageInvoiceItems();
			$this->Flash->success('The invoice saved');
			$this->updateInvoiceStatus($cust_id);
			// change the involved orders to 'invoiced' status
			// use the ordercontroller method? or write a new one?
		} else {
			$this->Flash->error('The invoice did not save. Please try again');
		}
		$this->set('invoiceId', $this->Invoice->id);
		$this->set('showInvoicePDF', $this->Invoice->id);
		// temporary destination
		$this->redirect(array('controller' => 'users', 'action' => 'edit_userGrain', $cust_id, $this->secureHash($cust_id), $this->Invoice->id));
//		$this->redirect(array('controller' => 'clients', 'action' => 'status'));
	}
	
	/**
	 * After an invoice is sent to epms, bind its line items to an ivoice record
	 */
	private function packageInvoiceItems() {
		$this->Invoice->InvoiceItem->lineItems = $this->invoice;
		if (!$this->Invoice->newInvoice()) {
			$this->Flash->error('An invoice was not created. Please try again.');
			return FALSE;
		}
		if(!$this->Invoice->InvoiceItem->linkInvoiceItems($this->Invoice->id)){
			$this->Flash->error('An invoice was created, but the charge items failed to link. Please try again.');
			return FALSE;
		}
		$this->Flash->success('The charge items were successfully linked to an invoice');
		return TRUE;
	}
	
	private function updateInvoiceStatus($cust_id) {
		$orders = $this->Invoice->InvoiceItem->Order->find('list', array(
			'conditions' => array(
				'Order.user_customer_id' => $cust_id,
				'Order.transaction' => 1,
				'Order.status' => 'Shipped',
                'Order.exclude' => 0
			)
		));
		$exclude = $this->Invoice->InvoiceItem->Order->find('list', array(
			'conditions' => array(
				'Order.user_customer_id' => $cust_id,
				'Order.transaction' => 1,
				'Order.status' => 'Shipped',
                'Order.exclude' => 1
			)
		));
		foreach ($orders as $order) {
			$this->requestAction(array('controller' => 'orders', 'action' => 'statusChange', $order, 'Invoice', 'robot' => TRUE));
//			$this->Invoice->InvoiceItem->Order->statusChange($order, 'Invoice');
		}
        if (count($exclude) > 0) {
            foreach ($exclude as $order) {
                $this->Invoice->InvoiceItem->Order->id = $order;
                $this->Invoice->InvoiceItem->Order->saveField('exclude', 0);
            }
        }        
	}
	
	/**
	 * @todo Line 235 sets request::params['ext'] which is abandoned
	 * 
	 * @param string $invoiceId
	 */
	public function viewOldInvoice($invoiceId) {
		set_time_limit(300);
		$this->layout = 'default';
		$invoice = $this->Invoice->fetchInvoice($invoiceId);
		$total = $this->Invoice->fetchInvoiceTotal($invoiceId);
//		$this->ddd($invoice, 'invoice');
//		die;
		$customer = $this->Invoice->InvoiceItem->Customer->fetchCustomer($invoice['Invoice']['customer_id']);
		$data = array(
			'reference' => array(
				'labels' => array('Date', 'Invoice Number'),
				'data' => array(date('m/d/y', strtotime($invoice['Invoice']['created'])), $invoice['Invoice']['job_number'])
			),
//			'items' => $invoice['InvoiceItem'],
			'groupedCharges' => $this->Invoice->groupedCharges,
			'groupedSummary' => $this->Invoice->groupedSummary,
            'orderItems' => $this->Invoice->orderItems,
			'summary' => array(
				'labels' => array('Date', 'Customer Id', 'Total'),
				'data' => array(
					date('m/d/y', strtotime($invoice['Invoice']['modified'])),
					$customer['Customer']['customer_code'],
					'$' . number_format($total, 2)
					),
				),
			'headerRow' => array('#', 'Desc', 'Qty', 'Unit', 'Price', 'Subtotal'),
			'shipping' => array(),
			'customer_type' => $customer['Customer']['customer_type'],
			'billing' => array(
				$customer['User']['username'],
				"Attn: {$customer['Customer']['billing_contact']}",
				$customer['Address']['address'],
				$customer['Address']['address2'],
				"{$customer['Address']['city']} {$customer['Address']['state']} {$customer['Address']['zip']} {$customer['Address']['country']}"
			)
		);
//		$this->ddd($data);
//		die;
        $ordItems = array();
        foreach ($this->Invoice->groupedCharges as $order_id => $charges) {
            $ordItems[$order_id] = $this->Invoice->InvoiceItem->OrderItem->fetchByOrder($order_id);
        }
		$type = 'invoice';
		$this->set(compact('data', 'type', 'ordItems'));
	}
	
	/**
	 * Save the invoice job_number
	 * 
	 * @param type $id id of the invoice record
	 * @param type $jobNumber new job_number data
	 */
	public function saveInvoiceNumber($id, $jobNumber) {
		$this->layout = 'ajax';
		$this->Invoice->id = $id;
		if($this->Invoice->saveField('job_number', $jobNumber)){
			$jsonReturn = array('response' => TRUE);
		} else {
			$jsonReturn = array('response' => FALSE);
		}
		$this->set('jsonReturn', $jsonReturn);
		$this->render('/AppAjax/json_response');
	}
}
