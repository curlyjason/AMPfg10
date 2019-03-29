<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-03-12
 * Time: 20:53
 */

trait CatalogSaveTrait
{


    /**
     * The universal catalog save function
     *
     * Takes basic user import and builds out all automated parts of the
     * catalog and item records
     *
     * Array required (Image element is optional):
     *  array(
    'Catalog' => array(
    'parent_id' => '51/95e894db3306532762674f0cf1f05c5ff508df81',
    'id' => '',
     *          'sequence' => '9.5'
    'name' => 'Yeah',
    'type' => '4',
    'active' => '1',
    'kit_prefs' => '128',
    'can_order_components' => '0',
    'item_id' => '',
    'item_code' => 'let's',
    'customer_item_code' => 'try',
    'description' => 'this',
    'sell_unit' => 'ea',
    'sell_quantity' => '1',
    'price' => '0.00',
    'max_quantity' => ''
    ),
    'Item' => array(
    'source' => '0',
    'id' => '',
    'po_unit' => 'ea',
    'po_quantity' => '1',
    'cost' => '0.00',
    'vendor_id' => '48',
    'po_item_code' => '',
    'reorder_level' => '1',
    'reorder_qty' => '0',
    'quantity' => '0'
    ),
    'Image' => array(
    'img_file' => array(
    'name' => 'HeadShot.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '/private/var/tmp/phpUO8yXJ',
    'error' => (int) 0,
    'size' => (int) 1065571
    )
    )
    )
     *
     * @param $data the save array
     * @return bool
     */
    public function universalSave($data)
    {
        //setup data for saving
        $data = $this->assembleSaveArray($data);

        $this->Catalog->create();

        if ($this->Catalog->saveAll($data)) {
            $itemId = $this->Catalog->Item->id;
            //new items need their uncommitted values initialized
            if ($itemId) {
                $this->Catalog->Item->manageUncommitted($itemId);
                $this->Catalog->Item->managePendingQty($itemId);
            }
            if ($this->Catalog->refreshPermissions) {
                $this->setNodeAccess($this->Auth->user('id'));
            }

            // Image.img_file = '' means no file upload
            // **************************************** SUGGESTED REFACTOR ******************
            // $this->Catalog->Item->Image->saveFromPost($data)
            // **************************************** SUGGESTED REFACTOR ******************
            if (isset($data['Image']) && $data['Image']['img_file'] != '') {
                $this->Catalog->Item->Image->deleteExistingImage($itemId);
                //redo name
                if(isset($this->Catalog->Item->Image->ext[$data['Image']['img_file']['type']])){
                    $name = substr($this->secureHash($data['Image']['img_file']['name']), 0, 10);
                    $imageExt = $this->Catalog->Item->Image->ext[$data['Image']['img_file']['type']];
                    $saveName = $name . '.' . $imageExt;

                    $data['Image']['img_file']['name'] = $saveName;
                    $data['Image']['item_id'] = $itemId;
                    $this->Catalog->Item->Image->create();
                    $this->Catalog->Item->Image->save($data);
                }
            }
            return $itemId;
        } else {
            return false;
        }

    }

    /**
     * Assemble save array for ajaxEdit
     *
     */
    private function assembleSaveArray($data) {

        // set the customer_user_id
        if ($data['Catalog']['parent_id'] != '') {
            $a = explode('/', $data['Catalog']['parent_id']);
            $this->Catalog->id = $a[0];
            $cust_u_id = $this->Catalog->read('customer_user_id');
            $data['Catalog']['customer_user_id'] = $cust_u_id['Catalog']['customer_user_id'];
        }

        //creating new record, with no item
        if(empty($data['Item']['id']) && $data['Catalog']['id'] == ''){
            //if this is a new record, update the item with the data from the catalog
            $item = array_merge($data['Item'], $data['Catalog']);
            $item['id'] = '';
            $data['Item'] = $item;
        } else if(isset($data['Item']) && $this->Catalog->Item->field('catalog_count', array('Item.id' => $data['Item']['id'])) == 1){
            //if there is only 1 catalog connected to the item, update the item with data from the catalog
            $item = array_merge($data['Item'], $data['Catalog']);
            $item['id'] = $data['Item']['id'];
            $data['Item'] = $item;
        }

        // HACK HACK HACK
        // this was my big hammer solution to wrongly creating new items when existing was intended
        // it may defeat the elseif just above
        if (isset($data['Catalog']['item_id']) && $data['Catalog']['item_id'] > 0) {
            unset($data['Item']);
        }

        //check if this catalog record is a folder
        if($data['Catalog']['type'] & FOLDER){
            unset($data['Item']);
            unset($data['Image']);
        }

        //check if this catalog record is a KIT
        if($data['Catalog']['type'] & KIT) {
            $type = KIT + $data['Catalog']['kit_prefs'];
            if($data['Catalog']['can_order_components']){
                $type = ($type + ORDER_COMPONENT);
            }
            $data['Catalog']['type'] = $type;
        }

        //check if this catalog record is a component
        if($data['Catalog']['type'] & COMPONENT){
            $data['Catalog']['type'] = COMPONENT;
            if($data['Catalog']['can_order_components']){
                $data['Catalog']['type'] = (COMPONENT + ORDER_COMPONENT);
            }
        }

        //setup item data for saving
        if (!$data['Catalog']['type'] & FOLDER) {
            $data['Item']['name'] = $data['Catalog']['name'];
            $data['Item']['description'] = $data['Catalog']['description'];
            //if this is a new item, set available quantity and pending quantity
            if (empty($data['Item']['id'])) {
                $data['Item']['available_qty'] = $data['Item']['quantity'];
            }
        }
        return $data;
    }


}