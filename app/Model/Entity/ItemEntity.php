<?php

/**
 * Description of ItemEntityClass
 *
 * @author jasont
 */

App::uses('CatalogEntity', 'Model/Entity/');

class ItemEntity extends Entity {

    protected $_rawItem;
    protected $_importMap;
    protected $_catalog;
	
	/**
	 * Validation errors returned from the model on attempted save
	 * 
	 * There may be multiple field errors per Item, and because there can 
	 * be additional catalogs for the Item, there may be multiple 
	 * error packages.
	 * 
	 * If an array has a 'catalog' key it applies to a catalog and the 
	 * value of that 'catalog' node is the index into _catalog property
	 * 
	 * If the 'catalog' node is not present, it is the Item and its 
	 * default catalog that did not save. In this case, we will attempt to save
     * the additional catalogs to a valid item_id. If we cannot find that item_id
     * or the catalog fails to save for its own reasons, we will log those
     * failures as well.
	 * 
	 * [
	 *		['name-of-field' => 
	 *			[ 
	 *				//array of error messages for this field
	 *			]
	 *		 'another-field-name' => // optional secondary field errors
	 *			[
	 *				// array of errors
	 *			]
	 *		]
	 * ]
	 *		---------  OR  -----------------------------------
	 * [
	 *		['catalog' => 2, //index of the unsaved _catalog
	 *		 'name-of-field' => 
	 *			[ 
	 *				// array of error message fro this field
	 *			]
	 *			... //optional secondary field errors
	 *		],
	 *		[
	 *			//optional additional catalog save errors
	 *		]
	 * ]
	 */
	protected $_db_validation_errors = [];
	
	
    protected $_requiredCatalogColumns =
        [
            'customer_item_code',
            'name',
            'description',
            'sell_unit',
            'sell_qty'
        ];

	// <editor-fold defaultstate="collapsed" desc="UNUSED possibly">
		protected $_saveTemplate =         [
			'Catalog' =>
			[
				'parent_id' => '',
				'id' => '',
				'type_context' => '',
				'currentNode' => '',
				'name' => '',
				'type' => '4',
				'active' => '1',
				'kit_prefs' => '128',
				'can_order_components' => '0',
				'item_id' => '',
				'item_code' => '',
				'customer_item_code' => '',
				'description' => '',
				'sell_unit' => 'ea',
				'sell_quantity' => '1',
				'price' => '',
				'max_quantity' => ''
			],
			'Item' =>
			[
				'source' => '0',
				'id' => '',
				'po_unit' => 'ea',
				'po_quantity' => '',
				'cost' => '',
				'vendor_id' => '',
				'po_item_code' => '',
				'reorder_level' => '',
				'reorder_qty' => '',
				'quantity' => ''
			]
		]; // </editor-fold>
		
	public $ItemImportMap;

    protected $_mappedItem;

    protected $_parentId;

    protected $_itemId = '';

    protected $_keyedItem;

    protected $_header;

    public function import($data)
    {
        $this->_rawItem = $data;
	}

	public function rawCatalogs() {
		return $this->_catalog;
	}
	
	public function hasCatalogs() {
		return isset($this->_catalog) && !empty($this->_catalog);
	}

	public function insertCatalog($data)
    {
        $this->_catalog[] = new CatalogEntity($data);
	}
	
	public function importValues() {
		return $this->_rawItem;
	}
	
	public function mapColumn($index) {
		if(isset($this->_rawItem[$index])) {
			return $this->_rawItem[$index];
		}
		return '';
	}
	
	/**
	 * Were there errors during the save process
	 * 
	 * The errors may have been on the Item (so nothing saved) or on 
	 * one or more suplimentary catalog, allowing some data to save
	 * 
	 * @return boolean
	 */
	public function hasError() {
		return ($this->errors());
	}
	
	/**
	 * Was there an error trying to save the Item and default Catalog
	 * 
	 * This would prevent any additional catalogs from saving also
	 * 
	 * @return boolean
	 */
	public function hasItemError() {
		return $this->hasError() && !isset($this->errors()[0]['catalog']);
	}
	
	public function setErrors($errors) {
		array_push($this->_db_validation_errors, $errors);
	}
	
	public function errors() {
		return $this->_db_validation_errors;
	}

