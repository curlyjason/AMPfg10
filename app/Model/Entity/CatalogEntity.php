<?php

/**
 * Description of CatalogEntity
 *
 * @author dondrake
 */
class CatalogEntity {
	
	/*
	 * Always required structure (in this order without string indexes):
	 * 'Catalog'
	 * 'CustomerItemCode'
	 * 'Name'
	 * 'Description'
	 * 'Unit'
	 * 'Qty'
	 */
	protected $_rawCatalog;

	/**
	 * This was an empty catalog created to produce the default Catalog record
	 *
	 * @var boolean
	 */
	protected $_default = false;

	public function __construct($data = FALSE) {
		if($data) {
			$this->_rawCatalog = $data;
		} else {
			$this->_default = TRUE;
		}
	}
	
	public function customerItemCode($Item = null) {
	    if($this->_default){
	        return $Item->customerItemCode();
        } else {
            return $this->_rawCatalog[1];
        }
	}
	
	public function name($Item = null) {
	    if($this->_default){
	        return ($Item->name());
        } else {
            return $this->_rawCatalog[2];
        }
	}
	
	public function description($Item = null) {
	    if($this->_default){
	        return $Item->description();
        } else {
            return $this->_rawCatalog[3];
        }
	}

    public function price($Item = null) {
        if($this->_default){
            return $Item->price();
        } else {
            return "0.00";
        }
    }

    public function maxQuantity($Item = null) {
        if($this->_default){
            return $Item->maxQuantity();
        } else {
            return "";
        }
    }

	public function unit($Item = null) {
	    if($this->_default){
	        return 'ea';
        } else {
            return $this->_rawCatalog[4];
        }
	}

    public function quantity($Item = null) {
        if($this->_default){
            return '1';
        } else {
            return $this->_rawCatalog[5];
        }
    }

	/**
     * Provide the catalog element of the save array
     *
     * @param $Item the item object
     * @return array
     */
	public function saveArray($Item)
    {
        return [
			// always default values
			'id' => '',
			'type_context' =>		'',
			'currentNode' =>		'',
			'type' =>				'4',
			'active' =>				'1',
			'kit_prefs' =>			'128',
			'can_order_components' => '0',
			'item_code' =>			'',
			
			// must come from Item
			'parent_id' =>			$Item->parentId(),
			'item_id' =>			$Item->itemId(),
			
			// might come from Item, might come from Catalog
			'customer_item_code' => $this->customerItemCode($Item),
			'name' =>				$this->name($Item),
			'description' =>		$this->description($Item),
			'price' =>				$this->price($Item),
			'max_quantity' =>		$this->maxQuantity($Item),
			
			// might come from Catalog, might default
			'sell_unit' =>			$this->unit($Item),
			'sell_quantity' =>		$this->quantity($Item),
		];
	}

}
