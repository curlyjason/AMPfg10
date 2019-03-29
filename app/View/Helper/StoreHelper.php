<?php
//App::uses('FgForm', '/View/Helper');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Store
 * @author dondrake
 */
class StoreHelper extends AppHelper {

	public $helpers = array('FgHtml', 'FgForm', 'Number', 'Session');

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
	}

	public function beforeRender($viewFile) {
		
	}

	public function afterRender($viewFile) {
		
	}

	public function beforeLayout($viewLayout) {
		
	}

	public function afterLayout($viewLayout) {
		
	}

	/**
	 * Prepare the Reveal Components link for kit records in store grain
	 * 
	 * This method filters for missing components and produces a standard Cake link
	 * 
	 * @param array $entry
	 * @return html
	 */
	public function revealComponentsLink($entry) {
		if ($entry['Catalog']['type'] & KIT) {
			if (!empty($entry['Catalog']['Components'])) {
				$label = 'Show components for this kit';
			} else {
				$label = 'There are no components for this kit';
			}
			return $this->FgHtml->link($label, '', array('class'=>'reveal toggle', 'id' => 'revealComponents'.$entry['Catalog']['id']));
		} else {
			return '';
		}
	}
	
	/**
	 * Prepare the Add to Cart button or Can't Add message for a store grain
	 * 
	 * Method uses the entry and the catalog.type constants to derive appropriate
	 * availability of the Add to Cart button, or, alternately, a reasonable
	 * 'you can't add the cart' message.
	 * 
	 * @param array $entry
	 * @param boolean $backorderAllow
	 * @return html
	 */
	public function addToCartBlock($entry, $backorderAllow, $itemLimitBudget) {
		// THE GRAIN CONTAINS A FORM
		$output = array( $this->FgForm->create(NULL, array(
				'url' => array('controller' => 'shop', 'action' => 'add'),
				'class' => 'addToCartForm'))
			);
		// HIDDEN ID INPUT
		$output[] = $this->FgForm->input('id', array('type' => 'hidden', 'value' => $entry['Item']['id']));

		// ITEM-LIMIT BUDGET ALERT	
		$entry['sell_unit'] = $entry['Catalog']['sell_unit'];
		$output[] = $this->FgHtml->itemLimitAlert($entry, $itemLimitBudget);

		//Set allow variable
		$addToCartAllow = FALSE;
		//disallow all guests up front
		if ($this->Session->read('Auth.User.access') != 'Guest') {
			if ($backorderAllow || $entry['Item']['available_qty'] > 0) {
				//@todo need to address kit and component availability, not just item availability
				//
			//There's inventory to order, or the user is allowed to backorder products
				if (($entry['Catalog']['type'] & COMPONENT) && ($entry['Catalog']['type'] & ORDER_COMPONENT) && ($entry['ParentCatalog']['type'] & ORDER_COMPONENT)) {
					//This record is a COMPONENT, and both it and it's parent allows ordering
					$addToCartAllow = TRUE;
				} elseif (($entry['Catalog']['type'] & KIT) || ($entry['Catalog']['type'] & PRODUCT)) {
					//This record is a KIT or PRODUCT
					$addToCartAllow = TRUE;
				} else {
					$addToCartAllow = FALSE;
					$message = 'Can\'t Order Component';
				}
			} else {
				$addToCartAllow = FALSE;
				$message = 'Out of Stock';
			}
		} else {
			$addToCartAllow = FALSE;
			$message = '';
		}
		$priceBlock = $this->priceBlock($entry);
		
		if ($addToCartAllow) {
			$output[] = $this->FgForm->input('quantity', array(
				'label' => 'Qty',
				'class' => 'cartQuantityInput',
				'id' => $entry['Item']['id'] . 'quantity',
				'default' => 1,
				'before' => "$priceBlock<p>{$this->FgHtml->calculatedQuantity($entry,'Catalog')}</p> ",
				'div' => array('class' => 'input text quantity')
				));

			$output[] = $this->FgForm->button('Add to Cart', array(
				'class' => 'btn btn-primary addtocart',
				'catalogId' => $entry['Catalog']['id'],
				'bind' => 'click.addToCart'));
		} else {
			$output[] = "$priceBlock<p>{$this->FgHtml->calculatedQuantity($entry,'Catalog')}</p>";
			$output[] = $this->FgHtml->para('noAdd', $message);
		}
		
		$output[] = $this->FgForm->end();

		return implode("\n", $output);
	}
	
	/**
	 * Build a price block for a store grain based upon price > 0
	 * 
	 * @param array $entry
	 * @return html
	 */
	public function priceBlock($entry) {
		if($entry['Catalog']['price'] > 0){
			$priceBlock = "Price: {$this->Number->currency($entry['Catalog']['price'], 'USD')}/{$entry['Catalog']['sell_unit']}\r";
		} else {
			$priceBlock = '';
		}
		return $priceBlock;
	}
}
