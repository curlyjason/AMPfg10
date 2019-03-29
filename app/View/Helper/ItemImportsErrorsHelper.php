<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP ItemImportsErrorsHelper
 * @author dondrake
 */
class ItemImportsErrorsHelper extends AppHelper
{
	protected $format = '%s %s: %s';
	
	protected $result = [];

	public $helpers = array();
	
	public function setFormat($format)
	{
		$this->format = $format;
	}

	public function __construct(View $View, $settings = array())
	{
		parent::__construct($View, $settings);
	}

	/**
	 * Simplify a nested error array from the cake save validation system
	 * 
	 * @param array $errors
	 * @param string $style 'String' or 'KeyValue'
	 * @return array
	 */
	public function simplify($errors, $style)
	{
        $this->result = [];
        foreach ($errors as $fieldErrors) {
			$this->simplifyFieldErrors($fieldErrors, $style);
		}
        return $this->result;
    }
	
	protected function simplifyFieldErrors($fieldErrors, $style)
	{
	    if(array_key_exists('catalog', $fieldErrors)) {
	        $number = $fieldErrors['catalog'] + 1;
            $type = "Catalog #$number: ";
        } else {
	        $type = 'Item: ';
        }
        foreach ($fieldErrors as $field => $errors) {
            if ($field != 'catalog') {
                $method = "error$style";
                foreach ($errors as $error) {
                    $this->$method($type, $field, $error);
                }
            }
        }
    }
	
	protected function errorString($type, $field, $error)
	{
		$this->result[] = sprintf($this->format, $type, $field, $error);
	}
	
	/**
	 * Array
(
    [0] => Array
        (
            [customer_item_code] => Array
                (
                    [0] => Only unique customer item codes are allowed.
                )

        )

)

	 * 
	 * @param type $errors
	 */
	protected function errorKeyValue($field, $error)
	{
		array_push($this->result, [$field => $error]) ;
	}

}
