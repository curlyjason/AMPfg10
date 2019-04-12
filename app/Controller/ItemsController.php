<?php
App::uses('AppController', 'Controller');
App::uses('Catalog', 'View/Helper');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('FileExtension', 'Lib');

/**
 * Items Controller
 *
 * @property Item $Item
 */
class ItemsController extends AppController {

    public $helpers = array('Catalog', 'Report');
   
    public $error = '';
    
    public $itemRecord = array();
	
	public $report = array();
	
	public $uses = array('Item', 'Log');
    
    public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array('index', 'listInventoryLevels', 'view');
		$this->accessPattern['Guest'] = array('listInventoryLevels');
		$this->accessPattern['Warehouse'] = array('listInventoryLevels');
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
		$this->Item->recursive = 0;
		$this->set('items', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null, $hash = null) {
	    if ($hash != null) {
		if (!$this->secureID($id, $hash)) {
		    throw new NotFoundException('Security checkpoint: Hey! You hacking the URL?');
		}
	    }
		if (!$this->Item->exists($id)) {
			throw new NotFoundException(__('Invalid item'));
		}
		$options = array('conditions' => array('Item.' . $this->Item->primaryKey => $id));
		$this->set('item', $this->Item->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Item->create();
			if ($this->Item->save($this->request->data)) {
				$this->Session->setFlash(__('The item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The item could not be saved. Please, try again.'));
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
	public function edit($id = null, $hash = null) {
	    if ($hash != null) {
		if (!$this->secureID($id, $hash)) {
		    throw new NotFoundException('Security checkpoint: Hey! You hacking the URL?');
		}
	    }
		if (!$this->Item->exists($id)) {
			throw new NotFoundException(__('Invalid item'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		if (!$this->secureData($this->request->data,'Item')) {
		    throw new NotFoundException('Security checkpoint: Hey! You messing with the form data?');
		}
			if ($this->Item->save($this->request->data)) {
				$this->Session->setFlash(__('The item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The item could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Item.' . $this->Item->primaryKey => $id));
			$this->request->data = $this->Item->find('first', $options);
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
		$this->Item->id = $id;
		if (!$this->Item->exists()) {
			throw new NotFoundException(__('Invalid item'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Item->delete()) {
			$this->Session->setFlash(__('Item deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Item was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
    
    //Copied from shoppingcart
    
    
    public function listInventoryLevels(){
        $this->index();
        $this->render('index');
    }
	
    
    /**
     * Update inventory levels for an item
     * 
     * @param int $itemId The id of the item
     * @param int $pullQty The requested quantity change, negative for pulls, positive for receipts
     * @param int $onHand The item's onHand inventory level
     * @param string $rowId Order or Replenishemnt id
     * @param string $mode order or replenishment
     * @return JSON with the error message (if any) and the final item record
     */
    public function updateInventory(){
//		$this->ddd($this->request->data);
			extract($this->request->data);
			$customerUserId = $this->Item->discoverCustomerUserId($itemId);
			//this->request->data contains
			//array(
			//	'pullQty' => '1',
			//	'itemId' => '90',
			//	'rowId' => '52d08471-8838-4330-b765-00ed47139427',
			//	'onHand' => 'undefined',
			//	'mode' => 'pull'
			//)
			//setup return array
			$returnArray['rowId'] = '#row-'.  $rowId;

			//update the orderItem pull status, conditionally
			if ($mode == 'pull') {
				$onHand = $this->updateKitInventory();
				$pullFlag = ($pullQty > 0) ? 0 : 1;
				$this->Item->OrderItem->id = $rowId;
				$this->Item->OrderItem->saveField('pulled', $pullFlag);
				$orderNumber = $this->Item->OrderItem->pullOrderNumber($rowId);
			} else {
				$pullFlag = ($pullQty < 0) ? 0 : 1;
				$this->Item->ReplenishmentItem->id = $rowId;
				$this->Item->ReplenishmentItem->saveField('pulled', $pullFlag);
				$orderNumber = $this->Item->ReplenishmentItem->pullOrderNumber($rowId);
			}
			//validate the inventory change and handle onHand changes
			$success = $this->validateInventoryChange($itemId, $pullQty, $onHand);

			//if validation succeeds, proceed ($this->itemRecord set by validate method)
			if ($success) {
				$origQty = $this->itemRecord['Item']['quantity'];

				$this->itemRecord['Item']['quantity'] = $newQty = $this->calculateItemQuantity($this->request->data);

//		$this->ddd($this->itemRecord, 'itemRecord');
//		die;

				if ($this->Item->save($this->itemRecord)) {
					$returnArray['item'] = $this->itemRecord;
				} else {
					$returnArray['item'] = false;
				}
				$itemId = $this->itemRecord['Item']['id'];
//				$itemName = $this->itemRecord['Item']['name'];
				
				$this->Log->create('adjustment');
				$this->Log
					->set('customer', $customerUserId)
					->set('id', $itemId)
					->set('name', $this->itemRecord['Item']['name'])
					->set('from', $origQty)
					->set('to', $newQty)
					->set('change', $newQty-$origQty)
					->set('number', $orderNumber)
					->set('by', $this->Item->OrderItem->Order->User->discoverName($this->Auth->user('id')));
				$this->Log->toString();
				CakeLog::write('inventory', $this->Log->logLineOut);
//				CakeLog::write('inventory', "[$customerUserId] Inventory adjustment of id:$itemId::$itemName from $origQty to $newQty ($pullQty) by " . $this->Item->OrderItem->Order->User->discoverName($this->Auth->user('id')));

				if ($mode = 'replenishment') {
					array_merge($returnArray, $this->Item->manageUncommitted($itemId));
					array_merge($returnArray, $this->Item->managePendingQty($itemId));
				}
			} else {
				$returnArray['item'] = false;
			}
			$returnArray['error'] = $this->error;
        $this->autoRender = false;
			echo json_encode($returnArray);
    }
	
	/**
	 * Trigger automatic kitUp or breakKit functions for handling
	 * OnDemand and InventoryOnly Kits
	 * 
	 */
	public function updateKitInventory() {
		extract($this->request->data);
		//this->request->data contains
		//array(
		//	'pullQty' => '1',
		//	'itemId' => '90',
		//	'rowId' => '52d08471-8838-4330-b765-00ed47139427',
		//	'onHand' => 'undefined',
		//	'mode' => 'pull'
		//)
		//setup return array
		$orderItemType = $this->Item->OrderItem->field('catalog_type', array('id' => $rowId));
		if($orderItemType & (ON_DEMAND | INVENTORY_KIT)){
			$orderItemCatId = $this->Item->OrderItem->field('catalog_id', array('id' => $rowId));
			$this->requestAction(array('controller' => 'Catalogs', 'action' => 'kitAdjustment', $orderItemCatId, abs($pullQty), $orderItemType));
			$onHand = $this->Item->field('quantity', array('id' => $itemId));
		}
		return $onHand;
	}
	
	/**
	 * Handle manual adjustment of inventory
	 * 
	 * this ajax call expects
	 * <pre>
	 * array(
	 *  'Item' => array(
	 *		'id' => '89',
	 *		'quantity' => '50.0'
	 *	),
	 *	'rowId' => '#row-52d48b5f-2518-45d4-87c0-082c47139427'
	 * )
	 * </pre>
	 * But you should be able to call it with just the Item array from a controller
	 */
	public function adjustOnHand() {
		$this->autoRender = false;
		// verify numeric data first
		// check the item exists too I guess
		
		$id = $this->request->data['Item']['id'];

		// the next method expects a different property
		// and it's structured a little differently
		$this->itemRecord = $this->request->data; 
		$this->itemRecord['Item']['quantity'] = $this->request->data['Item']['orig_value'];
		$save = $this->updateOnHandInventory($this->request->data['Item']['quantity']);
		
		if (strlen($this->error) > 0) {
			$this->request->data['Item']['error'] = is_string($this->error);
		}
		if ($save) {
			$this->request->data['Item'] = array_merge($this->request->data['Item'], $this->Item->manageUncommitted($id));
			$this->request->data['Item'] = array_merge($this->request->data['Item'], $this->Item->managePendingQty($id));
			
			// the return array now has all the update values we will need for the page
		}
		echo json_encode($this->request->data);
	}
    
    /**
     * Pull the data for the requested item and validate the requested inventory change
     * 
     * @param int $item_id The id of the item
     * @param int $qty The requested change quantity, negative for pull and positive for receipt
     * @param int $onHand The onHand inventory value
     * @return boolean
     */
    private function validateInventoryChange($item_id, $qty, $onHand) {
        //setup success variable
        $success = true;
        
        //fetch the item record
        $this->itemRecord = $this->Item->find('first', array(
            'conditions' => array(
                'Item.id' => $item_id
            ),
            'contain' => false
        ));
        
        //check for onHand change
        if($onHand != $this->itemRecord['Item']['quantity']){
            $success = $this->updateOnHandInventory($onHand);
        }
        
        //check for available inventory to pull
        if($success){
            if($this->itemRecord['Item']['quantity'] + $qty >= 0 && $success){
                $success = true;
            } else {
                $this->error = 'There is not enough inventory to pull this item';
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Return the proper Item quantity for this situation
     * 
     * @param int $itemId The id of the item
     * @param int $pullQty The requested quantity change, negative for pulls, positive for receipts
     * @param int $onHand The item's onHand inventory level
     * @param string $rowId Order or Replenishemnt id
     * @param string $mode order or replenishment
     * @return float the proper quantity
     */
    public function calculateItemQuantity($data){
		extract($data);
		if ($mode == 'order' || $mode == 'pull') {
			$sell_qty = $this->Item->OrderItem->field('sell_quantity', array('id' => $rowId));
			return $this->itemRecord['Item']['quantity'] + ($pullQty * $sell_qty);
		} elseif ($mode == 'replenishment') {
			// we need the quantity per unit for this replenishment item
			$po_qty = $this->Item->ReplenishmentItem->field('po_quantity', array('id' => $rowId));
			return $this->itemRecord['Item']['quantity'] + ($pullQty * $po_qty);
		}
    }
    /**
     * Update inventory level to onHand value provided by user
     * 
     * This function performs an error captured save of the inventory record
     * and writes a log of the change to the inventory log
     * 
     * @param int $onHand
     * @return boolean The success of the method
     */
    private function updateOnHandInventory($onHand) {
        $origQty = $this->itemRecord['Item']['quantity'];
        $this->itemRecord['Item']['quantity'] = $onHand;
//		$customerUserId = $this->discoverCustomerUserId($this->itemRecord['Item']['id']);
//		$this->ddd($this->itemRecord, 'Item.itemRecord property');
        if($this->Item->save($this->itemRecord)){
			
			$this->Log->create('override');
			$this->Log
				->set('customer', $this->Item->discoverCustomerUserId($this->itemRecord['Item']['id']))
				->set('id', $this->itemRecord['Item']['id'])
				->set('name', $this->itemRecord['Item']['name'])
				->set('from', $origQty)
				->set('to', $onHand)
				->set('change', $onHand - $origQty)
				->set('number', 'OVERRIDE')
				->set('by', $this->Item->OrderItem->Order->User->discoverName($this->Auth->user('id')));
			$this->Log->toString();
			CakeLog::write('inventory', $this->Log->logLineOut);
//            CakeLog::write('inventory', "[$customerUserId] ON HAND INVENTORY OVERRIDE FOR id:$itemId::$itemName from $origQty to $onHand by " . $this->Item->OrderItem->Order->User->discoverName($this->Auth->user('id')));			

			$success = true;
        } else {
            $this->error = 'On Hand value update failed, please try again.';
            $success = false;
        }
        return $success;
    }
	
	/**
	 * Find a single item record based upon id
	 * 
	 * @param string $id, the item id
	 * @return array, the found record
	 */
	public function fetchJsonItem($id) {
		$this->autoRender = FALSE;
		$item = $this->Item->find('first', array(
			'conditions' => array(
				'Item.id' => $id
			),
			'contain' => array(
				'Image'
			),
			'fields' => array(
				'Item.id as ItemId',
				'Item.name as CatalogName', 
				'Item.max_quantity as CatalogMaxQuantity', 
				'Item.price as CatalogPrice', 
				'Item.description as CatalogDescription', 
				'Item.item_code as CatalogItemCode', 
				'Item.customer_item_code as CatalogCustomerItemCode',
				'Item.quantity as ItemQuantity'
			)
		));
		
		// move the image in and default it if necessary
		$idir = 'no';
		$iname = 'image.jpg';
		if (!empty($item['Image'][0]['img_file'])) {
			$iname = $item['Image'][0]['img_file'];
			$idir = $item['Image'][0]['id'];
		}
		$item['Item']['ajaxEditImage']['name'] = $iname;
		$item['Item']['ajaxEditImage']['dir'] = $idir;
		
		return json_encode($item['Item']);
//		return json_encode(array_merge($item, $item['Item']));
	}
	
	/**
	 * Generate an inventory activity report
	 * 
	 * @param type $start
	 * @param type $end
	 * @param type $customer
	 */
	public function activity($start, $end, $customer) {
//		debug(func_get_args());die;date('Y-m-d H:i:s', $end)
		
		$start = date('Y-m-d H:i:s', $start);
		$end = date('Y-m-d H:i:s', $end);
		//note here
        if(isset($this->request->params['ext']) && $this->request->params['ext'] == 'pdf'){
			$this->layout = 'default';
		}
		$this->report['customer'] = $customer;
		
		$this->discoverOldestLogTime(); // sets report['startLimit']
		$this->report['firstTime'] = strtotime($start);
		if ($this->report['firstTime'] < $this->report['startLimit']) {
			$this->report['firstTime'] = $this->report['startLimit'];
		}
		
		$this->report['firstSnapshot'] = strtotime(date('F j, Y', $this->report['firstTime']) . ' - 1 month');
		
		$this->discoverNewestLogTime(); // sets report['endLimit']
		$this->report['finalTime'] = strtotime($end);
		if ($this->report['finalTime'] >= $this->report['endLimit']) {
			$this->report['finalTime'] = $this->report['endLimit'];
			$this->logInventoryTotals();
		}
		
		$this->report['firstYear'] = date('Y', $this->report['firstTime']);
		$this->report['firstWeek'] = date('W', $this->report['firstTime']);
		$this->report['finalYear'] = date('Y', $this->report['finalTime']);
		$this->report['finalWeek'] = date('W', $this->report['finalTime']);
		
		$this->report['finalWeekTime'] = strtotime("+{$this->report['finalWeek']} week 1/1/{$this->report['finalYear']}");
		$this->report['firstWeekTime'] = strtotime("+{$this->report['firstWeek']} week 1/1/{$this->report['firstYear']}");
		
		$this->report['items'] = $this->Item->Catalog->allItemsForCustomer($this->report['customer']);
		if ($this->report['items']) {
			$this->logsInWeekRange();
			$this->logEntriesInDateRange();
			$this->snapshotEntriesInDateRange();
		} else {
			$this->Session->setFlash('There are no items for this customer.');
		}
		$this->set('customers', $this->User->getPermittedCustomers($this->Auth->user('id')));
		$this->set('customerName', $this->Item->Catalog->User->discoverName($customer));
		$this->set('report', $this->report);
		$this->set(compact('start', 'end', 'customer'));
		if ($this->request->params['action'] == 'activity') {
			$this->render('activity');
		} else {
			return;
		}
//		debug($this->report['activityEntries']);
//		debug($this->report);
	}
	
	/**
	 * Record inventory logs that overlap the weeks in $this->report property
	 * 
	 * Sets the $this->report['files] property
	 */
	private function logsInWeekRange() {
		$dir = LOGS.'Inventory/inventory/';
		$this->report['files'] = array();

		// Open a known directory, and proceed to read its contents
		if (is_dir($dir)) {
			$dh = new Folder($dir);
			$dh->sort = TRUE;
//			$st = $this->startYear;
//			$sm = $this->startMonth;
//			$ey = $this->endYear;
//			$em = $this->endMonth;
			
			$files = $dh->find('.*\.log');
			foreach ($files as $file) {
				$name = preg_replace('/inventory\.(\d+)\.[\d]+\.(\d+)\.log/', '+$2 week 1/1/$1 -1 week', $file);
				if (stristr($file, 'inventory') && strtotime($name) >= $this->report['firstWeekTime'] && strtotime($name) <= $this->report['finalWeekTime']) {
					$this->report['files'][] = $dir . $file;
				}
			}
//			closedir($dh);
//			foreach ($files as $file) {
//				$this->report['files'][] = $file;
//			}
//			if ($dh = opendir($dir)) {
//				// walk it
//				while (($file = readdir($dh)) !== false) {
//					// make a date string from the log file name
//					$name = preg_replace('/inventory\.(\d+)\.[\d]+\.(\d+)\.log/', '+$2 week 1/1/$1 -1 week', $file);
//
//					// if the log file is within the requested range of weeks, save the file name for later processing
//					if (stristr($file, 'inventory') && strtotime($name) >= $this->report['firstWeekTime'] && strtotime($name) <= $this->report['finalWeekTime']) {
//						$this->report['files'][] = $dir . $file;
//					}
//				}
//				closedir($dh);
//			}
		}
		
		$dir = LOGS.'Inventory/snapshot/';
		$this->report['snapshot'] = array();
		
		// Open a known directory, and proceed to read its contents
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				// walk it
				while (($file = readdir($dh)) !== false) {
						$name = preg_replace('/snapshot.(\d+).(\d+).log/', '$2/1/$1', $file);
//						$this->ddd($name, 'Snapshot File Name');
//						$this->ddd(strtotime($name), 'String to time $name');
//						$this->ddd(date('F j, Y', $this->report['firstTime']), 'firstTime');
//						$this->ddd($this->report['firstTime'] - MONTH, 'first time minus month');
						if (stristr($file, 'snapshot') && strtotime($name) >= ($this->report['firstSnapshot']) && strtotime($name) <= $this->report['finalTime']) {
							$n = (count($this->report['snapshot']) > 0) ? 1 : 0;
							$this->report['snapshot'][$n] = $dir . $file;
						}
				}
				closedir($dh);
			}
		}
		
		// finish directory loop
	}

	/**
	 * Collect log entries for the customer in the date range
	 * 
	 * everything uses $this->report property
	 */
	private function logEntriesInDateRange() {
		// loop the discovered files		
		foreach ($this->report['files'] as $file) {
			$handle = @fopen($file, "r");
			if ($handle) {
				while (($buffer = fgets($handle, 4096)) !== false) {
					$this->Log->parseLogLine($buffer);
					// is it an log entry for the customer of interest?
					if ($this->Log->logLine['customer'] == $this->report['customer']) {
						// if the log entry is in the specific date range, save it
						if ($this->Log->meta['datetime'] >= $this->report['firstTime'] && $this->Log->meta['datetime'] <= $this->report['finalTime']) {
							$this->report['items'][$this->Log->logLine['id']]['Activity'][] = array_merge($this->Log->meta, $this->Log->logLine);
						}
					}			
				}
				if (!feof($handle)) {
					echo "Error: unexpected fgets() fail\n<br />";
				}
				fclose($handle);
			}
		}
	}
	
	private function snapshotEntriesInDateRange() {
		// loop the discovered files
		foreach ($this->report['snapshot'] as $file) {
			$handle = @fopen($file, "r");
			if ($handle) {
				while (($buffer = fgets($handle, 4096)) !== false) {
					$this->Log->parseLogLine($buffer);
					// is it an log entry for the customer of interest?
					if ($this->Log->logLine['customer'] == $this->report['customer']) {
						// if the log entry is in the specific date range, save it
//							$this->ddd($this->Log->meta, 'pre');
//						if ($this->Log->meta['datetime'] >= $this->report['firstTime'] && $this->Log->meta['datetime'] <= $this->report['finalTime']) {
//							$this->ddd($this->Log->meta, 'post');
						$this->report['items'][$this->Log->logLine['id']]['Snapshot'][] = array_merge($this->Log->meta, $this->Log->logLine);
//						}
					}			
				}
				if (!feof($handle)) {
					echo "Error: unexpected fgets() fail\n<br />";
				}
				fclose($handle);
			}
		}
	}
	
	/**
	 * 
	 */
	public function logInventoryTotals() {
		$file = LOGS.'Inventory/snapshot/snapshot'.date('.Y.m').'.log';
		touch($file);
		unlink($file);
		$this->autoRender = FALSE;
		$allItems = $this->Item->find('all', array(
			'fields' => array(
				'Item.id',
				'Item.name',
				'Item.quantity',
				'Item.active',
				'Item.customer_item_code',
				'Item.item_code'
			),
			'contain' => array(
				'Catalog' => array(
					'conditions' => array(
						'Catalog.type !=' => FOLDER
					),
					'fields' => array(
						'Catalog.id',
						'Catalog.ancestor_list',
						'Catalog.active'
						)
					)
		)));
		foreach ($allItems as $index => $item) {
			$i = $item['Item'];
			$active = ($this->determineActiveSates($item['Catalog']) && $i['active'])
				? 'active'
				: 'inactive';
			$clientsCode = (strlen($i['customer_item_code']) > 0) ? $i['customer_item_code'] : 'ID'.$i['id'];
			$code = (strlen($i['item_code']) > 0) ? $i['item_code'] : 'ID'.$i['id'];
			$customerUserId = $this->Item->discoverCustomerUserId($i['id']);
			
			$this->Log->create('Snapshot');
			$this->Log
				->set('customer', $customerUserId)
				->set('id', $i['id'])
				->set('Clients_code', $clientsCode)
				->set('Staff_code', $code)
				->set('name', $i['name'])
				->set('state', $active)
				->set('inventory', $i['quantity']);
			
			$this->Log->toString();
			CakeLog::write('snapshot', $this->Log->logLineOut);
		}
	}
	
	/**
	 * Determine if any of the provided catalogs are active
	 * 
	 * array (Catalog => array(
	 *		0 => array(fields)
	 *		1 => array(fields)
	 *		...
	 * 
	 * @param array $catalogs A set of catalog records
	 * @return int The number of active catalogs
	 */
	private function determineActiveSates($catalogs) {
		$active = 0;
		foreach ($catalogs as $catalog) {
			$active = $active + $catalog['active'];
		}
		return ($active > 0);
	}
	
	/**
	 * Use the saved log files to determine the month & year of the oldest file
	 * 
	 * Files are sorted by name, guaranteeing first file is the oldest
	 * ((files are named inventory.2014.01.03.log 'inventory.#year#.#month#.#weeknumber#.log'))
	 * 
	 * Sets report['startLimit'] as the time of the logs first day of month
	 * 
	 * @return string - the year.month (xxxx.xx) of the oldest available log file
	 */
    public function discoverOldestLogTime() {
        $this->autoRender = FALSE;
        $dir = LOGS.'Inventory/inventory/';
        if (!is_dir($dir)) {
            $dh = new Folder($dir, true, 0755);
        } else {
            $dh = new Folder($dir);
        }

        $dh->sort = TRUE;
        $files = $dh->find('.*\.log');
        if(!empty($files)){
            preg_match('/inventory.(\d+.\d+)./', $files[0], $match);
            $this->report['startLimit'] = strtotime(preg_replace('/(\d+).(\d+)/','$2/1/$1', $match[1]));
        } else {
            $this->report['startLimit'] = time();
        }
    }
	
	/**
	 * Discover the latest possible date for a report end-date
	 * 
	 * snapshot logs are written at midnight on the last day of the month
	 * so no inventory activity report can be generated on or ending in the current month
	 * 
	 */
	public function discoverNewestLogTime() {
//		debug(date('F 1, Y', time()) . ' + 1 month -1 day');
		$this->report['endLimit'] = strtotime(date('F 1, Y', time()) . ' + 1 month -1 day');
	}
	
	/**
	 * Show the data behind the current inventory levels of a single item
	 * 
	 * @param int $id hashed catalog id
	 */
	public function itemHistory($catalog_id) {
		//pull catalog id from the hash
		$c = $this->validateSelect($catalog_id, 'li');
		//discover item id from catalog record
		$id = $this->Item->Catalog->field('item_id', array('id' => $c[0]));
		$item = $this->Item->fetchItemHistory($id);
		if(!empty($item['Cart'])){
			foreach ($item['Cart'] as $index => $cart) {
				$item['Cart'][$index]['username'] = $this->User->discoverName($cart['user_id']);
			}
		}
		
		// generate an iventory report for the past 3 months
		// this could be improved to start on the first of a month. 
		// it just does 90 day backwards from today
		$end = time();
		$start = time() - (3*MONTH);
		$this->activity($start, $end, $item['Catalog'][0]['customer_user_id']);
		$raw = $this->report;
		
		// only report ont this one inventory item
		foreach($raw['items'] as $item_id => $data) {
			if ($item_id != $id) {
				unset($this->report['items'][$item_id]);
			}
		}
		
		$this->set('report', $this->report);
		
		$this->set('item', $item);
		$this->render();
	}
	
	/**
	 * Universal testMe function
	 */
	public function testMe() {
		$this->logInventoryTotals();
		exit;
	}
	
		
	//============================================================
	// METHODS FOR AUTOMATIC COMPONENT
	//============================================================
	
//	public function fetchInventorySnapshot($token){
//		$token = '52d6d83d-5bb4-4724-93ef-038e47139427';//to be replace with the user's UUID token from input data
//		$this->Automatic = $this->Components->load('Automatic');
//		$this->Automatic->settings = array(
//			'mode' => 'inventorySnapshot',
//			'xmlTemplate' => array(),
//			'fetchRecordData' => 'fetchInventorySnapshotData',
//		);
//		$this->Automatic->initialize($this);
//		$customerUserId = $this->User->Customer->fetchCustomerUserId($token);
//		$xml = $this->Item->customerInventorySnapshot($customerUserId);
//		$this->Automatic->output($xml);
//	}
//	
//	public function reportLowInventory($data) {
//		$token = 'blahfoo';//to be replace with the user's UUID token from input data
//		$this->Automatic = $this->Components->load('Automatic');
//		$this->Automatic->settings = array(
//			'mode' => 'lowItemInventory',
//			'xmlTemplate' => array(),
//			'fetchRecordData' => 'fetchLowInventoryData',
//		);
//		$this->Automatic->initialize($this);
//		$this->Automatic->output($token);
//	}
//
//	public function addSecureRecordData() {
//		$this->ddd('In addSecureRecordData Items', 'addSecureRecordData');
//		return TRUE;
//	}
//	
//	public function saveRecordData() {
//		$this->ddd('In saveRecordData Items', 'saveRecordData');
//		return TRUE;
//	}
//	
//	public function fetchInventorySnapshotData() {
//		$this->ddd('In fetchInventorySnapshotData', 'fetchInventorySnapshotData');
//		return TRUE;
//	}
//	
//	public function fetchLowInventoryData() {
//		$this->ddd('In fetchLowInventoryData', 'fetchLowInventoryData');
//		return TRUE;
//	}
//	
//	public function testMe() {
//		$this->Item->customerInventorySnapshot(46);
////		$this->autoRender = false;
////		$this->Automatic->input('<xml><stuff>my content from item</stuff></xml>');
//	}

} 
