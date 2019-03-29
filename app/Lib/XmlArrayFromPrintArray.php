<?php
/**
 * CakePHP XmlArrayFromPrintArray
 * @author dondrake
 */
class XmlArrayFromPrintArray {

	/**
	 * provide xml array assembly with an index translation for OrderItems
	 * 
	 * getOrderForPrint() result array is the basis for xml response arrays
	 * but it was a bit clumsy in index construction for OrderItem
	 * data points and their labels. This provides a work-around.
	 *
	 * @var array
	 */
	protected $itemLabel = array(
		1 => 'quantity', 
		2 => 'code',
		3 => 'name'
	);
	
	/**
	 * The assembly container and eventual return value
	 *
	 * @var array
	 */
	public $data = array();
	
	/**
	 * The model alias
	 *
	 * @var string
	 */
	public $alias;

	public function __construct($alias) {
		$this->alias = $alias;
	}

	/**
	 * Convert a print-order array to an xml response array
	 * 
	 * @param array $data An array from $this->getOrderForPrint()
	 */
	public function xmlArrayFromPrintArray($data) {
	    debug($data);
	    debug($this->alias);die;
		$this->data = $data;
		$result =  array(
			'body' => array(
				$this->alias => array(
					'Summary' => $this->orderMeta(),
					'Addreses' => array(
						'Billing' => $this->address($this->data['billing']),
						'Shipping' => $this->address($this->data['shipping'])
					),
					"{$this->alias}Items" => array(
						"{$this->alias}Item" => $this->items()
					)
				)
			)
		);
		
		return $result;
	}
	
	/**
	 * munge together all print-order label/data pairs to create a Summary secion
	 * 
	 * @return array
	 */
	protected function orderMeta() {
		return array_merge(
				$this->labels($this->data['reference']), 
				$this->labels($this->data['summary'])
			);
	}
	
	/**
	 * munge together a particular print-order label/data pair set
	 * 
	 * @param array $block The specific print-order label/data pair set
	 * @return array
	 */
	private function labels($block) {
		$result = array();
		foreach ($block['labels'] as $i => $v) {
			$result[str_replace(' ', '', $block['labels'][$i])] = $block['data'][$i];
		}
		return $result;
	}

	/**
	 * Turn a print-order address into an address string
	 * 
	 * @param array $address
	 * @return array
	 */
	protected function address($address) {
		return str_replace(', , ', ', ', implode(', ', $address));
	}
	
	/**
	 * From OrderItems in a print-order array, make the xml response array version
	 * 
	 * @return array
	 */
	protected function items() {
		$result = array();
		foreach ($this->data['items'] as $i => $item) {
			$result[$i] = $this->item($item);
		}
		return $result;
	}
	
	/**
	 * Make a single OrderItem array for the xml response
	 * 
	 * @param array $item
	 * @return array
	 */
	private function item($item) {
		$result = array();
		$c = 1;
		while ($c < 4) {
			$result[$this->data['headerRow'][$c]] = $item[$this->itemLabel[$c++]];
		}
		return $result;
	}

}
