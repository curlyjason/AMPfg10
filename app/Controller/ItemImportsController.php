<?php
App::uses('AppController', 'Controller');
App::uses('ItemEntity', 'Model/Entity');
App::uses('ItemRegistry', 'Model/Entity');
App::uses('Inflector', 'Cake/Utitlity');
App::uses('Catalog', 'Model');
App::uses('SidebarTrait', 'Lib/Trait');
App::uses('ItemImportMap', 'Lib');
App::uses('CatalogSaveTrait', 'Lib/Trait');
/**
 * ItemImports Controller
 *
 */
class ItemImportsController extends AppController {
    use SidebarTrait;
    use CatalogSaveTrait;

    public $helpers = ['ItemImport'];
    public $initialCSVImport;
    protected $_requiredColumns =
        [
            'item.customer_item_code',
            'item.name',
            'item.description',
            'item.description_2',
            'item.price',
            'item.initial_inventory'
        ];
    protected $_ItemEntities;
    protected $_userColumns;
    public $ItemRegistry;
    public $ItemImportMap;


	/**
	 * After saving imported data, any records [Catalog => data, Item => data] 
	 * that failed to save will be stored in here. 
	 *
	 * @var array
	 */
	public $unsavedRecords;

    public function __construct(CakeRequest $request = null, CakeResponse $response = null)
    {
        parent::__construct($request, $response);
        $this->loadModel('Catalog');
        $this->ItemImportMap = new ItemImportMap();
        $this->ItemRegistry = new ItemRegistry($this->ItemImportMap);

    }

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

    public function index()
    {
        $this->clearUpload();

        if ($this->request->is('post') || $this->request->is('put')) {
//			debug($this->request->data);die;
			$filename = $this->request->data['File']['filename']['tmp_name'];
			$this->ItemRegistry->setImportFileName($this->request->data['File']['filename']['name']);
            $this->processUpload($filename);
			$this->ItemRegistry->validateData();
			//***I HAD TO ADD THIS BECAUSE DATA WASN'T PERSISTING***
            //***LIKELY THIS NEED TO GET ADDRESSED IN A SEPARATE WAY***
            //***THAT ACCOMMODATES BETTER PERSISTENCE OF THE DATA***
            $this->_persistItemRegistry();
            $this->set('ItemRegistry', $this->ItemRegistry);
            $this->set('requiredColumns', $this->ItemImportMap->getRequiredColumns());

        }
    }

    public function preview()
    {
        $this->prepareCatalogSidebar();
        //get the imported file
        $this->_loadItemRegistry();

        //save the map
//        $this->ItemRegistry->setMap($this->request->data['item']);
        $this->ItemImportMap->setMap($this->request->data['item']);
        $this->ItemRegistry->setHeaderRow($this->request->data['ItemImports']['first_row_headers']);

        //save out the updated Item Registry
        $this->_persistItemRegistry();

        $this->set('ItemRegistry', $this->ItemRegistry);

        $this->layout = 'sidebar';
    }

    /**
     * Save the mapped items from the preview
     *
     */
    public function saveItems()
    {
        //get the ItemRegistry
        $this->_loadItemRegistry();

        //put the form content parent_id in a property
        $this->ItemRegistry->setParentId($this->secureSelect($this->request->data['catalog']['parent_id']));
        $this->ItemRegistry->setVendorId($this->Catalog->fetchItemVendor($this->ItemRegistry->parentId()));

        //Call a method that makes save arrays from our ItemRegistry
        $this->ItemRegistry->rewind();
		$this->successfulSaveCount = 0;
        While ($this->ItemRegistry->valid()) {
			$this->ItemRegistry->saveResult(+1);
            if (!$item_id = $this->universalSave($this->ItemRegistry->saveArray())) {
                $this->_failedSave($this->Catalog->validationErrors);
				$this->ItemRegistry->saveResult(-1);
            }
            $this->saveAdditionalCatalogs($item_id);
            $this->ItemRegistry->next();
        }
        $this->set('ItemRegistry', $this->ItemRegistry);
        $this->prepareCatalogSidebar();
        $this->layout = 'sidebar';
    }

	
	/**
	 * Save any catalogs the user defined for their items
	 * 
	 * A default catalog is always created and saved. After that's done 
	 * we come here with the new item id and take care of any suplimentary 
	 * catalogs that were defined for the same item
	 * 
	 * @param int $item_id
	 * @return void
	 */
	protected function saveAdditionalCatalogs($item_id) {

		if(!$this->ItemRegistry->item()->hasCatalogs()){ return; }

		if($item_id == false) {
		    if($item_id = $this->missingItemId() == false){ return; }
		}
		
		$catalogSet = $this->ItemRegistry->additionalCatalogs($item_id);
		foreach ($catalogSet as $key => $catalog){
			$this->ItemRegistry->saveResult(+1);
			if(!$this->universalSave($catalog)){
				$errors = $this->Catalog->validationErrors + ['catalog' => $key];
				$this->_failedSave($errors);
				$this->ItemRegistry->saveResult(-1);
			}
		}
		
	}

