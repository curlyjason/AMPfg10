<?php
App::uses('UsersController', 'Controller');

/**
 * UsersController Test Case
 */
class UsersControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
//		'app.user',
//		'app.customer',
//		'app.address',
//		'app.tax_rate',
//		'app.image',
//		'app.catalog',
//		'app.item',
//		'app.order_item',
//		'app.order',
//		'app.budget',
//		'app.shipment',
//		'app.invoice_item',
//		'app.invoice',
//		'app.document',
//		'app.replenishment_item',
//		'app.replenishment',
//		'app.location',
//		'app.cart',
//		'app.catalogs_user',
//		'app.price',
//		'app.time',
//		'app.project',
//		'app.observer',
//		'app.users_user',
//		'app.menu',
//		'app.user_registry',
//		'app.preference',
//		'app.gateway'
	);

    /**
     * testSetLock method [AppController]
     *
     * @return void
     */
    public function testSetLock() {
        $controller = new UsersController();
//        $controller->User = $this->createMock(\App\Model\Table\UsersTable::class);
//        $controller->Auth = $this->createMock(AuthComponent::class);
//        $controller->Session = $this->createMock(SessionComponent::class);
//        $controller->Session->expects($this->exactly(3))
//            ->method('write');
        $result = $controller->setLock($controller->User, 3);
        $this->assertFalse($result , 'message: ' . "\$this->exactly(3)");
//       debug($this->exactly(3));die;

//        $this->markTestIncomplete('testValidateSelectTakingOverTheWorld not implemented.');
//        $mock = $this->getMockBuilder(stdClass::class)
//            ->setMethods(['set'])
//            ->getMock();
//
//        $mock->expects($this->exactly(2))
//            ->method('set')
//            ->withConsecutive(
//                [$this->equalTo('foo'), $this->greaterThan(0)],
//                [$this->equalTo('bar'), $this->greaterThan(0)]
//            );
//
//        $mock->set('foo', 21);
//        $mock->set('bar', 48);
    }

    /**
 * testLogin method
 *
 * @return void
 */
	public function testLogin() {
	    $this->assertFalse(true);
//		$this->markTestIncomplete('testLogin not implemented.');
	}

