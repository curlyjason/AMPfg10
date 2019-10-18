<?php

/**
 * Catalogs Controller
 *
 * Catalog is the Tree that organizes Items
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Controller
 */
App::uses('AppController', 'Controller');

/**
 * Catalogs Controller
 *
 * Catalog is the Tree that organizes Items
 *
 * @package	app.Controller
 * @property Catalog $Catalog
 */
class CatalogsController extends AppController {
// <editor-fold defaultstate="collapsed" desc="Properties">

	private $containers = array();
	private $itemsIn = array();
	private $catalogs = array();
	private $items = array();
	private $defaultLimit = 10;
	protected $_activeChange;
	
	public $helpers = array(
		'Status'
	);
	
	public $kit = array();
	public $components = array('Paginator');
	public $maxKitUp = '';

// </editor-fold>


	public function beforeFilter() {
        parent::beforeFilter();
        $this->Catalog->userId = $this->Auth->user('id');
        $roots = $this->Auth->user("CatalogRoots");
        $this->Catalog->rootOwner = isset($roots[$this->Catalog->ultimateRoot]);

		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('all');
		$this->accessPattern['Guest'] = array ('all');
		$this->accessPattern['AdminsManager'] = array ('all');
    }

    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }

    //============================================================
    // BASIC CRUD
    //============================================================

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->Catalog->recursive = 2;
        $this->Catalog->recursive = 0;
        $this->set('catalogs', $this->paginate());
    }

    /**
     * Make a shop-able product detail page
	 * 
	 * This serves as the single-page vesion of store-grain
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Catalog->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
		
		$options = array(
			'conditions' => array(
				'Catalog.' . $this->Catalog->primaryKey => $id
				),
			'contain' => array(
				'Item' => array(
					'Image'
				)
			));
        $product = $this->Catalog->find('first', $options);
		
		// work out whether backordering will be allowed
		$ancestors = explode(',', $product['Catalog']['ancestor_list']);
		$topCatalogNode = $ancestors[2];
		$itemCustomer = $this->Catalog->field('customer_id', array('id' => $topCatalogNode));
		$backorderAllow = $this->Catalog->User->Customer->field('allow_backorder', array ('id' => $itemCustomer));
		
		$itemLimitBudget = $this->Auth->user('use_item_limit_budget');
		
		$this->set(compact('product', 'backorderAllow', 'itemLimitBudget'));
    }

	/**
	 * Ajax in a detail view of a product
	 * 
	 * This is the reference div you can see from OrderItems
	 * in the status page and places like that where navigating
	 * to the product detail page would not be appropriate but
	 * the user wants to see more complete product information
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function item_peek($id = null) {

		$this->layout = 'ajax';
		if (!$this->Catalog->exists($id)) {
			throw new NotFoundException(__('Invalid item'));
		}
		$options = array(
			'conditions' => array(
				'Catalog.' . $this->Catalog->primaryKey => $id
				),
			'contain' => array(
				'Item' => array(
					'Image'
				)
			));
        $product = $this->Catalog->find('first', $options);
		$this->set(compact('product'));
	}

    /**
     * add method
     * 
     * BTW, this is also used for ajax adding, and thus we have some
     * switches on $this->request->action to limit functionality in those cases
     *
     * @return void
     */
    public function add() {
//	debug($this->params);
        // ajax calls post the first time, but then put... who knows why
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Catalog->create();
            if ($this->request->action == 'edit_saveEditForm') {
                // ajax editing has 'extra field' problems. this solves them
                // ajax add works fine
                $this->Catalog->data = $this->request->data;
            }
            if ($this->Catalog->save($this->request->data)) {
                if ($this->Catalog->refreshPermissions) {
                    $this->setNodeAccess($this->Auth->user('id'));
                }
                $this->Flash->success(__('The catalog has been saved'));

                // normal CRUD adds redirect to index
                if ($this->request->action == 'add') {
                    return $this->redirect(array('action' => 'index'));

                    // ajax calls need to head back to prepare the response
                } elseif ($this->request->action == 'edit_newChild' || $this->request->action == 'edit_newSibling' || $this->request->action == 'edit_saveEditForm') {
                    return;
                }
            }
            $this->Flash->error(__('The catalog could not be saved. Please, try again.'));
        }
//        if ($this->request->action == 'add' || $this->request->action == 'edit_renderEditForm') {
        $this->fetchVariablesForEdit();
