<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-03-05
 * Time: 17:46
 */

app::uses('ItemEntity', 'Model/Entity');

class ItemRegistry
{

    //<editor-fold desc="Properties" defaultstate="collapsed">
    protected $_current = -1;
    protected $_map = [];
    protected $_items = [];
	protected $_count_not_array = 0;
	protected $_count_nodes = [];
	protected $_count_encoding_rejects = 0;
	protected $_reject_char_count = 0;
	protected $_short_char_count = 0;
	protected $_count_too_short = 0;
	protected $_reject_catalog = 0;
	protected $_has_header_row = FALSE;
	protected $_header_row;
	public $ItemImportMap;
	protected $_parentId;
	protected $_vendorId;
	protected  $_reportStatus = [];
	public $successfulSaveCount = 0;
	/**
	 * Name of the file the user submitted for import
	 * @var string
	 */
	protected $importFile;
	//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="property access">

    public function __construct($ItemImportMap)
    {
        $this->ItemImportMap = $ItemImportMap;
    }

	
	/**
	 * Return the requested item or the current item
	 * 
	 * @param int|null $index
	 * @return ItemEntity
	 */
	public function item($index = null) {
	    if(is_null($index)){
	        $index = $this->current();
        }
	    if(!isset($this->_items[$index])){
	        $result = [];
        } else {
	        $result = $this->_items[$index];
        }
		return $result;
	}

	/**
	 * Return the array of Item Entities
	 * 
	 * @return array
	 */
	public function items() {
		return $this->_items;
	}
	
	public function saveResult($outcome)
	{
		$this->successfulSaveCount += $outcome;
	}

// </editor-fold>

    /**
	 * Try to make and store an Item Entity or Catalog for an Item
	 * 
	 * Validate the data. If it passes, make a new entity and store it 
	 * or, if it is a catalog block, store it in its entity
	 * 
	 * @param mixed $data
	 * @return boolean
	 */
	public function registerItem($data) {
		if (!$this->validate($data)) {
			return FALSE;
		}
		if ($this->isCatalog($data)) {
		    if(!$this->validateCatalog($data)){
		        return false;
            } else {
                $this->item()->insertCatalog($data);
            }
		} else {
			$this->insert(new ItemEntity())->import($data);
		} 
	}
	
	/**
	 * Place a new entity into the array
	 * 
	 * @param ItemEntity $entity
	 * @return ItemEntity
	 */
	protected function insert($entity) {
		$this->next();
		$this->_items[$this->current()] = $entity;
		return $this->item();
	}
	