    /**
     * Attempt to find a usable item id for an item that failed to save
     * Pass the item id back to the additionalCatalog save
     * -or-
     * Write a failedSave message for each catalog
     *
     */
	protected function missingItemId()
    {
        $item_id = $this->Catalog->findItemIdByCustomerItemCode(
            $this->ItemRegistry->item()->customerItemCode(),
            $this->ItemRegistry->parentId()
        );
        if($item_id == false){
            foreach ($this->ItemRegistry->item()->rawCatalogs() as $index => $catalog) {
                $errors = [
                  'catalog' => $index,
                  'customer_item_code' => [
                      'Could not find customer_item_code'
                  ]
                ];

                $this->_failedSave($errors);
            }
        }
        return $item_id;
	}

	protected function _failedSave($validationErrors)
    {
        $this->ItemRegistry->item()->setErrors($validationErrors);
    }
	
	/**
	 * Get a set of sample data for the User Interface (as json)
	 * 
	 * @param int $header Is the first data row a header
	 * @param int $length How many samples per page
	 * @param int $page Which page to return
	 */
    public function getSampleData($header, $length, $page)
    {
        $this->layout='ajax';
        $this->_loadItemRegistry();
        $result = $this->ItemRegistry->getJsonChunk($header, $length, $page);
        $this->set(compact('result'));
        $this->render('get_json');
    }
	
	/**
	 * Create json data to help map required columns to user columns
	 * 
	 * Ajax call point
	 */
	public function getMapTemplate() {
	    $this->layout = 'ajax';
		$this->set('result', json_encode($this->ItemImportMap->getMapTemplate()));
	}

    /**
     * Derive a filename from the user's username
     *
     * @param $ext string the desired file extension
     * @return string
     */
    public function getFileName($ext = '.txt')
    {
        $path = APP.'tmp'.DS.'csv_imports'.DS;
        $filename = sha1($this->Auth->user('username'));
        $extension = $ext;

        return $path.$filename.$extension;
    }

    /**
     * Delete any existing file with user's filename
     *
     * Makes sure that we are only ever working with the 
	 * current, just uploaded file
     */
    public function clearUpload()
    {
        if(file_exists($this->getFileName())){
            unlink($this->getFileName());
        }
    }

    /**
     * Handle the processing of uploaded CSV file
     *
	 * Will produce an ItemRegistry object and store it serialized 
	 * whether or not data is imported. The object will be able 
	 * to report on its status. 
	 * 
	 * @param string $filename
	 * @return boolean
	 */
    public function processUpload($filename)
    {
		$size = filesize($filename);
		ini_set("auto_detect_line_endings", true);

        if (($handle = fopen($filename,'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $this->ItemRegistry->registerItem($data);
            }
            fclose($handle);
            $result = true;
        } else {
            $result = false;
        }
        //set the Header Row to default to no file headers
        $this->ItemRegistry->setHeaderRow(0);
        $this->_persistItemRegistry();
		return $result;
    }

    protected function _persistItemRegistry()
    {
        file_put_contents($this->getFileName(), serialize($this->ItemRegistry));
    }

    protected function _loadItemRegistry()
    {
        $this->ItemRegistry = unserialize(file_get_contents($this->getFileName()));
        $this->ItemImportMap = $this->ItemRegistry->ItemImportMap;
    }

}
