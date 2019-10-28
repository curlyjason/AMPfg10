<?php
App::uses('InvoicesController', 'Controller');
App::uses('Invoice', 'Model');
App::uses('ControllerTrait', 'Test/Case');

/**
 * InvoicesController Test Case
 */
class InvoicesControllerTest extends ControllerTestCase {

    use ControllerTrait;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
//		'app.invoice',
//		'app.invoice_item',
//		'app.order',
//		'app.user',
//		'app.customer',
//		'app.address',
//		'app.tax_rate',
//		'app.image',
//		'app.catalog',
//		'app.item',
//		'app.order_item',
//		'app.replenishment_item',
//		'app.replenishment',
//		'app.location',
//		'app.cart',
//		'app.catalogs_user',
//		'app.price',
//		'app.time',
//		'app.project',
//		'app.observer',
//		'app.budget',
//		'app.users_user',
//		'app.shipment',
//		'app.document',
//		'app.menu',
//		'app.user_registry',
//		'app.preference',
//		'app.gateway'
	);

/**
 * testIsAuthorized method
 *
 * @return void
 */
	public function testIsAuthorized() {
		$this->markTestIncomplete('testIsAuthorized not implemented.');
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		$this->markTestIncomplete('testIndex not implemented.');
	}

/**
 * testView method
 *
 * @return void
 */
	public function testView() {
		$this->markTestIncomplete('testView not implemented.');
	}

/**
 * testAdd method
 *
 * @return void
 */
	public function testAdd() {
		$this->markTestIncomplete('testAdd not implemented.');
	}

/**
 * testEdit method
 *
 * @return void
 */
	public function testEdit() {
		$this->markTestIncomplete('testEdit not implemented.');
	}

/**
 * testDelete method
 *
 * @return void
 */
	public function testDelete() {
		$this->markTestIncomplete('testDelete not implemented.');
	}

/**
 * testListInvoices method
 *
 * @return void
 */
	public function testListInvoices() {
		$this->markTestIncomplete('testListInvoices not implemented.');
	}

/**
 * testInvoice method
 *
 * @return void
 */
	public function testInvoice() {
		$this->markTestIncomplete('testInvoice not implemented.');
	}

/**
 * testSubmitInvoice method
 *
 * @return void
 */
    public function testSubmitInvoice() {
        $this->markTestIncomplete('testSubmitInvoice not implemented.');
    }

    /**
     * testViewOldInvoice method
     *
     * @return void
     */
    public function testViewOldInvoice() {
        $this->markTestIncomplete('testSubmitInvoice not implemented.');
    }

    /**
     * Don't know how to resolve rendering/event problems
     */
	public function testSaveInvoiceNumberSuccess() {
        /** @var InvoicesController $controller */
//	    $controller = $this->makeController('Invoices');
//	    $controller->Invoice = $this->getMockForModel('Invoice');
//        $controller->Invoice
//            ->method('save')
//            ->willReturn(true);
//
//	    $result = $controller->saveInvoiceNumber('1', 'ABC123');
//	    $expected = json_encode(['response' => TRUE]);
//
//		$this->assertEquals($result == $expected, 'A successful save did not return the expected json');
	}

/**
 * testSaveInvoiceNumber method
 *
 * @return void
 */
	public function testSaveInvoiceNumberFail() {
		$this->markTestIncomplete('testSaveInvoiceNumber not implemented.');
	}

}
