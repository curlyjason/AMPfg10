<?php
/**
 * Created by PhpStorm.
 * User: jasont
 * Date: 2019-02-06
 * Time: 11:44
 */

App::uses('Catalog', 'Model');
App::uses('RobotStatusPacket', 'Lib');

class JSONStatus extends RobotStatusPacket
{

    /**
     * Create order object
     *
     * Order array structure:
        {
        "billing_company":"Sad New Vistas in Testing",
        "first_name":"Jason",
        "last_name":"Tempestini",
        "phone":"925-895-4468",
        "billing_address":"1107 Fountain Street",
        "billing_address2":"",
        "billing_city":"Alameda",
        "billing_state":"CA",
        "billing_zip":"94501",
        "billing_country":"US",
        "order_reference":"order1",
        "note":"This is a note for this shipment. It really could be quite a long note.\n It might even have carriage returns.",
        "OrderItem":
        [
        {
        "catalog_id":"52",
        "customer_item_code":"",
        "name":"Eucalyptus",
        "quantity":"1"
        },
        {
        "catalog_id":"",
        "customer_item_code":"bc1",
        "name":"Ball Cap",
        "quantity":"1"
        },
        {
        "catalog_id":"100",
        "customer_item_code":"",
        "name":"Bag o Rocks",
        "quantity":"5"
        }
        ],
        "Shipment":
        {
        "billing":"Sender",
        "carrier":"UPS",
        "method":"1DA",
        "billing_account":"",
        "first_name":"Jason",
        "last_name":"Tempestini",
        "email":"jason@tempestinis.com",
        "phone":"925-895-4468",
        "company":"Curly Media",
        "address":"1107 Fountain Street",
        "address2":"",
        "city":"Alameda",
        "state":"CA",
        "zip":"94501",
        "country":"US",
        "tpb_company":"",
        "tpb_address":"",
        "tpb_city":"",
        "tpb_state":"",
        "tpb_zip":"",
        "tpb_phone":""
        }
        },
     *
     * JSONOrder constructor.
     * @param $order
     * @param $RobotCredential object
     */


    //Marshalling functions

}