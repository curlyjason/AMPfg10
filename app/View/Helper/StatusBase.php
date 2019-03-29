<?php

/**
 * CakePHP StatusBase
 * @author jasont
 */

App::uses('AppHelper', 'View/Helper');
App::uses('FgHtml', 'View/Helper');
App::uses('Html', 'View/Helper');
App::uses('Form', 'View/Helper');
App::uses('FgForm', 'View/Helper');
App::uses('Session', 'View/Helper');

class StatusBase extends AppHelper {
	public $access = '';
	public $group = '';
	public $tools = array();
	public $user = '';
	
	/**
	 * Helper Objects
	 */
	public $Html;
	public $FgHtml;
	public $Form;
	public $FgForm;
	public $Session;
	
	/**
	 * The Status steps and next-steps for orders
	 * 
	 * These states are used in Order and Replenishment Models
	 * to order the output on status page. Any changes here
	 * will require changes in those places also to control output order
	 *
	 * @var array
	 */
	public $orderProcess = array(
		//Order States
		'Backordered' => 'Submit',
		'Submitted' => 'Approve',
		'Approved' => 'Release',
		'Released' => 'Pull',
		'Pulled' => 'Ship',
		'Shipping' => 'Ship',
		'Shipped' => 'Invoice',
		'Invoiced' => 'Archive',
		
		//Replenishment States
		'Open' => 'Place',
		'Placed' => 'Complete',
		'Completed' => 'Archive'
	);



//	public $helpers = array('Html', 'Session', 'Number', 'FgForm', 'Markdown.Markdown');

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		
		// Load helpers
		// Once the inheritance is fixed, these could be moved to the normal $helpers property
		$this->Html = $this->_View->Helpers->load('Html');
		$this->FgHtml = $this->_View->Helpers->load('FgHtml');
		$this->Form = $this->_View->Helpers->load('Form');
		$this->FgForm = $this->_View->Helpers->load('FgForm');
		$this->Session = $this->_View->Helpers->load('Session');
		
		$this->access = $this->Session->read('Auth.User.access');
		$this->group = $this->Session->read('Auth.User.group');
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
	 * Add a link to the tools property to reveal the line items of an order or replenishment
	 * 
	 * @param string $alias Order or Replenishment
	 * @return sting HTML link added to tools property
	 */
	function revealItemsLink($alias) {
		$this->tools[] = $this->Html->link('Reveal Items', '', array(
			'id' => $this->data[$alias]['id'],
			'class' => 'revealItems',
			'bind' => 'click.revealItemsToggle'
		));
		return;
	}
	
	/**
	 * Make a link to add charges to an Order or OrderItem
	 * 
	 * @param string $itemId The Order or OrderItem id that will serve as the Charge Group filter
	 * @param string $alias Order or Replenishment
	 * @param string $product The name of the product (to add to the tool pallet header)
	 * @return string An HTML link
	 */
	public function chargesTool($alias, $product, $type, $itemId = NULL) {
		if($itemId == NULL){
			$id = $this->data[$alias]['id'];
		} else {
			$id = $itemId;
		}
		if ($this->data[$alias]['status'] != 'Invoiced'
			&& ($this->group != 'Clients'
				&& ($this->access == 'Manager' || $this->access == 'Admin'))
			&& $alias != 'Replenishment') {
				$this->tools[] = $this->Html->link(
					'Charges', 
					array(
						'controller' => 'invoice_items',
						'action' => 'fetchInvoiceLis',
						$id,
						$type),
					array(
						'bind' => 'click.fetchChargeItems',
						'customer' => $this->data[$alias]['billing_company'] . ' Order ' . $this->data[$alias]['order_number'] . ' ' . $product
				));
			}
		return;
	}
	