	public function saveArray($parentId, $vendorId, $ItemImportMap) {
        $this->_parentId = $parentId;
		$this->_keyedItem = $ItemImportMap->keyRecord($this->importValues());
		$this->_keyedItem['vendor_id'] = $vendorId;
        $result =
			[
				'Catalog' => (new CatalogEntity(false))->saveArray($this),
				'Item' => $this->_keyedItem
			];

        return $result;
				

//		$cat = [
//			'parent_id' => '', // provided and calc
//            'type' => '4', //default
//            'active' => '1', //default
//            'kit_prefs' => '128', //default
//            'can_order_components' => '0', //default
//            'customer_item_code' => '',
//            'sell_unit' => 'ea', //default for first
//            'sell_quantity' => '1', //default for first
//            'sequence' => '',
//            'id' => '',
//            'type_context' => '',
//            'item_id' => '',
//            'item_code' => '',
//            'customer_item_code' => '',
//            'description' => '',
//            'price' => '2',
//            'max_quantity' => '4'
//		];
//

		// NEEDED
		// catalog id
		// vendor id
		// type (one of these)
			// define("KIT", 1); has 4 different kit_prefs
			// define("FOLDER", 2);
			// define("PRODUCT", 4);
			// define('COMPONENT', 8);
			
		// MOVE
		// customer_item_code to catalog
		//     the first catalog will share moved data back to the item
		//     subsequent catalogs hold their own data and don't touch item
		// name to catalog
		/*
		 *             'item' => array(
                'customer_item_code' => '6', // to catalog
                'name' => '1',				 // to catalog
                'description' => '2',		 // to catalog
                'description_2' => '4',		 //
                'price' => '3',				 // to catalog
                'initial_inventory' => '7'	 // to item quantity
                )

		 */
	}
	
	/**
	 * UNUSED? <==================================UNUSED? <==================================UNUSED? <==================================
	 * 
	 * @param type $map
	 * @param type $format
	 * @return type
	 */
	public function mapRecord($map, $format = '%s: %s') {
		$result = [];
		foreach($map as $label => $index) {
			$result[] = sprintf($format, $label, $this->mapColumn($index));
		}
		return $result;
	}
	
	/**
	 * Provide a sample for an accumulating set of samples
	 * 
	 * @return json
	 */
	public function json() {
		$data = [
			'item' => $this->_rawItem,
			'catalogs' => $this->_catalog
		];
		return json_encode($data);
	}


    public function customerItemCode() {
	    if(isset($this->_keyedItem['customer_item_code'])){
            return $this->_keyedItem['customer_item_code'];
        } else {
	        return '';
        }
    }

    public function name() {
	    if(isset($this->_keyedItem['name'])){
            return $this->_keyedItem['name'];
        } else {
	        return '';
        }
    }

    public function description() {
        if(isset($this->_keyedItem['description'])){
            return $this->_keyedItem['description'];
        } else {
            return '';
        }
    }

    public function price() {
        if(isset($this->_keyedItem['price'])){
            return $this->_keyedItem['price'];
        } else {
            return '';
        }
    }

    public function maxQuantity() {
        if(isset($this->_keyedItem['max_quantity'])){
            return $this->_keyedItem['max_quantity'];
        } else {
            return '';
        }
    }

    public function unit() {
        if(isset($this->_keyedItem['unit'])){
            return $this->_keyedItem['unit'];
        } else {
            return '';
        }
    }

    public function quantity() {
        if(isset($this->_keyedItem['quantity'])){
            $result = $this->_keyedItem['quantity'];
        } else {
            $result = 0;
        }
        return intval($result);
    }

	/**
	 * SET BUT UNUSED <=================== SET BUT UNUSED? <======================= SET BUT UNUSED? <=====================
	 * 
	 * @param int $item_id
	 */

    public function setItemId($item_id)
    {
        $this->_itemId = $item_id;
    }

    public function itemId()
    {
        return $this->_itemId;
    }

    public function additionalCatalogs($itemId)
    {
        $result = [];
        foreach ($this->_catalog as $catalog){
            $product =
                [
                    'Catalog' => $catalog->saveArray($this),
                    'Item' => $this->_keyedItem += ['id' => $itemId]
                ];
            array_push($result, $product);
        }
        return $result;
    }

    public function parentId()
    {
        return $this->_parentId;
    }

// <editor-fold defaultstate="collapsed" desc="Legacy methods">
	public function __toString() {
		$string = <<<"STRING"
<h3 style="margin: 0;">$this->name</h3>
<p style="margin: 0;">$this->description</p>
<ul style="font-size:90%; margin-top: 0; padding-left: 14px;">
		<li>CODE: $this->item_code</li>
		<li>CUST CODE: $this->customer_item_code</li>
		<li>QTY: $this->quantity</li>
		<li>AVAIL: $this->available_qty</li>
		<li>PEND: $this->pending_qty</li>
</ul>
STRING;
		return $string;
	}

	public function __get($key) {
        $value = parent::__get($key);
		switch ($key) {
			case 'pending_qty':
				return $this->pending_qty($value);
				break;
			case 'quantity':
			case 'available_qty':
				return $this->numFormat($value);
				break;

			default:
				return $value;
				break;
		}
	}

	/**
	 * Adjust pending quantity to a human readable value
	 *
	 * pending_qty stores qty+(on order qty). Humans expect to
	 * see how many are on order.
	 *
	 * @param string $pending_qty
	 * @return string
	     */
	public function pending_qty($pending_qty) {
		if (!$pending_qty or $pending_qty == 0) {
			// hopefully NULL, 0, '0', '0.0', ''
			return '0';
		}
		return $pending_qty - $this->quantity;
	}

	public function numFormat($value) {
		return $value - 0;
	}

// </editor-fold>


}
