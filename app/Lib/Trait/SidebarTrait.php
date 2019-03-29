<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-03-05
 * Time: 20:53
 */
app::uses('User', 'Model');


Trait SidebarTrait
{
    /**
     * Prepare all Catalog tree sidebar data and send it to the view
     *
     * @todo How about css and scripts? can we send vars that will let those load too?
     */
    public function prepareCatalogSidebar() {

        $this->loadModel('User');
        $conditions = array('Catalog.active' => 1/* , 'Catalog.folder' => 1 */);
        $flatNodes = $this->User->getAccessibleCatalogNodes($this->Auth->user('CatalogRoots'), $conditions);
        $this->passRootNodes('Catalog');
        $this->set('tree', $this->User->Catalog->nodeGroups($flatNodes));
    }

    public function passRootNodes($type) {
        $this->set('rootNodes', $this->Auth->user($type . 'Roots'));
    }

}