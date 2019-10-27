<?php
App::uses('UsersController', 'Controller');
App::uses('User', 'Model');
App::uses('SessionComponent', 'Controller/Component');
App::uses('AuthComponent', 'Controller/Component');
App::uses('CakeEventManager', 'Event');

/**
 * UsersController Test Case
 */
class UsersControllerTest extends ControllerTestCase
{

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
    public function testSetLockSuccessfulSave()
    {
        $controller = new UsersController();
        $controller->components = ['Auth'];
        $controller->constructClasses();
        $controller->User = $this->getMockForModel('User');
        $controller->Session = $this->createMock('SessionComponent');

        $controller->User->method('save')
            ->willReturn(true);
        $controller->Auth->login($this->userArray());

        $controller->Session->expects($this->exactly(3))
            ->method('write');

        $result = $controller->setLock($controller->User, 3);
        $this->assertTrue($result, 'setLock failed to return true on successful data save');
    }

    public function testSetLockFailedSave()
    {
        $controller = new UsersController();
        $controller->components = ['Auth'];
        $controller->constructClasses();
        $controller->User = $this->getMockForModel('User');
        $controller->Session = $this->createMock('SessionComponent');

        $controller->User->method('save')
            ->willReturn(false);
        $controller->Auth->login($this->userArray());

        $controller->Session->expects($this->exactly(0))
            ->method('write');

        $result = $controller->setLock($controller->User, 3);
        $this->assertFalse($result, 'setLock returned true on failed data save');
    }

    protected function _loadController($settings = array()) {
        $request = new CakeRequest($this->url);
        $request->addParams(Router::parse($this->url));
        $this->Controller = new Controller($request);
        $this->Controller->uses = null;
        $this->Controller->components = array('Toolbar' => $settings + array('className' => 'TestToolbar'));
        $this->Controller->constructClasses();
        $this->Controller->Components->trigger('initialize', array($this->Controller));
        return $this->Controller;
    }


    /**
     * testLogin method
     *
     * @return void
     */
    public function testLogin()
    {
//	    $this->assertFalse(true);
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

    protected function userArray()
    {
    return [
            'id' => '3',
            'email' => NULL,
            'first_name' => 'Don',
            'last_name' => 'Drake',
            'active' => '1',
            'username' => 'ddrake@dreamingmind.com',
            'role' => 'Admins Manager',
            'created' => '2013-12-05 10:17:29',
            'modified' => '2019-10-25 22:26:10',
            'parent_id' => '11',
            'ancestor_list' => ',1,11,',
            'lock' => '0',
            'sequence' => '3',
            'folder' => false,
            'session_change' => false,
            'verified' => false,
            'logged_in' => '0',
            'cart_session' => NULL,
            'use_budget' => true,
            'budget' => '1500',
            'use_item_budget' => true,
            'item_budget' => '15',
            'rollover_item_budget' => false,
            'rollover_budget' => false,
            'use_item_limit_budget' => true,
            'name' => 'Don Drake',
            'ParentUser' => [
                'id' => '11',
                'email' => NULL,
                'password' => 'e05cec3b7353d9ae530c8423101a6ea241b5dbbd',
                'first_name' => '',
                'last_name' => '',
                'active' => '1',
                'username' => 'Developer Staff',
                'role' => 'Admins Manager',
                'created' => '2014-01-27 14:50:22',
                'modified' => '2015-04-13 10:43:07',
                'parent_id' => '1',
                'ancestor_list' => ',1,',
                'lock' => '0',
                'sequence' => '5',
                'folder' => true,
                'session_change' => false,
                'verified' => false,
                'logged_in' => '0',
                'cart_session' => NULL,
                'use_budget' => false,
                'budget' => NULL,
                'use_item_budget' => false,
                'item_budget' => NULL,
                'rollover_item_budget' => false,
                'rollover_budget' => false,
                'use_item_limit_budget' => false,
                'name' => ' ',
                ],
            'Customer' => [
                'id' => NULL,
                'user_id' => NULL,
                'created' => NULL,
                'modified' => NULL,
                'customer_code' => NULL,
                'order_contact' => NULL,
                'billing_contact' => NULL,
                'allow_backorder' => NULL,
                'allow_direct_pay' => NULL,
                'address_id' => NULL,
                'release_hold' => NULL,
                'taxable' => NULL,
                'rent_qty' => NULL,
                'rent_unit' => NULL,
                'rent_price' => NULL,
                'item_pull_charge' => NULL,
                'order_pull_charge' => NULL,
                'token' => NULL,
                'customer_type' => NULL,
                'image_id' => NULL,
                'name' => 'ddrake@dreamingmind.com',
                'role' => 'Admins Manager',
                ],
            'group' => 'Admins',
            'access' => 'Manager',
            'CatalogRoots' => [
                1 => [
                    'id' => '1',
                    'created' => NULL,
                    'modified' => '2019-04-02 07:43:30',
                    'item_id' => NULL,
                    'name' => 'root',
                    'parent_id' => '-1',
                    'ancestor_list' => ',',
                    'item_count' => NULL,
                    'lock' => '0',
                    'sequence' => '1',
                    'active' => '1',
                    'customer_id' => NULL,
                    'customer_user_id' => NULL,
                    'sell_quantity' => NULL,
                    'sell_unit' => NULL,
                    'max_quantity' => NULL,
                    'price' => NULL,
                    'description' => NULL,
                    'type' => '2',
                    'item_code' => '',
                    'customer_item_code' => NULL,
                    'comment' => NULL,
                    'folder' => '2',
                    'kit' => '0',
                    'product_test' => '0',
                    'CatalogsUser' => [
                        'id' => '6',
                        'created' => NULL,
                        'modified' => '0000-00-00 00:00:00',
                        'catalog_id' => '1',
                        'user_id' => '3',
                        ],
                    ],
                ],
            'UserRoots' => [
                1 => [
                    'id' => '1',
                    'email' => NULL,
                    'password' => 'xx',
                    'first_name' => NULL,
                    'last_name' => NULL,
                    'active' => '1',
                    'username' => 'root',
                    'role' => 'Clients Guest',
                    'created' => NULL,
                    'modified' => '2015-04-13 13:40:04',
                    'parent_id' => '-1',
                    'ancestor_list' => ',',
                    'lock' => '0',
                    'sequence' => '1',
                    'folder' => true,
                    'session_change' => false,
                    'verified' => false,
                    'logged_in' => '0',
                    'cart_session' => NULL,
                    'use_budget' => false,
                    'budget' => NULL,
                    'use_item_budget' => false,
                    'item_budget' => NULL,
                    'rollover_item_budget' => false,
                    'rollover_budget' => false,
                    'use_item_limit_budget' => false,
                    'name' => NULL,
                    'UsersUser' => [
                        'id' => '2',
                        'created' => NULL,
                        'modified' => NULL,
                        'user_managed_id' => '1',
                        'user_manager_id' => '3',
                        ],
                    ],
                ],
            'edit' => array(
                'mode' => false,
                'model' => NULL,
                'id' => NULL,
                ),
            'budget_id' => '27',
            ];
    }
}