//        }
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Catalog->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Catalog->save($this->request->data)) {
                $this->Flash->success(__('The catalog has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('The catalog could not be saved. Please, try again.'));
            }
        } else {
            $this->fetchRecordForEdit($id);
        }
        $this->fetchVariablesForEdit();
    }
	
	/**
	 * Reactivation interface for Catalog entries
	 * 
	 * @todo Need to add node-permission filtering to the query
	 * @todo Need to get Customer info for labeling the elements on the view
	 */
	public function inactive($customer = NULL, $state = NULL) {
		
		$customers = $this->User->getPermittedCustomers($this->Auth->user('id'), FALSE);
		$this->processed = false;
		
		// default data to populate filter-inputs. these will match url values
		$this->request->data = array(
			'customer' => '',
			'active' => 'inactive',
			'paginationLimit' => (isset($this->request->params['named']['limit'])) ? $this->request->params['named']['limit'] : 25
		);
		
		// build up proper query conditions given current url params and other factors
		$allowed = $this->Catalog->getAccessibleCatalogInList();
		$this->conditions = array(
			'Catalog.id' => $allowed,
			'Catalog.active' => '0',
			'NOT' => array(
				'Catalog.ancestor_list' => ',1,',
				'Catalog.name' => 'root'
				)
			);
//			debug($this->conditions);
		$args = func_get_args();
		if (!empty($args)) {
			foreach ($args as $index => $arg) {
				$result = preg_match('/\d*/', $arg, $match);
				
				// detected a customer id in the url
				if ($match[0] == $arg) {
					$this->intArg($arg, $customers);
					
				// detected a state-filter in the url (actually, it's a string that doesn't appear to be a cust id	
				} elseif (!$this->processed) {
					$this->stringArg($arg);
				}
			}
		}
//		// query conditions are now constructed
		// and trd matches so the filter inputs will match the url values

		try {
			$this->paginate = array(
				'limit' => $this->request->data['paginationLimit'],
				'conditions' => $this->conditions,
				'contain' => FALSE
			);
			$this->Paginator->settings = $this->paginate;
			$catalogs = $this->Paginator->paginate();
			
		} catch (Exception $exc) {
			// probably a filter changed the return count and the 
			// previous page # is now out of range. go back to page 1.
			$this->request->params['named']['page'] = 1;
			$catalogs = $this->Paginator->paginate();
		}
		
		$catalogs = $this->Catalog->injectCustomerName($catalogs);

		$this->set('customers', $customers);
		$this->set('catalogs', $catalogs);
	}
	
	/**
	 * From state filter choice, set query condition and UI input value
	 * 
	 * @param string $arg assumed to be a state filter choice
	 */
	private function stringArg($arg) { 
		$this->processed = true;
		switch ($arg) {
			case 'all' :
				$this->request->data['active'] = 'all';
				unset($this->conditions['Catalog.active']);
				break;
			case 'active' :
				$this->conditions['Catalog.active'] = '1';
				$this->request->data['active'] = 'active';
				break;
			case 'inactive' :
			default:
				$this->conditions['Catalog.active'] = '0';
				$this->request->data['active'] = 'inactive';
				break;
		}
	}

	/**
	 * From customer filter choice, set the query condition and UI input value
	 * 
	 * @param int $arg requested cust id filter value
	 * @param array $allowed List of cust ids allowed for this user
	 */
	private function intArg($arg, $allowed) {
		if (array_key_exists($arg, $allowed)) {
			$this->conditions['Catalog.customer_user_id'] = $arg;
			$this->request->data['customer'] = $arg;
		}		
	}

	/**
	 * Set catalog record active state to provided parameter
	 * 
	 * @param int $id the id of the catalog record
	 * @param int $state the desired state of the catalog record
	 * @return html Elements/Catalog/inactive_row
	 */
	public function setActive($id, $state) {
		$this->layout = 'ajax';
		$c = array('Catalog' => array('id' => $id, 'active' => $state));
		try {
			$this->Catalog->save($c);
			$catalog = $this->Catalog->find('all', array(
				'conditions' => array('id' => $id),
				'contain' => false));
			$catalog = $this->Catalog->injectCustomerName($catalog);
		} catch (Exception $exc) {
			return $exc->getTraceAsString();
		}
        
        //This direct query updates item records to reflect a catalog record's active state.
        //It sums all of the attached catalog record's active states, and leaves the item active for ANY
        //active catalogs, and inactive if NO active catalogs
        
        $item_id = $catalog[0]['Catalog']['item_id'];
        if (!empty($item_id)){
            $this->Catalog->Item->query("UPDATE items i, (SELECT item_id, sum(active)  as activesum
                                           FROM catalogs WHERE item_id = $item_id GROUP BY item_id) as c
                                            SET i.active = (c.activesum > 0)
                                            WHERE i.id = $item_id");
        }

		$this->set('catalog', $catalog[0]);
		$this->render('/Elements/Catalog/inactive_row');
	}

	/**
     * General use Fetch of a user record when a form needs populating data
     * 
     * @param type $id
     */
    public function fetchRecordForEdit($id) {
		//setup options array
        $options = array(
            'conditions' => array('Catalog.' . $this->Catalog->primaryKey => $id),
            'contain' => array(
                'Item' => array('Image', 'Catalog', 'Location'),
				'ParentCatalog'
        ));
		
		//perform find
        $this->request->data = $this->Catalog->find('first', $options);
		
		//update TRD to create control fields and appropriate array structure
        $this->request->data['Catalog']['parent_id'] = $this->secureSelect($this->request->data['Catalog']['parent_id']);
		$catalog = $this->request->data['Catalog'];
		$catalog['Item'] = $this->request->data['Item'];
		$this->request->data['Catalog']['available_qty'] = $this->Catalog->deriveKitOrComponentAvailability($catalog);
		$this->request->data['Catalog']['can_order_components'] = (($this->request->data['Catalog']['type'] & ORDER_COMPONENT) ===  ORDER_COMPONENT);
		$this->request->data['Catalog']['kit_prefs'] = ($this->request->data['Catalog']['type'] & (INVENTORY_BOTH | ON_DEMAND | INVENTORY_KIT));
    }

    /**
     * Set variables necessary for editing and adding catalog records
     * 
     * These variables are to support inputs with options on the add
     * and edit forms used in CRUD, grain and tree edits
     * 
     * These variables are all set to the View vars
     * 
     */
    public function fetchVariablesForEdit() {
//		echo Debugger::trace();
        $items = $this->Catalog->Item->find('list');
		// +++++++++++++++++++++++++++++++++++++++ dump prev, use following +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$items = array('' => 'Loading item list... ');
//        $combinedCatalogs = $this->Catalog->User->getSecureList($this->Auth->user('CatalogRoots'), 'catalog');
//        $parent_catalogs = $combinedCatalogs['list'];
//		$this->request->data['Catalog']['type'] = FOLDER;
		$ItemVendorId = $this->Catalog->Item->Vendor->fetchCustomerVendorList();
        $this->set(compact('items', 'ItemVendorId'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Catalog->id = $id;
        if (!$this->Catalog->exists()) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Catalog->delete()) {
            $this->Flash->success(__('Catalog deleted'));
            $this->redirect($this->referer());
        }
        $this->Flash->error(__('Catalog was not deleted'));
        $this->redirect($this->referer());
    }

    //============================================================
    // TREE MANAGEMENT METHODS
    //============================================================

    /**
     * The method to allow editing of Catalog Trees
     * 
     * @param type $id
     * @param type $hash
     */
    public function edit_catalog($id = null, $hash = null) {
        $this->layout = 'sidebar';
        if ($this->request->is('get')) {
            if ($id != null && $hash != null && $this->secureId($id, $hash)) {
                $editTree = $this->Catalog->getFullNode($id);
                $this->set('editTree', $editTree);
            } elseif ($id != null && $hash != null && !$this->secureId($id, $hash)) {
                throw new ForbiddenException("Security validation failed on your request for \r
		    {$this->request->url}\rContact your admin for more information.");
            }
//	debug($editTree);
        }
        $tree = $this->Catalog->find('all', array(
            'conditions' => array(
                'user_id' => $this->Auth->user('id')
            )
        ));
//	debug($tree);
        $this->set('tree', $this->Catalog->nodeGroups($tree));
//	debug($this->viewVars['tree']);
    }

    /**
     * The call point for all ajax tree edits
     * 
     * Simple pass through to AppController which
     * handles the tree edits for everybody
     */
    public function edit_tree() {
        $this->treeJax($this->Catalog);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'demote to child' tool entry point
     * 
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_toChild() {
        $this->ajax_toChild($this->Catalog);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'new child' tool entry point
     * 
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_newChild() {
        $this->ajax_newChild($this->Catalog);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax 'new sibling' tool entry point
     * 
     * Logic is in AppController to serve Catalog too.
     * This pass through sets the proper Model context
     */
    public function edit_newSibling() {
        $this->ajax_newSibling($this->Catalog);
        $this->render('/AppAjax/edit_tree');
    }

    /**
     * Ajax call to add() for SAVE services
	 * 
	 * This processes the tree edits as well as the new entries
     */
//    public function edit_add() {
//        $this->ajaxEdit();
//    }

    /**
     * AJAX EDIT RENDER PHASE
     * 
     * Pull the data that will populate the form
     * Then use add to generate the form inputs
     * 
     * @param type $id
     * @throws BadRequestException
     */
    public function edit_renderEditForm($id) {
        $this->ajax_RenderEditForm($id);
		$this->backorderTool = FALSE;
        $this->render('ajax_edit');
    }

    /**
     * AJAX ADD RENDER PHASE
     * 
     * Pull the data that will populate the form
     * Then use add to generate the form inputs
     * 
     * @param type $id
     * @throws BadRequestException
     */
    public function add_renderEditForm($call = NULL, $typeContext = NULL, $parent = FALSE) {
		
		if($parent){
			$this->request->data['Item']['vendor_id'] = $this->Catalog->fetchItemVendor($parent);
		}
		$this->assesEditFormTypeContext($call, $typeContext);
        $this->fetchVariablesForEdit();
        $this->layout = 'ajax';
        $this->render('ajax_edit');
    }
	
	/**
	 * Set TRD['Catalog']['type'] to the proper setting for kit components
	 * 
	 * When adding records associated with Kits, there are only two circumstances:
	 * 1. Adding a child of a Kit itself
	 * 2. Adding a sibling of a Component
	 * 
	 * In the case of a component, we set the new record to have a type that matches the component
	 * In the case of a Kit, we set the new record to COMPONENT type, with ORDER_COMPONENT as appropriate
	 * 
	 * @param type $typeContext
	 * @return none the function sets $this->request->data
	 */
	private function assesEditFormTypeContext($call, $typeContext) {
		if($typeContext == NULL || $call == NULL) {
			return;
		}
		//setup the Can Order Components button based upon Can Order of parent or sibling chosen
		$this->request->data['Catalog']['can_order_components'] = (($typeContext & ORDER_COMPONENT) == ORDER_COMPONENT);
		
		//if we're in catalog context and we're asking for the sibling of a COMPONENT type catalog record,
		//set the catalog type
		if ($typeContext & COMPONENT) {
			$this->request->data['Catalog']['type'] = $typeContext;
		} elseif(($typeContext & KIT) && $call == 'child'){
			//if we're in a catalog context AND we're asking for the child of a KIT AND this work wasn't already done by ajax_newSibling, above
			//set the catalog type
			if($typeContext & ORDER_COMPONENT){
				$this->request->data['Catalog']['type'] = ORDER_COMPONENT | COMPONENT;
			} else {
				$this->request->data['Catalog']['type'] = COMPONENT;
			}

		}
		return;
	}

    /**
     * This is the catalog trees add/edit processor
     */
    public function ajaxEdit() {
        $itemId = false;
        if ($this->request->is('post') || $this->request->is('put')) {
            if($this->universalSave($this->request->data)){
                $this->Flash->success('The catalog has been saved');
            } else {
                $this->Flash->error(__('The catalog has NOT been saved'));
            }
        }
        $this->layout = 'ajax';
        $this->render('ajax_edit');
    }

    /**
     * The universal catalog save function
     *
     * Takes basic user import and builds out all automated parts of the
     * catalog and item records
     *
     * Array required (Image element is optional):
     *  array(
            'Catalog' => array(
                'parent_id' => '51/95e894db3306532762674f0cf1f05c5ff508df81',
                'id' => '',
     *          'sequence' => '9.5'
                'name' => 'Yeah',
                'type' => '4',
                'active' => '1',
                'kit_prefs' => '128',
                'can_order_components' => '0',
                'item_id' => '',
                'item_code' => 'let's',
                'customer_item_code' => 'try',
                'description' => 'this',
                'sell_unit' => 'ea',
                'sell_quantity' => '1',
                'price' => '0.00',
                'max_quantity' => ''
            ),
            'Item' => array(
                'source' => '0',
                'id' => '',
                'po_unit' => 'ea',
                'po_quantity' => '1',
                'cost' => '0.00',
                'vendor_id' => '48',
                'po_item_code' => '',
                'reorder_level' => '1',
                'reorder_qty' => '0',
                'quantity' => '0'
            ),
            'Image' => array(
                'img_file' => array(
                    'name' => 'HeadShot.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => '/private/var/tmp/phpUO8yXJ',
                    'error' => (int) 0,
                    'size' => (int) 1065571
                )
            )
        )
     *
     * @param $data the save array
     * @return bool
     */
    public function universalSave($data)
    {
        //setup data for saving
        $data = $this->assembleSaveArray($data);

        $this->Catalog->create();

        if ($this->Catalog->saveAll($data)) {
            $itemId = $this->Catalog->Item->id;
            //new items need their uncommitted values initialized
            if ($itemId) {
                $this->Catalog->Item->manageUncommitted($itemId);
                $this->Catalog->Item->managePendingQty($itemId);
            }
            if ($this->Catalog->refreshPermissions) {
                $this->setNodeAccess($this->Auth->user('id'));
            }

            // Image.img_file = '' means no file upload
			// **************************************** SUGGESTED REFACTOR ******************
 			// $this->Catalog->Item->Image->saveFromPost($data)
			// **************************************** SUGGESTED REFACTOR ******************
            if (isset($data['Image']) && $data['Image']['img_file'] != '') {
                $this->Catalog->Item->Image->deleteExistingImage($itemId);
                //redo name
                if(isset($this->Catalog->Item->Image->ext[$data['Image']['img_file']['type']])){
                    $name = substr($this->secureHash($data['Image']['img_file']['name']), 0, 10);
                    $imageExt = $this->Catalog->Item->Image->ext[$data['Image']['img_file']['type']];
                    $saveName = $name . '.' . $imageExt;

                    $data['Image']['img_file']['name'] = $saveName;
                    $data['Image']['item_id'] = $itemId;
                    $this->Catalog->Item->Image->create();
                    $this->Catalog->Item->Image->save($data);
                }
            }
            return $itemId;
        } else {
            return false;
        }

    }
	
	/**
	 * Assemble save array for ajaxEdit
	 * 
	 */
	private function assembleSaveArray($data) {
		
		// set the customer_user_id
		if ($data['Catalog']['parent_id'] != '') {
			$a = explode('/', $data['Catalog']['parent_id']);
			$this->Catalog->id = $a[0];
			$cust_u_id = $this->Catalog->read('customer_user_id');
			$data['Catalog']['customer_user_id'] = $cust_u_id['Catalog']['customer_user_id'];
		}
		
		//creating new record, with no item
		if(empty($data['Item']['id']) && $data['Catalog']['id'] == ''){
			//if this is a new record, update the item with the data from the catalog
			$item = array_merge($data['Item'], $data['Catalog']);
			$item['id'] = '';
			$data['Item'] = $item;
		} else if(isset($data['Item']) && $this->Catalog->Item->field('catalog_count', array('Item.id' => $data['Item']['id'])) == 1){
			//if there is only 1 catalog connected to the item, update the item with data from the catalog
			$item = array_merge($data['Item'], $data['Catalog']);
			$item['id'] = $data['Item']['id'];
			$data['Item'] = $item;
		}
		
		// HACK HACK HACK
		// this was my big hammer solution to wrongly creating new items when existing was intended
		// it may defeat the elseif just above
		if (isset($data['Catalog']['item_id']) && $data['Catalog']['item_id'] > 0) {
			unset($data['Item']);
		}
		
		//check if this catalog record is a folder
		if($data['Catalog']['type'] & FOLDER){
			unset($data['Item']);
			unset($data['Image']);
		}	
		
		//check if this catalog record is a KIT
		if($data['Catalog']['type'] & KIT) {
			$type = KIT + $data['Catalog']['kit_prefs'];
			if($data['Catalog']['can_order_components']){
				$type = ($type + ORDER_COMPONENT);
			}
			$data['Catalog']['type'] = $type;
		}
		
		//check if this catalog record is a component
		if($data['Catalog']['type'] & COMPONENT){
			$data['Catalog']['type'] = COMPONENT;
			if($data['Catalog']['can_order_components']){
				$data['Catalog']['type'] = (COMPONENT + ORDER_COMPONENT);
			}
		}
			
		//setup item data for saving
		if (!$data['Catalog']['type'] & FOLDER) {
			$data['Item']['name'] = $data['Catalog']['name'];
			$data['Item']['description'] = $data['Catalog']['description'];
			//if this is a new item, set available quantity and pending quantity
			if (empty($data['Item']['id'])) {
				$data['Item']['available_qty'] = $data['Item']['quantity'];
			}
		}
		return $data;
	}

    /**
     * AJAX EDIT SAVE PHASE: entry point for the edit tool pallet choice
     */
    public function edit_saveEditForm() {
        $this->ajax_newChild($this->Catalog);
        $this->render('/AppAjax/edit_tree');
    }
    
/**
 * Deactivate selected catalog, all it's descendents, and any items associated with both
 * 
 * 
 * @param string $id the selected catalog's id, with li separated hash
 * @param string $activeToggle whether to active or deactivate the user
 */
    public function edit_deactivate($id, $activeToggle = 'deactivate') {
        $this->_activeChange = ($activeToggle == 'deactivate') ? -1 : 1;
        $check = $this->validateSelect($id, 'li');
        //check integrity of chosen catalog entry
        if (!$check[2]){
            return false;
        }
        $id = $check[0];
        //find the selected catalog entry
        $selectedCatalogEntry = $this->Catalog->find('first', array(
            'conditions' => array(
                'Catalog.id' => $id
            ),
            'fields' => array('Catalog.id', 'Catalog.item_id', 'Catalog.type', 'Catalog.active'),
            'contain' => false
        ));

        //add selected catalog entry to the list for deactivation
        $this->listSorter(array($selectedCatalogEntry), TRUE);

        do {
            $this->getContainer();
            $this->scanItemsForContainers();

        } while (!empty($this->containers));


        //find all catalog items connected to itemsIn
        if($this->Catalog->saveAll($this->catalogs)){
            $message = "Catalog and descendents {$activeToggle}d.";
            $messageType = 'flash_success';
        } else {
            $message = "Failed to $activeToggle items, please try again.";
            $messageType = 'flash_error';
        }

        if($this->Catalog->Item->saveAll($this->items)){
            $message .= " Linked items have been {$activeToggle}d.";
            $messageType = 'success';
        } else {
            $message .= " Failed to $activeToggle items, please try again.";
            $messageType = 'error';
        }
        $this->Flash->$messageType($message);
        $this->redirect($this->referer());

    }
    
    /**
     * See if any items are also folder nodes
     * 
     */
    function scanItemsForContainers() {
	if(empty($this->itemsIn)) {
	    return;
	}
	$itemCatalogs = $this->Catalog->find('all', array(
	    'conditions' => array(
		'Catalog.item_id' => $this->itemsIn
	    ),
	    'fields' => array('Catalog.id', 'Catalog.item_id', 'Catalog.type', 'Catalog.active'),
	    'contain' => false,
	    'group' => 'Catalog.id'
	));

	$holdContainers = true;
	$this->listSorter($itemCatalogs, $holdContainers);
    }
   
    /**
     * If we have container nodes to deactivate, dig in!
     * 
     * for deactivate process
     * 
     */
    private function getContainer(){

	if(!empty($this->containers)){
            foreach ($this->containers as $index => $record) {
                //get and process descendents
		$this->getContainerDescendents($record);
            }
        }
	$this->containers = array();
    }
    
    /**
     * Get descendents and process them for ONE container node
     * 
     * For deactivate process
     * 
     * 
     * @param type $record
     */
    private function getContainerDescendents($record){
        $conditions = [];
        $options = array(
            'fields' => array('Catalog.id', 'Catalog.item_id', 'Catalog.type', 'Catalog.active'),
            'contain' => false
        );

        //get all the selected catalog entry's descendents for inactivation
        $descendents = $this->Catalog->getDecendents($record['id'], false, $conditions, $options);
        //process new descendents into various lists
        if (!empty($descendents)) {
	    $holdContainers = false;
	    $this->listSorter($descendents, $holdContainers);
        }        
    }
    
    /**
     * process the descendents found in a container node
     * 
     * for deactivate process
     * 
     * @param type $data
     * @param type $holdContainers
     */
    private function listSorter($data, $holdContainers){
		foreach ($data as $index => $record) {

			$id = $record['Catalog']['id'];

			if ($holdContainers) {
				if ($record['Catalog']['type'] & FOLDER || $record['Catalog']['type'] & KIT) {
					if (!isset($this->catalogs[$id])) {
						$this->containers[$id] = array('id' => $id);
					}
				}
			}

			//add this catalog entry to the list for deactivation
			$this->catalogs[$record['Catalog']['id']] = $record;
			$this->catalogs[$record['Catalog']['id']]['Catalog']['active'] = $record['Catalog']['active'] + $this->_activeChange;
			//add selected catalog entry to folders list for descendent finding

			//add selected catalog entry to items list for deactivation
			//also add selected item entry to itemsIn list to search for other catalog use
			if ($record['Catalog']['item_id'] != NULL){
				$itemId = $record['Catalog']['item_id'];

				$this->itemsIn[$itemId] = $itemId;

				$this->items[$itemId] = array(
					'Item' => array(
					'id' => $itemId,
					'active' => 0
					)
				);
			}
		}
    }
    
    private function compareCompanyLists($element){
        
    }
    


    //============================================================
    // ADMIN UTILITY TOOLS
    //============================================================

    /**
     * Utility to build base catalogs from inported ITEM data
     */
    public function createCatalog() {
        $this->Catalog->Behaviors->unload('ThinTree');
        $items = $this->Catalog->Item->find('list', array(
            'fields' => array('id', 'name', 'category_code')
        ));
        $count = 0;
        $collectionCount = 1;
        foreach ($items as $cat => $list) {
            $data = array();
            $data['Catalog']['name'] = $cat;
            $data['Catalog']['sequence'] = $collectionCount++;
            $data['Catalog']['parent_id'] = 1;
            $data['Catalog']['type'] = FOLDER;
            $data['Catalog']['ancestor_list'] = ',1,';
            $this->Catalog->create();
            $this->Catalog->save($data, false);
            $id = $this->Catalog->id;
            $item_data = array();
            foreach ($list as $item_id => $name) {
                $item_data[$count]['Catalog']['parent_id'] = $id;
                $item_data[$count]['Catalog']['name'] = $name;
                $item_data[$count]['Catalog']['sequence'] = $count + 1;
                $item_data[$count]['Catalog']['ancestor_list'] = ",1,$id,";
                $item_data[$count++]['Catalog']['item_id'] = $item_id;
            }
            $this->Catalog->saveMany($item_data, array(
                'validate' => false
            ));
        }
        $this->render('index');
    }

    public function userStores($id) {
        $this->Catalog->retrieveUserStores($id);
    }

    //============================================================
    // SHOPPING TOOLS
    //============================================================

    /**
     * The method get a page of store items for shopping
     * 
     * @todo Add access node points so interface can do tool filtering
     * @param type $id
     * @param type $hash
     */
    public function shopping($id = null, $hash = null) {

        $this->layout = 'sidebar';
        $pageHeading = $title_for_layout = 'Shopping';
		$itemLimitBudget = $this->Auth->user('use_item_limit_budget');
        $this->set(compact('pageHeading', 'title_for_layout', 'itemLimitBudget'));
        $this->set('controller', 'catalogs'); //override the normal path data used by js url constructors
		$accessibleCatalogInList = $this->Catalog->getAccessibleCatalogInList($this->Auth->user('CatalogRoots'));

        if ($id == null || isset($accessibleCatalogInList[$id])) {
            // the user has requested a node for shopping
            if ($id != null && $hash != null && $this->secureId($id, $hash)) {
				//set the paginationLimit preference
				if($this->Session->read['Prefs.Catalog.paginationLimit']){
					$this->defaultLimit = $this->Session->read['Prefs.Catalog.paginationLimit'];
				}
				
                $this->set('renderNode', $id);
                $shopItems = $this->retrieveStoreItems($id); //change this to get all decendent grain
				$shopItems = $this->Catalog->gatherComponentGrain($shopItems);
				$this->set('shopItems', $shopItems);
				
				//setup backorder allow var
				if (!empty($this->viewVars['shopItems'])) {
					$ancestors = explode(',', $this->viewVars['shopItems'][0]['Catalog']['ancestor_list']);
					$topCatalogNode = $ancestors[2];
				} else {
					$topCatalogNode = '';
				}
				$itemCustomer = $this->Catalog->field('customer_id', array('id' => $topCatalogNode));
				$backorderAllow = $this->Catalog->User->Customer->field('allow_backorder', array ('id' => $itemCustomer));
				$this->set('backorderAllow', $backorderAllow);

				
			// We're just KILLING this guy if the url params don't validate
            } elseif ($id != null && $hash != null && !$this->secureId($id, $hash)) {
                throw new ForbiddenException("Security validation failed on your request for \r
                {$this->request->url}\rContact your admin for more information.");
            }

            // In all cases, get the side-panel selector tree data and send it to the view
            $this->prepareShoppingSidebar();
        } else {
            $this->Flash->set('You don\'t have permission to shop from this Catalog');
			$this->redirect(array('action' => 'shopping'));
        }
        // uses store_grain Element
        $this->render('/Common/manage_tree_object');
    }
	
	public function setPaginationLimit($limit){
		$this->defaultLimit = $limit;
		$this->paginationLimitPreference($this->defaultLimit);
		
	}
	
	/**
	 * Get page x of this store-node
	 * 
	 * @param type $id, The node
	 * @param string $page The page
	 * @return array the page of store items
	 */
    public function retrieveStoreItems($id) {
		//read the pagination limit from the session prefs, or use the default
		$limit = $this->Session->read('Prefs.Catalog.paginationLimit');
		if($limit){
			$this->defaultLimit = $limit;
		}
		
		$this->paginate = array(
			'conditions' => array(
				'Catalog.active' => 1,
				'Catalog.parent_id' => $id//,
				),
			'limit' => $this->defaultLimit,
			'contain' => array(
				'Item' => array(
					'Image'
				),
				'ParentCatalog'
			),
			'order' => 'Catalog.sequence ASC'
		);

		return $this->paginate();
	
    }

    /**
     * Prepare all Catalog tree sidebar data and send it to the view
     * 
     * @todo How about css and scripts? can we send vars that will let those load too?
     */
    private function prepareShoppingSidebar() {
        $conditions = array('Catalog.active' => 1, 'Catalog.type' => FOLDER);
        $flatNodes = $this->User->getAccessibleCatalogNodes($this->Auth->user('CatalogRoots'), $conditions);
        $this->passRootNodes('Catalog');
        $this->set('tree', $this->User->Catalog->nodeGroups($flatNodes));
    }


    //============================================================
    // KIT TOOLS
    //============================================================
	
	public function kitAdjustment($catalogId, $qty, $catalogType) {
		$this->layout = 'ajax';
		if($catalogType & KIT){
			if (!$this->kitUp($catalogId, $qty)) {
				$this->set('available', FALSE);
				return;
			}		
		} else {
			$kitId = $this->Catalog->field('parent_id', array('id' => $catalogId));
			if(!$this->breakKit($kitId, $qty)){
				$this->set('available', FALSE);
				return;
			}
		}
		$this->injectInventoryVals();
		$this->set('available', $this->Catalog->Item->available);
	}
	
	public function injectInventoryVals() {
//		$this->ddd($this->Catalog->Item->available, 'this');
		$result = $this->Catalog->Item->available['Available'];
		foreach ($result as $entry) {
			$pattern = array(
				'/C\d*/', '/I/', '/-/'
			);
			$itemId = preg_replace($pattern, '', $entry[0]);
			$invQty = $this->Catalog->Item->field('quantity', array('id' => $itemId));
			$key = "I{$itemId}-C";
			$this->Catalog->Item->available['Available'][] = array(
				"-$key-",
				$invQty,
				1,
				'ea'
			);		
		}
	}


	/**
	 * Increase kit inventory quantity by building kits from component inventory
	 * 
	 * @param string $kitId
	 * @param string $qty
	 * @return boolean
	 */
	public function kitUp($kitId, $qty = 1) {
		//get the kit product itself
		$this->kit = $this->Catalog->fetchKit($kitId);
		if(empty($this->kit)){
			$this->Flash->error("This product is not a kit");
			return FALSE;			
		}
		
		//fetch the kit components
		$this->components = $this->Catalog->fetchComponents($kitId);
		if(empty($this->components)){
			$this->Flash->error("This kit has no components");
			return FALSE;
		}
				
		//set the maxKitUp
		$this->maxKitUp = $this->Catalog->fetchMaxKitUp($kitId);
		
		//make sure there's enough inventory to fulfill this
		if ($this->maxKitUp < $qty){
			$this->Flash->error("There is only enough inventory to make {$this->maxKitUp} kit(s)");
			return FALSE;
		}
		
		//update component inventory
		$this->updateKitInventory($qty, $this->components, 'reduce');
		
		//update kit inventory
		$this->updateKitInventory($qty, array(0 => $this->kit), 'increase');
				
		$this->Flash->success("$qty kit(s) were created from components.");
	}
	
	/**
	 * Decrease kit inventory by breaking kits into component parts
	 * 
	 * @param string $kitId
	 * @param string $qty
	 * @return boolean
	 */
	public function breakKit($kitId, $qty=1) {
		//get the kit product itself
		$this->kit = $this->Catalog->fetchKit($kitId);
		if(empty($this->kit)){
			$this->Flash->error("This product is not a kit");
			return FALSE;			
		}
		
		//fetch the kit components
		$this->components = $this->Catalog->fetchComponents($kitId);
		if(empty($this->components)){
			$this->Flash->error("This kit has no components");
			return FALSE;
		}
				
		//make sure there's enough kit inventory to fulfill this
		if ($this->kit['Item']['available_qty'] < $qty){
			$this->Flash->error("There is only enough inventory to break {$this->kit['Item']['available_qty']} kit(s)");
			return FALSE;
		}
		
		//update component inventory
		$this->updateKitInventory($qty, $this->components, 'increase');
		
		//update kit inventory
		$this->updateKitInventory($qty, array(0 => $this->kit), 'reduce');
		
		$this->Flash->success("$qty kit(s) were broken.");
	}
	
	/**
	 * Update the inventory value of kits or components
	 * 
	 * Using the requested qty, passed data array (see Array Structure, below)
	 * and a mode ('reduce' or 'increase'),
	 * adjust the inventory amount in Item.quantity based upon the Catalog.sell_quantity,
	 * the $qty provided and the mode (reduce subtracts and increase adds)
	 * 
	 * @param string $qty
	 * @param array $data
	 * @param string $mode
	 */
	
	// <editor-fold defaultstate="collapsed" desc="Array Structure">
	//array(
	//	(int) 0 => array(
	//		'Catalog' => array(
	//			'id' => '77',
	//			'item_id' => '109',
	//			'item_code' => '',
	//			'customer_item_code' => null,
	//			'name' => 'Notebook case',
	//			'parent_id' => '76',
	//			'customer_id' => null,
	//			'customer_user_id' => null,
	//			'sell_quantity' => '1',
	//			'sell_unit' => 'ea',
	//			'price' => '0.00',
	//			'type' => '4'
	//		),
	//		'Item' => array(
	//			'quantity' => '5.0',
	//			'available_qty' => '5.0',
	//			'pending_qty' => '0.0',
	//			'id' => '109'
	//		),
	//		(int) 0 => array(
	//			'product_avail' => '5'
	//		)
	//	)
	// </editor-fold>
	private function updateKitInventory($qty, $data, $mode) {
		//manage qty positive/negative based upon mode
		if($mode == 'reduce'){
			$qty = $qty * -1;
			$message = 'KIT - Inventory reduction of ';
		} elseif($mode == 'increase'){
			$qty = $qty;
			$message = 'KIT - Inventory increase of ';
		}
		
		//step through the provided data records
		foreach ($data as $index => $component) {
			//setup variables
//			$catalogName = $component['Catalog']['name'];
			$itemId = $component['Item']['id'];
			$itemQty = $this->Catalog->Item->field('quantity', array('Item.id' => $itemId));
			$adjustment = $qty * $component['Catalog']['sell_quantity'];
			$newQty = $itemQty + $adjustment;
//			$userName = $this->Catalog->Item->OrderItem->Order->User->discoverName($this->Auth->user('id'));
			
			//
			$this->Catalog->Item->create();
			$this->Catalog->Item->id = $itemId = $component['Item']['id'];
			$save = $this->Catalog->Item->saveField('quantity', $newQty);
			if ($save) {
//				$this->request->data['Item'] = array('id' => $itemId, 'quantity' => )
				$this->Catalog->Item->manageUncommitted($itemId);
//				$this->ddd($component['Catalog']);
//				$this->Catalog->Item->discoverCustomerUserId($itemId);
				
				$this->Log = ClassRegistry::init('Log');
				$this->Log->create('inventory');
				$this->Log
					->set('event', $message)
					->set('customer', $this->Catalog->Item->discoverCustomerUserId($itemId))
					->set('id', $itemId)
					->set('name', $component['Catalog']['name'])
					->set('from', $itemQty)
					->set('to', $newQty)
					->set('change', $qty * $component['Catalog']['sell_quantity'])
					->set('number', 'KIT')
					->set('by', $this->Catalog->Item->OrderItem->Order->User->discoverName($this->Auth->user('id')));
				$this->Log->toString();
				CakeLog::write('inventory', $this->Log->logLineOut);
				
//				CakeLog::write('inventory', "[{$component['Catalog']['user_customer_id']}] $message id:$itemId::$catalogName from $itemQty to $newQty ($adjustment) by $userName");
				// the return array now has all the update values we will need for the page
			}
		}
	}
	
	//============================================================
    // CATALOG CUSTOMER TOOLS
    //============================================================
	
	public function fetchCutomerItemList($catalogSecureId, $secureString = NULL) {
		if(!$customerUserId = $this->discoverCustomerUserId($catalogSecureId, $secureString)){
			$this->Flash->error('Could not validate the customer, please try again.');
			return FALSE;
		}
		$items = $this->Catalog->allItemsForCustomer($customerUserId);
		if(empty($items)){
			$this->Flash->error('Could not retreive the item list, please try again.');
			return FALSE;
		}
		$itemList = array();
		foreach ($items as $id => $record) {
			$itemList[$id] = $record['name'];
		}
		$this->set('itemList', $itemList);
	}
	
	/**
	 * Provided with a catalog secure ID, return the customer User Id
	 * of the associated customer
	 * 
	 * @param string $catalogSecureId
	 * @return string $customerUserId
	 */
	public function discoverCustomerUserId($catalogSecureId, $secureString = NULL) {
		if($secureString === NULL){
			preg_match('/\d+([\/lui]*)/', $catalogSecureId, $matches);
			$delimeter = $matches[1];
		} else {
			$catalogSecureId .= '/' . $secureString;
			$delimeter = '/';
		}
		$catalogIdArray = $this->validateSelect($catalogSecureId, $delimeter);
		if(!$catalogIdArray[2]){
			$this->Flash->error('This catalog failed to validate, please try again.');
			return FALSE;
		}
		$catalogId = $catalogIdArray[0];
		if(!$customerUserId = $this->Catalog->fetchCustomerUserId($catalogId)){
			$this->Flash->error('This catalog did not retreive, please try again.');
			return FALSE;
		}
		return $customerUserId;
	}
	
	public function testMe() {
		$this->Catalog->updateItemVendor();
	}

}