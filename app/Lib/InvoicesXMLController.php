<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * CakePHP InvoicesXMLController
 * @author jasont
 */
class InvoicesXMLController extends AppController {

	public function index($id) {
		
	}
	
	// ========================================================
	// SOAP TOOLS FOR ENTERPRISE/INVOICE PROCESS
	// ========================================================
	
	//This is the former Submit Invoice, setup for XML use
	
	/**
	 * The process to finalize an invoice
	 * 
	 * Send it to EPMS
	 * Link the line items up to an Invoice record
	 * Possibly show the invoice again, though this seems unnecessary
	 * 
	 * @param type $cust_id
	 */
	public function submitInvoice($cust_id) {
		$this->Invoice->customer = $cust_id;
		if ($this->Invoice->epmsJobId = $this->createXmlInvoice($cust_id)) {
			$this->packageInvoiceItems();
			$this->Session->setFlash('The invoice was recorded in EPMS', 'flash_success');
			$this->updateInvoiceStatus($cust_id);
			// change the involved orders to 'invoiced' status
			// use the ordercontroller method? or write a new one?
		} else {
			$this->Session->setFlash('The invoice was not recorded in EPMS. Please try again', 'flash_error');
		}
		$this->set('invoiceId', $this->Invoice->id);
//		$this->viewOldInvoice($this->Invoice->id);
		// temporary destination
//		$this->redirect(array('action' => 'viewOldInvoice', $this->Invoice->id . '.pdf'), NULL, FALSE);
//		$this->redirect(array('controller' => 'clients', 'action' => 'status'));
	}

	
	public function createXmlInvoice($cust_id) {
		$this->invoice = $this->requestAction(array('controller' => 'invoice_items', 'action' => 'fetchInvoiceLis', $cust_id, 'Customer'));
		if (empty($this->invoice)) {
			return FALSE;
		}
		$order =  
			array_merge_recursive(//$this->orderWrapper(),
			$this->jobNode(),
			$this->userDefinedNode(),
			$this->taxNode(),
			$this->salesNode(),
			$this->templateNode(),
			$this->priceNode(),
			$this->componentsWrapper(),
			$this->shippingWrapper()
		);
		
		$this->invoiceSOAP = array(
			'soap12:Envelope' => array(
				'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
				'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
				'xmlns:soap12' => "http://www.w3.org/2003/05/soap-envelope",
				'soap12:Body' => array(
					'SubmitOrder' => array(
						'xmlns:' => "http://localhost/EnterpriseWebService/Enterprise%20Connect",
						'Credentials' => $this->xmlCredentials['Credentials'],
						'Order' => $order['Order']
					)
				)
			)
		);
		
		$obj = Xml::fromArray($this->invoiceSOAP);
		echo Debugger::trace();
		$this->ddd($this->invoiceSOAP, 'invoiceSOAP');
		$this->ddd($obj->asXML(), 'obj');
		die;
		
//		$this->ddd(str_replace('><', ">\n<", $obj->asXML()));
//		// 1. initialize
//		$ch = curl_init();
//
//		// 2. set the options, including the url
//		curl_setopt($ch, CURLOPT_URL, "/EnterpriseWebService/Service.asmx");
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_HEADER, 0);
//
//		// 3. execute and fetch the resulting HTML output
//		$output = curl_exec($ch);
//
//		// 4. free up the curl handle
//		curl_close($ch);	
		
		$epmsJobId = '14-11454'; // temporary value TODO ************************ TODO
		return $epmsJobId;
	}
	
	/*
	 * Build the SOAP XML job nodes for an Invoice
	 * <Order>
	 * ...jobNode
	 * ...userDefinedNode
	 * ...taxNode
	 * ...salesNode
	 * ...template node
	 * ...price node
	 * ...componentsWrapper
	 * ......componentNode
	 * ...componentsWrapper
	 * ...shipmentsWrapper
	 * ......shipmentNode
	 * ...shipmentsWrapper
	 * </Order>
	 */
//	private function orderWrapper() {
//		return array('Order' => array());
//	}

	/*
	 * Build the SOAP XML credential nodes for an Invoice
     *       <Credentials>
     *         <Username>Jason</Username>
     *         <Password>tanstaafl</Password>
     *       </Credentials>
	 */
