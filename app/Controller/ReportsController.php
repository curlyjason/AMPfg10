<?php

App::uses('AppController', 'Controller');
App::uses('Customer', 'Model');
App::uses('User', 'Model');
App::uses('AButton', 'Lib');
App::uses('AssociateTableHelper', 'Helper');

/**
 * Addresses Controller
 *
 * @property Address $Address
 */
class ReportsController extends AppController {
	
	public $uses = array();
	
	public $months = array(
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December',
		'13' => 'January'
	);

	
	function beforeFilter() {
		parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('addressAdd', 'addressEdit', 'addressDelete', 'getAddress');
		$this->accessPattern['Guest'] = array('addressAdd', 'addressEdit', 'addressDelete');
	}

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }
	
	public function report(){
		if ($this->request->is('post')) {
			//process dates
			$start = strtotime($this->request->data['User']['start_month']['month'] . '/01/' . $this->request->data['User']['start_year']['year']);
			$endMonth = $this->months[$this->request->data['User']['end_month']['month']];
			$end = strtotime($endMonth . '1, ' . $this->request->data['User']['end_year']['year'] . '+1 month - 1 second');
			if(!$start && $end){
				$this->Session->setFlash('Invalid date', 'flash_error');
			} else {
				if ($start > $end) {
					$e = $end;
					$end = $start;
					$start = $e;
					unset($e);
				}
				$customer = $this->validateSelect($this->request->data['User']['customers']);
				$this->redirect(array('controller' => $this->request->data['User']['report'], 'action' => 'activity', $start, $end, $customer[0]));
			}
		}
		$customers = $this->User->getPermittedCustomers($this->Auth->user('id'));
		$this->set(compact('customers'));
	}
	
	/**
	 * Run a Current Inventory Levels report for any number of customers
	 * 
	 * Report will sort:
	 *		Active customers
	 *			Active Items
	 *			Inactive Items
	 *		Inactive customers
	 *			Items
	 */
	public function inventoryLevels() {
			$customers = array(1 => 'All Customers', 2 => 'Only Active');
			$customers += $this->User->getPermittedCustomers($this->Auth->user('id'), TRUE, FALSE);
			$this->set(compact('customers'));
	}
	
	/**
	 * Output side of the Inventory Levels report
	 * 
	 * This side is used by both the screen view and the PDF view
	 * In the case of the PDF view, we pass the customer list and sort to the function
	 * In the case of the screen view, these parameters are passed on TRD
	 * 
	 * @param string $pdfData the customer list for PDF view
	 * @param string $sort the sort criteria for PDF view
	 */
	public function inventoryStateReport($pdfData = NULL, $sort = NULL) {
		set_time_limit(300);
		if(isset($this->request->params['ext']) && $this->request->params['ext'] == 'pdf'){
			$this->layout = 'default';
			$pdfArray = explode('-', $pdfData);
			foreach ($pdfArray as $id) {
				$this->request->data['Reports']['customer'][] = $id . '/' . $this->User->secureHash($id);
			}
			$this->request->data['Reports']['sort'] = $sort;
		}
		// 'all' or just some customers chosen
		$cust = array();
		if (in_array('1', $this->request->data['Reports']['customer'])) {
			// this is the 'all customers' choice
			$cust = array_flip($this->User->getPermittedCustomers($this->Auth->user('id'), FALSE, FALSE));
		} elseif (in_array('2', $this->request->data['Reports']['customer'])) {
			// this is the 'all active customers' choice
			$cust = array_flip($this->User->getPermittedCustomers($this->Auth->user('id'), FALSE));
		} else {
			// this is some arbitrary selection of customers
			foreach ($this->request->data['Reports']['customer'] as $hash) {
				$explodedHash = explode('/', $hash);
				if($this->User->secureId($explodedHash[0], $explodedHash[1])){
					$cust[] = $explodedHash[0];
				}
			}				
		}
				
		if(count($cust) === 0){
			$this->Session->setFlash('Please choose a customer', 'flash_error');
		} else {
			$User = ClassRegistry::init('User');
			$sort = $this->request->data['Reports']['sort'];
			$customers = $User->inventoryReportCustomers($cust, $sort);
			$this->set(compact('customers', 'sort'));
		}
	}

}