	/**
	 * Add a link to the tools array to show the Documents element for an order
	 * 
	 * @param string $alias Order or Replenishment
	 * @return sting HTML link added to tools property
	 */
	public function documentTool($alias) {
		if($alias == 'Order'){
			$url = Router::url(array(
				'controller' => 'documents',
				'action' => 'order',
						$this->data[$alias]['id'])
			);
			if(empty($this->data['Document'])){
				$a = '';
			} else {
				$c = count($this->data['Document']);
				$l = ($c>1)?"are $c documents":"is $c document";
				$a = $this->FgHtml->countAlert($c, "There $l attached.", 0, array('bind' => 'click.fetchDocuments', 'href' => $url));
			}
			$a .= $this->Html->link('Docs', array(
							'controller' => 'documents',
							'action' => 'order',
							$this->data[$alias]['id']),
						array(
							'bind' => 'click.fetchDocuments'
							));
			$this->tools[] = $a;
		}
		return;
	}
	
	/**
	 * Conditionally create the note element for status display
	 * 
	 * @param array $data
	 * @return string
	 */
	public function noteIndicator(){
		$note = NULL;
		$noteText = NULL;
		if ($this->data['Order']['note'] != '') {
			$note = $this->Html->link('Note', array(''), array('id' => 'statusNote-'.$this->data['Order']['id'], 'bind' => 'click.noteLink'));
			$theX = $this->Html->para('close toggle', 'X', array('id' => "statusNoteX-{$this->data['Order']['id']}"));
			$text = $theX . $this->Html->para('', 'Note') . $this->Html->div('', $this->FgHtml->markdown($this->data['Order']['note']));
			$noteText = $this->Html->div("toolPallet hide statusNote-{$this->data['Order']['id']} statusNoteX-{$this->data['Order']['id']}", $text);
//			$note = $this->div('statusNote',$this->para('statusNoteLabel', 'Note'. $this->div('statusNoteText hide', $this->markdown($data['Order']['note']))));
		}
		return $note . $noteText;
	}
    
	/**
	 * Construct shipping cell content, with tracking links where appropriate
	 * 
	 * @return string
	 */
	public function shippingCell($data) {
		$content = $this->shippingCellEdit($data) . ' ';
		if (in_array($data['Order']['status'], array('Shipped', 'Shipping', 'Invoiced'))) {
			if (isset($data['Shipment'][0]['tracking'])) {
				if ($data['Shipment'][0]['carrier'] === 'FedEx') {
					$content .= 'FedEx :: ' . $this->Html->link($data['Shipment'][0]['tracking']
							, "http://www.fedex.com/insight/findit/nrp.jsp?tracknumbers={$data['Shipment'][0]['tracking']}&language=en&opco=FX&clientype=ivshpalrt");
				} elseif ($data['Shipment'][0]['carrier'] === 'UPS') {
					$content .= 'UPS :: ' . $this->Html->link($data['Shipment'][0]['tracking']
							, "http://wwwapps.ups.com/WebTracking/processRequest?HTMLVersion=5.0&Requester=NES&AgreeToTermsAndConditions=yes&loc=en_US&tracknum={$data['Shipment'][0]['tracking']}&WT.z_eCTAid=ct1_eml_Tracking");
				} else {
					$content .= $data['Shipment'][0]['method'] . ' :: ' . $data['Shipment'][0]['tracking'];
				}
			} else {
				$content .= $data['Shipment'][0]['method'] . ' :: No track#';
			}
		}  else {
			$content .= $this->Html->tag(
					'span',
					(isset($data['Shipment'][0]['carrier'])) ? $data['Shipment'][0]['carrier'] . ' :: ' . $data['Shipment'][0]['method'] : 'None',
					array(
						'class' => 'shipCell_' . $data['Order']['id']
					)
				);
		}
		return str_replace('http://', 'https://', $content);
	}


	/**
	 * Construct a proper edit button for the shipping information
	 * 
	 * @param array $data
	 * @return string the edit link
	 */
	protected function shippingCellEdit($data) {
		if ((in_array($this->group, array('Staff', 'Admins', 'Warehouses'))) 
                && ($this->access == 'Manager')
                && (!in_array($data['Order']['status'], array('Invoiced', 'Archived')))){
			return $this->Html->link('edit', array(
						'controller' => 'shipments',
						'action' => 'editOrderShipment',
						$data['Order']['id']),
					array(
						'bind' => 'click.editShipment',
						'class' => 'editShipment'
			));
		} else {
			return '';
		}
	}
}
