<?php


trait ControllerTrait
{

    /**
     * Make a Controller with Auth and a logged in user
     *
     * @param $controllerName Short name of the contoller to make
     * @return Controller
     */
    protected function makeController($controllerName, $userType = null)
    {
        $className = "{$controllerName}Controller";
        App::uses($className, 'Controller');

        $controller = new $className();
        $controller->components = ['Auth'];
        $controller->constructClasses();

        $controller->Auth->login($this->userArray());

        return $controller;
    }

    /**
     * @return array logged in user 'ddrake@dreamoingmind.com'
     */
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