	/**
	 * Is this likely to be catalog data and is there an item to receive it?
	 * 
	 * @param array $data
	 * @return boolean
	 */
	protected function isCatalog($data) {
		$indicator = strtoupper($data[0]);
		if (!empty($this->items()) && ($indicator == 'CATALOG')) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function setImportFileName($filename)
	{
		$this->importFile = $filename;
	}
	
	public function importFileName()
	{
		return $this->importFile;
	}
	/**
	 * Valid and record data characteristics
	 * 
	 * Even valid data will have information recorded about its 
	 * nature so that this registry can report on the likely 
	 * quaility of its contents
	 * 
	 * @param mixed $data
	 * @return boolean True = likely good, FALSE = don't use
	 */
	protected function validate($data) {
		if ($this->validArray($data) && $this->validEncoding($data)) {
			$this->trackCountVariation($data);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Track how many columns are in the arrays and how many of each
	 * 
	 * Too much variation in column count is an indicator 
	 * of bad data. 
	 * 
	 * @param array $data
	 */
	protected function trackCountVariation($data) {
		$node_count = count($data);
		if (isset($this->_count_nodes[$node_count])) {
			$this->_count_nodes[$node_count]++;
		} else {
			$this->_count_nodes[$node_count] = 1;
		}
	}
	
	/**
	 * insure the data is an array of usable length
	 * 
	 * It has to have at least 4 elements to be a catalog
	 * 
	 * @param mixed $data
	 * @return boolean
	 */
	protected function validArray($data) {
		$result = FALSE;
		if (!is_array($data)) {
			$this->_count_not_array++;
			return $result;
		}
		if (count($data) < 2) {
			$this->_count_too_short++;
			$string = implode('', $data);
			$this->_short_char_count += strlen($string);
			return $result;
		}
		return TRUE;
	}
	
	/**
	 * only allow UTF-8 encoded data
	 * 
	 * @param array $data
	 * @return boolean
	 */
	protected function validEncoding($data) {
		$check_string = implode('', $data);
		if (!mb_check_encoding($check_string, 'UTF-8')) {
			$this->_count_encoding_rejects++;
			$this->_reject_char_count += strlen($check_string);
			return FALSE;
		}
		return TRUE;
	}

    /**
     * Check validation of catalog nodes
     * Make sure catalog nodes have six elements
     * And that the sixth element is an integer
     *
     * @param $data
     * @return bool
     */
	protected function validateCatalog($data)
    {
        if(count(array_filter($data)) != 6){
            $this->_reject_catalog++;
            return false;
        } elseif (!is_numeric($data[5])){
            $this->_reject_catalog++;
            return false;
        } else{
            return true;
        }
	}
	
// <editor-fold defaultstate="collapsed" desc="ITERATOR">
	/**
	 * Iterator
	 * @return int
	 */
	public function current() {
		return $this->_current;
	}

	/**
	 * Iterator
	 */
	public function next() {
		$this->_current++;
	}

	/**
	 * Iterator
	 */
	public function rewind() {
		$this->_current = 0;
	}

	/**
	 * Iterator
	 * @return boolean
	 */
	public function valid() {
		return ($this->current() < count($this->_items)) && ($this->current() >= 0);
	}

// </editor-fold>

	/**
	 * Was the upload parsed into (probaby) valid data?
	 * 
	 * THIS NEEDS TO BE BEEFED UP ********************************************************
	 * For one thing, we have to check for unique item_code data
	 * But we also have statistics that we can use to judge with 
	 * more refinement than empty()
	 * 
	 * @return boolean
	 */
    public function isValid()
    {
        if(($this->qualityIsGreen() || $this->qualityIsOrange()) && !empty($this->items())){
            return true;
        }
        return false;
	}
	
	public function qualityIsGreen()
	{
		return !$this->qualityIsOrange() && !$this->qualityIsRed();
	}
	
	public function qualityIsOrange()
	{
		return !$this->qualityIsRed() && !empty($this->_reportStatus['orange']);
	}
	
	public function qualityIsRed()
	{
		return !empty($this->_reportStatus['red']);
	}

    /**
     * Was the first row of uploaded data a set of user column names?
     *
     * @return boolean
     */
    public function hasHeaderRow() {
        return $this->_has_header_row == 1;
    }

    /**
	 * User tell us if the first record is column labels
	 * 
	 * 1, they are labels; 0, there are no labels - all rows are data
	 * 
	 * @param int $headerSetting 1 or 0
	 */
	public function setHeaderRow($headerSetting) {
		if ($this->_has_header_row === FALSE){
		    //initialize new header row object
			$this->_has_header_row = $headerSetting;
			if ($this->hasHeaderRow()) {
				$this->_header_row = array_shift($this->_items);
			} else {
				$this->_header_row = $this->item(0);
			}
		} else {
			$this->manageHeaderRow($headerSetting);
		}
	}

    /**
     * Manage the first user data record and header record
     *
     * The first data row may be header labels or it may be data. A value
     * sent from the UX tells us which and the data is manipulated
     * to insure we have a header record to use and the data always has
     * only record data (and all the records).
     *
     * @param int $headerSetting 1 or 0
     * @return void
     */
    protected function manageHeaderRow($headerSetting) {
        if ($headerSetting == $this->_has_header_row ) { return; }

        if ($headerSetting == 1) {
            $this->_header_row = array_shift($this->_items);
        } else {
            $this->_items = array_unshift($this->items(), $this->_header_row);
        }
    }

    /**
	 * Get the user column labels
	 * 
	 * @return array
	 */
    public function userColumns()
    {
        return $this->_header_row->importValues();
	}
	
	public function hasSaveErrors() {
		$this->_save_errors = [];
		foreach ($this->items() as $index => $item) {
			if ($item->hasError()) {
				$this->_save_errors[$index] = $item->errors();
			}
		}
		return !empty($this->_save_errors);
	}
	
	public function saveErrors() {
		if(!isset($this->_save_errors)) {
			$this->hasSaveErrors();
		}
		return $this->_save_errors;
	}
	
	/**
	 * Return the array of import information and statistics
	 * 
	 * @return array
	 */
    public function validateData()
    {
		// 1%-5% of data lost is orange. greater is red
		// 1%-5% of the lines rejected is orange. greater is red
		// no records collected is red
		// 5 - 10 different node counts is orange, 11+ is red
		// 
		// any encoding rejects is orange
		// any short rejects is orange

        //setup report array
        $this->_reportStatus = [
            'green' => [],
            'orange' => [],
            'red' => []
        ];

        //run tests
        $this->_reportDataLostError();
        $this->_reportNoRecordsCollected();
        $this->_reportVariableNodeCounts();
        $this->_reportUtf8EncodingReject();
        $this->_reportTooShort();

        //add raw data report
        $this->_reportStatus['raw'] = [
			'Nummber of records collected' => count($this->_items),
            'Number of records that included non UTF-8 encoding' => $this->_count_encoding_rejects,
			"Character count in UTF-8 reject records" => $this->_reject_char_count,
            'Number of records with too few columns' => $this->_count_too_short,
			'Character count lost in rejected short records' => $this->_short_char_count,
            'Number of records that weren\'t arrays' => $this->_count_not_array,
            'Number of rejected catalogs' => $this->_reject_catalog,
			'Column counts and how many records had each count' => $this->_count_nodes,
        ];
    }
	
	public function reportErrors()
	{
		switch (TRUE) {
			case $this->qualityIsRed():
				$result = ['messages' => $this->_reportStatus['red']];
				break;
			case $this->qualityIsOrange():
				$result = ['messages' => $this->_reportStatus['orange']];
				break;
			default:
				$result = ['messages' => $this->_reportStatus['green']];
				break;
		}
		$result['processing details'] = $this->_reportStatus['raw'];
		return $result;
	}

    /**
     * Check the percentage of bad records in the import set
     *
     * 0% is green
     * 1% - 10% is orange
     * >10% is red
     *
     */
    protected function _reportDataLostError()
    {
        if ($this->_reportCountGoodRecords() > 0) {
            $test = $this->_reportCountBadRecords() / $this->_reportCountGoodRecords();
        } else {
            $test = 0;
        }
        $percentage = intval($test*100) . "%";
        switch (true){
            case ($test > 0.1):
                $this->_reportStatus['red'][] = "$percentage of the records were rejected for various reasons";
                break;
            case ($test > 0):
                $this->_reportStatus['orange'][] = "$percentage of the records were rejected for various reasons";
                break;
            default:
                $this->_reportStatus['green'][] = "All records imported";
        }
    }

    /**
     * Check number of records imported
     *
     * Only "0" is bad
     *
     */
    protected function _reportNoRecordsCollected()
    {
        $count = $this->_reportCountGoodRecords();
        switch (true){
            case ($count == 0):
                $this->_reportStatus['red'][] = "No records collected";
                break;
            default:
                $this->_reportStatus['green'][] = "$count records collected";
        }
    }

    /**
     * Check for variation in node counts in imported records
     *
     * This counts the number of columns in each records imported
     * 1-4 is green
     * 5-10 is orange
     * >10 is red
     *
     */
    protected function _reportVariableNodeCounts()
    {
        $countNodeVariants = count($this->_count_nodes);
		$recordCount = count($this->_items);
        switch (true){
            case($countNodeVariants > 10):
                $this->_reportStatus['red'][] = "$countNodeVariants different column counts were discovered "
					. "in the $recordCount records. More than 10 diffent counts in a data set is considered bad data. "
					. "You will need to clean your data before we can import it.";
                break;
            case($countNodeVariants > 4):
                $this->_reportStatus['orange'][] = "$countNodeVariants different column counts were discovered "
					. "in the $recordCount records. Up to 10 differnt column counts are allowed in an import file. "
					. "However, you will want to review your data throughly before saving it.";
                break;
            default:
                $this->_reportStatus['green'][] = "$countNodeVariants column counts were discovered. "
					. "This is considered acceptable.";
        }
    }

    protected function _reportUtf8EncodingReject()
    {
        if($this->_count_encoding_rejects > 0){
            $this->_reportStatus['red'][] = "$this->_count_encoding_rejects records were found with non-UTF8 encoding. "
					. "The system only accepts UTF-8 data.";
        }
    }

    protected function _reportTooShort()
    {
        if($this->_count_too_short){
            $this->_reportStatus['orange'][] = "$this->_count_too_short records were found with too few columns to import. "
					. "A minimum of 2 columns is required to import the record.";
        }
    }

    /**
     * Calculate sum of good records
     *
     * @return int|void
     */
    protected function _reportCountGoodRecords()
    {
        return count($this->_items);
    }

    /**
     * Calculate sum of bad records
     *
     * @return int count of all bad records
     */
    protected function _reportCountBadRecords()
    {
        $count =
            $this->_count_encoding_rejects +
            $this->_count_too_short +
            $this->_count_not_array +
            $this->_reject_catalog;
        return $count;
    }

	/**
	 * Get a page of item data for mapping previews for the UX
	 * 
	 * @param int $header 1 or 0 indicating if the first user row is header labels
	 * @param int $length
	 * @param int $page
	 * @return json
	 */
    public function getJsonChunk($header, $length, $page)
    {
        $sourceArray = $this->items();
        $sourceChunk = array_chunk($sourceArray, $length)[$page - 1];
        $jsonSamples = [];
        foreach($sourceChunk as $entity) {
            $jsonSamples[] = $entity->json();
        }
        return json_encode($jsonSamples);

    }

	/**
	 * Get the required columns as cake dot strings
	 * 
	 * These are the dot strings that would define trd structure 
	 * for form inputs. 
	 * 
	 * @return array
	 */
    public function getRequiredColumns()
    {
        return $this->ItemImportMap->getRequiredColumns();
    }

	/**
	 * Get the required columns as display labels
	 * 
	 * Padding will return an array wide enough for the preview table. 
	 * 
	 * @param boolean $padding
	 * @return array
	 */
    public function getRequiredLabels($padding = false)
    {
        if($padding){
            $result = array_pad($this->ItemImportMap->getRequiredLabels(), $this->calculatePadValue(), '');
        } else {
            $result = $this->ItemImportMap->getRequiredLabels();
        }
        return $result;
    }

    /**
     * map
     *         [
    'item.customer_item_code' => '',
    'item.name' => 4,
    'item.description' => 5,
    'item.description_2' => 0,
    'item.price' => 2,
    'item.initial_inventory' => ''
    ];

     * Get a user record arranged into the map order
	 * 
	 * User choices on an input form provides this mapping. We return all 
	 * the user data. The first columns are the required columns. Some or all 
	 * will have mapped user data. The remainder of the array will contain 
	 * the unmapped user data.
     *
     * @param $index
     * @return array
     */
    public function getMappedRecord($index)
    {
        return $this->ItemImportMap->mapRecord($this->item($index)->importValues());
    }
	
	/**
	 * Get the user header fields mapped to the required columns
	 * 
	 * The user header may actually be thier header column names 
	 * or, if they didn't provide those, this will be the first record values
	 * 
	 * @return array
	 */
	public function getMappedHeader()
    {
		return $this->ItemImportMap->mapRecord($this->userColumns());
	}

	/**
	 * Calculate the required number of columns for the preview display table
	 * 
	 * Once the user has mapped user columns to required columns the 
	 * width of the table can be calculated. 
	 * 
	 * @return int
	 */
    public function calculatePadValue()
    {
        $largestArrayLine = max(array_keys($this->_count_nodes));
        $dupeLoopArray = [];
        $dupeLoopArray[''] = 0;
		$trim = array_slice($this->getMappedHeader(), 0, $this->ItemImportMap->requiredColumnCount());
        foreach ($trim as $value){
            if(isset($dupeLoopArray[$value])){
                $dupeLoopArray[$value]++;
            } else {
                $dupeLoopArray[$value] = 0;
            }
        }
        return (array_sum($dupeLoopArray)) + $largestArrayLine;
    }

	/**
	 * secure has version of the id
	 * 
	 * @param string $data
	 */
    public function setParentId($data)
    {
        $this->_parentId = $data;
    }

	/**
	 * return the secure hash version of the id
	 * 
	 * @return string
	 */
    public function parentId()
    {
        return $this->_parentId;
    }

	/**
	 * Produce the Item/defaultCatalog save-array for the current item
	 * 
	 * @return array
	 */
    public function saveArray()
    {
        return $this->item()->saveArray($this->parentId(), $this->vendorId(), $this->ItemImportMap);
    }

	/**
	 * Produce save arrays for any additional catalogs on the current item
	 * 
	 * All items get a default catalog. This handles user defined catalogs.
	 * 
	 * @param int $item_id
	 * @return array
	 */
    public function additionalCatalogs($item_id){
        $this->item()->setItemId($item_id);
        return $this->item()->additionalCatalogs($item_id);
    }

    public function ItemImportMap()
    {
        return $this->ItemImportMap;
    }

    public function setVendorId($vendorId)
    {
        $this->_vendorId = $vendorId;
    }

    public function vendorId()
    {
        return $this->_vendorId;
    }


}