//	private function credentialNode() {
//		return array('Order' => $this->xmlCredentials);
//	}
	
	/*
	 * Build the SOAP XML job nodes for an Invoice
	 *	 -- Order
	 *         <CustAccount>string</CustAccount>
     *         <JobDescription>string</JobDescription>
     *         <PONumber>string</PONumber>
	*/
	private function jobNode() {
		return array('Order' => array(
			'CustAccount' => 'string',
			'JobDescription' => 'string',
			'PONumber' => 'string'
			));		
	}
	
	/*
	 * Build the SOAP XML user defined nodes for an Invoice
	 *	 -- Order
     *         <UserDefinedField1>Pull & Ship</UserDefinedField1>
     *         <UserDefinedField4>Standard</UserDefinedField4>
	 */
	private function userDefinedNode() {
		return array('Order' => array(
			'UserDefinedField1' => 'Pull & Ship',
			'UserDefinedField4' => 'Standard'
			));				
	}
	
	/*
	 * Build the SOAP XML tax nodes for an Invoice
	 *	 -- Order
     *         <TaxJurisdiction>string</TaxJurisdiction>
     *         <TaxOverrideAmount>decimal</TaxOverrideAmount>
	 */
	private function taxNode() {
		return array('Order' => array(
			'TaxJurisdiction' => 'string',
			'TaxOverrideAmount' => 'decimal'
			));				
	}
	
	/*
	 * Build the SOAP XML sales nodes for an Invoice
	 *	 -- Order
     *         <PlantID>AMP</PlantID>
     *         <SalesRepCode>RL</SalesRepCode>
     *         <QuantityOrdered>long</QuantityOrdered>
	 */
	private function salesNode() {
		return array('Order' => array(
			'PlantID' => 'AMP',
			'SalesRepCode' => 'RL',
			'QuantityOrdered' => 'long'
			));		
	}
	
	/*
	 * Build the SOAP XML template node for an Invoice
	 *	 -- Order
     *         <HeaderTemplateCode>string</HeaderTemplateCode>
	 */
	private function templateNode() {
		return array('Order' => array('HeaderTemplateCode' => 'string'));
	}
	
	/*
	 * Build the SOAP XML price node for an Invoice
	 *	 -- Order
     *         <TotalSellPrice>decimal</TotalSellPrice>
	 */
	private function priceNode() {
		return array('Order' => array('TotalSellPrice' => 'decimal'));
	}
	
	/*
	 * Build the SOAP XML component wrapper node (a loop)
	 *	 -- Order
	 *		<Components>
	 *		...componentNode
	 *		</Components>
	 */
	private function componentsWrapper() {
		$components = array(
			'Order' => array(
				'Components' => array(
					'Component' => array())));
		$index = 0;
		foreach($this->Invoice->invoiceTotals as $id => $total){
			array_push($components['Order']['Components']['Component'], $this->componentNode($index++, $id));
		}
		
		return $components;
	}
	
	/*
	 * Build the SOAP XML component node (a single charge group)
	 *	 -- Order
	 *		-- Components
     *           <Component>
     *             <ComponentNumber>int</ComponentNumber> //incrementing index
     *             <Description>string</Description> 
     *             <QuantityOrdered>1</QuantityOrdered>
     *             <TemplateCode>string</TemplateCode> //may not need
     *             <SellPrice>decimal</SellPrice>
     *             <UserDefinedField1>1</UserDefinedField1>
     *           </Component>
	 */
	private function componentNode($index, $id) {
		$description = $id === 'general' ? ucfirst($id) . ' Charges' : 'Charges for Order ' . $this->Invoice->labelList[$id];
		
		return array(
			'ComponentNumber' => $index,
			'Description' => $description,
			'QuantityOrdered' => '1',
			'TemplateCode' => 'string',
			'SellPrice' => $this->Invoice->invoiceTotals[$id],
			'UserDefinedField1' => '1'			
		);
	}
	
	/*
	 * Build the SOAP XML shipping wrapper node (a loop) - use uncertain
	 *	 -- Order
	 *		<Shipments>
	 *		...shipmentNode
	 *		</Shipments>
	 */
	private function shippingWrapper() {
		$components = array(
			'Order' => array(
				'Shipments' => array(
					'Shipment' => array())));
		array_push($components['Order']['Shipments']['Shipment'], $this->shippingNode(0));
		array_push($components['Order']['Shipments']['Shipment'], $this->shippingNode(1));
		array_push($components['Order']['Shipments']['Shipment'], $this->shippingNode(2));	
		
		return $components;
	}
	
	/*
	 * Build the SOAP XML shipping wrapper node (a loop) - use uncertain
	 *	 -- Order
	 *		-- Shipments
     *          <Shipment>
     *            <JobNumber>string</JobNumber>
     *            <ShipName>string</ShipName>
     *            <ShipAddress1>string</ShipAddress1>
     *            <ShipCity>string</ShipCity>
     *            <ShipState>string</ShipState>
     *            <ShipZip>string</ShipZip>
     *            <ShipVia>string</ShipVia>
     *            <ShipViaService>string</ShipViaService>
     *            <Packages xsi:nil="true" />
     *          </Shipment>
	 */
	private function shippingNode() {
		return array(
			'JobNumber' => 'string',
			'ShipName' => 'string',
			'ShipAddress1' => 'string',
			'ShipCity' => 'string',
			'ShipState' => 'string',
			'ShipZip' => 'string',
			'ShipVia' => 'string',
			'ShipViaService' => 'string',
			'Packages' => array('@xsi:nil' => 'true')			
		);
	}
	


}
