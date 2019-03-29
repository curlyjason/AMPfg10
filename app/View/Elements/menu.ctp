<?php

$group = $this->Session->read('Auth.User.group');
$access = $this->Session->read('Auth.User.access');
//$check = new checkMenu();
echo '<div id="MM"><ul id="MainMenu" class="unsnap" bind="snap.scrollSnap" offset="0">';
foreach ($menuItems as $index => $node) {
//    debug($node['Menu']['group']);
    $itemGroup = $node['Menu']['group'];
    $itemAccess = $node['Menu']['access'];
    $itemController = $node['Menu']['controller'];
    if ($this->Html->checkMenuAccess($itemGroup, $itemAccess, $group, $access)) {
        //populate missing links
        $node['Menu']['controller'] = ($itemController != '') ? $itemController : $this->Html->populateController($group, $this->request->controller, $node['Menu']['action']);
//        debug($this->Html->populateController($group, $this->request->controller, $itemAccess));
        echo '<li>';
        echo $this->Html->link($node['Menu']['name'], array(
            'controller' => $node['Menu']['controller'],
            'action' => $node['Menu']['action']
        ));
        echo '<ul>';
        foreach ($node['ChildMenu'] as $index => $subnode) {
            if ($this->Html->checkMenuAccess($subnode['group'], $subnode['access'], $group, $access)) {
                //populate missing links
                $subnode['controller'] = ($subnode['controller'] != '') ? $subnode['controller'] : $this->Html->populateController($group, $this->request->controller, $subnode['action']);
                $link = $this->Html->link($subnode['name'], array(
                    'controller' => $subnode['controller'],
                    'action' => $subnode['action']
                ));
                echo $this->Html->tag('li', $link);
            }
        }
        echo '</ul> </li>';
    }
}
$cartCount = count($this->Session->read('Shop.OrderItem'));
$cartCountWrapper = '(<span id="cartCount">' . $cartCount . '</span>)';
    echo $this->Html->tag('li', $this->FgHtml->link("Cart $cartCountWrapper", array(
        'controller' => 'shop', 'action' => 'cart'), array(
//        'id' => 'cartbutton',
        'escape' => false)),
            array('id' => 'cartbutton')
    );
echo '</ul></div>';

////$this->Js->buffer('$("#MainMenu").menu();');
////echo $this->Js->writeBuffer();
//class checkMenu {
//
//    function checkMenuAccess($itemGroup, $itemAccess, $group, $access) {
//        //setup group and access as local variables
//        //Allow all for Admins Manager
////		debug(func_get_args());
//        if ($group == 'Admins') {
////			debug("$group == 'Admins'");
//            return true;
//        }
//		//Restrict access to admin level menus
//		if ($group != 'Admins' && $itemGroup == 'Admins'){
////			debug("$group != 'Admins' && $itemGroup == 'Admins'");
//			return false;
//		}
//		//Restrict warehouse menus
//		if($itemGroup == 'Warehouses' && $group != 'Warehouses'){
////			debug("$itemGroup == 'Warehouses' && $group != 'Warehouses'");
//			return false;
//		}
//		//Allow Manager Access
//		if($access == 'Manager' && $group == 'Staff'){
////			debug("$access == 'Manager'");
//			return true;
//		}
//        //Access for Buyer either client or staff
//        if ($access == 'Buyer' && $itemAccess != 'Manager') {
////			debug("$access == 'Buyer' && $itemAccess != 'Manager'");
//            return true;
//        }
//        //Access for Guest either client or staff
//        if ($access == 'Guest' && $itemAccess == 'Guest') {
////			debug("$access == 'Guest' && $itemAccess == 'Guest'");
//            return true;
//        }
//        //Access for Warehouse
//        if (($group == 'Warehouses' && $itemAccess == 'Guest') || ($group == 'Warehouses' && $itemGroup == 'Warehouses')) {
////			debug("($group == 'Warehouses' && $itemAccess == 'Guest') || ($group == 'Warehouses' && $itemGroup == 'Warehouses')");
//            return true;
//        }
//        //Access for Client Managers
//        if ($access == 'Manager' && $group == 'Clients' && $itemGroup == 'Clients') {
////			debug("$access == 'Manager' && $group == 'Clients' && $itemGroup == 'Clients'");
//            return true;
//        }
//        //Default
//        return false;
//    }
//
//    function populateController($group, $controller, $itemAccess) {
////        debug(func_get_args());
//        if ($itemAccess == 'status') {
//            return strtolower($group);
//        }
//        if ($itemAccess == '#') {
//            return $controller;
//        }
//    }
//
//}

?>