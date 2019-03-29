<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-03-07
 * Time: 13:40
 */

class ItemImportMap
{
    protected $_requiredColumns =
        [
            'item.customer_item_code',
            'item.name',
            'item.description',
            'item.price',
            'item.quantity',
        ];

    protected $_columnTransforms =
        [
            'customer_item_code' => 'text',
            'name' => 'text',
            'description' => 'text',
            'price' => 'float',
            'quantity' => 'int'
        ];

    public function columnTransforms($key, $data)
    {
        $transforms = [
            'none' =>
                function() use ($data) {
                    return $data;
                },
            'float' =>
                function() use ($data) {
                    return floatval($data);
                },
            'int' =>
                function() use ($data) {
                    return intval($data);
                },
            'text' =>
                function() use ($data) {
                    return mb_convert_encoding($data, "UTF-8");
                }
        ];
        return $transforms[$key]($data);

    }

    protected $_requiredLabels = [];
    
    protected $_requiredIndexes = [];

    protected $_map;

    public function __construct()
    {
        $this->_requiredLabels = $this->getRequiredLabels();
        $this->_requiredIndexes = array_keys($this->_requiredColumns);
    }
	
	public function requiredColumnCount() 
	{
		return count($this->_requiredColumns);
	}

// <editor-fold defaultstate="collapsed" desc="Universal map value access">
    
    public function index($value)     {
        if (in_array($value, $this->_requiredIndexes)) {
            return $value;
        } elseif (in_array($value, $this->_requiredLabels)) {
            $label_flip = array_flip($this->_requiredLabels);
            return $label_flip[$value];
        } elseif (in_array($value, $this->_requiredColumns)) {
            $label_flip = array_flip($this->_requiredColumns);
            return $label_flip[$value];
        } else {
            return false;
        }
    }

    public function column($value)     {
        if (in_array($value, $this->_requiredIndexes)) {
            $result = $this->_requiredColumns[$value];
        } elseif (in_array($value, $this->_requiredLabels)) {
            $label_flip = array_flip($this->_requiredLabels);
            $result = $this->_requiredColumns[$label_flip[$value]];
        } elseif (in_array($value, $this->_requiredColumns)) {
            $result = $value;
        } else {
            return false;
        }
        return str_replace('item.', '', $result);
    }

    public function label($value)     {
        if (in_array($value, $this->_requiredIndexes)) {
            return $this->_requiredLabels[$value];
        } elseif (in_array($value, $this->_requiredLabels)) {
            $label_flip = array_flip($this->_requiredLabels);
            return $this->_requiredLabels[$label_flip[$value]];
        } elseif (in_array($value, $this->_requiredLabels)) {
            return $value;
        } else {
            return false;
        }
    }

// </editor-fold>


    /**
     * Create json data to help map required columns to user columns
     *
     * Ajax call point
     */
    public function getMapTemplate() {
        $this->layout = 'ajax';
        $result = [
            'labels' => $this->_requiredLabels,
            'indexes' => array_pad([], count($this->_requiredLabels), null)
        ];
        return $result;
    }

    /**
     * Convert the list of required columns into display worthy strings
     *
     * @return array
     */
    public function getRequiredLabels() {
        $result = [];
        foreach ($this->_requiredColumns as $label){
            $label = str_replace('item.', '', $label);
            $label = str_replace('_', ' ', $label);
            $result[] = ucwords($label);
        }
        return $result;
    }

    /**
     * Get the array of item columns we'll need mapped to user data
     *
     * @return array
     */
    public function getRequiredColumns()
    {
        return $this->_requiredColumns;
    }

    public function setMap($map)
    {
        $this->_map = $map;
    }

    public function getMap()
    {
        return $this->_map;
    }

    public function mapRecord($record)
    {
        $remainder = $record;
        $result = [];
        foreach ($this->getMap() as $pointer){
            $result[] = ($pointer === '') ? '' : $record[$pointer];
            if($pointer !== ''){
                unset($remainder[$pointer]);
            }
        }
        return array_merge($result, $remainder);
    }

	public function keyRecord($record) {
        $result = [];
		$mapRecord = $this->mapRecord($record);
		$i = 0;
		while($i<$this->requiredColumnCount()){
		    $transform = $this->columnTransforms(
                $this->_columnTransforms[$this->column($i)],
                $mapRecord[$i]);
		    $result[$this->column($i)] = $transform;
		    $i++;
        }
        return $result;
		
	}

}