///**
// * testIsAuthorized method
// *
// * @return void
// */
//	public function testIsAuthorized() {
//		$this->markTestIncomplete('testIsAuthorized not implemented.');
//	}
//
///**
// * testLogout method
// *
// * @return void
// */
//	public function testLogout() {
//		$this->markTestIncomplete('testLogout not implemented.');
//	}
//
///**
// * testTimeout method
// *
// * @return void
// */
//	public function testTimeout() {
//		$this->markTestIncomplete('testTimeout not implemented.');
//	}
//
///**
// * testInitUser method
// *
// * @return void
// */
//	public function testInitUser() {
//		$this->markTestIncomplete('testInitUser not implemented.');
//	}
//
///**
// * testIndex method
// *
// * @return void
// */
//	public function testIndex() {
//		$this->markTestIncomplete('testIndex not implemented.');
//	}
//
///**
// * testView method
// *
// * @return void
// */
//	public function testView() {
//		$this->markTestIncomplete('testView not implemented.');
//	}
//
///**
// * testAjaxEdit method
// *
// * @return void
// */
//	public function testAjaxEdit() {
//		$this->markTestIncomplete('testAjaxEdit not implemented.');
//	}
//
///**
// * testAdd method
// *
// * @return void
// */
//	public function testAdd() {
//		$this->markTestIncomplete('testAdd not implemented.');
//	}
//
///**
// * testEdit method
// *
// * @return void
// */
//	public function testEdit() {
//		$this->markTestIncomplete('testEdit not implemented.');
//	}
//
///**
// * testDelete method
// *
// * @return void
// */
//	public function testDelete() {
//		$this->markTestIncomplete('testDelete not implemented.');
//	}
//
///**
// * testFetchRecordForEdit method
// *
// * @return void
// */
//	public function testFetchRecordForEdit() {
//		$this->markTestIncomplete('testFetchRecordForEdit not implemented.');
//	}
//
///**
// * testFetchVariablesForEdit method
// *
// * @return void
// */
//	public function testFetchVariablesForEdit() {
//		$this->markTestIncomplete('testFetchVariablesForEdit not implemented.');
//	}
//
///**
// * testEditUser method
// *
// * @return void
// */
//	public function testEditUser() {
//		$this->markTestIncomplete('testEditUser not implemented.');
//	}
//
///**
// * testAddUserWatcherCounts method
// *
// * @return void
// */
//	public function testAddUserWatcherCounts() {
//		$this->markTestIncomplete('testAddUserWatcherCounts not implemented.');
//	}
//
///**
// * testEditUserGrain method
// *
// * @return void
// */
//	public function testEditUserGrain() {
//		$this->markTestIncomplete('testEditUserGrain not implemented.');
//	}
//
///**
// * testFetchTreeCompliantArray method
// *
// * @return void
// */
//	public function testFetchTreeCompliantArray() {
//		$this->markTestIncomplete('testFetchTreeCompliantArray not implemented.');
//	}
//
///**
// * testEditCatalog method
// *
// * @return void
// */
//	public function testEditCatalog() {
//		$this->markTestIncomplete('testEditCatalog not implemented.');
//	}
//
///**
// * testEditCatalogGrain method
// *
// * @return void
// */
//	public function testEditCatalogGrain() {
//		$this->markTestIncomplete('testEditCatalogGrain not implemented.');
//	}
//
///**
// * testPrepareCatalogSidebar method
// *
// * @return void
// */
//	public function testPrepareCatalogSidebar() {
//		$this->markTestIncomplete('testPrepareCatalogSidebar not implemented.');
//	}
//
///**
// * testEditTree method
// *
// * @return void
// */
//	public function testEditTree() {
//		$this->markTestIncomplete('testEditTree not implemented.');
//	}
//
///**
// * testEditToChild method
// *
// * @return void
// */
//	public function testEditToChild() {
//		$this->markTestIncomplete('testEditToChild not implemented.');
//	}
//
///**
// * testEditNewChild method
// *
// * @return void
// */
//	public function testEditNewChild() {
//		$this->markTestIncomplete('testEditNewChild not implemented.');
//	}
//
///**
// * testEditNewSibling method
// *
// * @return void
// */
//	public function testEditNewSibling() {
//		$this->markTestIncomplete('testEditNewSibling not implemented.');
//	}
//
///**
// * testEditRenderEditForm method
// *
// * @return void
// */
//	public function testEditRenderEditForm() {
//		$this->markTestIncomplete('testEditRenderEditForm not implemented.');
//	}
//
///**
// * testEditSaveEditForm method
// *
// * @return void
// */
//	public function testEditSaveEditForm() {
//		$this->markTestIncomplete('testEditSaveEditForm not implemented.');
//	}
//
///**
// * testEditDeactivate method
// *
// * @return void
// */
//	public function testEditDeactivate() {
//		$this->markTestIncomplete('testEditDeactivate not implemented.');
//	}
//
///**
// * testForgotPassword method
// *
// * @return void
// */
//	public function testForgotPassword() {
//		$this->markTestIncomplete('testForgotPassword not implemented.');
//	}
//
///**
// * testSendRegisterEmail method
// *
// * @return void
// */
//	public function testSendRegisterEmail() {
//		$this->markTestIncomplete('testSendRegisterEmail not implemented.');
//	}
//
///**
// * testInitNewUserPass method
// *
// * @return void
// */
//	public function testInitNewUserPass() {
//		$this->markTestIncomplete('testInitNewUserPass not implemented.');
//	}
//
///**
// * testRegistration method
// *
// * @return void
// */
//	public function testRegistration() {
//		$this->markTestIncomplete('testRegistration not implemented.');
//	}
//
///**
// * testResetPassword method
// *
// * @return void
// */
//	public function testResetPassword() {
//		$this->markTestIncomplete('testResetPassword not implemented.');
//	}
//
///**
// * testUserEdit method
// *
// * @return void
// */
//	public function testUserEdit() {
//		$this->markTestIncomplete('testUserEdit not implemented.');
//	}
//
///**
// * testAddressEdit method
// *
// * @return void
// */
//	public function testAddressEdit() {
//		$this->markTestIncomplete('testAddressEdit not implemented.');
//	}
//
///**
// * testAddressAdd method
// *
// * @return void
// */
//	public function testAddressAdd() {
//		$this->markTestIncomplete('testAddressAdd not implemented.');
//	}
//
///**
// * testObserverEdit method
// *
// * @return void
// */
//	public function testObserverEdit() {
//		$this->markTestIncomplete('testObserverEdit not implemented.');
//	}
//
///**
// * testUserObserverEdit method
// *
// * @return void
// */
//	public function testUserObserverEdit() {
//		$this->markTestIncomplete('testUserObserverEdit not implemented.');
//	}
//
///**
// * testObserverAdd method
// *
// * @return void
// */
//	public function testObserverAdd() {
//		$this->markTestIncomplete('testObserverAdd not implemented.');
//	}
//
///**
// * testUserObserverAdd method
// *
// * @return void
// */
//	public function testUserObserverAdd() {
//		$this->markTestIncomplete('testUserObserverAdd not implemented.');
//	}
//
///**
// * testUserPermissionEdit method
// *
// * @return void
// */
//	public function testUserPermissionEdit() {
//		$this->markTestIncomplete('testUserPermissionEdit not implemented.');
//	}
//
///**
// * testCatalogPermissionEdit method
// *
// * @return void
// */
//	public function testCatalogPermissionEdit() {
//		$this->markTestIncomplete('testCatalogPermissionEdit not implemented.');
//	}
//
///**
// * testFetchOrphans method
// *
// * @return void
// */
//	public function testFetchOrphans() {
//		$this->markTestIncomplete('testFetchOrphans not implemented.');
//	}
//
///**
// * testInactive method
// *
// * @return void
// */
//	public function testInactive() {
//		$this->markTestIncomplete('testInactive not implemented.');
//	}
//
///**
// * testSetActive method
// *
// * @return void
// */
//	public function testSetActive() {
//		$this->markTestIncomplete('testSetActive not implemented.');
//	}
//
///**
// * testSecureRequestData method
// *
// * @return void
// */
//	public function testSecureRequestData() {
//		$this->markTestIncomplete('testSecureRequestData not implemented.');
//	}
//
///**
// * testValidateRequestData method
// *
// * @return void
// */
//	public function testValidateRequestData() {
//		$this->markTestIncomplete('testValidateRequestData not implemented.');
//	}
//
///**
// * testSecureSelectTakingOverTheWorld method
// *
// * @return void
// */
//	public function testSecureSelectTakingOverTheWorld() {
//		$this->markTestIncomplete('testSecureSelectTakingOverTheWorld not implemented.');
//	}
//
///**
// * testValidateSelectTakingOverTheWorld method
// *
// * @return void
// */
//	public function testValidateSelectTakingOverTheWorld() {
//		$this->markTestIncomplete('testValidateSelectTakingOverTheWorld not implemented.');
//	}

}
